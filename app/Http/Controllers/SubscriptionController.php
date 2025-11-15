<?php

namespace App\Http\Controllers;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\ShiftAction;
use App\Models\SubscriptionPlan;
use App\Support\InvoiceNumber;
use DB;
use Dotenv\Validator;
use Illuminate\Http\Request;
use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Shift;
use App\Models\SubscriptionVisit;

class SubscriptionController extends Controller
{

  public function index()
  {
    $plans = SubscriptionPlan::all();
    return view('subscription.index', compact('plans'));
  }
// use Illuminate\Http\Request;
// use App\Models\Subscription;

public function ajaxSearch(Request $request)
{
    // مرونة: نقبل q أو search أو query
    $q = $request->query('q') ?? $request->query('search') ?? $request->query('query');

    $statuses = $request->query('statuses', []);
    $plans = $request->query('plans', []);

    $subs = Subscription::with(['client', 'plan'])
        ->when($q, function ($query) use ($q) {
            $query->where(function($qq) use ($q) {
                // لو المستخدم كتب رقم (أو id) نجرب المطابقة بالـ id
                if (is_numeric($q)) {
                    $qq->orWhere('id', $q)
                       ->orWhereHas('client', fn($c) => $c->where('id', $q));
                }
                // البحث بـ name أو phone داخل علاقة client
                $qq->orWhereHas('client', fn($c) =>
                    $c->where('name', 'like', "%{$q}%")
                      ->orWhere('phone', 'like', "%{$q}%")
                );
            });
        })
        ->when($statuses, fn($query) => $query->whereIn('is_active', (array)$statuses))
        ->when($plans, fn($query) => $query->whereIn('plan_id', (array)$plans))
        ->orderBy('start_date', 'desc')
        ->get();

    return response()->json($subs->map(function ($s) {
        return [
            'id' => $s->id,
            'client_name' => $s->client->name ?? '',
            'client_phone' => $s->client->phone ?? '',
            'plan_name' => $s->plan->name ?? '',
            'start_date' => $s->start_date,
            'end_date' => $s->end_date,
            'remaining_visits' => $s->remaining_visits,
            'is_active' => $s->is_active ? 'فعال' : 'منتهي',
        ];
    }));
}


  public function create()
  {
    $plans = SubscriptionPlan::all();
    return view('subscription.create', compact("plans"));
  }

  public function index_manager()
  {
    $subscriptions = Subscription::with(['client', 'plan'])
      ->where('is_active', true)
      ->whereIn('id', function ($query) {
        $query->selectRaw('MAX(id)')
          ->from('subscriptions')
          ->where('is_active', true)
          ->groupBy('client_id');
      })
      ->get();

    return view('subscription.index-manager', compact('subscriptions'));
  }
  public function show($id)
  {
    // نجيب الاشتراك من الداتا بيز
    $subscription = Subscription::with(['client', 'plan'])->findOrFail($id);

    return view('subscription.show', [
      'subscription' => $subscription,
      'client' => $subscription->client,
      'plan' => $subscription->plan,
    ]);
  }



  // عرض كل الخطط
  public function plans()
  {
    $plans = SubscriptionPlan::all();
    return view('subscription.plans', compact('plans'));
  }

