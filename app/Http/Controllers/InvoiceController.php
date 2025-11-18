<?php

// app/Http/Controllers/InvoiceController.php
namespace App\Http\Controllers;

use App\Enums\SystemActionType;
use App\Http\Requests\StoreInvoiceRequest;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Shift;
use App\Models\ShiftAction;
use App\Services\ShiftService;
use App\Support\InvoiceNumber;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\SystemAction;
use Schema;
class InvoiceController extends Controller
{

  public function index()
  {
    $invoices = Invoice::with('items')->get();
    return view('invoices.index', compact('invoices'));
  }

  public function show(Invoice $invoice)
  {
    // صلاحيات: عرض الفاتورة للأدمن أو صاحب الفاتورة أو حسب policy


    // eager load relations: items, customer/user, any linked models
    $invoice->load(['items', 'user']); // تأكد أن relations موجودة

    // حسابات ملخص
    $total = $invoice->items->sum('total');
    $cost = $invoice->items->sum('cost') * 1; // مجموع تكلفة الوحدات (cost * qty إذا مخزن غير مضروب)
    // إذا لديك cost مخزن لكل item كمجموع، عدّل حسب الحاجة
    $profit = $total - $cost;

    return view('invoices.show', compact('invoice', 'total', 'cost', 'profit'));

  }
  // public function admin_show(Invoice $invoice)
//   {
//   $invoice->load(['items', 'user']); // تأكد أن relations موجودة

  //     // حسابات ملخص
//     $total = $invoice->items->sum('total');
//     $cost = $invoice->items->sum('cost') * 1; // مجموع تكلفة الوحدات (cost * qty إذا مخزن غير مضروب)
//     // إذا لديك cost مخزن لكل item كمجموع، عدّل حسب الحاجة
//     $profit = $total - $cost;

  //     return view('invoices.admin-show', compact('invoice', 'total', 'cost', 'profit'));

  //   }


  public function client_show(Invoice $invoice)
  {
    // تحميل العلاقات المطلوبة
    $invoice->load([
      'client',
      'items',
      'booking' => function ($query) {
        $query->with([
          'hall', // تحميل قاعة الحجز أو أي علاقة مرتبطة
        ]);
      },
    ]);

    // ترتيب البنود حسب النوع
    $groupedItems = [
      'product' => $invoice->items->where('item_type', 'product'),
      'subscription' => $invoice->items->where('item_type', 'subscription'),
      'booking' => $invoice->items->where('item_type', 'booking'),
      'session' => $invoice->items->where('item_type', 'session'),
      'deposit' => $invoice->items->where('item_type', 'deposit'),
    ];

    // ✅ أي فاتورة فيها منتجات تظهر مشترياتها (تمت استعادتها)
    $purchaseItems = collect();
    if ($groupedItems['product']->isNotEmpty()) {
      $purchaseItems = $groupedItems['product'];
    }

    // حساب الإجمالي الكلي
    $totalAmount = $invoice->items->sum('total');

    // نوع الفاتورة
    $invoiceType = $invoice->type;

    // بيانات إضافية لو نوعها deposit
    $extraData = [];
    if ($invoiceType === 'deposit') {
      $extraData = [
        'client_name' => $invoice->client->name ?? 'غير معروف',
        'booking_date' => optional($invoice->booking)->date ?? '-',
      ];
    }

    // التحقق من وجود مشتريات داخل فاتورة جلسة أو حجز
    $isHasPurchase = false;
    if (
      in_array($invoiceType, ['booking', 'session']) &&
      $invoice->items->where('item_type', 'product')->isNotEmpty()
    ) {
      $isHasPurchase = true;
    }

    // بيانات الحجز لو نوع الفاتورة booking
    $bookingData = null;
    $hourlyRate = null;
    $actualDurationMinutes = null;
    if ($invoiceType === 'booking' && $invoice->booking) {

      $bookingData = $invoice->booking;

      // التأكد إن القيم موجودة قبل الحساب
      if (!empty($bookingData->duration_minutes) && !empty($bookingData->estimated_total)) {
        $hours = $bookingData->duration_minutes / 60;
        if ($hours > 0) {
          $hourlyRate = $bookingData->estimated_total / $hours;
        }
      }
      // حساب المدة الفعلية بالدقائق من real_start_at و real_end_at
      if (!empty($bookingData->real_start_at) && !empty($bookingData->real_end_at)) {
        $start = Carbon::parse($bookingData->real_start_at);
        $end = Carbon::parse($bookingData->real_end_at);

        $secondsDiff = abs($end->diffInSeconds($start, false));
        $actualDurationMinutes = (int) round($secondsDiff / 60);
      }
    }

    // بيانات الجلسة المأخوذة من invoice_item -> session
    $sessionData = null;
    if ($invoiceType === 'session') {
      // نجيب أول item من نوع session
      $sessionItem = $invoice->items->firstWhere('item_type', 'session');
      if ($sessionItem && $sessionItem->session) {
        $sessionData = $sessionItem->session;

        // نحسب مدة الجلسة الفعلية لو فيها start/end
        if (!empty($sessionData->start_time) && !empty($sessionData->end_time)) {
          $start = Carbon::parse($sessionData->start_time);
          $end = Carbon::parse($sessionData->end_time);
          $sessionData->actual_duration_minutes = $end->diffInMinutes($start);
        }
      }
    }

    // تمرير كل البيانات إلى الواجهة
    return view('invoices.client_show', [
      'invoice' => $invoice,
      'groupedItems' => $groupedItems,
      'totalAmount' => $totalAmount,
      'invoiceType' => $invoiceType,
      'extraData' => $extraData,
      'isHasPurchase' => $isHasPurchase,
      'bookingData' => $bookingData,
      'hourlyRate' => $hourlyRate,
      'actualDurationMinutes' => $actualDurationMinutes,
      'sessionData' => $sessionData,
      'purchaseItems' => $purchaseItems, // ← تمت إعادتها للتمرير إلى الواجهة
    ]);
  }



