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
    Schema::create('bookings', function (Blueprint $table) {
      $table->id();
      // ربط بعميل
      $table->foreignId('client_id')->constrained()->cascadeOnDelete();
      // ربط بقاعة
      $table->foreignId('hall_id')->constrained()->cascadeOnDelete();
      $table->string('title');
      $table->unsignedSmallInteger('attendees');

      $table->unsignedInteger('duration_minutes');
      $table->dateTime('end_at'); // ميعاد البداية والنهاية الحقيقي 
      $table->dateTime('real_start_at')->nullable();
      $table->dateTime('start_at');
      $table->dateTime('real_end_at')->nullable();
      $table->enum('status', [
        'scheduled',
        'due',
        'in_progress',
        'finished',
        'cancelled'
      ])->default('scheduled');
      $table->decimal('base_hour_price', 10, 2);
      $table->decimal('extra_person_hour_price', 10, 2);
      $table->unsignedSmallInteger('min_capacity_snapshot');
      $table->decimal('estimated_total', 12, 2);
      $table->decimal('real_total', 12, 2)->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('bookings');
  }
};
