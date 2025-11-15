<?php
// database/migrations/2025_10_13_000000_create_subscription_visits_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionVisitsTable extends Migration
{
    public function up()
    {
        Schema::create('subscription_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')
                  ->constrained('subscriptions')
                  ->onDelete('cascade');
            $table->foreignId('client_id')
                  ->constrained('clients')
                  ->onDelete('cascade');

            // رقم هذه الزيارة داخل نفس الاشتراك (1,2,3,...)
            $table->unsignedInteger('visit_number')->nullable();

            // وقت الحضور والمغادرة (ممكن يبقى null لحد ما يختموا الخروج)
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();

            // مدة الجلسة بالدقائق - ممكن نحسبها من checked_in/out لكن نخزنها للسرعة
            $table->unsignedInteger('duration_minutes')->nullable();

            // هل الحضور كان مؤكد (attended) - يعكس attendance
            $table->boolean('attended')->default(true);

            // أي ملاحظات أو تفاصيل إضافية
            $table->text('notes')->nullable();

            // من سجل هذه الزيارة (user id) — اختياري
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscription_visits');
    }
}
