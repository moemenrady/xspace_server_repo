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
    Schema::create('invoices', function (Blueprint $table) {
    $table->id();
    $table->string('invoice_number')->unique(); // INV-20250825-0001

    $table->foreignId('booking_id')
        ->nullable()
        ->unique()
        ->constrained('bookings')
        ->nullOnDelete();

    $table->foreignId('client_id')
        ->nullable()
        ->constrained()
        ->nullOnDelete();

    // العمود الجديد: user الذي أنشأ الفاتورة
    $table->foreignId('created_by')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete()
        ->after('client_id');

    $table->enum('type', ['product', 'subscription', 'booking', 'session', 'deposit', 'mixed']);
    $table->decimal('total', 12, 2);
    $table->decimal('profit', 12, 2)->default(0);
    $table->text('notes')->nullable();
    $table->timestamps();

    $table->index(['client_id', 'type']);
});

  }
  public function down(): void
  {
    Schema::dropIfExists('invoices');
  }
};
