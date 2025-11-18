@extends('layouts.analytics')
@section('title','تحليل الجلسات')
@section('subtitle','جداول الجلسات وحضورها')
@section('stats')
  <div class="card"><div class="label">جلسات اليوم</div><div class="num">{{ $sessionsToday ?? '--' }}</div></div>
  <div class="card"><div class="label">متوسط الحضور</div><div class="num">{{ $avgAttendance ?? '--' }}</div></div>
@endsection
@section('content')
  <div class="card"><div style="font-weight:700">أقرب الجلسات</div>
    <div class="muted" style="margin-top:8px">قائمة الجلسات خلال 3 أيام</div>
  </div>
@endsection
