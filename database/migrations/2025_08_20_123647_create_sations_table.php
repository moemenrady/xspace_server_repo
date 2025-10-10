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
    Schema::create('sations', function (Blueprint $table) {
      $table->id();

      // هنا العلاقة
      
      $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
      $table->timestamp('start_time')->useCurrent();
      $table->timestamp('end_time')->nullable();
      $table->integer('persons')->default(1);
      $table->enum('status', ['active', 'closed'])->default('active');
      $table->timestamps();

    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('sations');
  }
};
