<?php

namespace App\Http\Controllers;

use App\Models\ImportantProduct;
use App\Models\Product;
use DB;
use Illuminate\Http\Request;

class ProductController extends Controller
{
  public function index()
  {
    $products = Product::all();
    $countProducts = $products->count();
    $countItems = $products->sum('quantity');

    return view('products.index', compact('products', 'countProducts', 'countItems'));
  }



  public function create()
  {
    return view('products.create');
  }
  public function createImportant()
  {

    $importantProducts = ImportantProduct::with('product')->latest()->paginate(20);
    // $products not mandatory here (we use AJAX search), لكن لو بدك تمرر:
    // $products = \App\Models\Product::take(30)->get();

    return view('managment.changes.important_products.create', compact('importantProducts'));

  }public function storeImportant(Request $request)
{
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'product_id' => 'required|exists:products,id',
    ]);

    // التحقق إذا الاسم موجود بالفعل
    $nameExists = ImportantProduct::where('name', $data['name'])->exists();
    if ($nameExists) {
        return redirect()->back()->with('error', 'هذا الاسم مرتبط بمنتج مهم بالفعل.');
    }

    // التحقق إذا المنتج مرتبط بالفعل
    $productExists = ImportantProduct::where('product_id', $data['product_id'])->exists();
    if ($productExists) {
        return redirect()->back()->with('error', 'هذا المنتج موجود بالفعل كمنتج مهم.');
    }

    // إنشاء المنتج المهم
    ImportantProduct::create([
        'product_id' => $data['product_id'],
        'name' => $data['name'],
    ]);

    return redirect()->back()->with('success', 'تم حفظ المنتج المهم بنجاح.');
}
public function updateImportant(Request $request, ImportantProduct $importantProduct)
{
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'product_id' => 'nullable|exists:products,id',
    ]);

    // تحقق من الاسم المكرر (باستثناء نفسه)
    $existsByName = ImportantProduct::where('name', $data['name'])
        ->where('id', '!=', $importantProduct->id)
        ->exists();
    if ($existsByName) {
        return redirect()->back()->with('error', 'هذا الاسم مستخدم بالفعل.');
    }

    // تحقق من المنتج المرتبط (باستثناء نفسه)
    if (!empty($data['product_id'])) {
        $existsByProduct = ImportantProduct::where('product_id', $data['product_id'])
            ->where('id', '!=', $importantProduct->id)
            ->exists();
        if ($existsByProduct) {
            return redirect()->back()->with('error', 'هذا المنتج مرتبط بالفعل بمنتج مهم آخر.');
        }
    }

    // التحديث
    $importantProduct->update([
        'name' => $data['name'],
        'product_id' => $data['product_id'] ?? $importantProduct->product_id,
    ]);

    return redirect()->back()->with('success', 'تم تحديث المنتج المهم بنجاح.');
}
public function update(Request $request, Product $product)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
        'cost' => 'required|numeric|min:0',
        'quantity' => 'required|integer|min:0',
    ]);

    $product->update($validated);

    return response()->json(['status' => 'success', 'message' => 'Product updated successfully']);
}

public function show(Product $product)
{
    $importantProduct = ImportantProduct::where('product_id', $product->id)->first();

    return view('products.show', compact('product', 'importantProduct'));
}

  public function addQuantityPage()
  {
    return view('products.add-quantity');
  }
  public function store(Request $request)
  {
    $request->validate([
      'name' => 'required|string|max:255',
      'price' => 'required|numeric',
      'cost' => 'required|numeric',
      'quantity' => 'required|integer|min:0',
    ]);
    $exists = Product::where('name', $request->name)->exists();


    if ($exists) {
      return redirect()->back()->with('error', 'هذا المنتج موجود في المخزن ❕');
    } else {
      Product::create($request->all());
      return redirect()->back()->with('success', 'تمت إضافة المنتج بنجاح ✅');

    }
  }
  public function addQuantity(Request $request, $id)
  {
    $request->validate([
      'quantity' => 'required|integer|min:1',
    ]);

    $product = Product::findOrFail($id);
    $product->quantity += $request->quantity;
    $product->save();

    return redirect()->route('products.index')->with('success', 'تمت إضافة الكمية بنجاح ✅');
  }
  public function search(Request $request)
  {
    $query = $request->get('query');

    $results = Product::where('name', 'LIKE', "%{$query}%")
      ->orWhere('id', $query)
      ->select('id', 'name', 'price', 'cost', 'quantity') // هات اللي محتاجه
      ->limit(10)
      ->get();

    return response()->json($results);
  }

}
