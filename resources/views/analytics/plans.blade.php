@extends('layouts.analytics')
@section('title','تحليل الخطط')
@section('subtitle','اشتراكات وخطط العملاء')
@section('stats')
  <div class="card"><div class="label">مشتركين في الخطط</div><div class="num">{{ $subscribers ?? '--' }}</div></div>
  <div class="card"><div class="label">الخطط الأكثر شيوعاً</div><div class="num">{{ $topPlan ?? '--' }}</div></div>
@endsection
@section('content')
  <div class="card"><div style="font-weight:700">تفاصيل الاشتراكات</div><div class="muted" style="margin-top:8px">قابلة للتصدير CSV</div></div>
@endsection
