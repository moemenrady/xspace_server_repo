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
    Schema::create('venue_pricing', function (Blueprint $table) {
      $table->id();
      $table->decimal('base_hour_price', 10, 2);
      $table->string('setter_name')->nullable();
      $table->boolean('is_active')->default(1);
      $table->timestamps();
    });

  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('venue_pricing');
  }
};