  public function ajaxSearch(Request $request)
  {
    $query = Invoice::query()->with('client')->where('total', '>', 0);

    if ($q = $request->query('q')) {
      $query->whereHas('client', fn($q2) => $q2->where('name', 'like', "%{$q}%"))
        ->orWhere('invoice_number', 'like', "%{$q}%");
    }

    if ($types = $request->query('types')) {
      $types = explode(',', $types);
      $query->whereIn('type', $types);
    }

    if ($from = $request->query('from')) {
      $query->whereDate('updated_at', '>=', $from);
    }
    if ($to = $request->query('to')) {
      $query->whereDate('updated_at', '<=', $to);
    }

    return $query->orderByDesc('updated_at')->limit(50)->get()->map(function ($inv) {

      return [

        'id' => $inv->id,
        'invoice_number' => $inv->invoice_number,
        'client_name' => $inv->client->name ?? null,
        'type' => $inv->type,
        'total' => $inv->total,
        'created_at' => $inv->created_at,
        'updated_at' => $inv->updated_at,
      ];
    });
  }


  // لو عايز المعاينة بدون حفظ (يعتمد على نفس فورم الداتا)
  public function preview(StoreInvoiceRequest $request)
  {


    [$items, $totals] = $this->buildItemsFromRequest($request->validated()['items']);

    // نحدد نوع الفاتورة تلقائيًا
    $type = $this->determineInvoiceType($items);

    return view('sale_proccess.invoice', [
      'items' => collect($items)->map(fn($it) => [
        'qty' => $it['qty'],
        'name' => $it['name'],
        'total' => $it['total'],
        // لو محتاج تعرض price/cost في الـ Blade عدّل القالب
        'price' => $it['price'],
        'cost' => $it['cost'],
      ]),
      'type' => $type,
      'grandTotal' => $totals['total'],
    ]);
  }