  public function renew(Request $request, Subscription $subscription)
  {
    // Validation
    $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
      'plan_id' => ['nullable', 'exists:subscription_plans,id'],
    ], [
      'plan_id.exists' => '⚠️ الخطة المحددة غير موجودة.',
    ]);

    if ($validator->fails()) {
      if ($request->expectsJson()) {
        return response()->json(['success' => false, 'errors' => $validator->errors(), 'message' => 'بيانات غير صحيحة'], 422);
      }
      return redirect()->back()->withErrors($validator)->withInput();
    }

    // التحضيرات: المستخدم والشيفت المفتوح والتحقق من صلاحية الادمن
    $user = Auth::user();
    $isAdmin = (method_exists($user, 'hasRole') ? $user->hasRole('admin') : ($user->role ?? '') === 'admin');
    $openShift = Shift::where('user_id', $user->id)->whereNull('end_time')->first();

    if (!$openShift && !$isAdmin) {
      session()->flash('shift_required', true);

      $msg = '⚠️ لا يوجد شيفت مفتوح — ابدأ شيفت أولاً أو تواصل مع الإدارة.';
      if ($request->expectsJson()) {
        return response()->json(['success' => false, 'message' => $msg], 400);
      }
      return redirect()->back()->with('error', $msg);
    }

    try {
      // منع التجديد لو الاشتراك ما زال فعال حتى تاريخ مستقبلي
      if ($subscription->is_active && $subscription->end_date && Carbon::parse($subscription->end_date)->gt(now())) {
        $msg = '❌ لا يمكن تجديد الاشتراك لأنه ما زال فعال.';
        if ($request->expectsJson()) {
          return response()->json(['success' => false, 'message' => $msg], 400);
        }
        return redirect()->back()->with('error', $msg);
      }

      // احضر الخطة: إما نفس خطة الاشتراك أو الخطة المرسلة في الريكوست
      $plan = $subscription->plan;
      if ($request->filled('plan_id')) {
        $plan = SubscriptionPlan::findOrFail($request->plan_id);
      }

      if (!$plan) {
        $msg = '❌ لم يتم العثور على بيانات الخطة.';
        if ($request->expectsJson()) {
          return response()->json(['success' => false, 'message' => $msg], 404);
        }
        return redirect()->back()->with('error', $msg);
      }

      DB::beginTransaction();

      // تحديث الاشتراك
      $newStart = now();
      $newEnd = now()->addDays((int) $plan->duration_days);

      $subscription->update([
        'plan_id' => $plan->id,
        'remaining_visits' => (int) $plan->visits_count,
        'start_date' => $newStart->toDateString(),
        'end_date' => $newEnd->toDateString(),
        'is_active' => true,
        'renewal_count' => $subscription->renewal_count + 1,
      ]);

      // إنشاء الفاتورة (TEMP ثم تحديث رقمها بعد الحفظ لتفادي سباقات)
      $invoice = Invoice::create([
        'invoice_number' => 'TEMP',
        'booking_id' => null,
        'client_id' => $subscription->client_id,
        'type' => 'subscription',
        'total' => $plan->price,
        'profit' => $plan->price - ($plan->cost ?? 0),
        'notes' => 'تجديد اشتراك للخطة: ' . $plan->name,
      ]);

      // توليد رقم فاتورة ثابت باستخدام id
      $invoice->invoice_number = InvoiceNumber::next();
      $invoice->save();

      // إضافة بند الفاتورة (snapshot)
      InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'item_type' => 'subscription',
        'product_id' => null,
        'subscription_id' => $subscription->id,
        'booking_id' => null,
        'session_id' => null,
        'name' => 'تجديد اشتراك - ' . $plan->name,
        'qty' => 1,
        'price' => $plan->price,
        'cost' => $plan->cost ?? 0,
        'total' => $plan->price,
        'description' => 'تجديد اشتراك للعميل #' . $subscription->client_id,
      ]);


      if ($invoice && !$isAdmin) {
        ShiftAction::create([
          'shift_id' => $openShift->id,
          'action_type' => 'renew_subscription',
          'invoice_id' => $invoice->id,
          'expense_draft_id' => null,
          'expense_amount' => null,
          'amount' => $invoice->total,
          'notes' => 'تجديد اشتراك - عميل : ' . $subscription->client->name . ' - اشتراك #' . $subscription->plan->name,
        ]);
      }
      if ($invoice && $isAdmin) {
        ShiftAction::create([
          'shift_id' => $openShift->id,
          'action_type' => 'renew_subscription',
          'invoice_id' => $invoice->id,
          'expense_draft_id' => null,
          'expense_amount' => null,
          'amount' => $invoice->total,
          'notes' => 'تجديد اشتراك - عميل : ' . $subscription->client->name . ' - اشتراك #' . $subscription->plan->name,
        ]);
      }

      DB::commit();

      $successMessage = '✅ تم تجديد الاشتراك وإنشاء فاتورة بنجاح.';

      if ($request->expectsJson()) {
        return response()->json([
          'success' => true,
          'message' => $successMessage,
          'subscription' => $subscription->fresh(),
          'redirect' => route('subscriptions.show', $subscription->id),
        ], 200);
      }

      return redirect()->route('subscriptions.show', $subscription->id)->with('success', $successMessage);

    } catch (\Exception $e) {
      DB::rollBack();
      \Log::error('Subscription renew failed: ' . $e->getMessage(), [
        'user_id' => $user->id ?? null,
        'subscription_id' => $subscription->id ?? null,
        'plan_id' => $plan->id ?? ($request->plan_id ?? null),
      ]);

      $errMsg = '❌ حدث خطأ أثناء تجديد الاشتراك. الرجاء المحاولة مرة أخرى أو التواصل مع الدعم.';
      if ($request->expectsJson()) {
        return response()->json(['success' => false, 'message' => $errMsg], 500);
      }
      return redirect()->back()->with('error', $errMsg);
    }
  }


  public function subscribe(Request $request)
  {
    // Validation (كما عندك)
    $request->validate([
      'name' => [
        'required',
        'string',
        'regex:/^[\pL\s]+$/u',
        'min:3',
        'max:50',
      ],
      'phone' => [
        'required',
        'regex:/^(010|011|012|015)[0-9]{8}$/'
      ],
      'plan_id' => [
        'required',
        'exists:subscription_plans,id'
      ],
    ], [
      'name.required' => 'من فضلك أدخل اسم العميل',
      'name.string' => 'الاسم يجب أن يكون نصًا',
      'name.regex' => 'الاسم يجب أن يحتوي على حروف فقط',
      'name.min' => 'الاسم يجب أن لا يقل عن 3 أحرف',
      'name.max' => 'الاسم يجب أن لا يزيد عن 50 حرفًا',

      'phone.required' => 'من فضلك أدخل رقم الهاتف',
      'phone.regex' => 'ادخل رقم مصري صحيح (11 رقم ويبدأ بـ 010 أو 011 أو 012 أو 015)',

      'plan_id.required' => 'يجب اختيار خطة اشتراك',
      'plan_id.exists' => 'الخطة المختارة غير موجودة',
    ]);

    $phone = $request->input('phone');
    $name = $request->input('name');
    $planId = $request->input('plan_id');

    // جلب الخطة (ستطرح ModelNotFoundException لو غير موجود)
    $plan = SubscriptionPlan::findOrFail($planId);

    // تحقق مستخدم الشيفت المفتوح (شيفت الموظف الحالي) — نحتاجه لتسجيل الفعل
    $user = Auth::user();
    $isAdmin = $user->hasRole('admin');

    // نجد شيفت مفتوح للمستخدم (غير منتهي)
    $openShift = Shift::where('user_id', $user->id)->whereNull('end_time')->first();

    if (!$openShift && !$isAdmin) {
      session()->flash('shift_required', true);

      return back()->with('error', '⚠️ لا يوجد شيفت مفتوح — ابدأ شيفت أولاً أو تواصل مع الإدارة.');
    }

    // ابدأ المعاملة
    DB::beginTransaction();

    try {
      // تحقق هل العميل موجود
      $client = Client::where('phone', $phone)->first();

      if ($client) {
        // هل لديه اشتراك نشط؟
        $hasActive = Subscription::where('client_id', $client->id)
          ->where('is_active', true)
          ->whereDate('end_date', '>=', now())
          ->exists();

        if ($hasActive) {
          DB::rollBack();
          return back()->with('error', 'هذا العميل (' . $client->name . ') لديه اشتراك نشط بالفعل.');
        }
      } else {
        // انشاء العميل جديد
        $client = Client::create([
          'phone' => $phone,
          'name' => $name,
        ]);
      }

      // انشاء الاشتراك واحتفظ بالنسخة
      $subscription = $client->subscriptions()->create([
        'plan_id' => $plan->id,
        'remaining_visits' => (int) $plan->visits_count,
        'start_date' => now()->toDateString(),
        'end_date' => now()->addDays((int) $plan->duration_days)->toDateString(),
        'is_active' => true,
        'renewal_count' => 0,

      ]);

      // انشاء الفاتورة (ننشئها أولاً ثم نحدّث رقم الفاتورة بعد الحصول على id لتجنب الـ race)
      $invoice = Invoice::create([
        'invoice_number' => 'TEMP', // نحدثه بعد الحفظ
        'booking_id' => null,
        'client_id' => $client->id,
        'type' => 'subscription',
        'total' => $plan->price,
        'profit' => $plan->price - ($plan->cost ?? 0),
        'notes' => 'اشتراك جديد للخطة: ' . $plan->name,
      ]);

      // تحديث invoice_number بمعلومية الـ id لتفادي race condition
      $invoice->invoice_number = 'INV-' . now()->format('Ymd') . '-' . str_pad($invoice->id, 4, '0', STR_PAD_LEFT);
      $invoice->save();

      // انشاء بند الفاتورة (snapshot للبيانات وقت البيع)
      InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'item_type' => 'subscription',
        'product_id' => null,
        'subscription_id' => $subscription->id,
        'booking_id' => null,
        'session_id' => null,
        'name' => 'اشتراك - ' . $plan->name,
        'qty' => 1,
        'price' => $plan->price,
        'cost' => $plan->cost ?? 0,
        'total' => $plan->price,
        'description' => 'إنشاء اشتراك جديد للعميل #' . $client->id,
      ]);

      // تسجيل اكشن في الشيفت (نستخدم جدول shift_actions مباشرة لتجنب الاعتمادية على Model غير موجود)
      if ($invoice && !$isAdmin) {
        ShiftAction::create([
          'shift_id' => $openShift->id,
          'action_type' => 'new_subscription',
          'invoice_id' => $invoice->id,
          'expense_draft_id' => null,
          'expense_amount' => null,
          'amount' => $invoice->total,
          'notes' => 'اشتراك جديد - عميل: ' . $client->name . ' - اشتراك #' . $subscription->id,
        ]);
      }
      if ($invoice && $isAdmin) {
        ShiftAction::create([
          'shift_id' => $openShift->id,
          'action_type' => 'new_subscription',
          'invoice_id' => $invoice->id,
          'expense_draft_id' => null,
          'expense_amount' => null,
          'amount' => $invoice->total,
          'notes' => 'اشتراك جديد - عميل: ' . $client->name . ' - اشتراك #' . $subscription->id,
        ]);
      }


      DB::commit();

      return redirect()->route('subscriptions.index-manager')->with('success', '✅ تم إنشاء الاشتراك والفاتورة بنجاح.');

    } catch (\Exception $e) {
      DB::rollBack();
      \Log::error('Subscribe error: ' . $e->getMessage(), [
        'user_id' => $user->id ?? null,
        'phone' => $phone,
        'plan_id' => $planId
      ]);
      // رسالة ودودة ومفهومة للمستخدم
      return back()->with('error', '❌ حدث خطأ أثناء إنشاء الاشتراك. الرجاء المحاولة مرة أخرى أو التواصل مع الدعم.');
    }
  }

 
