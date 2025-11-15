<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateClientsAddAgeAndRelations extends Migration
{
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            // إضافة العمر (nullable)
            if (! Schema::hasColumn('clients', 'age')) {
                $table->integer('age')->nullable()->after('phone');
            }

            // إضافة أعمدة العلاقات كـ unsigned big integer nullable
            if (! Schema::hasColumn('clients', 'specialization_id')) {
                $table->unsignedBigInteger('specialization_id')->nullable()->after('age');
            }

            if (! Schema::hasColumn('clients', 'education_stage_id')) {
                $table->unsignedBigInteger('education_stage_id')->nullable()->after('specialization_id');
            }
        });

        // بعدها نضيف الـ foreign keys في عملية منفصلة (حتى لو في نفس الميجريشن)
        Schema::table('clients', function (Blueprint $table) {
            // تأكد إن الأعمدة موجودة قبل إضافة القيود
            if (Schema::hasColumn('clients', 'specialization_id')) {
                $table->foreign('specialization_id')
                      ->references('id')->on('specializations')
                      ->onDelete('set null')
                      ->onUpdate('cascade');
            }

            if (Schema::hasColumn('clients', 'education_stage_id')) {
                $table->foreign('education_stage_id')
                      ->references('id')->on('education_stages')
                      ->onDelete('set null')
                      ->onUpdate('cascade');
            }
        });
    }

    public function down()
    {
        // أَزل القيود أولاً ثم الأعمدة
        Schema::table('clients', function (Blueprint $table) {
            // drop foreign keys if exist
            if (Schema::hasColumn('clients', 'specialization_id')) {
                // استخدم اسم القيد الافتراضي الذي ينشئه لارافيل
                $table->dropForeign(['specialization_id']);
            }

            if (Schema::hasColumn('clients', 'education_stage_id')) {
                $table->dropForeign(['education_stage_id']);
            }
        });

        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'education_stage_id')) {
                $table->dropColumn('education_stage_id');
            }
            if (Schema::hasColumn('clients', 'specialization_id')) {
                $table->dropColumn('specialization_id');
            }
            if (Schema::hasColumn('clients', 'age')) {
                $table->dropColumn('age');
            }
        });
    }
}
