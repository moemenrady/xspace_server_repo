<?php

namespace App\Http\Controllers;

use App\Models\Booking;

use App\Models\Client;

use App\Models\Sation;

use App\Models\Subscription;

use Illuminate\Http\Request;

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
  public function search(Request $request)
  {
    $query = $request->get('query');

    $results = Client::where('phone', 'LIKE', "%{$query}%")
      ->orWhere('name', 'LIKE', "%{$query}%")
      ->orWhere('id', $query)
      ->select('id', 'phone', 'name') // هات اللي محتاجه
      ->limit(10)
      ->get();

    return response()->json($results);
  }

}
