@extends('layouts.analytics')
@section('title','تحليل المستخدمين')
@section('subtitle','نشاط المستخدمين وصلاحياتهم')
@section('stats')
  <div class="card"><div class="label">مستخدمون نشطون</div><div class="num">{{ $activeUsers ?? '--' }}</div></div>
  <div class="card"><div class="label">مستخدمون جدد</div><div class="num">{{ $newUsers ?? '--' }}</div></div>
@endsection
@section('content')
  <div class="card"><div style="font-weight:700">أدوار وصلاحيات</div><div class="muted" style="margin-top:8px">عدد المشرفين، المديرين ...</div></div>
@endsection
