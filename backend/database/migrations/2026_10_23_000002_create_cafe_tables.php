<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('categories', function (Blueprint $table) {
            $table->id(); $table->string('name')->unique(); $table->timestamps();
        });
        Schema::create('products', function (Blueprint $table) {
            $table->id(); $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name'); $table->unsignedInteger('price'); $table->unsignedInteger('cost')->default(0);
            $table->unsignedInteger('stock')->default(0); $table->boolean('track_stock')->default(false);
            $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('cafe_tables', function (Blueprint $table) {
            $table->id(); $table->string('name')->unique(); $table->unsignedTinyInteger('seats')->default(2); $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table) {
            $table->id(); $table->string('order_number')->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('cafe_table_id')->nullable()->constrained('cafe_tables')->restrictOnDelete();
            $table->string('type', 20); $table->string('status', 20)->default('pending');
            $table->unsignedBigInteger('total_amount'); $table->text('notes')->nullable(); $table->timestamps();
            $table->index(['created_at', 'status']);
        });
        Schema::create('order_items', function (Blueprint $table) {
            $table->id(); $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name'); $table->unsignedInteger('qty');
            $table->unsignedInteger('unit_price'); $table->unsignedInteger('unit_cost');
            $table->unsignedBigInteger('subtotal');
        });
        Schema::create('payments', function (Blueprint $table) {
            $table->id(); $table->foreignId('order_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('method', 20); $table->unsignedBigInteger('amount');
            $table->timestamp('paid_at'); $table->timestamps();
            $table->index('paid_at');
        });
        Schema::create('expenses', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('title'); $table->unsignedBigInteger('amount');
            $table->date('spent_on'); $table->text('notes')->nullable(); $table->timestamps();
            $table->index('spent_on');
        });
    }
    public function down(): void {
        foreach (['expenses','payments','order_items','orders','cafe_tables','products','categories'] as $name) Schema::dropIfExists($name);
    }
};
