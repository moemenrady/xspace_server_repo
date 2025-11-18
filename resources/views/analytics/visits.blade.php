@extends('layouts.analytics')
@section('title','تحليل الزيارات')
@section('subtitle','حركة الزوار وسجل الزيارات')
@section('stats')
  <div class="card"><div class="label">زيارات اليوم</div><div class="num">{{ $visitsToday ?? '--' }}</div></div>
  <div class="card"><div class="label">متوسط الزيارة</div><div class="num">{{ $avgVisit ?? '--' }}</div></div>
@endsection
@section('content')
  <div class="card"><div style="font-weight:700">سجل الزيارات الأخير</div><div class="muted" style="margin-top:8px">قم بتمكين تتبع الزيارات للحصول على تفاصيل</div></div>
@endsection
