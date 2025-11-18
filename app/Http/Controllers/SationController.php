<?php

namespace App\Http\Controllers;
use App\Enums\SystemActionType;
use App\Models\FullDayHour;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\SessionPurchase;
use App\Models\Shift;
use App\Models\SystemAction;
use App\Models\Visit;
use App\Services\ShiftService;
use App\Models\Hall;
use App\Models\Booking;
use App\Support\InvoiceNumber;
use Auth;
use Carbon\Carbon;
use App\Models\Client;
use App\Models\Sation;
use App\Models\Product;
use DB;
use Illuminate\Http\Request;
use App\Models\ImportantProduct;



class SationController extends Controller
{
public function split(Request $request)
{
    // ✅ تحقق من وجود شيفت مفتوح
    $user = Auth::user();
    $openShift = Shift::where('user_id', $user->id)->whereNull('end_time')->first();
    $isAdmin = $user->hasRole('admin');
    if (!$openShift && !$isAdmin) {
      session()->flash('shift_required', true);
      return redirect()->back()
        ->with('error', '⚠️ لا يوجد شيفت مفتوح، ابدأ شيفت أولاً.');
    }

    // 1) قواعد الفاليديشن المحسنة
    $request->validate([
      'session_id' => 'required|exists:sations,id',
      'split_persons' => 'required|integer|min:1',
      'hours' => 'required|numeric|min:0',
      'items' => 'nullable|array',
      'items.*' => 'nullable|integer|min:0', // القيم (الكميات)
    ]);

    // ✅ تحقق إضافي: لازم يكون فيه ساعات أو مشتريات
    $submittedItems = $request->input('items', []);
    $hasItems = collect($submittedItems)->filter(function ($qty) {
      return intval($qty) > 0;
    })->isNotEmpty();

    if (floatval($request->hours) <= 0 && !$hasItems) {
      return redirect()->back()
        ->withInput()
        ->with('error', '⚠️ لا يمكن عمل حساب منفصل من جلسة لا يوجد بها مشتريات وعدد الساعات = 0.');
    }

    DB::beginTransaction();
    try {
      // lock session row to avoid concurrent modifications
      $parentSession = Sation::where('id', $request->session_id)
        ->lockForUpdate()
        ->with(['purchases.product']) // جلب مشتريات الجلسة مع علاقة المنتج إن وُجدت
        ->first();

      if (!$parentSession || $parentSession->status !== 'active') {
        DB::rollBack();
        return redirect()->back()->with('error', '⚠️ الجلسة غير موجودة أو منتهية بالفعل.');
      }

      // تحقق أن عدد الأشخاص المطلوب فصلهم لا يتجاوز الموجود بالفعل
      if ($request->split_persons > $parentSession->persons) {
        DB::rollBack();
        return redirect()->back()->with('error', '⚠️ عدد الأفراد المختارين أكبر من الموجود في الجلسة.');
      }

      // إذا المستخدم لم يرسل أي items لكن الجلسة الأصلية لديها مشتريات --> نمنع العملية ونعطي تحذير واضح
      if (!$hasItems && $parentSession->purchases->isNotEmpty()) {
        DB::rollBack();
        return redirect()->back()
          ->withInput()
          ->with('error', '⚠️ الجلسة الأصلية تحتوي على مشتريات. اختر المنتجات/الكميات التي تريد نقلها للحساب المنفصل أو أكد أنك تريد فصل الحساب بدون نقل مشتريات.');
      }

      // 3) تحقق تفصيلي: أن كل منتج مرسل موجود فعلاً في مشتريات الجلسة وبكمية كافية
      $insufficient = []; // سنجمع هنا أي منتجات بكميات ناقصة
      foreach ($submittedItems as $prodIdStr => $qty) {
        $prodId = intval($prodIdStr);
        $qty = intval($qty);
        if ($qty <= 0) continue;

        // حاول إيجاد السطر المقابل في مشتريات الجلسة الأصلية
        $parentPurchase = $parentSession->purchases->firstWhere('product_id', $prodId);
        if (!$parentPurchase) {
          // المنتج غير موجود أساساً في مشتريات الجلسة
          $insufficient[] = [
            'product_id' => $prodId,
            'needed' => $qty,
            'available' => 0,
            'name' => optional($parentPurchase)->product->name ?? "ID: {$prodId}"
          ];
        } else {
          // تحقق الكمية
          $availableQty = intval($parentPurchase->quantity ?? 0);
          if ($availableQty < $qty) {
            $insufficient[] = [
              'product_id' => $prodId,
              'needed' => $qty,
              'available' => $availableQty,
              'name' => optional($parentPurchase->product)->name ?? "ID: {$prodId}"
            ];
          }
        }
      }

      if (!empty($insufficient)) {
        // جهّز رسالة خطأ مفصّلة
        $messages = ['⚠️ لا يمكن إتمام الفصل بسبب اختلاف في كميات المنتجات المرسلة:'];
        foreach ($insufficient as $it) {
          $messages[] = "- {$it['name']}: مطلوب {$it['needed']}، متاح في الجلسة الأصلية {$it['available']}.";
        }
        DB::rollBack();
        return redirect()->back()->withInput()->with('error', implode("\n", $messages));
      }

      // 4) إنشاء الجلسة الجديدة (لا تضع end_time هنا — اترك checkout يتولّى ذلك)
      $newSession = Sation::create([
        'client_id' => $parentSession->client_id,
        'persons' => $request->split_persons,
        'status' => 'active',
        'start_time' => $parentSession->start_time,
        'end_time' => null,
      ]);

      // 5) تجهيز المشتريات: قفل صفوف المنتجات ثم إنشاء سجلات session_purchases للجلسة الجديدة
      $purchasesForInvoice = [];
      $now = now();
      foreach ($submittedItems as $prodIdStr => $qty) {
        $prodId = intval($prodIdStr);
        $qty = intval($qty);
        if ($qty <= 0) continue;

        // lock product row to avoid concurrent stock changes
        $product = Product::where('id', $prodId)->lockForUpdate()->first();
        if (!$product) {
          DB::rollBack();
          return redirect()->back()->with('error', "⚠️ المنتج (ID: {$prodId}) غير موجود.");
        }

        // prepare invoice item snapshot
        $purchasesForInvoice[] = [
          'product_id' => $product->id,
          'name' => $product->name,
          'qty' => $qty,
          'price' => $product->price,
          'cost' => $product->cost,
        ];

        // deduct from parent session purchase (lock that row too)
        $parentPurchaseRow = $parentSession->purchases()->where('product_id', $prodId)->lockForUpdate()->first();
        if ($parentPurchaseRow) {
          $parentPurchaseRow->quantity = max(0, $parentPurchaseRow->quantity - $qty);
          if ($parentPurchaseRow->quantity == 0) {
            $parentPurchaseRow->delete();
          } else {
            $parentPurchaseRow->save();
          }
        }

        // أنشئ سجل session_purchases للجلسة الجديدة (سجل تاريخي)
        DB::table('session_purchases')->insert([
          'sation_id' => $newSession->id,
          'product_id' => $product->id,
          'quantity' => $qty,
          'created_at' => $now,
          'updated_at' => $now,
        ]);
      }

      // 6) تقليل الأشخاص في الجلسة الأصلية
      $parentSession->persons = max(0, $parentSession->persons - $request->split_persons);
      $parentSession->save();

      // استخرج سعر الساعة الحالي
      $hourly_rate = DB::table('venue_pricing')
        ->where('is_active', true)
        ->orderByDesc('id')
        ->value('base_hour_price');

      // ===== هنا التعديل: حول الساعات المُدخلة إلى ساعات قابلة للفوترة =====
      $originalHours = floatval($request->hours);
      $splitPersons = intval($request->split_persons);
      $billableHours = $originalHours * max(1, $splitPersons);

      // 7) جهز الـ request للـ checkout (نمرّر billableHours حتى لا نلمس checkout)
      $checkoutRequest = new Request([
        'hours' => $billableHours,
        'hourly_rate' => $hourly_rate,
        'purchases' => json_encode($purchasesForInvoice),
      ]);
      // ===================================================================

      // 8) سجل system_action للـ session الجديدة (START_SESSION)
      SystemAction::create([
        'user_id' => Auth::id(),
        'action' => SystemActionType::START_SESSION->value,
        'actionable_type' => Sation::class,
        'actionable_id' => $newSession->id,
        'note' => "إنشاء جلسة منفصله (new session) من الجلسة رقم {$parentSession->id}",
        'meta' => json_encode([
          'from_session_id' => $parentSession->id,
          'split_persons' => $request->split_persons,
        ]),
        'shift_id' => $openShift?->id,
        'ip' => request()->ip(),
        'source' => 'web',
      ]);

      // 9) سجل system_action لفعل "تقسيم الجلسة" (SPLIT_SESSION) مربوط بالجلسة الأصلية
      SystemAction::create([
        'user_id' => Auth::id(),
        'action' => SystemActionType::SPLIT_SESSION->value,
        'actionable_type' => Sation::class,
        'actionable_id' => $parentSession->id,
        'note' => "فصل حساب منفصل (split) - تم إنشاء جلسة جديدة رقم {$newSession->id} من الجلسة {$parentSession->id}",
        'meta' => json_encode([
          'new_session_id' => $newSession->id,
          'split_persons' => $splitPersons,
          'original_hours' => $originalHours,
          'billable_hours' => $billableHours,
          'purchases' => $purchasesForInvoice,
        ]),
        'shift_id' => $openShift?->id,
        'ip' => request()->ip(),
        'source' => 'web',
      ]);

      // استدعاء checkout على الجلسة الجديدة (سيستخدم hours التي مررناها = billableHours)
      $response = app()->call([$this, 'checkout'], ['request' => $checkoutRequest, 'session' => $newSession]);

      DB::commit();

      return redirect()->route('session.show', $parentSession->id)
        ->with('success', '✅ تم إنشاء حساب منفصل وإنهاءه بنجاح.');
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()->with('error', '❌ حدث خطأ أثناء تقسيم الجلسة: ' . $e->getMessage());
    }
}


