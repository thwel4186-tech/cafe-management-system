<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CafeTable;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CafeController extends Controller
{
    private function admin(Request $request): void
    {
        abort_unless($request->user()->role === 'admin', 403, 'Admin only.');
    }

    private function loadedOrder(Order $order): Order
    {
        return $order->load(['items', 'table:id,name,seats', 'user:id,name', 'payment']);
    }

    public function dashboard()
    {
        $today = now()->toDateString();
        return response()->json([
            'revenue_today' => (int) Payment::whereDate('paid_at', $today)->sum('amount'),
            'orders_today' => Order::whereDate('created_at', $today)->count(),
            'unpaid_orders' => Order::where('status', '!=', 'cancelled')->whereDoesntHave('payment')->count(),
            'products' => Product::where('is_active', true)->count(),
            'recent_orders' => Order::with(['table:id,name', 'payment'])->latest()->limit(8)->get(),
        ]);
    }

    public function categories() { return Category::orderBy('name')->get(); }

    public function storeCategory(Request $request)
    {
        $this->admin($request);
        $data = $request->validate(['name' => ['required','string','max:100','unique:categories,name']]);
        return response()->json(Category::create($data), 201);
    }

    public function updateCategory(Request $request, Category $category)
    {
        $this->admin($request);
        $data = $request->validate(['name' => ['required','string','max:100', Rule::unique('categories')->ignore($category->id)]]);
        $category->update($data);
        return $category;
    }

    public function deleteCategory(Request $request, Category $category)
    {
        $this->admin($request);
        if ($category->products()->exists()) throw ValidationException::withMessages(['name' => ['Remove category products first.']]);
        $category->delete();
        return response()->noContent();
    }

    public function products()
    {
        return Product::with('category:id,name')->orderBy('name')->get();
    }

    private function productData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'exists:categories,id'],
            'price' => ['required', 'integer', 'min:0', 'max:100000000'],
            'cost' => ['required', 'integer', 'min:0', 'max:100000000'],
            'stock' => ['required', 'integer', 'min:0', 'max:100000000'],
            'track_stock' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    public function storeProduct(Request $request)
    {
        $this->admin($request);
        return response()->json(Product::create($this->productData($request)), 201);
    }

    public function updateProduct(Request $request, Product $product)
    {
        $this->admin($request);
        $product->update($this->productData($request));
        return $product;
    }

    public function deleteProduct(Request $request, Product $product)
    {
        $this->admin($request);
        if ($product->orderItems()->whereHas('order', fn ($q) => $q->whereNotIn('status', ['completed', 'cancelled']))->exists()) {
            throw ValidationException::withMessages(['name' => ['Cannot delete a product belonging to an open order.']]);
        }
        $product->delete();
        return response()->noContent();
    }

    public function tables()
    {
        return CafeTable::withCount(['orders as open_orders_count' => function ($q) {
            $q->whereNotIn('status', ['completed', 'cancelled']);
        }])->orderBy('id')->get();
    }

    public function storeTable(Request $request)
    {
        $this->admin($request);
        $data = $request->validate(['name' => ['required','string','max:80','unique:cafe_tables,name'], 'seats' => ['required','integer','min:1','max:30']]);
        return response()->json(CafeTable::create($data), 201);
    }

    public function updateTable(Request $request, CafeTable $table)
    {
        $this->admin($request);
        $data = $request->validate(['name' => ['required','string','max:80', Rule::unique('cafe_tables')->ignore($table->id)], 'seats' => ['required','integer','min:1','max:30']]);
        $table->update($data);
        return $table;
    }

    public function deleteTable(Request $request, CafeTable $table)
    {
        $this->admin($request);
        if ($table->orders()->exists()) throw ValidationException::withMessages(['name' => ['Table has order history; cannot delete it.']]);
        $table->delete();
        return response()->noContent();
    }

    public function orders(Request $request)
    {
        $data = $request->validate(['status' => ['sometimes', Rule::in(['pending','preparing','served','completed','cancelled'])]]);
        $query = Order::with(['table:id,name', 'user:id,name', 'payment'])->latest();
        if (isset($data['status'])) $query->where('status', $data['status']);
        return $query->limit(150)->get();
    }

    public function order(Order $order) { return $this->loadedOrder($order); }

    public function storeOrder(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['dine_in', 'takeaway'])],
            'cafe_table_id' => ['nullable', 'exists:cafe_tables,id', 'required_if:type,dine_in', 'prohibited_if:type,takeaway'],
            'notes' => ['nullable','string','max:1000'],
            'items' => ['required','array','min:1','max:50'],
            'items.*.product_id' => ['required','integer','exists:products,id','distinct'],
            'items.*.qty' => ['required','integer','min:1','max:100'],
        ]);

        $created = DB::transaction(function () use ($request, $data) {
            $tableId = null;
            if ($data['type'] === 'dine_in') {
                // Lock table to prevent two simultaneous open orders at the same café table.
                $table = CafeTable::whereKey($data['cafe_table_id'])->lockForUpdate()->firstOrFail();
                if ($table->orders()->whereNotIn('status', ['completed', 'cancelled'])->exists()) {
                    throw ValidationException::withMessages(['cafe_table_id' => ['This table already has an open order.']]);
                }
                $tableId = $table->id;
            }

            $items = [];
            $total = 0;
            $requested = collect($data['items'])->sortBy('product_id');
            // Consistent lock order minimizes deadlocks when stock is purchased together.
            foreach ($requested as $line) {
                $product = Product::whereKey($line['product_id'])->lockForUpdate()->firstOrFail();
                if (! $product->is_active) throw ValidationException::withMessages(['items' => ["{$product->name} is unavailable."]]);
                if ($product->track_stock) {
                    if ($product->stock < $line['qty']) throw ValidationException::withMessages(['items' => ["Not enough stock for {$product->name}."]]);
                    $product->decrement('stock', $line['qty']);
                }
                $subtotal = $product->price * $line['qty'];
                $total += $subtotal;
                $items[] = [
                    'product_id' => $product->id, 'product_name' => $product->name, 'qty' => $line['qty'],
                    'unit_price' => $product->price, 'unit_cost' => $product->cost, 'subtotal' => $subtotal,
                ];
            }
            $order = Order::create([
                'order_number' => 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(10)),
                'user_id' => $request->user()->id, 'cafe_table_id' => $tableId,
                'type' => $data['type'], 'status' => 'pending', 'total_amount' => $total,
                'notes' => $data['notes'] ?? null,
            ]);
            $order->items()->createMany($items);
            return $order;
        }, 3);
        return response()->json($this->loadedOrder($created), 201);
    }

    public function changeOrderStatus(Request $request, Order $order)
    {
        $data = $request->validate(['status' => ['required', Rule::in(['preparing', 'served', 'completed', 'cancelled'])]]);
        DB::transaction(function () use ($order, $data) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $next = [
                'pending' => ['preparing', 'cancelled'],
                'preparing' => ['served', 'cancelled'],
                'served' => ['completed', 'cancelled'],
                'completed' => [], 'cancelled' => [],
            ];
            if (! in_array($data['status'], $next[$locked->status] ?? [], true)) {
                throw ValidationException::withMessages(['status' => ['Invalid status transition.']]);
            }
            if ($data['status'] === 'cancelled') {
                if ($locked->payment()->exists()) throw ValidationException::withMessages(['status' => ['Paid orders cannot be cancelled; refunds are not implemented.']]);
                foreach ($locked->items as $item) {
                    if ($item->product_id) {
                        $product = Product::whereKey($item->product_id)->lockForUpdate()->first();
                        if ($product && $product->track_stock) $product->increment('stock', $item->qty);
                    }
                }
            }
            $locked->update(['status' => $data['status']]);
        }, 3);
        return $this->loadedOrder($order->fresh());
    }

    public function payOrder(Request $request, Order $order)
    {
        $data = $request->validate(['method' => ['required', Rule::in(['cash', 'kbzpay', 'wavepay', 'card'])]]);
        DB::transaction(function () use ($request, $order, $data) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($locked->status === 'cancelled') throw ValidationException::withMessages(['order' => ['Cannot pay a cancelled order.']]);
            if ($locked->payment()->exists()) throw ValidationException::withMessages(['order' => ['This order is already paid.']]);
            $locked->payment()->create([
                'user_id' => $request->user()->id, 'method' => $data['method'],
                'amount' => $locked->total_amount, 'paid_at' => now(),
            ]);
        }, 3);
        return $this->loadedOrder($order->fresh());
    }

    public function expenses(Request $request)
    {
        $this->admin($request);
        return Expense::orderByDesc('spent_on')->limit(200)->get();
    }

    public function storeExpense(Request $request)
    {
        $this->admin($request);
        $data = $request->validate([
            'title' => ['required','string','max:150'], 'amount' => ['required','integer','min:1','max:1000000000'],
            'spent_on' => ['required','date_format:Y-m-d'], 'notes' => ['nullable','string','max:1000'],
        ]);
        return response()->json(Expense::create([...$data, 'user_id' => $request->user()->id]), 201);
    }

    public function deleteExpense(Request $request, Expense $expense)
    {
        $this->admin($request);
        $expense->delete();
        return response()->noContent();
    }

    public function reports(Request $request)
    {
        $this->admin($request);
        $data = $request->validate([
            'from' => ['required','date_format:Y-m-d'],
            'to' => ['required','date_format:Y-m-d', 'after_or_equal:from'],
        ]);
        $from = $data['from'] . ' 00:00:00';
        $to = $data['to'] . ' 23:59:59';
        $payments = Payment::whereBetween('paid_at', [$from, $to])->get();
        $revenue = $payments->sum('amount');
        $cost = OrderItem::whereHas('order.payment', fn ($q) => $q->whereBetween('paid_at', [$from, $to]))
            ->get()->sum(fn ($line) => $line->unit_cost * $line->qty);
        $expenses = Expense::whereBetween('spent_on', [$data['from'], $data['to']])->sum('amount');
        $daily = $payments->groupBy(fn ($p) => $p->paid_at->format('Y-m-d'))
            ->map(fn ($group, $date) => ['date' => $date, 'revenue' => (int) $group->sum('amount'), 'payments' => $group->count()])
            ->values()->sortBy('date')->values();
        return response()->json([
            'revenue' => (int) $revenue, 'cost_of_goods' => (int) $cost,
            'expenses' => (int) $expenses, 'estimated_profit' => (int) ($revenue - $cost - $expenses),
            'paid_orders' => $payments->count(), 'daily' => $daily,
        ]);
    }

    public function staff(Request $request)
    {
        $this->admin($request);
        return User::select(['id','name','email','role','is_active','created_at'])->orderBy('name')->get();
    }

    public function storeStaff(Request $request)
    {
        $this->admin($request);
        $data = $request->validate([
            'name' => ['required','string','max:100'],
            'email' => ['required','email','max:200','unique:users,email'],
            'password' => ['required','string','min:10'],
            'role' => ['required', Rule::in(['admin', 'cashier'])],
        ]);
        $user = User::create([...$data, 'password' => Hash::make($data['password']), 'is_active' => true]);
        return response()->json($user->only(['id','name','email','role','is_active']), 201);
    }

    public function updateStaff(Request $request, User $staff)
    {
        $this->admin($request);
        $data = $request->validate([
            'name' => ['required','string','max:100'],
            'email' => ['required','email','max:200',Rule::unique('users')->ignore($staff->id)],
            'password' => ['nullable','string','min:10'],
            'role' => ['required', Rule::in(['admin', 'cashier'])],
            'is_active' => ['required','boolean'],
        ]);
        if ($staff->id === $request->user()->id && ($data['role'] !== 'admin' || ! $data['is_active'])) {
            throw ValidationException::withMessages(['role' => ['You cannot disable or demote your own account.']]);
        }
        if (empty($data['password'])) unset($data['password']);
        else $data['password'] = Hash::make($data['password']);
        $staff->update($data);
        return $staff->only(['id','name','email','role','is_active']);
    }
}
