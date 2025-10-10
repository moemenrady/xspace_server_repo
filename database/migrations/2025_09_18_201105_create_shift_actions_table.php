<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shift_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shift_id')->nullable();

            $table->enum('action_type', [
                'new_subscription',
                'renew_subscription',
                'end_session',
                'separate_sale',
                'add_booking',
                'end_booking',
                'expense_note'
            ]);

            $table->unsignedBigInteger('invoice_id')->nullable(); // لو أكشن مرتبط بفاتورة

            // نضيف العمود الجديد قبل عمل الـ FK عليه
            $table->unsignedBigInteger('expense_draft_id')->nullable();

            $table->decimal('amount', 12, 2)->default(0); // الإيراد
            $table->decimal('expense_amount', 12, 2)->nullable(); // قيمة المصروف لو موجود

            $table->text('notes')->nullable();
            $table->timestamps();

            // الفهارس والـ FKs
            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('cascade');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
            $table->foreign('expense_draft_id')->references('id')->on('expense_drafts')->onDelete('set null');

            // إضافة index سريع للبحث
            $table->index(['shift_id', 'action_type']);
        });
    }

    public function down()
    {
        Schema::table('shift_actions', function (Blueprint $table) {
            // نحذف الـ FKs بأمان قبل حذف العمود/الجدول
            if (Schema::hasColumn('shift_actions', 'expense_draft_id')) {
                try { $table->dropForeign(['expense_draft_id']); } catch (\Throwable $e) {}
            }
            try { $table->dropForeign(['invoice_id']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['shift_id']); } catch (\Throwable $e) {}
        });

        Schema::dropIfExists('shift_actions');
    }
};
