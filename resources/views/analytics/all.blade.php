@extends('layouts.analytics')
@section('title','التحليل العام (All)')
@section('subtitle','ملخص شامل من جميع الصفحات')

@section('stats')
  <div class="card"><div class="label">إجمالي العملاء</div><div class="num">{{ $totalClients ?? '--' }}</div></div>
  <div class="card"><div class="label">إجمالي الحجوزات</div><div class="num">{{ $totalBookings ?? '--' }}</div></div>
  <div class="card"><div class="label">إجمالي الإيرادات</div><div class="num">{{ $totalRevenue ?? '--' }}</div></div>
  <div class="card"><div class="label">المنتجات المباعة</div><div class="num">{{ $soldProducts ?? '--' }}</div></div>
@endsection

@section('content')
  <div class="card" style="margin-bottom:14px">
    <div style="display:flex; justify-content:space-between; align-items:center">
      <div style="font-weight:700">مؤشرات الأداء الرئيسية (KPIs)</div>
      <div class="muted">تحديث آلي</div>
    </div>
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:10px; margin-top:12px">
      <div class="glass-box"><div class="label">ARPU</div><div class="num">{{ $arpu ?? '--' }}</div></div>
      <div class="glass-box"><div class="label">Retention</div><div class="num">{{ $retention ?? '--' }}</div></div>
      <div class="glass-box"><div class="label">Churn</div><div class="num">{{ $churn ?? '--' }}</div></div>
    </div>
  </div>

  <div class="card">
    <div style="font-weight:700">ملاحظات سريعة</div>
    <div class="muted" style="margin-top:8px">نصائح: تحسين تسويق القاعات، متابعة العملاء المتروكين، مراجعة الأسعار.</div>
  </div>
@endsection