  public function show(Sation $session)
  {
    try {
      // تأكد من وجود وقت بداية
      if (empty($session->start_time)) {
        return redirect()->route('admin-error.create')
          ->with('message', 'غير ممكن حساب مدة الجلسة: تاريخ البداية غير موجود.');
      }

      $start = Carbon::parse($session->start_time);
      $end = $session->end_time ? Carbon::parse($session->end_time) : Carbon::now();

      // الفرق بالدقائق كـ integer
      $minutes = (int) $start->diffInMinutes($end);

      // حساب عدد الساعات (منطقك: أقل من أو يساوي 15 دقيقة => 0 ساعة، وإلا نقسم ونقرب حسب >15 دقيقة)
      if ($minutes <= 15) {
        $hours = 0;
      } else {
        $fullHours = intdiv($minutes, 60);     // عدد الساعات الكاملة
        $remainingMinutes = $minutes % 60;

        if ($remainingMinutes > 15) {
          $fullHours += 1;
        }

        $hours = max(0, (int) $fullHours);
      }

      // جلب آخر FullDayHour مفعل (أحدث واحد)
      $fullDayHoursModel = FullDayHour::where('is_active', 1)
        ->orderByDesc('created_at')
        ->first();

      if (!$fullDayHoursModel) {
        // رجع رسالة واضحة لو مفيش record مفعل
        return redirect()->route('admin-error.create')
          ->with('message', 'عدد ساعات اليوم الكامل لم تضاف');
      }

      // نحول القيمة لعدد (int) — هنا نتجنّب وضع الموديل داخل $hours
      $fullDayHoursCount = (int) $fullDayHoursModel->hours_count;
      if ($fullDayHoursCount < 0)
        $fullDayHoursCount = 0; // حماية

      // لو وصل أو تجاوز عدد الساعات قيمة Full Day -> نعتبره full day
      $isFullDay = ($hours >= $fullDayHoursCount);
      if ($isFullDay) {
        $hours = $fullDayHoursCount;
      }

      // جلب سعر الساعة بأمان (float)
      $hourly_rate = (float) (DB::table('venue_pricing')
        ->where('is_active', true)
        ->orderByDesc('id')
        ->value('base_hour_price') ?? 0);

      // تأكد من عدد الأشخاص (fallback إلى 1 لو مفيش)
      $persons = max(1, (int) ($session->persons ?? 1));

      // حساب سعر الساعات (عدد * أشخاص * سعر الساعة)
      $hours_price = round($hours * $persons * $hourly_rate, 2);

      // eager load للعلاقات المهمة (لو مش محملة)
      $session->loadMissing('purchases.product');

      // حساب سعر المنتجات المرتبطة (حماية من عدم وجود المنتج أو السعر)
      $products_price = $session->purchases->sum(function ($purchase) {
        $price = (float) optional($purchase->product)->price ?? 0.0;
        $qty = (int) ($purchase->quantity ?? 0);
        return $price * $qty;
      });

      $total = $hours_price + $products_price;

      return view('session.show', compact(
        'session',
        'hours',
        'hours_price',
        'products_price',
        'total',
        'isFullDay',
        'hourly_rate'
      ) + [
        'purchases' => $session->purchases,
        'importantProducts' => ImportantProduct::all(),
      ]);
    } catch (\Throwable $e) {
      \Log::error('Error in session.show: ' . $e->getMessage(), [
        'session_id' => $session->id ?? null,
        'trace' => $e->getTraceAsString()
      ]);
      return redirect()->route('admin-error.create')->with('message', 'حدث خطأ أثناء حساب تكلفة الجلسة، يرجى المحاولة أو مراجعة السجلات.');
    }
  }

