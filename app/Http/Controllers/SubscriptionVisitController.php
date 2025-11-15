<?php
namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\SubscriptionVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SubscriptionVisitController extends Controller
{
  // صفحة الزيارات (Blade)
public function showVisits(Subscription $subscription)
{
    // جلب العلاقات اللي هنستخدمها
    $subscription->load('client', 'plan');

    // نجيب الزيارات مباشرة من الداتا بيز - pagination على 12 عنصر
    $visits = $subscription->visits()
        ->with(['client', 'creator'])
        ->orderBy('checked_in_at', 'desc')
        ->paginate(12);

    // حسابات بسيطة للـ header (مثل used/percent)
    $plan = $subscription->plan;
    $used = ($plan && $plan->visits_count)
        ? ($plan->visits_count - $subscription->remaining_visits)
        : $subscription->visits()->count();
    $percent = ($plan && $plan->visits_count)
        ? round(($used / $plan->visits_count) * 100)
        : 0;

    return view('subscription.subscriptions-visits', compact('subscription', 'plan', 'used', 'percent', 'visits'));
}

  // API: يرجع لائحة الزيارات (يدعم pagination, search, sort)
  public function visitsList(Request $request, Subscription $subscription)
  {
    $q = $request->input('q'); // بحث نصي
    $perPage = (int) $request->input('per_page', 12);
    $page = (int) $request->input('page', 1);

    $query = $subscription->visits()->with(['client', 'creator'])->orderBy('checked_in_at', 'desc');

    if ($q) {
      $query->where(function ($qq) use ($q) {
        $qq->where('notes', 'like', "%{$q}%")
          ->orWhereHas('client', function ($c) use ($q) {
            $c->where('name', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%");
          });
      });
    }

    $visits = $query->paginate($perPage, ['*'], 'page', $page);

    // format timestamps nicely for frontend
    $visits->getCollection()->transform(function ($v) {
      return [
        'id' => $v->id,
        'visit_number' => $v->visit_number,
        'checked_in_at' => $v->checked_in_at ? $v->checked_in_at->format('Y-m-d H:i') : null,
        'checked_out_at' => $v->checked_out_at ? $v->checked_out_at->format('Y-m-d H:i') : null,
        'duration_minutes' => $v->duration_minutes,
        'attended' => (bool) $v->attended,
        'notes' => $v->notes,
        'client' => $v->client ? ['id' => $v->client->id, 'name' => $v->client->name, 'phone' => $v->client->phone] : null,
        'creator' => $v->creator ? ['id' => $v->creator->id, 'name' => $v->creator->name] : null,
      ];
    });

    return response()->json($visits);
  }

  // ختم الخروج: يحدث checked_out_at و duration_minutes
  public function checkout(Request $request, SubscriptionVisit $visit)
  {
    // تأكد أن المستخدم مصرح له لو عندك policies (optional)
    // $this->authorize('update', $visit);

    if ($visit->checked_out_at) {
      return response()->json(['success' => false, 'message' => 'تم ختم الخروج بالفعل.'], 422);
    }

    $checkedOutAt = $request->input('checked_out_at') ? Carbon::parse($request->input('checked_out_at')) : now();

    $visit->checked_out_at = $checkedOutAt;
    $visit->duration_minutes = $visit->checked_in_at ? $checkedOutAt->diffInMinutes($visit->checked_in_at) : null;
    $visit->save();

    return response()->json([
      'success' => true,
      'message' => 'تم ختم الخروج وتحديث مدة الجلسة.',
      'visit' => [
        'id' => $visit->id,
        'checked_out_at' => $visit->checked_out_at->format('Y-m-d H:i'),
        'duration_minutes' => $visit->duration_minutes,
      ]
    ]);
  }
}