public function decrease(Subscription $subscription)
{
    // نفتح transaction و lock للصف الخاص بالاشتراك عشان نمنع حالات السباق
    return DB::transaction(function () use ($subscription) {

        // إعادة جلب الاشتراك مع lock
        $subscription = Subscription::lockForUpdate()->find($subscription->id);

        // تحقق إن الاشتراك موجود
        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription not found',
            ], 404);
        }

        // تحقق من صلاحية الاشتراك بالتاريخ والحالة
        if (!$subscription->is_active || ($subscription->end_date && Carbon::today()->gt(Carbon::parse($subscription->end_date)))) {
            return response()->json([
                'success' => false,
                'redirect' => route('subscriptions.index'),
                'message' => '❌ لا يمكن استخدام هذا الاشتراك لأنه منتهي أو غير فعال.'
            ], 422);
        }

        // ============================
        // إنشاء سجل الزيارة (قبل أو بعد التنقيص حسب منطقك)
        // ============================
        // حساب رقم الزيارة داخل الاشتراك
        $visitNumber = $subscription->visits()->count() + 1;

        $visit = SubscriptionVisit::create([
            'subscription_id'   => $subscription->id,
            'client_id'         => $subscription->client_id,
            'visit_number'      => $visitNumber,
            'checked_in_at'     => now(),
            'attended'          => true,
            'notes'             => null,               // لو عايز تجيب من request ممكن تعدل
            'created_by'        => auth()->id() ?? null,
        ]);

        // ============================
        // تنقيص الزيارات أو التعامل مع اشتراكات غير محددة
        // ============================
        // لو remaining_visits معرف ومقيد
        if (!is_null($subscription->remaining_visits)) {
            if ($subscription->remaining_visits > 1) {
                $subscription->decrement('remaining_visits');
                $subscription->update([
                    'attendees' => 1,
                    'visit_date' => now()
                ]);

                // إعادة البيانات للـ frontend
                return response()->json([
                    'success' => true,
                    'message' => 'تم تسجيل الزيارة وتنقيص عدد الزيارات.',
                    'remaining_visits' => $subscription->remaining_visits,
                    'visit' => $visit
                ]);
            } elseif ($subscription->remaining_visits == 1) {
                // هذه آخر زيارة
                $subscription->decrement('remaining_visits');
                $subscription->update([
                    'attendees' => 1,
                    'visit_date' => now(),
                    'is_active' => false,
                ]);

                return response()->json([
                    'success' => true,
                    'redirect' => route('subscriptions.index'),
                    'message' => '✅ هذه آخر زيارة في اشتراك العميل (' . $subscription->client->name . ')',
                    'remaining_visits' => $subscription->remaining_visits,
                    'visit' => $visit
                ]);
            } else {
                // remaining_visits <= 0 (لا توجد زيارات متبقية) — لكن لأننا هنا أنشأنا زيارة، يمكن التراجع أو رفض
                // أفضل سلوك: rollback بالـ transaction وارجاع خطأ
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'redirect' => route('subscriptions.index'),
                    'message' => '❌ لا يوجد زيارات متبقية للاشتراك الخاص بالعميل (' . $subscription->client->name . ')'
                ], 422);
            }
        }

        // لو الاشتراك غير مقيد بعدد زيارات (remaining_visits == null) — نسجل الزيارة فقط ولا ننقص شيئًا
        $subscription->update([
            'attendees' => 1,
            'visit_date' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الزيارة (اشتراك غير مقيد بعدد جلسات).',
            'visit' => $visit
        ]);
    }); // end transaction
}



}