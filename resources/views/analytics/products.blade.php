@extends('layouts.analytics')
@section('title','تحليل المنتجات')
@section('subtitle','مخزون ومبيعات')
@section('stats')
  <div class="card"><div class="label">مبيعات اليوم</div><div class="num">{{ $soldToday ?? '--' }}</div></div>
  <div class="card"><div class="label">أكثر المنتج مبيعاً</div><div class="num">{{ $topProduct ?? '--' }}</div></div>
@endsection
@section('content')
  <div class="card"><div style="font-weight:700">مخزون المنتج</div>
    <table class="analytics-table" style="margin-top:12px"><thead><tr><th>المنتج</th><th>المخزون</th></tr></thead><tbody>
      @foreach($products ?? [] as $p) <tr><td>{{ $p->name }}</td><td>{{ $p->stock }}</td></tr>@endforeach
    </tbody></table>
  </div>
@endsection
