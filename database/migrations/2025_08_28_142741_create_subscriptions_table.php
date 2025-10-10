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
    Schema::create('subscriptions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
      $table->foreignId('plan_id')->constrained('subscription_plans')->onDelete('cascade');
      $table->integer('remaining_visits');
      $table->date('start_date');
      $table->date('end_date'); // يساوي start_date + duration_days
      $table->boolean('is_active')->default(true);
      $table->unsignedInteger('renewal_count')->default(0); // ✅ عدد مرات التجديد
      $table->date('visit_date')->nullable();
      $table->boolean('attendees')->default(false);
      $table->timestamps();
    });

  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('subscriptions');
  }
};
