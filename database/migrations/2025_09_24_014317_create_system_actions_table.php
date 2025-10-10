<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\SystemActionType;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_actions', function (Blueprint $table) {
            $table->bigIncrements('id');

            // أي مستخدم (admin أو employee أو أي واحد)
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // نستخدم string لإحتواء قيم ال enum (backup)
            $table->string('action', 64)->index();

            // polymorphic relation -> actionable_type/actionable_id
            $table->nullableMorphs('actionable'); // actionable_type (string) + actionable_id (unsignedBigInteger)

            // ربط اختياري ب invoice لو موجود
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();

            // لو مرتبط بشيفت
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();

            // مبلغ مصروف/مدخول إن وُجد
            $table->decimal('amount', 15, 2)->nullable();

            // وصف أو ملاحظة
            $table->text('note')->nullable();

            // بيانات إضافية json (مثلاً: old_values, new_values, device info)
            $table->json('meta')->nullable();

            // IP أو جهاز
            $table->string('ip', 45)->nullable();

            // مصدر (web, pos, api) إن احتجت
            $table->string('source', 32)->nullable()->index();

            $table->timestamps();

            // بعض الـ indexes للبحث السريع
            $table->index(['user_id', 'action']);
        });

        // (اختياري) تقييد على مستوى قاعدة البيانات باستخدام checked values:
        // بعض المشاريع يضيفون ENUM في MySQL. لكن لأن عندنا PHP enum ونريد مرونة، تركناه string.
    }

    public function down(): void
    {
        Schema::dropIfExists('system_actions');
    }
};
