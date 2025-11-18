@extends('layouts.analytics')
@section('title','تحليل الاشتراكات')
@section('subtitle','تجدد، انتهاء، وإيراد من الاشتراكات')
@section('stats')
  <div class="card"><div class="label">اشتراكات جديدة</div><div class="num">{{ $newSubs ?? '--' }}</div></div>
  <div class="card"><div class="label">اشتراكات منتهية</div><div class="num">{{ $expiring ?? '--' }}</div></div>
@endsection
@section('content')
  <div class="card"><div style="font-weight:700">قوائم الاشتراكات</div><div class="muted">تصدير سريع أو فلتر حسب التاريخ</div></div>
@endsection
