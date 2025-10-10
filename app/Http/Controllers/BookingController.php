<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingDeposit;
use App\Models\Client;
use App\Models\Hall;
use App\Models\ImportantProduct;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Shift;
use App\Models\VenuePricing;
use App\Services\BookingConflictService;
use App\Services\PricingService;
use App\Services\ShiftService;
use App\Support\InvoiceNumber;
use Carbon\Carbon;
use DB;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Schema;

class BookingController extends Controller
{


  public function byDate(Request $request)
  {
    $date = $request->get('date'); // شكل: YYYY-MM-DD
    $dayStart = Carbon::parse($date)->startOfDay();
    $dayEnd = Carbon::parse($date)->endOfDay();

    $bookings = Booking::with('hall', 'client')
      ->activeStatuses()
      ->where(function ($q) use ($dayStart, $dayEnd) {
        $q->where('start_at', '<=', $dayEnd)
          ->where('end_at', '>=', $dayStart);
      })
      ->orderBy('start_at')
      ->get();

    // ممكن تختصر الحقول كما في الـ front-end
    return response()->json($bookings->map(function ($b) {
      return [
        'id' => $b->id,
        'title' => $b->title,
        'hall' => ['id' => $b->hall_id, 'name' => $b->hall->name ?? null],
        'client' => $b->client ? ['id' => $b->client->id, 'name' => $b->client->name] : null,
        'start_at' => $b->start_at,
        'end_at' => $b->end_at,
        'status' => $b->status,
      ];
    }));
  }

  public function show($id, PricingService $pricingService)
  {
    $booking = Booking::with([
      'client',
      'hall',
      'deposits',
      'purchases.product',
    ])->findOrFail($id);

    // مجموع الدفعة المقدمة
    $deposit_paid = $booking->deposits->sum('amount');

    $actual_duration = null;
    $real_total = null;

    if ($booking->real_start_at) {
      $endTime = $booking->real_end_at ?? now();

      $actual_duration = Carbon::parse($booking->real_start_at)
        ->diffInMinutes($endTime);

      // هذا يحسب تكلفة الساعات (بدون مشتريات)
      $real_total = $pricingService
        ->setBase($booking->base_hour_price, $booking->extra_person_hour_price)
        ->total(
          $booking->attendees,
          $booking->min_capacity_snapshot,
          $actual_duration
        );
    }

    // احسب مجموع المشتريات من الموديل (price * qty)
    $purchases = $booking->purchases ?? collect();
    $purchases_total = $purchases->sum(function ($purchase) {
      return ($purchase->product->price ?? 0) * ($purchase->quantity ?? 0);
    });

    // المتبقي للدفع (لو فيه real_total نستخدمه بدل estimated_total)
    // ملاحظة: إذا estimated_total يشمل المشتريات، نخصم المشتريات للحصول على جزء الساعات
    $total_for_calc = $real_total ?? $booking->estimated_total;

    // محاولة استخراج "سعر الساعات" عندما لا يوجد real_total:
    if ($real_total !== null) {
      $hours_total = $real_total;
    } else {
      // نفترض estimated_total قد يشمل المشتريات — نطرحها للحصول على جزء الساعات
      $possible_hours = $booking->estimated_total - $purchases_total;
      // حماية من القيم السالبة — استخدم estimated_total كبديل
      $hours_total = $possible_hours > 0 ? $possible_hours : $booking->estimated_total;
    }

    $combined_actual = $hours_total + $purchases_total;

    $remaining = $combined_actual - $deposit_paid;
    $extraPersonHourPrice = $booking->base_hour_price / 2;
    $importantProducts = ImportantProduct::get();
    $bookingHourPrice = $pricingService->readPerHour($booking->attendees, $booking->hall->min_capacity, $booking->base_hour_price, $extraPersonHourPrice);
    return view('bookings.show', compact(
      'booking',
      'deposit_paid',
      'actual_duration',
      'real_total',
      'total_for_calc',
      'remaining',
      'purchases',
      'importantProducts',
      'bookingHourPrice',
      'purchases_total',
      'hours_total',
      'combined_actual'
    ));
  }



  public function index_manager(Request $request)
  {
    $query = Booking::with(['hall', 'client'])
      ->whereNotIn('status', ['finished', 'cancelled']);

    // بحث بالكلمة
    if ($request->filled('q')) {
      $q = $request->q;
      $query->where(function ($sub) use ($q) {
        $sub->where('title', 'like', "%$q%")
          ->orWhereHas('client', function ($c) use ($q) {
            $c->where('name', 'like', "%$q%")
              ->orWhere('phone', 'like', "%$q%")
              ->orWhere('id', $q);
          });
      });
    }

    // فلتر بالحالة
    if ($request->filled('status') && $request->status !== 'all') {
      if ($request->status === 'due_or_in_progress') {
        $query->whereIn('status', ['due', 'in_progress']);
      } else {
        $query->where('status', $request->status);
      }
    }

    // فلتر بالتاريخ
    if ($request->filled('from')) {
      $query->whereDate('start_at', '>=', $request->from);
    }
    if ($request->filled('to')) {
      $query->whereDate('start_at', '<=', $request->to);
    }

    $bookings = $query->latest()->paginate(10)->withQueryString();

    return view('bookings.index-manager', compact('bookings'));
  }