  public function store(StoreInvoiceRequest $request, ShiftService $shiftService)
  {
    $validated = $request->validated();
    $user = Auth::user();
    $openShift = Shift::where('user_id', $user->id)->whereNull('end_time')->first();
    // داخل أي مكان (Controller أو Middleware)
    if (!$openShift && !$user->hasRole('admin')) {
      // نعلم الواجهة أننا محتاجين فتح شيفت
      session()->flash('shift_required', true);
      // برده نقدر نرجع رسالة خطأ للمستخدم
      return redirect()->back()->with('error', '⚠️ لا يوجد شيفت مفتوح، ابدأ شيفت أولاً.');
    }

    // تحقق آمن من صلاحية الادمن
    $isAdmin = $user->hasRole('admin');
    if (!$openShift && !$isAdmin) {
      session()->flash('shift_required', true);
      return response()->json([
        'status' => 'error',
        'message' => "هناك خطأ غير مفهوم",
        'requireShift' => true
      ], 400);
    }

    [$items, $totals] = $this->buildItemsFromRequest($validated['items']);
    $type = $this->determineInvoiceType($items);

    try {

      $invoice = DB::transaction(function () use ($validated, $items, $totals, $type, $user, $openShift) {
        // 1) جمع متطلبات المنتجات per product_id
        $productRequirements = [];
        foreach ($items as $it) {
          if ($it['item_type'] === 'product' && isset($it['product_id'])) {
            $pid = $it['product_id'];
            $productRequirements[$pid] = ($productRequirements[$pid] ?? 0) + $it['qty'];
          }
        }

        // 2) تحقق من توفر المنتجات مع قفل FOR UPDATE
        if (!empty($productRequirements)) {
          $productIds = array_keys($productRequirements);
          $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

          $shortages = [];
          foreach ($productRequirements as $pid => $requiredQty) {
            $product = $products->get($pid);
            $available = $product ? $product->quantity : 0;
            if ($available < $requiredQty) {
              $shortages[] = [
                'product_id' => $pid,
                'product_name' => $product ? $product->name : null,
                'required' => $requiredQty,
                'available' => $available
              ];
            }
          }

          if (!empty($shortages)) {
            throw new \RuntimeException(json_encode($shortages), 422);
          }
        }

        // 3) إنشاء الفاتورة وسجّل البنود
        $invoice = Invoice::create([
          'invoice_number' => InvoiceNumber::next(),
          'client_id' => $validated['client_id'] ?? null,
          'type' => $type,
          'total' => $totals['total'],
          'profit' => $totals['profit'],
          'notes' => $validated['notes'] ?? null,
        ]);

        foreach ($items as $it) {
          $row = [
            'invoice_id' => $invoice->id,
            'item_type' => $it['item_type'],
            'product_id' => $it['product_id'] ?? null,
            'subscription_id' => $it['subscription_id'] ?? null,
            'booking_id' => $it['booking_id'] ?? null,
            'session_id' => $it['session_id'] ?? null,
            'name' => $it['name'],
            'qty' => $it['qty'],
            'price' => $it['price'],
            'cost' => $it['cost'],
            'total' => $it['total'],
            'description' => $it['description'] ?? null,
          ];

          InvoiceItem::create($row);

          // لو المنتج موجود، قلل الكمية من جدول products
          if ($it['item_type'] === 'product' && isset($it['product_id'])) {
            Product::where('id', $it['product_id'])->decrement('quantity', $it['qty']);
          }
        }

        // 4) سجل الـ system_action داخل نفس الـ transaction لربط الحدث بالفاتورة
        SystemAction::create([
          'user_id' => $user->id,
          'action' => SystemActionType::SALE_PROCESS->value,
          'actionable_type' => Invoice::class,
          'actionable_id' => $invoice->id,
          'invoice_id' => $invoice->id,
          'shift_id' => $openShift?->id,
          'amount' => $invoice->total,
          'note' => "إنشاء فاتورة بيع منفصلة - #{$invoice->invoice_number}",
          'meta' => json_encode([
            'client_id' => $invoice->client_id,
            'items' => $items,
            'totals' => $totals,
            'type' => $invoice->type,
          ]),
          'ip' => request()->ip(),
          'source' => 'web',
        ]);

        return $invoice->load('items');
      });

      // لو وصل هنا معناه الفاتورة اتخلقت بنجاح والـ transaction انتهى commit
    } catch (\RuntimeException $e) {
      if ((int) $e->getCode() === 422) {
        $shortages = json_decode($e->getMessage(), true);
        return response()->json([
          'status' => 'error',
          'message' => 'كمية بعض المنتجات غير كافية لإتمام الفاتورة.',
          'shortages' => $shortages
        ], 400);
      }
      throw $e;
    }

    // ✅ سجّل الحركة في الشيفت بعد نجاح إنشاء الفاتورة (كالسابق)
    if ($invoice && !$isAdmin) {
      ShiftAction::create([
        'shift_id' => $openShift->id,
        'action_type' => 'separate_sale',
        'invoice_id' => $invoice->id,
        'expense_draft_id' => null,
        'amount' => $invoice->total,
        'expense_amount' => null,
        'notes' => "عملية بيع منتجات (فاتورة رقم {$invoice->invoice_number})",
      ]);
    }
    if ($invoice && $isAdmin) {
      ShiftAction::create([
        'shift_id' => $openShift->id,
        'action_type' => 'separate_sale',
        'invoice_id' => $invoice->id,
        'expense_draft_id' => null,
        'amount' => $invoice->total,
        'expense_amount' => null,
        'notes' => "عملية بيع منتجات (فاتورة رقم {$invoice->invoice_number})",
      ]);
    }


    return response()->json([
      'message' => 'Invoice created successfully.',
      'invoice' => $invoice
    ]);
  }



