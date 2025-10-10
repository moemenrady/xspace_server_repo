<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('invoice_items', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('invoice_id');

      // نوع البند ومرجعه
      $table->enum('item_type', ['product', 'subscription', 'booking', 'session', 'deposit']);
      $table->unsignedBigInteger('product_id')->nullable();
      $table->unsignedBigInteger('subscription_id')->nullable();
      $table->unsignedBigInteger('booking_id')->nullable();
      $table->unsignedBigInteger('session_id')->nullable();

      // بيانات البند وقت البيع (immutable snapshot)
      $table->string('name');                    // الاسم وقت البيع (حتى لو اتغير لاحقًا)
      $table->integer('qty')->default(1)->nullable();
      $table->decimal('price', 10, 2);          // سعر البيع للوحدة
      $table->decimal('cost', 10, 2)->default(0); // تكلفة الوحدة (للحساب الربحي)
      $table->decimal('total', 12, 2);          // price * qty
      $table->string('description')->nullable(); // مفيد للـ deposit

      $table->timestamps();

      $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
      $table->index(['invoice_id', 'item_type']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('invoice_items');
  }
};