  public function index(Request $request)
  {
    $query = $request->get('search');

    $sessions = Sation::with('client')
      ->when($query, function ($q) use ($query) {
        $q->whereHas('client', function ($sub) use ($query) {
          $sub->where('phone', 'like', "%{$query}%")
            ->orWhere('name', 'like', "%{$query}%");
        });
      })
      ->where('status', 'active') // فلتر للجلسات النشطة فقط
      ->latest()
      ->get();
    $halls = Hall::all();
     $sessions_count = Sation::where('status', 'active')->sum('persons');

    $private_sessions_count = Booking::where('status', 'in_progress')->count();
    return view('session.index-manager', compact('sessions', 'query', "halls", "private_sessions_count", "sessions_count"));
  }
   // public function create()
  // {
  //   return view("session.create");
  // }
  public function storeFromManager(Request $request)
  {
    // input validated phone
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
      "persons" => [
        "required",
      ]
    ], [
      'name.required' => 'من فضلك أدخل اسم العميل',
      'name.string' => 'الاسم يجب أن يكون نصًا',
      'name.regex' => 'الاسم يجب أن يحتوي على حروف فقط',
      'name.min' => 'الاسم يجب أن لا يقل عن 3 أحرف',
      'name.max' => 'الاسم يجب أن لا يزيد عن 50 حرفًا',
      'phone.required' => 'من فضلك أدخل رقم الهاتف',
      'phone.regex' => ' ادخل رقم مصري صحيح (11 رقم ويبدأ بـ 010 أو 011 أو 012 أو 015)',
      'persons.required' => '',
    ]);

    $phone = $request->input('phone');
    $name = $request->input('name');
    $persons = $request->input('persons');

    // helper to check if request expects JSON (AJAX)
    $isJson = $request->wantsJson() || $request->ajax();

    try {
      // حاول الحصول على العميل مرة واحدة
      $client = Client::where('phone', $phone)->first();

      if ($client) {
        // تحقق لو لدى العميل جلسة نشطة
        $hasActive = Sation::where('client_id', $client->id)
          ->where('status', 'active')
          ->exists();

        if ($hasActive) {
          $msg = 'هذا العميل (' . $client->name . ') لديه جلسة نشطة بالفعل';

          if ($isJson) {
            return response()->json([
              'success' => false,
              'error' => $msg,
            ], 409); // Conflict
          }

          return back()->with('error', $msg);
        }

        // لا توجد جلسة نشطة -> إنشاء جلسة جديدة للعميل الموجود
        $session = Sation::create([
          'client_id' => $client->id,
          'start_time' => now(),
          'persons' => $persons,
          'status' => 'active',
        ]);

        // سجل العملية
        SystemAction::create([
          'user_id' => Auth::id(),
          'action' => SystemActionType::START_SESSION->value,
          'actionable_type' => Sation::class,
          'actionable_id' => $session->id,
          'note' => "بدء جلسة جديدة للعميل: {$client->name} - هاتف: {$client->phone}",
          'meta' => json_encode([
            'persons' => $persons,
            'client_id' => $client->id,
            'client_phone' => $client->phone,
          ]),
          'ip' => request()->ip(),
          'source' => 'web',
        ]);

        if ($isJson) {
          return response()->json([
            'success' => true,
            'message' => 'تم بدء الجلسة بنجاح',
            'session' => $session,
          ], 201);
        }

        return redirect()->route('session.index-manager')->with('success', 'تم إضافة الجلسة بنجاح');
      }

$client = Client::create([
    'phone' => $phone,
    'name' => $name,
    'age' => $request->input('age'),
    'specialization_id' => $request->input('specialization_id'),
    'education_stage_id' => $request->input('education_stage_id'),
]);

      // Log → إضافة عميل جديد
      SystemAction::create([
        'user_id' => Auth::id(),
        'action' => SystemActionType::ADD_NEW_CLIENT->value,
        'actionable_type' => Client::class,
        'actionable_id' => $client->id,
        'note' => "تم إضافة عميل جديد: {$client->name} - هاتف: {$client->phone}",
        'meta' => json_encode([
          'client_id' => $client->id,
          'phone' => $client->phone,
          'name' => $client->name,
        ]),
        'ip' => request()->ip(),
        'source' => 'web',
      ]);

      // 2. إنشاء جلسة
      $session = Sation::create([
        'client_id' => $client->id,
        'persons' => $persons,
        'start_time' => now(),
        'status' => 'active',
      ]);

      // Log → بدء جلسة جديدة
      SystemAction::create([
        'user_id' => Auth::id(),
        'action' => SystemActionType::START_SESSION->value,
        'actionable_type' => Sation::class,
        'actionable_id' => $session->id,
        'note' => "بدء جلسة جديدة للعميل: {$client->name}",
        'meta' => json_encode([
          'persons' => $persons,
          'client_id' => $client->id,
        ]),
        'ip' => request()->ip(),
        'source' => 'web',
      ]);

      if ($isJson) {
        return response()->json([
          'success' => true,
          'message' => 'تم بدء الجلسة بنجاح',
          'session' => $session,
        ], 201);
      }

      return redirect()->route('session.index-manager')->with('success', 'تم إضافة الجلسة بنجاح');
    } catch (\Exception $e) {
      // لو حدث استثناء، نرجع JSON مع كود 500 لو AJAX، وإلا نذهب لصفحة الخطأ كما سابقًا
      \Log::error('Session store error: ' . $e->getMessage(), ['exception' => $e]);

      if ($isJson) {
        return response()->json([
          'success' => false,
          'error' => 'حدث خطأ أثناء معالجة الطلب، الرجاء المحاولة لاحقًا'
        ], 500);
      }

      return to_route("error.create");
    }
  }



  
  public function store(Request $request)
  {

    // input validated phone
    $request->validate([
      'name' => [
        'required',
        'string',
        'regex:/^[\pL\s]+$/u', // يقبل الحروف (عربي/انجليزي) والمسافات فقط
        'min:3',
        'max:50',
      ],
      'phone' => [
        'required',
        'regex:/^(010|011|012|015)[0-9]{8}$/'
      ],
      "persons" => [
        "required",
      ]
    ], [
      // رسائل الاسم
      'name.required' => 'من فضلك أدخل اسم العميل',
      'name.string' => 'الاسم يجب أن يكون نصًا',
      'name.regex' => 'الاسم يجب أن يحتوي على حروف فقط',
      'name.min' => 'الاسم يجب أن لا يقل عن 3 أحرف',
      'name.max' => 'الاسم يجب أن لا يزيد عن 50 حرفًا',

      // رسائل الهاتف
      'phone.required' => 'من فضلك أدخل رقم الهاتف',
      'phone.regex' => ' ادخل رقم مصري صحيح (11 رقم ويبدأ بـ 010 أو 011 أو 012 أو 015)',

      //persons
      'persons.required' => '',
    ]);

    $phone = $request->input('phone');
    $name = $request->input('name');
    $persons = $request->persons;
    // is client with this phone exists 
    $exists = Client::where('phone', $phone)->exists();
    // if exists, start session with this client_id
    if ($exists) {
      if (
        Sation::where('client_id', Client::where('phone', $phone)->first()->id)
          ->where('status', 'active')
          ->exists()
      ) {
        return back()->with('error', 'هذا العميل (' . (Client::where('phone', $phone)->first()->name) . ') لديه جلسة نشطة بالفعل');
      } else {
        try {
          $client = Client::where("phone", $phone)->first();

          $session = Sation::create([
            'client_id' => $client->id,
            'start_time' => now(),
            'persons' => $persons,
            'status' => 'active',
          ]);
          SystemAction::create([
            'user_id' => Auth::id(), // المستخدم الحالي (admin/employee)
            'action' => SystemActionType::START_SESSION->value,
            'actionable_type' => Sation::class,   // polymorphic relation
            'actionable_id' => $session->id,
            'note' => "بدء جلسة جديدة للعميل: {$client->name} - هاتف: {$client->phone}",
            'meta' => json_encode([
              'persons' => $persons,
              'client_id' => $client->id,
              'client_phone' => $client->phone,
            ]),
            'ip' => request()->ip(),
            'source' => 'web',
          ]);
        } catch (\Exception $e) {
          return to_route("error.create");
        }
        return redirect()->route('session.index-manager')->with('success', 'تم إضافة الجلسة بنجاح');

        //=============================================== go to session index
      }
    }
    // if not exists, create new client and start session
    else {
      try {
        // 1. إنشاء العميل
        $client = Client::create([
          'phone' => $phone,
          'name' => $name,
        ]);

        // Log → إضافة عميل جديد
        SystemAction::create([
          'user_id' => Auth::id(),
          'action' => SystemActionType::ADD_NEW_CLIENT->value,
          'actionable_type' => Client::class,
          'actionable_id' => $client->id,
          'note' => "تم إضافة عميل جديد: {$client->name} - هاتف: {$client->phone}",
          'meta' => json_encode([
            'client_id' => $client->id,
            'phone' => $client->phone,
            'name' => $client->name,
          ]),
          'ip' => request()->ip(),
          'source' => 'web',
        ]);

        // 2. إنشاء جلسة
        $session = Sation::create([
          'client_id' => $client->id,
          'persons' => $persons,
          'start_time' => now(),
          'status' => 'active',
        ]);

        // Log → بدء جلسة جديدة
        SystemAction::create([
          'user_id' => Auth::id(),
          'action' => SystemActionType::START_SESSION->value,
          'actionable_type' => Sation::class,
          'actionable_id' => $session->id,
          'note' => "بدء جلسة جديدة للعميل: {$client->name}",
          'meta' => json_encode([
            'persons' => $persons,
            'client_id' => $client->id,
          ]),
          'ip' => request()->ip(),
          'source' => 'web',
        ]);

      } catch (\Exception $e) {
        return to_route("error.create");
      }
      return redirect()->route('session.index-manager')->with('success', 'تم إضافة الجلسة بنجاح');

      //=============================================== go to session index

    }
    //check if the client_phone is provided
    //if provided s
    //if not, create a new client with request values
    //fetch the client_id by phone
    //save client_id in the in variable 
    //start session by this client_id
    //go route to session.index-manager with success message



    //   $client = Sation::create([
    //   'client_id' => $request->client_id,
    //   'hours' => 0,
    //   'rate_per_hour' => 0,
    // ]);
  }




  public function checkout(Request $request, Sation $session, ShiftService $shiftService)
  {
    // ✅ تحقق أولًا هل الجلسة ما زالت active
    if ($session->status !== 'active') {
      return redirect()->back()
        ->with('error', '⚠️ هذه الجلسة تم إنهاؤها مسبقًا.');
    }


    // ✅ تحقق من وجود شيفت مفتوح
    $user = Auth::user();
    $openShift = Shift::where('user_id', $user->id)->whereNull('end_time')->first();
    $isAdmin = $user->hasRole('admin');
    if (!$openShift && !$isAdmin) {
      session()->flash('shift_required', true);

      return redirect()->back()
        ->with('error', '⚠️ لا يوجد شيفت مفتوح، ابدأ شيفت أولاً.');
    }

    $request->validate([
      'hours' => 'required|numeric|min:0',
      'hourly_rate' => 'required|numeric|min:0',
      'purchases' => 'nullable|string',
      
    ]);
    $hours = $request->input('hours');
    $hourlyRate = $request->input('hourly_rate');
    $purchases = json_decode($request->input('purchases', '[]'), true);

    // تحقق إضافي: إذا الساعات صفر والمشتريات فارغة → منع العملية
    if ($hours == 0 && empty($purchases)) {
      return redirect()->back()
        ->with('error', 'لا يمكن إنهاء الجلسة: لم يتم قضاء أي ساعة ولم توجد مشتريات.');
    }
    if (!$hours == 0 && $hourlyRate == 0 && empty($purchases)) {
      return redirect()->back()
        ->with('error', 'سعر الساعه للنظام صفر');
    }
 if (!empty($purchases)) {
      foreach ($purchases as $idx => $item) {
        // توقع الحقول: product_id, qty, price, name, cost (حسب ما ترسل الواجهة)
        $productId = $item['product_id'] ?? null;
        $qtyRequested = isset($item['qty']) ? intval($item['qty']) : 0;
        $sentPrice = $item['price'] ?? null;

        if (empty($productId)) {
          return redirect()->back()
            ->with('error', "⚠️ أحد عناصر المشتريات لا يحتوي على product_id (عنصر رقم {$idx}).")
            ->withInput();
        }

        $product = Product::find($productId);
        if (!$product) {
          return redirect()->back()
            ->with('error', "⚠️ المنتج رقم {$productId} غير موجود حالياً.")
            ->withInput();
        }

        // تأكد الكمية المطلوبة موجبة
        if ($qtyRequested <= 0) {
          return redirect()->back()
            ->with('error', "⚠️ الكمية المطلوبة للمنتج {$product->name} غير صحيحة.")
            ->withInput();
        }

        // تأكد توفر الكمية في المخزون
        if ($product->quantity < $qtyRequested) {
          return redirect()->back()
            ->with('error', "⚠️ الكمية المطلوبة من المنتج \"{$product->name}\" غير متوفرة (المطلوب: {$qtyRequested}، المتوفر: {$product->quantity}).")
            ->withInput();
        }

        // تحقق مطابقة السعر المرسل مع السعر في DB (اختياري لكن مفيد لمنع تغير الأسعار أثناء العملية)
        if (!is_null($sentPrice) && floatval($sentPrice) !== floatval($product->price)) {
          return redirect()->back()
            ->with('error', "⚠️ سعر المنتج \"{$product->name}\" تغير (المرسل: {$sentPrice}، المسجل: {$product->price}). الرجاء تحديث الصفحة والمحاولة مجددًا.")
            ->withInput();
        }
      }
    }
    $invoice = DB::transaction(function () use ($session, $hours, $hourlyRate, $purchases, $openShift) {

      // 1. إنشاء الفاتورة
      $invoice = Invoice::create([
        'invoice_number' => InvoiceNumber::next(),
        'client_id' => $session->client_id,
        'type' => 'session',
        'total' => 0,
      ]);


      // 2. إضافة ساعات الجلسة
      $sessionItemTotal = $hours * $hourlyRate;
      if ($hours > 0) {
        InvoiceItem::create([
          'invoice_id' => $invoice->id,
          'item_type' => 'session',
          'session_id' => $session->id,
          'name' => "جلسة رقم {$session->id}",
          'qty' => $hours,
          'price' => $hourlyRate,
          'cost' => 0,
          'total' => $sessionItemTotal,
          'description' => null,
        ]);
      }

    // 3. إضافة المشتريات
$purchasesTotal = 0;        // إجمالي سعر البيع
$purchasesCostTotal = 0;    // إجمالي التكلفة

if (!empty($purchases) && is_array($purchases)) {

    foreach ($purchases as $item) {

        $productId = $item['product_id'] ?? $item['id'] ?? null;
        $qty = $item['qty'] ?? 1;

        if (!$productId) continue;

        // جلب المنتج
        $product = Product::find($productId);
        if (!$product) continue;

        // ❌ تأكد الكمية المطلوبة أقل من المتاحة
        if ($qty > $product->quantity) {
            return redirect()->back()->with(
                'error',
                "المنتج {$product->name} متوفر منه فقط {$product->quantity}، والمطلوب {$qty}."
            );
        }

        // بيانات الحساب
        $price = $product->price;
        $cost = $product->cost;
        $itemTotal = $price * $qty;      // ← إجمالي سعر البيع للصنف
        $itemCostTotal = $cost * $qty;   // ← إجمالي التكلفة للصنف

        // إجماليات كل المشتريات
        $purchasesTotal += $itemTotal;
        $purchasesCostTotal += $itemCostTotal;

        // 🧾 إضافة عنصر الفاتورة
        InvoiceItem::create([
            'invoice_id'      => $invoice->id,
            'item_type'       => 'product',
            'product_id'      => $product->id,
            'subscription_id' => null,
            'booking_id'      => $booking->id ?? null,
            'session_id'      => null,
            'name'            => $product->name,
            'qty'             => $qty,
            'price'           => $price,
            'cost'            => $cost,            // ← تكلفة الوحدة
            'total'           => $itemTotal,       // ← إجمالي السعر (بيع)
            'description'     => 'منتج مضاف عند إنهاء الحجز',
        ]);

        // 🧮 خصم الكمية من المخزون
        $product->decrement('quantity', $qty);

        if ($product->quantity < 0) {
            $product->quantity = 0;
            $product->save();
        }
    }

      }
$total=$sessionItemTotal+=$purchasesTotal;
      // 4. تحديث الفاتورة بالمجموع النهائي
      $invoice->update(['total' => $total,'profit'=>$total-$purchasesCostTotal]);

      // 5. تحديث حالة الجلسة إذا كانت هناك ساعات أو مشتريات

      $session->update([
        'status' => 'closed',
        'end_time' => now(),
      ]);

      // 6. تسجيل زيارة العميل
      Visit::create([
        'client_id' => $session->client_id,
      ]);
      SystemAction::create([
        'user_id' => Auth::id(),
        'action' => SystemActionType::SESSION_CHECKOUT->value, // أو استخدم قيمة تناسبك
        'actionable_type' => Sation::class,
        'actionable_id' => $session->id,
        'invoice_id' => $invoice->id,
        'shift_id' => $openShift?->id,
        'amount' => $invoice->total,
        'note' => "إنهاء جلسة رقم : {$session->id} - فاتورة : {$invoice->invoice_number} : للعميل : {$session->client->name}",
        'meta' => json_encode([
          'hours' => $hours,
          'hourly_rate' => $hourlyRate,
          'session_item_total' => $sessionItemTotal,
          'purchases' => $purchases,
          'old_status' => 'active',
          'new_status' => 'closed',
        ]),
        'ip' => request()->ip(),
        'source' => 'web',
      ]);
      return $invoice;
    });


    if ($invoice && !$isAdmin) {
      $shiftService->logAction(
        'end_session',
        $invoice->id,
        $invoice->total,
        null,
        "إنهاء جلسة رقم {$session->id}"
      );
    }
    if ($invoice && $isAdmin) {
      $shiftService->logAction(
        'end_session',
        $invoice->id,
        $invoice->total,
        null,
        "إنهاء جلسة رقم {$session->id}"
      );
    }

    $clientName = $session->client->name;

    return redirect()->route('session.index-manager')
      ->with('success', "✅ تم إنهاء الجلسة للعميل {$clientName}");
  }



  public function deleteEmpty(Sation $session, Request $request)
  {
    $user = auth()->user();

    // تحقق أولًا إذا الجلسة فارغة
    if ($session->hours > 0 || $session->purchases()->count() > 0) {
      return redirect()->back()->with('error', '⚠️ الجلسة ليست فارغة، لا يمكن حذفها.');
    }

    DB::beginTransaction();
    try {
      // جهّز بيانات الـ meta (مثلاً القيم القديمة قبل الحذف)
      $meta = [
        'old_values' => [
          'id' => $session->id,
          'hours' => $session->hours,
          'purchases_count' => $session->purchases()->count(),
          'created_at' => optional($session->created_at)?->toDateTimeString(),
          'extra' => $session->toArray(), // لو حابب تحفظ نسخة كاملة
        ],
        'user_agent' => $request->header('User-Agent'),
      ];

      // ابحث عن شيفت مفتوح للمستخدم (لو عندكم لوجيك مختلف غيّره)
      $openShift = Shift::where('user_id', $user->id)->whereNull('end_time')->first();

      // تسجيل الـ system action
      $action = SystemAction::create([
        'user_id' => $user->id,
        'action' => SystemActionType::DELETE_SESSION->value, // أو (string)SystemActionType::DELETE_SESSION
        'actionable_type' => Sation::class,
        'actionable_id' => $session->id,
        'invoice_id' => null,
        'shift_id' => $openShift?->id ?? null,
        'amount' => null,
        'note' => 'حذف جلسة فارغة للعميل : ' . $session->client->name,
        'meta' => json_encode($meta),
        'ip' => $request->ip(),
        'source' => 'web',
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
      ]);

      // الآن احذف الجلسة
      $session->delete();

      DB::commit();

      return redirect()->route('session.index-manager')
        ->with('success', '✅ تم حذف الجلسة الفارغة بنجاح.');
    } catch (\Throwable $e) {
      DB::rollBack();
      // لو حابب تحفظ خطأ في اللوج أو ترجع رسالة للمستخدم
      \Log::error('deleteEmpty error: ' . $e->getMessage(), [
        'user_id' => $user?->id,
        'session_id' => $session->id ?? null,
      ]);

      return redirect()->back()
        ->with('error', '⚠️ حدث خطأ أثناء محاولة حذف الجلسة. المرجو المحاولة لاحقًا.');
    }
  }


  public function search(Request $request)
  {
    $query = $request->get('query');

    $sessions = Sation::with('client')
      ->where('status', 'active') // ✅ يرجع بس الـ active
      ->when($query, function ($q) use ($query) {
        $q->where(function ($q2) use ($query) {
          if (is_numeric($query)) {
            // لو المستخدم دخل رقم → اعتبره client_id
            $q2->orWhere('client_id', $query);
          }

          // بحث بالاسم أو التليفون للعميل
          $q2->orWhereHas('client', function ($c) use ($query) {
            $c->where('name', 'LIKE', "%{$query}%")
              ->orWhere('phone', 'LIKE', "%{$query}%");
          });
        });
      })
      ->get();

    return response()->json($sessions);
  }public function updateStartTime(Request $request, Sation $session)
{
    $request->validate([
        'start_time' => 'required|date',
    ]);

    // تحقق إن الجلسة مفتوحة — اعتبارات متعددة لحالة الإغلاق
    if ($session->end_time !== null
        || ($session->status ?? '') === 'finished'
        || ($session->is_finished ?? false)
    ) {
        return response()->json([
            'status' => 'error',
            'message' => '⚠️ هذه الجلسة تم إنهاؤها من قبل، لا يمكن التعديل عليها.',
        ], 400);
    }

    $oldStart = $session->start_time ? Carbon::parse($session->start_time)->toIso8601String() : null;
    $newStart = Carbon::parse($request->start_time)->toIso8601String();

    DB::beginTransaction();

    try {
        $session->update(['start_time' => $request->start_time]);

        $user = Auth::user();
        $openShift = Shift::where('user_id', $user->id)
            ->whereNull('end_time')
            ->first();

        SystemAction::create([
            'user_id' => $user->id,
            'action' => SystemActionType::EDIT_SESSION_TIME->value,
            'actionable_type' => Sation::class,
            'actionable_id' => $session->id,
            'note' => "تعديل موعد الجلسة رقم {$session->id}",
            'meta' => json_encode([
                'old_start_time' => $oldStart,
                'new_start_time' => $newStart,
            ]),
            'shift_id' => $openShift?->id,
            'ip' => $request->ip(),
            'source' => 'web',
        ]);

        DB::commit();

        return response()->json([
            'status' => 'success',
            'message' => '✅ تم تعديل موعد الجلسة بنجاح',
            'start_time' => $newStart,
        ]);

    } catch (\Throwable $e) {
        DB::rollBack();
        \Log::error('updateStartTime error: ' . $e->getMessage());

        return response()->json([
            'status' => 'error',
            'message' => '⚠️ حدث خطأ أثناء تعديل موعد الجلسة، حاول لاحقًا.',
        ], 500);
    }
}

