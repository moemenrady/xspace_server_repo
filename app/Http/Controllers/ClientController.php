<?php

namespace App\Http\Controllers;

use App\Models\Booking;

use App\Models\Client;

use App\Models\Sation;

use App\Models\Subscription;

use Illuminate\Http\Request;

use App\Models\Specialization;

use App\Models\EducationStage;

use Illuminate\Validation\Rule;

class ClientController extends Controller
{
  public function show(Client $client)
  {
    // اشتراك واحد (أفترض hasOne relation name subscription)
    $subscription = $client->subscriptions()->first();

    // جلسة نشطة واحدة (أفترض علاقة sations() اسمها sations)
    $activeSession = $client->clientSession()
      ->where('status', 'active')
      ->orderByDesc('start_time')
      ->first();

    // حجوزات حالية (استبعاد finished و cancelled)
    $bookings = $client->bookings()
      ->whereNotIn('status', ['finished', 'cancelled'])
      ->orderBy('start_at')
      ->get();

    // مجمل الفواتير (مجموع العمود total)
    $invoicesTotal = $client->invoices()->sum('total');

    // جلسات سابقة لعرض سريع
    $recentSessions = $client->clientSession()->orderByDesc('start_time')->limit(5)->get();

    return view('clients.show', compact(
      'client',
      'subscription',
      'activeSession',
      'bookings',
      'invoicesTotal',
      'recentSessions'
    ));
  }

  // public function createBarcode($id)
  // {
  //   $client = Client::find($id);
  //   return view("barcode_generate.user_barcode", compact("client"));
  // }
  public function index()
  {
    $clients = Client::all();
    $count_client = $clients->count();


    $active_subscribers_count = Subscription::where('attendees', 1)->count();
    $active_session_persons = Sation::where('status', 'active')
      ->sum('persons');
    $active_attendees_in_bookings = Booking::where('status', 'in_progress')
      ->sum('attendees');
    $active_clients_count =$active_subscribers_count + $active_attendees_in_bookings +$active_session_persons ;
    return view('clients.index', compact("clients","active_clients_count", "count_client", ));

  }

  public function store(Request $request)
  {
    $client = Client::create([
      'name' => $request->get('name'),
      'phone' => $request->get('phone')
    ]);
  }
  
  public function searchId(Request $request)
  {
    $query = trim($request->get('query', ''));

    if ($query === '' || !ctype_digit($query)) {
      return response()->json([], 200, ['Content-Type' => 'application/json; charset=utf-8']);
    }

    $results = Client::where('id', $query)
      ->select(['id', 'name', 'phone', 'age', 'specialization_id', 'education_stage_id'])
      ->get();

    // هُنا نعيد JSON مع JSON_UNESCAPED_UNICODE ليُطبع العربي بدون \uXXXX
    return response()->json($results, 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
  }
  public function search(Request $request)
  {
    $query = $request->get('query');

    $results = Client::where('phone', 'LIKE', "%{$query}%")
      ->orWhere('name', 'LIKE', "%{$query}%")

      ->select('id', 'name', 'phone', 'age', 'specialization_id', 'education_stage_id') // هات اللي محتاجه

      ->get();

    return response()->json($results);
  }
  
  public function edit(Client $client)
  {
    $specializations = Specialization::orderBy('name')->get();
    $educationStages = EducationStage::orderBy('name')->get();

    return view('clients.edit', compact('client', 'specializations', 'educationStages'));
  }

  // حفظ التعديلات
  public function update(Request $request, Client $client)
  {
    // قواعد التحقق
    $rules = [
      'name' => ['required', 'string', 'max:191'],
      'phone' => [
        'required',
        'string',
        'max:20',
        // تضمن عدم تكرار رقم التليفون لعميل آخر
        Rule::unique('clients', 'phone')->ignore($client->id),
      ],
      'age' => ['nullable', 'integer', 'min:1', 'max:150'],
      'specialization_id' => ['nullable', 'integer', 'exists:specializations,id'],
      'education_stage_id' => ['nullable', 'integer', 'exists:education_stages,id'],
    ];

    $data = $request->validate($rules);

    // حط القيم على الموديل (fillable لابد أن يتضمن الحقول)
    $client->update($data);

    return redirect()->route('clients.show', $client->id)
      ->with('success', 'تم تحديث بيانات العميل بنجاح.');
  }
    public function nextId(Request $request)
  {
    if (!auth()->check()) {
      // اختياري: لو حابب ترجع خطأ واضح بدل إعادة توجيه لصفحة login
      // return response()->json(['success' => false, 'error' => 'unauthenticated'], 401);
    }

    try {
      $last = Client::orderBy('id', 'desc')->select('id')->first();
      $lastId = $last ? intval($last->id) : 0;
      return response()->json([
        'success' => true,
        'last_id' => $lastId
      ], 200);

    } catch (\Exception $e) {
      \Log::error('nextId error: ' . $e->getMessage());
      return response()->json(['success' => false, 'error' => 'خطأ في الحصول على المعرف'], 500);
    }
  }

}
