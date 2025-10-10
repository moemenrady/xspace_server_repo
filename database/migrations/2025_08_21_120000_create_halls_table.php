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
    Schema::create('halls', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('setter_name')->nullable();
      $table->unsignedSmallInteger('min_capacity');
      $table->unsignedSmallInteger('max_capacity');
      $table->boolean('is_active')->default(true);
      $table->timestamps();
    });

  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('halls');
  }
};
