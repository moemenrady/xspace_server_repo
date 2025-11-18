@extends('layouts.analytics')
@section('title','تحليل العملاء')
@section('subtitle','نمو، تفاعل، واحتفاظ')
@section('stats')
  <div class="card"><div class="label">عملاء جدد</div><div class="num">{{ $newClients ?? '--' }}</div></div>
  <div class="card"><div class="label">نشطون</div><div class="num">{{ $activeClients ?? '--' }}</div></div>
@endsection
@section('content')
  <div class="card">
    <div style="font-weight:700">أعلى العملاء نشاطاً</div>
    <div class="muted" style="margin-top:8px">قائمة قصيرة</div>
    <ul style="margin-top:10px">
      @foreach($topClients ?? [] as $c)<li class="muted">{{ $c->name }} — {{ $c->visits_count }}</li>@endforeach
    </ul>
  </div>
@endsection
