<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
  /**
   * عرض كل الخطط
   */
  public function index()
  {
    $subscriptionPlans = SubscriptionPlan::latest()->paginate(10);
    return view('managment.changes.subscription-plans.create', compact('subscriptionPlans'));

  }

  /**
   * تخزين خطة جديدة
   */
  public function create()
  {
    $records = SubscriptionPlan::latest()->get();
    return view('managment.changes.subscription-plans.create', compact('records'));
  }

  public function store(Request $request)
  {
    // ✅ Validation Rules
    $validated = $request->validate([
      'name' => ['required', 'string', 'max:255'],
      'setter_name' => ['nullable', 'string', 'max:255'],
      'visits_count' => ['required', 'integer', 'min:1'],
      'duration_days' => ['required', 'integer', 'min:1'],
      'price' => ['required', 'numeric', 'min:0'],
    ], [
      // ✅ Custom Messages بالعربي
      'name.required' => '⚠️ اسم الخطة مطلوب.',
      'name.string' => '⚠️ اسم الخطة يجب أن يكون نص.',
      'name.max' => '⚠️ اسم الخطة طويل جدًا.',

      'setter_name.string' => '⚠️ اسم المعد يجب أن يكون نص.',
      'setter_name.max' => '⚠️ اسم المعد طويل جدًا.',

      'visits_count.required' => '⚠️ عدد الزيارات مطلوب.',
      'visits_count.integer' => '⚠️ عدد الزيارات يجب أن يكون رقم صحيح.',
      'visits_count.min' => '⚠️ عدد الزيارات يجب أن يكون 1 على الأقل.',

      'duration_days.required' => '⚠️ مدة الأيام مطلوبة.',
      'duration_days.integer' => '⚠️ مدة الأيام يجب أن تكون رقم صحيح.',
      'duration_days.min' => '⚠️ مدة الأيام يجب أن تكون 1 على الأقل.',

      'price.required' => '⚠️ السعر مطلوب.',
      'price.numeric' => '⚠️ السعر يجب أن يكون رقم.',
      'price.min' => '⚠️ السعر لا يمكن أن يكون أقل من صفر.',
    ]);
    try {
      SubscriptionPlan::create($validated);
      return redirect()->back()->with('success', '✅ تم إضافة خطة الاشتراك بنجاح.');
    } catch (\Exception $e) {
      return redirect()->back()->with('error', '❌ حدث خطأ أثناء إضافة الخطة: ' . $e->getMessage());
    }
  }

  /**
   * تعديل خطة
   */
  public function edit($id)
  {
    $plan = SubscriptionPlan::findOrFail($id);
    return view('subscription_plans.edit', compact('plan'));
  }

  /**
   * تحديث خطة
   */
  public function update(Request $request, $id)
  {
    $plan = SubscriptionPlan::findOrFail($id);

    $validated = $request->validate([
      'name' => ['required', 'string', 'max:255'],
      'setter_name' => ['nullable', 'string', 'max:255'],
      'visits_count' => ['required', 'integer', 'min:1'],
      'duration_days' => ['required', 'integer', 'min:1'],
      'price' => ['required', 'numeric', 'min:0'],
    ], [
      'name.required' => '⚠️ اسم الخطة مطلوب.',
      'visits_count.required' => '⚠️ عدد الزيارات مطلوب.',
      'duration_days.required' => '⚠️ مدة الأيام مطلوبة.',
      'price.required' => '⚠️ السعر مطلوب.',
    ]);
    try {
      $plan->update($validated);
      return redirect()->route('subscription_plans.index')->with('success', '✅ تم تحديث الخطة بنجاح.');
    } catch (\Exception $e) {
      return redirect()->back()->with('error', '❌ حدث خطأ أثناء تحديث الخطة.');
    }
  }

  /**
   * حذف خطة
   */
  public function destroy($id)
  {
    $plan = SubscriptionPlan::findOrFail($id);

    try {
      $plan->delete();
      return redirect()->back()->with('success', '✅ تم حذف الخطة بنجاح.');
    } catch (\Exception $e) {
      return redirect()->back()->with('error', '❌ لم يتم حذف الخطة.');
    }
  }
}




