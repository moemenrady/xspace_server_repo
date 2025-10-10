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
    Schema::create('shifts', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('user_id'); // الموظف صاحب الشيفت

      $table->timestamp('start_time');
      $table->timestamp('end_time')->nullable(); // بيكون null لحد ما يتقفل
      $table->integer('duration')->nullable(); // مدة الشيفت بالدقايق أو الساعات

      $table->decimal('total_amount', 12, 2)->default(0);   // إجمالي الإيرادات في الشيفت
      $table->decimal('total_expense', 12, 2)->default(0);  // إجمالي المصروفات في الشيفت

      $table->timestamps();

      $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });

  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('shifts');
  }
};
