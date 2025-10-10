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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\SystemAction;
use Schema;
class InvoiceController extends Controller
{

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
    $perPage = 20;

    $useModel = class_exists(Invoice::class);
    if ($useModel) {
        // لا نطلب payments لأن الموديل ما فيه العلاقة
        $invoicesQuery = Invoice::with([
            'booking',
            'items',
            // 'payments' --> محذوف لأن العلاقة مفقودة في الموديل
        ])->where('client_id', $client->id)
          ->orderBy('created_at', 'desc');
    } else {
        if (! Schema::hasTable('invoices')) {
            return view('clients.invoices', [
                'client' => $client,
                'invoices' => collect(),
                'totalInvoices' => 0,
                'sumInvoices' => 0.0,
                'paidTotal' => 0.0,
                'dueTotal' => 0.0,
                'countsByStatus' => [],
                'typesCount' => [],
                'depositsTotal' => 0.0,
                'itemsTotal' => 0.0,
                'invoicesPerPage' => $perPage,
                'invoicesAll' => collect(),
            ]);
        }
        $invoicesQuery = DB::table('invoices')->where('client_id', $client->id)->orderBy('created_at', 'desc');
    }

    if ($useModel) {
        $invoicesAll = (clone $invoicesQuery)->get();
        $invoices = $invoicesQuery->paginate($perPage)->withQueryString();
    } else {
        $invoicesAll = $invoicesQuery->get();
        $invoices = DB::table('invoices')->where('client_id', $client->id)->orderBy('created_at','desc')->paginate($perPage);
    }

    // مجموع الفواتير
    $sumInvoices = 0.0;
    if (Schema::hasTable('invoices')) {
        $sumInvoices = (float) DB::table('invoices')
            ->where('client_id', $client->id)
            ->sum('total'); // عندك حقل total في السكيمة
    }

    // ======= المدفوعات: استخدم booking_deposits (invoice_id) كمصدر للمدفوع =======
    $paidTotal = 0.0;
    if (Schema::hasTable('booking_deposits')) {
        $paidTotal = (float) DB::table('booking_deposits')
            ->join('invoices', 'booking_deposits.invoice_id', '=', 'invoices.id')
            ->where('invoices.client_id', $client->id)
            ->sum('booking_deposits.amount');
    } else {
        // fallback: لو فيه حقل paid_amount داخل invoices استخدمه
        if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'paid_amount')) {
            $paidTotal = (float) DB::table('invoices')
                ->where('client_id', $client->id)
                ->sum('paid_amount');
        }
    }

    $dueTotal = max(0, $sumInvoices - $paidTotal);

    // حالات و أنواع
    $countsByStatus = [];
    if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'status')) {
        $rows = DB::table('invoices')
            ->select('status', DB::raw('COUNT(*) as cnt'))
            ->where('client_id', $client->id)
            ->groupBy('status')
            ->get();
        foreach ($rows as $r) $countsByStatus[$r->status] = (int)$r->cnt;
    }

    $typesCount = [];
    if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'type')) {
        $rows = DB::table('invoices')
            ->select('type', DB::raw('COUNT(*) as cnt'))
            ->where('client_id', $client->id)
            ->groupBy('type')
            ->get();
        foreach ($rows as $r) $typesCount[$r->type] = (int)$r->cnt;
    }

    // مجموع بنود الفاتورة
    $itemsTotal = 0.0;
    if (Schema::hasTable('invoice_items')) {
        $itemsTotal = (float) DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->where('invoices.client_id', $client->id)
            ->select(DB::raw('SUM(invoice_items.total) as total'))
            ->value('total') ?? 0.0;
    }

    // per-invoice aggregates (deposits/payments/items)
    $invoiceIds = $invoicesAll->pluck('id')->toArray();

    $depositsPerInvoice = [];
    if (Schema::hasTable('booking_deposits') && !empty($invoiceIds)) {
        $rows = DB::table('booking_deposits')
            ->select('invoice_id', DB::raw('SUM(amount) as total'))
            ->whereIn('invoice_id', $invoiceIds)
            ->groupBy('invoice_id')
            ->get();
        foreach ($rows as $r) $depositsPerInvoice[$r->invoice_id] = (float)$r->total;
    }

    // paymentsPerInvoice هنا نفس depositsPerInvoice لأننا لا نملك جدول مدفوعات منفصل
    $paymentsPerInvoice = $depositsPerInvoice;

    $itemsPerInvoice = [];
    if (Schema::hasTable('invoice_items') && !empty($invoiceIds)) {
        $rows = DB::table('invoice_items')
            ->select('invoice_id', DB::raw('SUM(total) as total'))
            ->whereIn('invoice_id', $invoiceIds)
            ->groupBy('invoice_id')
            ->get();
        foreach ($rows as $r) $itemsPerInvoice[$r->invoice_id] = (float)$r->total;
    }

    return view('clients.invoices', [
        'client' => $client,
        'invoices' => $invoices,
        'invoicesAll' => $invoicesAll,
        'totalInvoices' => $invoicesAll->count(),
        'sumInvoices' => (float) $sumInvoices,
        'paidTotal' => (float) $paidTotal,
        'dueTotal' => (float) $dueTotal,
        'countsByStatus' => $countsByStatus,
        'typesCount' => $typesCount,
        'depositsTotal' => (float) $paidTotal,      // نفس المدفوعات هنا
        'itemsTotal' => (float) $itemsTotal,
        'depositsPerInvoice' => $depositsPerInvoice,
        'paymentsPerInvoice' => $paymentsPerInvoice,
        'itemsPerInvoice' => $itemsPerInvoice,
        'invoicesPerPage' => $perPage,
    ]);
}

}