  private function buildItemsFromRequest(array $requestItems): array
  {
    $items = [];
    $grandTotal = 0.0;
    $grandProfit = 0.0;

    foreach ($requestItems as $in) {
      $type = $in['item_type'];

      if ($type === 'product') {
        $product = Product::findOrFail($in['product_id']);
        $qty = (int) ($in['qty'] ?? 1);
        $price = (float) $product->price; // ممكن تسمح override من $in['price'] لو عايز
        $cost = (float) $product->cost;

        $total = $price * $qty;
        $profit = ($price - $cost) * $qty;

        $items[] = [
          'item_type' => 'product',
          'product_id' => $product->id,
          'name' => $product->name,
          'qty' => $qty,
          'price' => $price,
          'cost' => $cost,
          'total' => $total,
        ];
      } elseif ($type === 'subscription') {
        // TODO: هات الاشتراك الحقيقي من جدول subscriptions
        // مؤقتًا نفترض جالك price مع الطلب
        $qty = (int) ($in['qty'] ?? 1);
        $price = (float) ($in['price'] ?? 0);
        $cost = (float) ($in['cost'] ?? 0);

        $items[] = [
          'item_type' => 'subscription',
          'subscription_id' => $in['subscription_id'] ?? null,
          'name' => $in['name'] ?? 'اشتراك',
          'qty' => $qty,
          'price' => $price,
          'cost' => $cost,
          'total' => $price * $qty,
        ];
      } elseif ($type === 'booking') {
        // TODO: هات بيانات الحجز من جدول bookings لو موجود
        $qty = (int) ($in['qty'] ?? 1); // عدد الساعات مثلاً
        $price = (float) ($in['price'] ?? 0);
        $cost = (float) ($in['cost'] ?? 0);

        $items[] = [
          'item_type' => 'booking',
          'booking_id' => $in['booking_id'] ?? null,
          'name' => $in['name'] ?? 'حجز قاعة',
          'qty' => $qty,
          'price' => $price,
          'cost' => $cost,
          'total' => $price * $qty,
        ];
      } elseif ($type === 'session') {
        // TODO: هات بيانات الجلسة من جدول sessions لو موجود (سعر الساعة)
        $qty = (int) ($in['qty'] ?? 1); // عدد الساعات
        $price = (float) ($in['price'] ?? 0);
        $cost = (float) ($in['cost'] ?? 0);

        $items[] = [
          'item_type' => 'session',
          'session_id' => $in['session_id'] ?? null,
          'name' => $in['name'] ?? 'جلسة',
          'qty' => $qty,
          'price' => $price,
          'cost' => $cost,
          'total' => $price * $qty,
        ];
      } elseif ($type === 'deposit') {
        // دفعة مقدمة (ممكن تكون لفاتورة مستقبلية أو لحجز)
        $amount = (float) $in['amount'];
        $desc = $in['description'] ?? null;

        $items[] = [
          'item_type' => 'deposit',
          'name' => 'دفعة مقدمة',
          'qty' => 1,
          'price' => $amount,
          'cost' => 0,
          'total' => $amount,
          'description' => $desc,
        ];
      }

      $grandTotal += end($items)['total'];
      $grandProfit += (end($items)['price'] - end($items)['cost']) * end($items)['qty'];
    }

    return [$items, ['total' => round($grandTotal, 2), 'profit' => round($grandProfit, 2)]];
  }

  private function determineInvoiceType(array $items): string
  {
    $types = collect($items)->pluck('item_type')->unique()->values()->all();
    return count($types) === 1 ? $types[0] : 'mixed';
  }




  public function print(Request $request)
  {

    $items = collect($request->items)->map(fn($item) => [
      'qty' => $item['qty'],
      'name' => $item['name'],
      'total' => $item['qty'] * $item['price'],

    ]);
    return view('sale_proccess.print', compact('items'));
  }


  public function clientInvoices(Request $request, $clientId)
  {
    $client = Client::findOrFail($clientId);
    $invoice = Invoice::where('client_id', $client->id);
    $invoiceCount = $invoice->count();
    $invoiceTotal = $invoice->sum('total');
    $invoices = $invoice->select('id', 'type', 'invoice_number', 'total')->get();
    return view('clients.invoices', compact('invoiceCount', 'invoiceTotal', 'invoices','client'));
  }

}