  public function index()
  {
    // لجلب بيانات افتراضية (plans في صفحة الاشتراكات) — هنا نقدر نجيب القاعات لو حبّيت فلتر
    $halls = Hall::all();
    // نجيب كل الحجوزات (لمرة التحميل المبكر) — لكن الواجهة بتعرض "جاري التحميل..." ثم JS يجلب عبر AJAX
    $bookings = Booking::with(['client', 'hall'])->orderByDesc('start_at')->get();

    return view('bookings.index', compact('halls', 'bookings'));
  }
  public function ajaxSearch(Request $request)
  {
    $query = Booking::with(['hall', 'client']);

    // لا نريد أبداً إرجاع الحجوزات المنتهية أو الملغاة
    $query->whereNotIn('status', ['finished', 'cancelled']);

    // 1) كلمة البحث العامة (title, client.name, client.phone, hall.name, or exact date)
    if ($request->filled('q')) {
      $q = $request->q;
      $query->where(function ($sub) use ($q) {
        $sub->where('title', 'like', "%{$q}%")
          ->orWhereHas('client', function ($c) use ($q) {
            $c->where('name', 'like', "%{$q}%")
              ->orWhere('phone', 'like', "%{$q}%")
              ->orWhere('id', $q);
          })
          ->orWhereHas('hall', function ($h) use ($q) {
            $h->where('name', 'like', "%{$q}%");
          })
          ->orWhereDate('start_at', $q)
          ->orWhereDate('end_at', $q);
      });
    }

    // 2) تواريخ (من - إلى) — تدعم from/to في querystring
    if ($request->filled('from')) {
      $query->whereDate('start_at', '>=', $request->from);
    }
    if ($request->filled('to')) {
      $query->whereDate('start_at', '<=', $request->to);
    }

    // 3) فلتر القاعات (halls[] يمكن أن يكون مصفوفة أو قيمة واحدة)
    if ($request->filled('halls')) {
      $halls = is_array($request->halls) ? $request->halls : [$request->halls];
      $query->whereIn('hall_id', $halls);
    }

    // 4) حالات (statuses[] — ممكن يختار أكثر من حالة)
    if ($request->filled('statuses')) {
      $statuses = is_array($request->statuses) ? $request->statuses : [$request->statuses];
      // سمحنا بفلترة الحالة لكن نضمن استبعاد finished/cancelled لاحقاً بواسطة whereNotIn أعلاه
      $query->whereIn('status', $statuses);
    }
    // ملاحظة: حتى لو مرر المستخدم حالات تتضمن finished/cancelled، فلن تُعاد لأننا استبعدناهم صراحة.

    // ترتيب النتائج حسب بداية الحجز
    $bookings = $query->orderBy('start_at', 'asc')->get();

    // تبسيط الحقول قبل الإرجاع (خلي JSON صغير وسهل الاستهلاك في الواجهة)
    $data = $bookings->map(function ($b) {
      return [
        'id' => $b->id,
        'title' => $b->title,
        'hall_id' => $b->hall_id,
        'hall_name' => $b->hall->name ?? '',
        'client_id' => $b->client_id,
        'client_name' => $b->client->name ?? '',
        'client_phone' => $b->client->phone ?? '',
        'start_at' => optional($b->start_at)->toIso8601String(),
        'end_at' => optional($b->end_at)->toIso8601String(),
        'date' => optional($b->start_at)->toDateString(),
        'time_from' => optional($b->start_at)->format('H:i'),
        'time_to' => optional($b->end_at)->format('H:i'),
        'status' => $b->status,
        'attendees' => $b->attendees ?? 0,
        'estimated_total' => (float) ($b->estimated_total ?? 0),
      ];
    });

    return response()->json($data);
  }
  public function ajaxSearchManager(Request $request)
{
    try {
        $query = Booking::with(['hall', 'client'])
            ->whereNotIn('status', ['finished', 'cancelled']);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhereHas('client', function ($c) use ($q) {
                        $c->where('name', 'like', "%{$q}%")
                          ->orWhere('phone', 'like', "%{$q}%")
                          ->orWhere('id', $q);
                    })
                    ->orWhereHas('hall', function ($h) use ($q) {
                        $h->where('name', 'like', "%{$q}%");
                    })
                    ->orWhereDate('start_at', $q)
                    ->orWhereDate('end_at', $q);
            });
        }

        if ($request->filled('from')) {
            $query->whereDate('start_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('start_at', '<=', $request->to);
        }

        if ($request->filled('halls')) {
            $halls = is_array($request->halls) ? $request->halls : [$request->halls];
            $query->whereIn('hall_id', $halls);
        }

        if ($request->filled('statuses')) {
            $statuses = is_array($request->statuses) ? $request->statuses : [$request->statuses];
            $query->whereIn('status', $statuses);
        }

        $bookings = $query->orderBy('start_at', 'asc')->get();

        $inProgress = $bookings->filter(fn($b) => $b->status === 'in_progress');
        $due        = $bookings->filter(fn($b) => $b->status === 'due');
        $scheduled  = $bookings->filter(fn($b) => $b->status === 'scheduled');

        $uniqueScheduled = $scheduled->filter(fn($b) => $b->client_id !== null)
                                     ->unique('client_id')
                                     ->values();

        $withoutClientScheduled = $scheduled->filter(fn($b) => $b->client_id === null);

        $finalScheduled = $uniqueScheduled->concat($withoutClientScheduled);

        $finalBookings = $inProgress->concat($due)->concat($finalScheduled);

        $data = $finalBookings->map(function ($b) {
            return [
                'id' => $b->id,
                'title' => $b->title,
                'hall_id' => $b->hall_id,
                'hall_name' => $b->hall->name ?? '',
                'client_id' => $b->client_id,
                'client_name' => $b->client->name ?? '',
                'client_phone' => $b->client->phone ?? '',
                'start_at' => optional($b->start_at)->toIso8601String(),
                'end_at' => optional($b->end_at)->toIso8601String(),
                'date' => optional($b->start_at)->toDateString(),
                'time_from' => optional($b->start_at)->format('H:i'),
                'time_to' => optional($b->end_at)->format('H:i'),
                'status' => $b->status,
                'attendees' => $b->attendees ?? 0,
                'estimated_total' => (float) ($b->estimated_total ?? 0),
            ];
        });

        return response()->json($data);
    } catch (\Throwable $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}



  public function checkConflict(Request $request, BookingConflictService $conflicts)
  {
    $request->validate([
      'hall_id' => ['required', 'exists:halls,id'],
      'start_at' => ['required', 'date'],
      'duration_minutes' => ['required', 'integer', 'min:30'],
    ]);

    try {
      $hallId = $request->hall_id;
      $start = Carbon::parse($request->start_at);
      $end = (clone $start)->addMinutes($request->duration_minutes);
      $excludeId = $request->get('exclude_booking_id');

      // 1) احتفظ باستدعاء الـ service الأصلي (لو عنده منطق إضافي)
      $hasConflict = $conflicts->hasConflict($hallId, $start, $end);

      // 2) لو الـ service ما لاقاش تعارض نرجع كما في القديم
      if (!$hasConflict) {
        return response()->json([
          'conflict' => false,
          'message' => 'لا يوجد تعارض في الميعاد'
        ]);
      }

      // 3) بما إن الـ service رجع تعارض، نعيد الحجوزات المتعارضة مع دعم استبعاد الحجز الحالي
      $query = Booking::where('hall_id', $hallId)
        ->where(function ($q) use ($start, $end) {
          $q->where('start_at', '<', $end)
            ->where('end_at', '>', $start);
        });

      // استبعاد حجز (لو وُجد exclude_booking_id)
      if ($excludeId) {
        $query->where('id', '!=', $excludeId);
      }

      // نطبق نفس فلترة الحالات الفعّالة كما في النسخة المحدثة
      if (method_exists(Booking::class, 'scopeActiveStatuses')) {
        $query = $query->activeStatuses();
      } else {
        $query = $query->whereIn('status', ['scheduled', 'due', 'in_progress']);
      }

      $conflictingBookings = $query->get();

      // لو لم يتبقى أي حجز متعارض بعد الاستبعاد نعيد conflict=false (هذا يغطي حالة أن التعارض الوحيد كان هو الحجز نفسه)
      if ($conflictingBookings->isEmpty()) {
        return response()->json([
          'conflict' => false,
          'message' => 'لا يوجد تعارض في الميعاد'
        ]);
      }

      // وإلا نعيد النتائج كما في النسخة الجديدة
      return response()->json([
        'conflict' => true,
        'bookings' => $conflictingBookings
      ]);
    } catch (\Throwable $e) {
      // حافظت على سلوك النسخة القديمة عند حدوث استثناء (redirect إلى صفحة الخطأ)
      return redirect()
        ->route('error.create', ['message' => $e->getMessage()]);
    }
  }
  public function update(Request $request, Booking $booking, PricingService $pricing, BookingConflictService $conflicts)
  {
    // 1) السماح بالتعديل فقط إذا كانت الحالة scheduled أو due
    if (!in_array($booking->status, ['scheduled', 'due'])) {
      return back()->with('error', 'لا يمكن تعديل هذا الحجز لأن حالته ليست "scheduled" أو "due".');
    }

    $data = $request->validate([
      'hall_id' => ['required', 'exists:halls,id'],
      'client_id' => ['nullable', 'exists:clients,id'],
      'client_name' => [
        'nullable',
        'string',
        'regex:/^[\pL\s]+$/u',
        'min:3',
        'max:50',
        'required_without:client_id',
      ],
      'client_phone' => [
        'nullable',
        'regex:/^(010|011|012|015)[0-9]{8}$/',
        'required_without:client_id',
      ],
      'title' => ['nullable', 'string', 'max:255'],
      'attendees' => [
        'required',
        'integer',
        'min:1',
        function ($attribute, $value, $fail) use ($request) {
          $hall = Hall::find($request->hall_id);
          if ($hall && $value > $hall->max_capacity) {
            $fail("عدد الأفراد لا يمكن أن يتخطى السعة القصوى للقاعة ({$hall->max_capacity}).");
          }
        }
      ],
      'start_at' => ['required', 'date'],
      'duration_minutes' => ['required', 'integer', 'min:30', 'max:720'],
      'status' => ['required', 'in:scheduled,due'],
    ], [
      'client_name.required_without' => 'من فضلك أدخل اسم العميل إذا لم تختَر عميلًا موجودًا',
      'client_phone.required_without' => 'من فضلك أدخل رقم الهاتف إذا لم تختَر عميلًا موجودًا',
    ]);

    try {
      // تحويل التواريخ وحساب النهاية
      $start = Carbon::parse($data['start_at']);
      $end = (clone $start)->addMinutes((int) $data['duration_minutes']);

      // حاول إيجاد عميل مطابق لو لم يحدد client_id (لتجنّب إنشاء مكرر)
      $incomingClientId = $data['client_id'] ?? null;
      $foundExistingClient = null;
      if (empty($incomingClientId) && !empty($data['client_name'])) {
        $queryClient = Client::where('name', $data['client_name']);
        if (!empty($data['client_phone'])) {
          $queryClient->where('phone', $data['client_phone']);
        }
        $foundExistingClient = $queryClient->first();
        if ($foundExistingClient) {
          $incomingClientId = $foundExistingClient->id;
        }
      }

      // تأكد من وجود سعر أساسي (نفس سلوك الـ store)
      if (VenuePricing::get()->isNotEmpty()) {
        $base = DB::table('venue_pricing')->value('base_hour_price');
      } else {
        return back()->withInput()->with('error', 'لا يوجد سعر اساسي للساعة حتى الآن');
      }

      // حساب السعر المقدر الجديد (نحتاجه للمقارنة)
      $hallForCalc = Hall::find($data['hall_id']);
      $minCapacity = $hallForCalc->min_capacity ?? $booking->min_capacity_snapshot;
      $estimated = $pricing->setBase($base)->total(
        $data['attendees'],
        $minCapacity,
        $data['duration_minutes']
      );

      // ----- مقارنة بالقيم الحالية للتأكد إن في تغييرات فعلًا -----
      $current = [
        'hall_id' => (int) $booking->hall_id,
        'client_id' => $booking->client_id ? (int) $booking->client_id : null,
        'title' => (string) $booking->title,
        'attendees' => (int) $booking->attendees,
        'start_at' => Carbon::parse($booking->start_at)->toDateTimeString(),
        'duration_minutes' => (int) $booking->duration_minutes,
        'end_at' => Carbon::parse($booking->end_at)->toDateTimeString(),
        'status' => (string) $booking->status,
        'estimated_total' => number_format((float) $booking->estimated_total, 2, '.', ''),
      ];

      $proposed = [
        'hall_id' => (int) $data['hall_id'],
        'client_id' => $incomingClientId ? (int) $incomingClientId : null,
        'title' => (string) ($data['title'] ?? ''),
        'attendees' => (int) $data['attendees'],
        'start_at' => $start->toDateTimeString(),
        'duration_minutes' => (int) $data['duration_minutes'],
        'end_at' => $end->toDateTimeString(),
        'status' => (string) $data['status'],
        'estimated_total' => number_format((float) $estimated, 2, '.', ''),
      ];

      // قارن الحقول الأساسية
      $allSame = true;
      foreach ($proposed as $k => $v) {
        if ($current[$k] !== $v) {
          $allSame = false;
          break;
        }
      }

      if ($allSame) {
        // لا تغييرات — نُرجع رسالة حذرة للمستخدم
        return back()->withInput()->with('error', 'لم يتم إجراء أي تغييرات — تفاصيل الحجز مطابقة للمدخلات.');
      }

      // ----- تحقق أولي من التعارض (نستبعد الحجز الحالي) ----- (قبل المعاملة)
      $conflictQuery = Booking::where('hall_id', $data['hall_id'])
        ->where('id', '!=', $booking->id)
        ->where(function ($q) use ($start, $end) {
          $q->where('start_at', '<', $end)
            ->where('end_at', '>', $start);
        });

      if (method_exists(Booking::class, 'scopeActiveStatuses')) {
        $conflictQuery = $conflictQuery->activeStatuses();
      } else {
        $conflictQuery = $conflictQuery->whereIn('status', ['scheduled', 'due', 'in_progress']);
      }

      if ($conflictQuery->exists()) {
        return back()->withInput()->with('error', 'تعارض في الميعاد مع حجز آخر بنفس القاعة.');
      }

      // ----- كل شيء واضح: نبدأ معاملة لإنشاء عميل (لو لزم) وتحديث الحجز -----
      DB::beginTransaction();

      // إذا لم يوجد client_id بالفعل وأننا لم نعثر على عميل مطابق، ننشئ واحدًا
      $finalClientId = $incomingClientId;
      if (empty($finalClientId) && !empty($data['client_name'])) {
        $newClient = Client::create([
          'name' => $data['client_name'],
          'phone' => $data['client_phone'] ?? null,
        ]);
        $finalClientId = $newClient->id;
      }

      // إعادة فحص تعارض سريعة داخل المعاملة (حماية ضد كمية صغيرة من race conditions)
      $conflictQuery2 = Booking::where('hall_id', $data['hall_id'])
        ->where('id', '!=', $booking->id)
        ->where(function ($q) use ($start, $end) {
          $q->where('start_at', '<', $end)
            ->where('end_at', '>', $start);
        });

      if (method_exists(Booking::class, 'scopeActiveStatuses')) {
        $conflictQuery2 = $conflictQuery2->activeStatuses();
      } else {
        $conflictQuery2 = $conflictQuery2->whereIn('status', ['scheduled', 'due', 'in_progress']);
      }

      if ($conflictQuery2->exists()) {
        DB::rollBack();
        return back()->withInput()->with('error', 'تعارض في الميعاد (تم العثور على حجز متعارض بعد محاولة التحقق).');
      }

      // حساب السعر مرة أخرى (ضمان اتساق) — قد لا يكون ضرورياً لكن نعيدها للتأكد
      $estimatedFinal = $pricing->setBase($base)->total(
        $data['attendees'],
        $minCapacity,
        $data['duration_minutes']
      );

      // التحديث
      $booking->update([
        'hall_id' => $data['hall_id'],
        'client_id' => $finalClientId,
        'title' => $data['title'] ?? $booking->title,
        'attendees' => $data['attendees'],
        'start_at' => $start,
        'duration_minutes' => $data['duration_minutes'],
        'end_at' => $end,
        'status' => $data['status'],
        // حافظ على base_hour_price القديم إن كان موجودًا، وإلا ضعه من الـ venue pricing
        'base_hour_price' => $booking->base_hour_price ?? $base,
        'extra_person_hour_price' => $booking->extra_person_hour_price ?? ($base / 2),
        'min_capacity_snapshot' => $hallForCalc->min_capacity ?? $booking->min_capacity_snapshot,
        'estimated_total' => $estimatedFinal,
      ]);

      DB::commit();

      return redirect()->route('bookings.show', $booking->id)->with('success', 'تم تحديث الحجز بنجاح.');
    } catch (\Throwable $e) {
      // تأكد من التراجع في حالة حدوث أي خطأ
      DB::rollBack();
      \Log::error('Booking update failed: ' . $e->getMessage(), [
        'booking_id' => $booking->id ?? null,
        'request' => $request->all(),
        'exception' => $e,
      ]);

      // رسالة عامة للمستخدم — لا نكشف تفاصيل الـ exception في الواجهة
      return back()->withInput()->with('error', 'حدث خطأ أثناء تحديث الحجز. تم تسجيل الخطأ وسيتم مراجعته.');
    }
  }




  public function calendar(Request $request)
  {
    $year = (int) $request->input('year', now()->year);
    $month = (int) $request->input('month', now()->month);

    // بداية ونهاية الشهر (بنقاط زمنية كاملة)
    $monthStart = Carbon::create($year, $month, 1)->startOfDay();
    $monthEnd = (clone $monthStart)->endOfMonth()->endOfDay();

    // query: جلب الحجوزات التي تتداخل مع فترة الشهر
    $query = Booking::with(['hall', 'client']);

    // لو عندك scope activeStatuses استخدمه، وإلا استعمل whereIn
    if (method_exists(Booking::class, 'scopeActiveStatuses')) {
      $query = $query->activeStatuses();
    } else {
      $query = $query->whereIn('status', ['scheduled', 'due', 'in_progress']);
    }

    $bookings = $query->where(function ($q) use ($monthStart, $monthEnd) {
      // أي حجز يتداخل مع الشهر (حتى لو بدأ قبل أو أنهى بعد الشهر)
      $q->where('start_at', '<=', $monthEnd)
        ->where('end_at', '>=', $monthStart);
    })
      ->get();

    // نجمع الحجوزات لكل يوم يتداخل فيه الحجز (لو الحجز يمتد لأكثر من يوم نُدخل كل يوم متداخل)
    $grouped = [];

    foreach ($bookings as $b) {
      $from = Carbon::parse($b->start_at)->startOfDay();
      $to = Carbon::parse($b->end_at)->startOfDay();

      // إذا end قبل start (خطأ)، نتأكد من ضبطه
      if ($to->lt($from)) {
        $to = $from;
      }

      // iterate days between from..to
      for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
        // ضع فقط الأيام الموجودة داخل الشهر المطلوب
        if ($d->between($monthStart, $monthEnd)) {
          $day = (int) $d->day;

          // حافظ على نفس شكل البيانات اللي قد تستخدمها في الـ frontend
          $grouped[$day][] = [
            'id' => $b->id,
            'title' => $b->title,
            'hall_id' => $b->hall_id,
            'hall_name' => $b->hall->name ?? null,
            'start_at' => $b->start_at,
            'end_at' => $b->end_at,
            'client' => $b->client ? ['id' => $b->client->id, 'name' => $b->client->name] : null,
            'status' => $b->status,
            // إذا حابب ترجع الكائن الكامل الغي التعليقات وارجع $b->toArray()
            // 'raw' => $b->toArray(),
          ];
        }
      }
    }

    // لو تحب المفاتيح تكون مرتبة أو تضم أيام بدون حجوزات أقدر أعدّل، حاليا نفس سلوكك: نُرجع فقط أيام فيها بيانات
    return response()->json($grouped);
  }


  public function create(Request $request)
  {

    try {
      $halls = Hall::where('is_active', true)->get();
      $sameDayBookings = collect();

      // لو المستخدم اختار قاعة وتاريخ في الفورم
      if ($request->filled(['hall_id', 'start_at'])) {
        $hallId = $request->input('hall_id');
        $date = Carbon::parse($request->input('start_at'))->toDateString();

        $sameDayBookings = Booking::where('hall_id', $hallId)
          ->whereDate('start_at', $date)
          ->orderBy('start_at')
          ->get();
      }

      return view('bookings.create', compact('halls', 'sameDayBookings'));

    } catch (\Throwable $e) {
      return redirect()
        ->route('error.create', ['message' => $e->getMessage()]);
    }
  }

  public function store(
    Request $request,
    PricingService $pricing,
    BookingConflictService $conflicts,
    ShiftService $shiftService
  ) {
    $user = \Auth::user();
    $openShift = Shift::where('user_id', $user->id)->whereNull('end_time')->first();
    $isAdmin = $user->hasRole('admin');
    // validate (بقي كما عندك)
  $data = $request->validate([
    'hall_id' => 'required|exists:halls,id',
    'client_id' => 'nullable|exists:clients,id',
    'client_name' => 'nullable|string|min:3|max:50|required_without:client_id',
    'client_phone' => ['nullable', 'regex:/^(010|011|012|015)[0-9]{8}$/', 'required_without:client_id'],
    'title' => 'nullable|string|max:255',
    'attendees' => [
        'required',
        'integer',
        'min:1',
        function ($attr, $val, $fail) use ($request) {
            $hall = Hall::find($request->hall_id);
            if ($hall && $val > $hall->max_capacity) {
                $fail("عدد الأفراد لا يمكن أن يتخطى السعة القصوى للقاعة ({$hall->max_capacity}).");
            }
        }
    ],
    'start_at_full' => 'required|date|after:now',
    'end_at_full' => 'required|date|after:start_at_full',
    'duration_minutes' => 'required|integer|min:30',
    'status' => 'nullable|in:scheduled,due,in_progress,finished,cancelled',
    'deposit' => 'nullable|numeric|min:0',
    
    // recurrence
    'recurrence_type' => 'nullable|in:none,weekly,biweekly,monthly,custom',
    'recurrence_interval' => 'nullable|integer|min:1',
    'recurrence_end_date' => 'nullable|date|after_or_equal:start_at_full',
], [
    'client_name.required_without' => 'من فضلك أدخل اسم العميل إذا لم تختَر عميلًا موجودًا',
    'client_phone.required_without' => 'من فضلك أدخل رقم الهاتف إذا لم تختَر عميلًا موجودًا',
    'client_phone.regex' => 'رقم الهاتف يجب أن يكون مصري صحيح (11 رقم ويبدأ بـ 010 أو 011 أو 012 أو 015)',
    'attendees.required' => 'عدد الأفراد مطلوب',
    'attendees.integer' => 'عدد الأفراد يجب أن يكون رقم صحيح',
    'attendees.min' => 'عدد الأفراد يجب أن يكون على الأقل 1',
    'start_at_full.after' => 'تاريخ البداية يجب أن يكون بعد الآن',
    'end_at_full.after' => 'تاريخ النهاية يجب أن يكون بعد بداية الحجز',
    'recurrence_type.in' => 'نوع التكرار غير صالح',
    'recurrence_interval.min' => 'المسافة يجب أن تكون على الأقل 1',
    'recurrence_end_date.after_or_equal' => 'تاريخ انتهاء التكرار يجب أن يكون بعد أو في نفس يوم بداية الحجز',
]);

    // تحقق الشيفت
    if (!$openShift && !$isAdmin && !empty($data['deposit']) && $data['deposit'] > 0) {
      session()->flash('shift_required', true);
      return redirect()->back()
        ->with('error', '⚠️ لا يوجد شيفت مفتوح، ابدأ شيفت أولاً.');
    }

    try {
      if (empty($data['client_id']) && empty($data['client_name'])) {
        return back()
          ->withErrors(['client_id' => 'اختر عميل أو أدخل عميل جديد'])
          ->withInput();
      }

      $hall = Hall::findOrFail($data['hall_id']);
      if (VenuePricing::get()->isNotEmpty()) {
        $base = DB::table('venue_pricing')->value('base_hour_price');
      } else {
        return redirect()
          ->route('error.create', ['message' => "لا يوجد سعر اساسي للساعة حتى الأن"]);
      }

      // حساب التواريخ الأساسية
      $start = Carbon::parse($data['start_at_full']);
      $end = (clone $start)->addMinutes((int) $data['duration_minutes']);

      // إعدادات التكرار
      $recurrenceType = $data['recurrence_type'] ?? 'none';
      $recurrenceInterval = isset($data['recurrence_interval']) ? (int) $data['recurrence_interval'] : 1;
      $recurrenceEnd = isset($data['recurrence_end_date']) ? Carbon::parse($data['recurrence_end_date'])->endOfDay() : null;

      // بناء المواعيد
      $occurrences = $this->generateRecurringDates($start, $end, $recurrenceType, $recurrenceInterval, $recurrenceEnd);

      // حماية: حد أقصى للتكرارات
      $MAX_OCCURRENCES = 500;
      if (count($occurrences) === 0) {
        return back()->withInput()->with('error', 'لا يوجد مواعيد مولدة — تحقق من بيانات التكرار.');
      }
      if (count($occurrences) > $MAX_OCCURRENCES) {
        return back()->withInput()->with('error', "عدد التكرارات كبير جدًا (" . count($occurrences) . "). الحد الأقصى: {$MAX_OCCURRENCES}.");
      }

      // فحص التعارضات لكل occurrence قبل DB (atomic check)
      $conflictingDates = [];
      foreach ($occurrences as $occ) {
        $occStart = $occ['start'];
        $occEnd = $occ['end'];
        if ($conflicts->hasConflict($hall->id, $occStart, $occEnd)) {
          $conflictingDates[] = $occStart->format('Y-m-d H:i');
        }
      }
      if (!empty($conflictingDates)) {
        return back()->withInput()->with('error', 'وجد تعارض مع حجوزات حالية على التواريخ التالية: ' . implode(', ', $conflictingDates))
          ->withErrors(['start_at_full' => 'تعارض مع حجز آخر لنفس القاعة']);
      }

      // حساب السعر مرة واحدة (Estimated)
      $estimated = $pricing->setBase($base)->total(
        $data['attendees'],
        $hall->min_capacity,
        $data['duration_minutes']
      );

      // إنشاء عميل جديد لو مش موجود
      if (empty($data['client_id']) && !empty($data['client_name'])) {
        $client = Client::create([
          'name' => $data['client_name'],
          'phone' => $data['client_phone'] ?? null,
        ]);
        $data['client_id'] = $client->id;
      }

      // === هنا التغيير الأساسي: فقط إضافة الـ deposit مرة واحدة ===
      $depositAmount = !empty($data['deposit']) ? (float) $data['deposit'] : 0;
      $depositApplied = false; // علم يفيد ما إذا طبقنا المقدم على أول حجز بالفعل

      DB::beginTransaction();
      $createdCount = 0;

      foreach ($occurrences as $occ) {
        $occStart = $occ['start'];
        $occEnd = $occ['end'];

        $booking = Booking::create([
          'hall_id' => $hall->id,
          'client_id' => $data['client_id'],
          'title' => $data['title'] ?? "لم يضاف",
          'attendees' => $data['attendees'],
          'start_at' => $occStart,
          'duration_minutes' => $data['duration_minutes'],
          'end_at' => $occEnd,
          'status' => $data['status'] ?? 'scheduled',
          'base_hour_price' => $base,
          'extra_person_hour_price' => $base / 2,
          'min_capacity_snapshot' => $hall->min_capacity,
          'estimated_total' => $estimated,
        ]);

        if (!empty($booking)) {
          $createdCount++;

          // إذا فيه مقدم ولم نطبقه بعد => ضعه مرتبطًا بهذا (الأول)
          if ($depositAmount > 0 && !$depositApplied) {
            $invoice = Invoice::create([
              'invoice_number' => InvoiceNumber::next(),
              'client_id' => $booking->client_id,
              'booking_id' => $booking->id,
              'type' => 'booking',
              'total' => $depositAmount,
              'profit' => $depositAmount,
              'notes' => 'فاتورة مقدم الحجز (مرتبط بالحجز الرئيسي)'
            ]);

            InvoiceItem::create([
              'invoice_id' => $invoice->id,
              'item_type' => 'deposit',
              'booking_id' => $booking->id,
              'name' => 'مقدم حجز: ' . $booking->title,
              'qty' => 1,
              'price' => $depositAmount,
              'cost' => 0,
              'total' => $depositAmount,
              'description' => 'مقدم مرتبط بالحجز #' . $booking->id,
            ]);

            BookingDeposit::create([
              'booking_id' => $booking->id, // يربط المقدم بالحجز الأول فقط كما طلبت
              'invoice_id' => $invoice->id,
              'amount' => $depositAmount,
            ]);

            // سجل الحركة على الشيفت (كما في كودك)
            $shiftService->logAction(
              'add_booking',
              $invoice->id,
              $invoice->total ?? $depositAmount,
              null,
              "اضافة حجز واستلام مقدم"
            );

            // علامة أننا طبقنا المقدم بالفعل على الحجز الأول
            $depositApplied = true;
          } else {
            // باقي الحجوزات أو حالة عدم وجود مقدم => أنشئ فاتورة صفرية كما في الكود الأصلي
            Invoice::create([
              'invoice_number' => InvoiceNumber::next(),
              'client_id' => $booking->client_id,
              'booking_id' => $booking->id,
              'type' => 'booking',
              'total' => 0,
              'profit' => 0,
              'notes' => 'فاتورة الحجز'
            ]);
          }
        }
      }

      DB::commit();

      return redirect()
        ->route('bookings.index-manager')
        ->with('success', "تم إضافة {$createdCount} حجز بنجاح.");

    } catch (\Throwable $e) {
      DB::rollBack();
      \Log::error('Error creating recurring bookings: ' . $e->getMessage());
      return redirect()
        ->route('error.create', ['message' => $e->getMessage()]);
    }
  }



  private function generateRecurringDates(Carbon $start, Carbon $end, string $type, int $interval = 1, ?Carbon $recurrenceEnd = null): array
  {
    $occurrences = [];
    // always include the original
    $currentStart = $start->copy();
    $currentEnd = $end->copy();

    // If no recurrence -> just single
    if ($type === 'none') {
      $occurrences[] = ['start' => $currentStart->copy(), 'end' => $currentEnd->copy()];
      return $occurrences;
    }

    // build loop
    $maxIterations = 1000; // protection
    $i = 0;

    while (true) {
      if ($i++ > $maxIterations)
        break;

      // stop condition: if recurrenceEnd is set and currentStart date > recurrenceEnd -> break
      if ($recurrenceEnd && $currentStart->greaterThan($recurrenceEnd))
        break;

      // push
      $occurrences[] = ['start' => $currentStart->copy(), 'end' => $currentEnd->copy()];

      // compute next
      if ($type === 'weekly') {
        $currentStart->addWeeks($interval);
        $currentEnd->addWeeks($interval);
      } elseif ($type === 'biweekly') {
        $currentStart->addWeeks(2 * $interval); // interval usually 1
        $currentEnd->addWeeks(2 * $interval);
      } elseif ($type === 'monthly') {
        // preserve time of day and day-of-month (careful with months shorter)
        $currentStart->addMonthsNoOverflow($interval);
        $currentEnd->addMonthsNoOverflow($interval);
      } elseif ($type === 'custom') {
        // custom means every N weeks
        $currentStart->addWeeks($interval);
        $currentEnd->addWeeks($interval);
      } else {
        // unknown type -> break
        break;
      }

      // If no recurrence_end provided, we should decide when to stop.
      // To avoid infinite loop, we stop after a reasonable number of occurrences (e.g., 200)
      if (!$recurrenceEnd && count($occurrences) >= 200) {
        break;
      }
    }

    return $occurrences;
  }

  public function sameDay(Request $request)
  {
    $hallId = $request->get('hall_id');
    $startAt = Carbon::parse($request->get('start_at_full')); // كامل datetime
    $duration = (int) $request->get('duration_minutes', 0);
    $endAt = (clone $startAt)->addMinutes($duration);

    $conflicts = Booking::with('hall', 'client')
      ->activeStatuses()
      ->where('hall_id', $hallId)
      ->where(function ($q) use ($startAt, $endAt) {
        $q->where('start_at', '<', $endAt)
          ->where('end_at', '>', $startAt);
      })
      ->orderBy('start_at')
      ->get();

    // تَبسيط الحقول
    return response()->json($conflicts->map(function ($b) {
      return [
        'id' => $b->id,
        'title' => $b->title,
        'hall_name' => $b->hall->name ?? null,
        'start_at' => $b->start_at,
        'end_at' => $b->end_at,
        'date' => Carbon::parse($b->start_at)->format('Y-m-d'),
        'client' => $b->client ? ['id' => $b->client->id, 'name' => $b->client->name] : null,
        'status' => $b->status,
      ];
    }));
  }




  public function edit(Booking $booking)
  {
    $halls = Hall::all();
    return view('bookings.edit', compact('booking', 'halls'));
  }



  public function destroy(Booking $booking)
  {
    $booking->delete();
    return redirect()->route('bookings.index')->with('success', 'تم حذف الحجز');
  }
  // app/Http/Controllers/BookingController.php
  public function search(Request $request)
  {
    $query = Booking::query();

    if ($request->filled('q')) {
      $search = $request->q;
      $query->where('title', 'like', "%$search%")
        ->orWhereDate('start_at', $search)
        ->orWhereDate('end_at', $search);
    }

    $bookings = $query->orderBy('start_at', 'asc')->get();

    return response()->json($bookings);
  }


  // بدء الحجز
  public function start(Booking $booking)
  {
    if ($booking->status !== 'scheduled' && $booking->status !== 'due') {
      return back()->with('error', 'لا يمكن بدء هذا الحجز.');
    }

    $booking->update([
      'status' => 'in_progress',
      'real_start_at' => Carbon::now(),
    ]);

    return back()->with('success', 'تم بدء الحجز بنجاح.');
  }


  // داخل الكلاس BookingController
  public function checkout(Request $request, Booking $booking, ShiftService $shiftService)
  {
    // تأكد الحالة الأساسية
    if ($booking->status !== 'in_progress') {
      return back()->with('error', 'لا يمكن إنهاء هذا الحجز لأن حالته ليست "جاري".');
    }

    // تحقق من الشيفت المفتوح (مثلما في store)
    $user = \Auth::user();
    $openShift = Shift::where('user_id', $user->id)->whereNull('end_time')->first();
    $isAdmin = $user->hasRole('admin') ?? false;
    if (!$openShift && !$isAdmin) {
      session()->flash('shift_required', true);
      return back()->with('error', '⚠️ لا يوجد شيفت مفتوح، ابدأ شيفت أولاً.');
    }

    // Validate المدخلات من الفورم (hidden inputs)
    $data = $request->validate([
      'hours_total' => ['nullable', 'numeric', 'min:0'],
      'purchases_total' => ['nullable', 'numeric', 'min:0'],
      'deposit_paid' => ['nullable', 'numeric', 'min:0'],
      'hourly_rate' => ['nullable', 'numeric', 'min:0'],
      'booking' => ['required', 'integer', 'exists:bookings,id'],
    ]);
    $hoursTotal = floatval($data['hours_total'] ?? 0);
    $purchasesTotal = floatval($data['purchases_total'] ?? 0);
    $depositPaid = floatval($data['deposit_paid'] ?? 0);
    $hourlyRate = floatval($data['hourly_rate'] ?? 0);

    DB::beginTransaction();
    try {
      // إجمالي الفاتورة (نفترض نضع كل المبالغ هنا)
      $invoiceTotal = $hoursTotal + $purchasesTotal;
      $hoursTotalWithDepositPaid = $hoursTotal - $depositPaid;
      $hoursTotalWithDepositPaidAndpurchasesTotal = $hoursTotalWithDepositPaid + $purchasesTotal;
      $invoice = Invoice::where('booking_id', $booking->id)->first();


      if ($hoursTotal > 0) {
        InvoiceItem::create([
          'invoice_id' => $invoice->id,
          'item_type' => 'booking',
          'product_id' => null,
          'subscription_id' => null,
          'booking_id' => $booking->id,
          'session_id' => null,
          'name' => 'سعر الساعات للحجز  : ' . $booking->id . 'ناقص المقدم',
          'qty' => 1,
          'price' => $hoursTotalWithDepositPaid,
          'cost' => 0,
          'total' => $hoursTotalWithDepositPaid,
          'description' => 'سعر الساعة: ' . number_format($hourlyRate, 2),
        ]);
      }


      if ($purchasesTotal > 0) {
        InvoiceItem::create([
          'invoice_id' => $invoice->id,
          'item_type' => 'product',
          'product_id' => null,
          'subscription_id' => null,
          'booking_id' => $booking->id,
          'session_id' => null,
          'name' => 'مشتريات - الحجز #' . $booking->id,
          'qty' => 1,
          'price' => $purchasesTotal,
          'cost' => 0,
          'total' => $purchasesTotal,
          'description' => 'ملخص المشتريات عند إنهاء الحجز',
        ]);
      }

      // حدث الحجز: من in_progress إلى finished ووقت النهاية الفعلي
      $booking->update([
        'status' => 'finished',
        'real_end_at' => Carbon::now(),
      ]);

      $invoice->update(['total' => $invoiceTotal, 'profit', $invoiceTotal]);
      if ($invoice && !$isAdmin) {
        $shiftService->logAction(
          'end_booking',
          $invoice->id,
          $hoursTotalWithDepositPaidAndpurchasesTotal,
          null,
          " 'انهاء حجز'.':'. $booking->id"
        );
      }
      if ($invoice && $isAdmin) {
        $shiftService->logAction(
          'end_booking',
          $invoice->id,
          $hoursTotalWithDepositPaidAndpurchasesTotal,
          null,
          " 'انهاء حجز'.':'. $booking->id"
        );
      }


      DB::commit();

      return redirect()->route('bookings.index-manager')
        ->with('success', 'تم إنهاء الحجز وإنشاء الفاتورة بنجاح.');
    } catch (Exception $e) {
      DB::rollBack();
      return redirect()->back()->with('error', 'حدث خطأ أثناء إنهاء الحجز: ' . $e->getMessage());
    }
  }


  public function estimate(Request $request, PricingService $pricing)
  {
    $data = $request->validate([
      'hall_id' => ['required', 'exists:halls,id'],
      'attendees' => ['required', 'integer', 'min:1'],
      'duration_minutes' => ['required', 'integer', 'min:1'],
    ]);

    try {
      // تأكد أن هناك سعر أساسي متوفر
      if (VenuePricing::get()->isNotEmpty()) {
        $base = DB::table('venue_pricing')->value('base_hour_price');
        if ($base === null) {
          return response()->json(['error' => 'لا يوجد سعر أساسي مضبوط حالياً.'], 422);
        }
      } else {
        return response()->json(['error' => 'لا يوجد سعر اساسي للساعة حتى الآن.'], 422);
      }

      $hall = Hall::findOrFail($data['hall_id']);
      $minCapacity = $hall->min_capacity ?? 1;

      // استخدم الService لحساب التقدير
      $estimated = $pricing->setBase((float) $base)->total(
        (int) $data['attendees'],
        (int) $minCapacity,
        (int) $data['duration_minutes']
      );

      // حساب سعر الساعة للعرض (اختياري)
      $perHour = $pricing->readPerHour((int) $data['attendees'], (int) $minCapacity, (int) $base, (int) ($base / 2));

      // رجّع JSON (مع تنسيق رقمى قابل للعرض)
      return response()->json([
        'success' => true,
        'estimated' => round($estimated, 2),
        'estimated_formatted' => number_format($estimated, 2, '.', ','),
        'per_hour' => round($perHour, 2),
        'per_hour_formatted' => number_format($perHour, 2, '.', ','),
        'currency' => 'جنيه'
      ]);
    } catch (\Throwable $e) {
      \Log::error('Estimate pricing failed: ' . $e->getMessage(), ['request' => $request->all()]);
      return response()->json(['error' => 'حدث خطأ أثناء حساب السعر.'], 500);
    }
  }


