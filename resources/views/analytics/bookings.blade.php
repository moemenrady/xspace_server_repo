@extends('layouts.analytics')

@section('title', 'تحليل الحجوزات')
@section('subtitle', 'مقاييس وأداء الحجوزات')

@section('stats')
  <div class="card">
    <div class="label">إجمالي الحجوزات</div>
    <div class="num">{{ $totalBookings ?? '--' }}</div>
    <div class="muted" style="margin-top:6px">اليوم</div>
  </div>
  <div class="card">
    <div class="label">حجوزات ملغاة</div>
    <div class="num">{{ $cancelled ?? '--' }}</div>
    <div class="muted" style="margin-top:6px">هذا الأسبوع</div>
  </div>
  <div class="card">
    <div class="label">متوسط مدة الحجز (دقائق)</div>
    <div class="num">{{ $avgDuration ?? '--' }}</div>
    <div class="muted" style="margin-top:6px">آخر 30 يوم</div>
  </div>
@endsection

@section('content')
  <div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
      <div style="font-weight:700">أحدث الحجوزات</div>
      <div class="muted">آخر 10</div>
    </div>

    <table class="analytics-table" style="margin-top:12px">
      <thead>
        <tr><th>العميل</th><th>قاعة</th><th>تاريخ</th><th>حالة</th></tr>
      </thead>
      <tbody>
        @forelse($latestBookings ?? [] as $b)
          <tr>
            <td>{{ $b->client_name }}</td>
            <td>{{ $b->hall_name }}</td>
            <td>{{ \Carbon\Carbon::parse($b->start_at)->format('Y-m-d H:i') }}</td>
            <td>{{ $b->status }}</td>
          </tr>
        @empty
          <tr><td colspan="4" class="muted">لا توجد بيانات</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endsection
