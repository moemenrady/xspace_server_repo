<?php

namespace App\Http\Controllers;

use App\Models\VenuePricing;
use Illuminate\Http\Request;

class VenuePricingController extends Controller
{
     public function create()
    {
        $records = VenuePricing::latest()->get();
        return view('managment.changes.base-hour-price.create', compact('records'));
    }
    public function store(Request $request,VenuePricing $venuePricing)
{
    // 1- التحقق من البيانات
    $request->validate([
        'setter_name' => 'required|string|max:255',
        'hour_price'  => 'required|numeric|min:0',
    ]);

    // 2- نوقف كل الأسعار القديمة
    $venuePricing->update(['is_active' => 0]);

    // 3- نضيف السعر الجديد كمتفعل
VenuePricing::create([
        'base_hour_price' => $request->hour_price,
        'setter_name'     => $request->setter_name,
        'is_active'       => 1,
        'created_at'      => now(),
        'updated_at'      => now(),
    ]);

    // 4- رجوع برسالة نجاح
    return redirect()->back()->with('success', 'تم إضافة السعر الجديد وتفعيله بنجاح ✅');
}

}
