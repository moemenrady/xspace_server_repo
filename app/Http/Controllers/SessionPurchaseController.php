<?php

namespace App\Http\Controllers;

use App\Enums\SystemActionType;
use App\Models\Sation;

use App\Models\Product;

use App\Models\SessionPurchase;

use App\Models\Shift;
use App\Models\SystemAction;
use Auth;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

class SessionPurchaseController extends Controller
{

public function updatePurchases(Request $request, $sessionId)
{
    $quantities = $request->input('quantities', []);
    $removed = json_decode($request->input('removed', '[]'), true);

    // 🟩 تحديث الكميات
    foreach ($quantities as $purchaseId => $qty) {
        SessionPurchase::where('id', $purchaseId)
            ->where('sation_id', $sessionId)
            ->update(['quantity' => $qty]);
    }

    // 🟥 حذف المنتجات اللي اتشالت
    if (!empty($removed)) {
        SessionPurchase::whereIn('id', $removed)
            ->where('sation_id', $sessionId)
            ->delete();
    }

    return response()->json(['status' => 'success']);
}
  public function create(Request $request, Sation $session)
  {
    if ($session->end_time !== null || $session->status === 'closed') {
      return redirect()->back()->with('error', 'هذه الجلسه منتهيه لا يمكن اضافة مشتريات ');
    }
    return view("purchase.session.create", compact("session"));
  }
  public function storeItem(Request $request, Sation $session)
  {
    $user = Auth::user();
    $openShift = Shift::where('user_id', $user->id)
      ->whereNull('end_time')
      ->first();

    // 1) تأكد إن الـ $session فعلاً موجود (Model binding عادة يرمى 404 تلقائياً)
    // لكن لو عايز نتعامل برفق بدل 404، نقوم بتحقق إضافي:
    if (!$session) {
      if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
        return response()->json(['status' => 'error', 'message' => 'الجلسة غير موجودة.'], 404);
      }
      return redirect()->back()->with('error', 'الجلسة غير موجودة.');
    }

    $isEnded = (!is_null($session->end_time) || (isset($session->status) && $session->status !== 'active'));
    if ($isEnded) {
      $msg = '⚠️ لا يمكن إضافة مشتريات: هذه الجلسة مُنتهية بالفعل.';
      if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
        return response()->json(['status' => 'error', 'message' => $msg], 422);
      }
      return redirect()->route('session.show', $session->id)->with('error', $msg);
    }

    // 3) فك الـ JSON مع تحقق
    $rawItems = $request->get('items');
    $itemsData = json_decode($rawItems, true);

    if (!is_array($itemsData) || count($itemsData) === 0) {
      $msg = 'لم تصل أي منتجات صحيحة للإضافة.';
      if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
        return response()->json(['status' => 'error', 'message' => $msg], 422);
      }
      return redirect()->back()->with('error', $msg);
    }

    DB::beginTransaction();
    try {
      $itemsResponse = [];
      foreach ($itemsData as $item) {
        // تحقق من الشكل المتوقع
        if (!isset($item['id']) || !isset($item['qty']))
          continue;

        $product = Product::find($item['id']);
        if (!$product) {
          // تجاهل أو أرجع خطأ؟ هنا سنتجاهل العنصر غير الموجود ونستمر
          continue;
        }

        $qty = (int) $item['qty'];
        if ($qty <= 0)
          continue;
        $itemTotal = $product->price * $qty;

        // أضف للسجل للإرجاع إن أردنا
        $itemsResponse[] = [
          'id' => $product->id,
          'name' => $product->name,
          'price' => $product->price,
          'qty' => $qty,
          'total' => $product->price * $qty,
        ];

        // الحدث: تحديث أو إنشاء SessionPurchase
        $purchase = SessionPurchase::where('sation_id', $session->id)
          ->where('product_id', $product->id)
          ->first();
        $actionType = SystemActionType::ADD_SESSION_PUNISHMENT->value; // استخدمت النوع الموجود في enum

        if ($purchase) {
          $purchase->quantity += $qty;
          $purchase->save();
          SystemAction::create([
            'user_id' => $user->id,
            'action' => $actionType,
            'actionable_type' => Sation::class,
            'actionable_id' => $session->id,
            'note' => "اضافة مشتريات للجلسه: {$product->name} ({$product->id})",
            'meta' => json_encode([
              'product_id' => $product->id,
              'qty_added' => $qty,
              'new_quantity_in_session' => $purchase->quantity,
              'price' => $product->price,
              'item_total' => $itemTotal,
            ]),
            'shift_id' => $openShift?->id,
            'ip' => request()->ip(),
            'source' => 'web',
          ]);
        } else {
          SessionPurchase::create([
            'sation_id' => $session->id,
            'product_id' => $product->id,
            'quantity' => $qty,
          ]);
          SystemAction::create([
            'user_id' => Auth::id(),
            'action' => $actionType,
            'actionable_type' => Sation::class,
            'actionable_id' => $session->id,
            'note' => "إضافة منتج جديد للجلسة: {$product->name} ({$product->id})",
            'meta' => json_encode([
              'product_id' => $product->id,
              'qty_added' => $qty,
              'price' => $product->price,
              'item_total' => $itemTotal,
            ]),
            'shift_id' => $openShift?->id,
            'ip' => request()->ip(),
            'source' => 'web',
          ]);
        }
      }

      DB::commit();

      $msg = '✅ تم إضافة المشتريات إلى الجلسة بنجاح.';
      if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
        return response()->json([
          'status' => 'success',
          'message' => $msg,
          'items' => $itemsResponse
        ], 201);
      }

      return redirect()->route('session.show', $session->id)->with('success', $msg);

    } catch (\Throwable $e) {
      DB::rollBack();
      \Log::error('storeItem error: ' . $e->getMessage());

      $msg = 'حدث خطأ أثناء إضافة المنتجات. الرجاء المحاولة مرة أخرى.';
      if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
        return response()->json(['status' => 'error', 'message' => $msg], 500);
      }

      return redirect()->back()->with('error', $msg);
    }
  }



}