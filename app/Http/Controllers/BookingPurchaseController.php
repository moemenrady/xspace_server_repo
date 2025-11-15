<?php
namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingPurchase;
use App\Models\Sation;

use App\Models\Product;
use App\Models\SessionPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class BookingPurchaseController extends Controller
{
public function updatePurchases(Request $request, $bookingId)
{
    $quantities = $request->input('quantities', []);
    $removed = json_decode($request->input('removed', '[]'), true);

    // 🟩 تحديث الكميات
    foreach ($quantities as $purchaseId => $qty) {
        BookingPurchase::where('id', $purchaseId)
            ->where('booking_id', $bookingId)
            ->update(['quantity' => $qty]);
    }

    // 🟥 حذف المنتجات اللي اتشالت
    if (!empty($removed)) {
        BookingPurchase::whereIn('id', $removed)
            ->where('booking_id', $bookingId)
            ->delete();
    }

    return response()->json(['status' => 'success']);
}
  public function create(Request $request, Booking $booking)
  {
    return view("purchase.booking.create", compact("booking"));
  }

  public function storeItem(Request $request, Booking $booking)
  {


    $itemsData = json_decode($request->get('items'), true);
    $items = [];

    foreach ($itemsData as $item) {
      // نجيب المنتج من الـ DB
      $product = Product::find($item['id']);

      if ($product) {
        $qty = $item['qty'];

        $items[] = [
          'id' => $product->id,
          'name' => $product->name,
          'price' => $product->price,
          'cost' => $product->cost,
          'qty' => $qty,
          'total' => $product->price * $qty,
        ];

        // ابحث عن نفس المنتج في الجلسة
        $purchase = BookingPurchase::where('booking_id', $booking->id)
          ->where('product_id', $product->id)
          ->first();

        if ($purchase) {
          // لو موجود زود الكمية
          $purchase->quantity += $qty;
          $purchase->save();
        } else {
          // منتج جديد للجلسة
          BookingPurchase::create([
            'booking_id' => $booking->id,
            'product_id' => $product->id,
            'quantity' => $qty,
          ]);
        }
      }
    }

    return to_route('bookings.show', $booking->id);
  }





  public function storeItems(Request $request, Sation $session)
  {
    $validated = $request->validate([
      'items' => ['required', 'array', 'min:1'],
      'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
      'items.*.quantity' => ['required', 'integer', 'min:1'],
    ]);

    foreach ($validated['items'] as $item) {
      $purchase = SessionPurchase::firstOrCreate(
        ['session_id' => $session->id, 'product_id' => $item['product_id']],
        ['quantity' => 0]
      );

      $purchase->increment('quantity', $item['quantity']);
    }

    return redirect()->back()->with('success', 'تم إضافة المشتريات بنجاح ✅');
  }



}
