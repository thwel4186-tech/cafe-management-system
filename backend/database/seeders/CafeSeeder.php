<?php
namespace Database\Seeders;

use App\Models\CafeTable;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CafeSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(['email' => 'admin@cafe.test'], [
            'name' => 'Cafe Admin', 'password' => Hash::make('ChangeMe123!'), 'role' => 'admin', 'is_active' => true,
        ]);
        User::updateOrCreate(['email' => 'cashier@cafe.test'], [
            'name' => 'Cafe Cashier', 'password' => Hash::make('ChangeMe123!'), 'role' => 'cashier', 'is_active' => true,
        ]);
        foreach (['Coffee', 'Tea', 'Snacks', 'Desserts'] as $name) Category::firstOrCreate(['name' => $name]);
        foreach ([
            ['Coffee', 'Espresso', 3500, 1100, false, 0],
            ['Coffee', 'Cappuccino', 5000, 2000, false, 0],
            ['Coffee', 'Latte', 5500, 2200, false, 0],
            ['Tea', 'Milk Tea', 3000, 1000, false, 0],
            ['Tea', 'Lemon Tea', 3000, 800, false, 0],
            ['Snacks', 'Croissant', 4000, 1500, true, 25],
            ['Desserts', 'Chocolate Cake', 6500, 2800, true, 12],
        ] as [$category, $name, $price, $cost, $track, $stock]) {
            Product::firstOrCreate(['name' => $name], [
                'category_id' => Category::where('name', $category)->value('id'),
                'price' => $price, 'cost' => $cost, 'track_stock' => $track, 'stock' => $stock,
            ]);
        }
        for ($i = 1; $i <= 8; $i++) CafeTable::firstOrCreate(['name' => 'Table ' . $i], ['seats' => ($i <= 4 ? 2 : 4)]);
    }
}