public function adjustStartTime(Request $request, Sation $session)
{
    $request->validate([
        'amount' => 'required|integer|min:1',
        'unit' => 'required|in:minutes,hours',
        'direction' => 'required|in:forward,backward',
    ]);

    // تحقق إن الجلسة مفتوحة — نفس الشروط كـ updateStartTime
  if ($session->end_time !== null || $session->status === 'closed') {
        return response()->json([
            'status' => 'error',
            'message' => '⚠️ هذه الجلسة تم إنهاؤها من قبل، لا يمكن التعديل عليها.',
        ], 400);
    }

    // تأكد أن هناك start_time صالح
    if (empty($session->start_time)) {
        return response()->json([
            'status' => 'error',
            'message' => '⚠️ لا يوجد وقت بداية مسجل لهذه الجلسة، لا يمكن التعديل.',
        ], 400);
    }

    $oldStart = Carbon::parse($session->start_time);
    $newStart = $oldStart->copy();

    // تعديل الوقت حسب الاتجاه
    if ($request->direction === 'forward') {
        $newStart->{$request->unit === 'minutes' ? 'addMinutes' : 'addHours'}($request->amount);
    } else {
        $newStart->{$request->unit === 'minutes' ? 'subMinutes' : 'subHours'}($request->amount);
    }

    DB::beginTransaction();
    try {
        $session->update([
            'start_time' => $newStart->toDateTimeString(),
        ]);

        $user = Auth::user();
        $openShift = Shift::where('user_id', $user->id)
            ->whereNull('end_time')
            ->first();

        SystemAction::create([
            'user_id' => $user->id,
            'action' => SystemActionType::EDIT_SESSION_TIME->value,
            'actionable_type' => Sation::class,
            'actionable_id' => $session->id,
            'note' => "تعديل موعد الجلسة رقم {$session->id} بـ {$request->amount} {$request->unit} ({$request->direction})",
            'meta' => json_encode([
                'old_start_time' => $oldStart->toIso8601String(),
                'new_start_time' => $newStart->toIso8601String(),
                'direction' => $request->direction,
            ]),
            'shift_id' => $openShift?->id,
            'ip' => request()->ip(),
            'source' => 'web',
        ]);

        DB::commit();

        return response()->json([
            'status' => 'success',
            'message' => '✅ تم تعديل الموعد بنجاح، سيتم تحديث الصفحة...',
            'start_time' => $newStart->toIso8601String(),
        ]);
    } catch (\Throwable $e) {
        DB::rollBack();
        \Log::error('adjustStartTime error: ' . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => '❌ حدث خطأ أثناء تعديل الموعد، حاول مجددًا.',
        ], 500);
    }
}




}
