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
    Schema::create('expense_drafts', function (Blueprint $table) {
      $table->id();
      $table->text('note')->nullable(); // وصف قصير للمصروف
      $table->decimal('estimated_amount', 10, 2)->nullable(); // تقدير الموظف (اختياري)
      $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // الموظف اللي أنشأ الدرفت
      $table->foreignId('expense_type_id')->nullable()->constrained('expense_types')->onDelete('set null');
      $table->timestamps();
    });

  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('expense_drafts');
  }
};
