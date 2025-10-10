<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
class SaleProccess extends Controller
{
    public function create(){
      return view("sale_proccess.create",);
    }


public function createInvoice(Request $request)
{
    // نفك JSON اللي جاي من الـ request
    $itemsData = json_decode($request->get('items'), true);

    $items = [];

    foreach ($itemsData as $item) {
        // نجيب المنتج من الـ DB
        $product = Product::find($item['id']);

        if ($product) {
            $items[] = [
                'id'    => $product->id,
                'name'  => $product->name,       // الاسم الحقيقي من الجدول
                'price' => $product->price,      // سعر البيع
                'cost'  => $product->cost,       // سعر التكلفة
                'qty'   => $item['qty'],         // العدد جاي من الـ request
                'total' => $product->price * $item['qty'], // مجموع السعر × العدد
            ];
        }
    }

    return view("sale_proccess.invoice", compact("items"));
}

}
