@extends('layouts.analytics')
@section('title','تحليل القاعات')
@section('subtitle','شعبية القاعات واستخدامها')
@section('stats')
  <div class="card"><div class="label">القاعات المستخدمة</div><div class="num">{{ $usedHalls ?? '--' }}</div></div>
  <div class="card"><div class="label">أكثر قاعة حجزاً</div><div class="num">{{ $topHallName ?? '--' }}</div></div>
@endsection
@section('content')
  <div class="card"><div style="font-weight:700">شغل القاعات (آخر 30 يوم)</div><div class="chart-placeholder">مخطط شغل القاعات</div></div>
@endsection