public function clientBookings(Request $request, $clientId)
{
    // جلب العميل صريحاً بالـ id (سيعطي 404 إذا غير موجود)
    $client = Client::findOrFail($clientId);

    $perPage = 20;
    $bookingsQuery = Booking::with([
        'hall',
        'deposits',
        'purchases.product'
    ])->where('client_id', $client->id)
      ->orderBy('start_at', 'desc');

    $bookingsAll = (clone $bookingsQuery)->get();
    $bookings = $bookingsQuery->paginate($perPage)->withQueryString();

    $statusCounts = Booking::select('status', DB::raw('COUNT(*) as cnt'))
        ->where('client_id', $client->id)
        ->groupBy('status')
        ->pluck('cnt', 'status')
        ->toArray();

    $statuses = ['scheduled','due','in_progress','finished','cancelled'];
    $countsByStatus = [];
    foreach ($statuses as $s) {
        $countsByStatus[$s] = isset($statusCounts[$s]) ? (int)$statusCounts[$s] : 0;
    }

    $depositsTotal = 0;
    if (Schema::hasTable('booking_deposits')) {
        $depositsTotal = DB::table('booking_deposits')
            ->join('bookings', 'booking_deposits.booking_id', '=', 'bookings.id')
            ->where('bookings.client_id', $client->id)
            ->sum('booking_deposits.amount');
    }

    $receivedTotal = Booking::where('client_id', $client->id)
        ->whereNotNull('real_total')
        ->sum('real_total');

    $estimatedTotal = Booking::where('client_id', $client->id)->sum('estimated_total');

    $purchasesTotal = 0;
    if (Schema::hasTable('booking_purchases') && Schema::hasTable('products')) {
        $purchasesTotal = DB::table('booking_purchases')
            ->join('bookings', 'booking_purchases.booking_id', '=', 'bookings.id')
            ->join('products', 'booking_purchases.product_id', '=', 'products.id')
            ->where('bookings.client_id', $client->id)
            ->select(DB::raw('SUM(products.price * booking_purchases.quantity) as total'))
            ->value('total') ?? 0;
    }

    $bookingIds = $bookingsAll->pluck('id')->toArray();

    $depositsPerBooking = [];
    if (Schema::hasTable('booking_deposits') && !empty($bookingIds)) {
        $rows = DB::table('booking_deposits')
            ->select('booking_id', DB::raw('SUM(amount) as total'))
            ->whereIn('booking_id', $bookingIds)
            ->groupBy('booking_id')
            ->get();
        foreach ($rows as $r) $depositsPerBooking[$r->booking_id] = (float)$r->total;
    }

    $purchasesPerBooking = [];
    if (!empty($bookingIds) && Schema::hasTable('booking_purchases') && Schema::hasTable('products')) {
        $rows = DB::table('booking_purchases')
            ->select('booking_purchases.booking_id', DB::raw('SUM(products.price * booking_purchases.quantity) as total'))
            ->join('products', 'booking_purchases.product_id', '=', 'products.id')
            ->whereIn('booking_purchases.booking_id', $bookingIds)
            ->groupBy('booking_purchases.booking_id')
            ->get();
        foreach ($rows as $r) $purchasesPerBooking[$r->booking_id] = (float)$r->total;
    }

    return view('clients.bookings', [
        'client' => $client,
        'bookings' => $bookings,
        'totalBookings' => $bookingsAll->count(),
        'countsByStatus' => $countsByStatus,
        'depositsTotal' => (float)$depositsTotal,
        'receivedTotal' => (float)$receivedTotal,
        'estimatedTotal' => (float)$estimatedTotal,
        'purchasesTotal' => (float)$purchasesTotal,
        'depositsPerBooking' => $depositsPerBooking,
        'purchasesPerBooking' => $purchasesPerBooking,
    ]);
}

}

