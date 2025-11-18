@extends('layouts.analytics')
@section('title','تفاصيل الدخل والربح')

@section('content')
    <style>
/* ---------- Layout ---------- */
.income-profit-scene {
    display: flex;
    gap: 40px;
    align-items: center;
    justify-content: center;
    padding: 28px 12px;
    flex-wrap: wrap;
}

/* Glass cards */
.cinema-card {
    width: 420px;
    min-width: 300px;
    border-radius: 18px;
    padding: 28px;
    background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
    border: 1px solid var(--glass-border);
    box-shadow: 0 18px 50px rgba(2,6,23,0.6);
    position: relative;
    overflow: hidden;
}

/* Floating life animation */
.float {
    animation: floatSlow 6.5s ease-in-out infinite;
}

@keyframes floatSlow {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
    100% { transform: translateY(0px); }
}

/* Big headline numbers */
.big-num {
    font-weight: 900;
    font-size: 36px;
    line-height: 1;
    background: var(--accent-grad);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-top: 6px;
}
.muted-sm { color: var(--muted); font-size: 13px; }

/* center energy line */
.energy-wrap {
    width: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.energy-line {
    width: 8px;
    height: 420px;
    border-radius: 999px;
    background: linear-gradient(180deg, rgba(248,224,193,0.12), rgba(217,177,171,0.05));
    box-shadow:
        0 0 12px rgba(248,224,193,0.06),
        0 0 30px rgba(217,177,171,0.04);
    position: relative;
    overflow: visible;
}

/* moving glow inside line */
.energy-line::before {
    content: "";
    position: absolute;
    left: -12px;
    top: -40%;
    width: 40px;
    height: 60%;
    background: radial-gradient(closest-side, rgba(255,210,150,0.65), transparent 50%);
    filter: blur(14px);
    transform-origin: center;
    animation: glide 3.2s ease-in-out infinite;
}

@keyframes glide {
    0% { transform: translateX(0) translateY(0) scale(0.9); opacity:0.6; }
    50% { transform: translateX(12px) translateY(60%) scale(1.05); opacity:1; }
    100% { transform: translateX(0) translateY(120%) scale(0.9); opacity:0.6; }
}

/* Small moving pulses along the line */
.pulse {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    width: 6px;
    height: 6px;
    border-radius: 999px;
    background: rgba(248,224,193,0.9);
    box-shadow: 0 0 18px rgba(248,224,193,0.55);
    animation: pulseMove 2.6s linear infinite;
}
.pulse.p1 { top:10%; animation-delay:-0.6s; }
.pulse.p2 { top:45%; animation-delay:-0.2s; }
.pulse.p3 { top:75%; animation-delay:0.2s; }

@keyframes pulseMove {
    0% { transform:translateX(-6px) scale(0.8); opacity:0.5; }
    50% { transform:translateX(6px) scale(1.2); opacity:1; }
    100% { transform:translateX(-6px) scale(0.8); opacity:0.5; }
}

/* Income list */
.income-list {
    margin-top: 14px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.income-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    padding: 10px;
    border-radius: 10px;
    background: linear-gradient(180deg, rgba(255,255,255,0.01), rgba(255,255,255,0.005));
    border: 1px solid rgba(255,255,255,0.02);
}
.income-item .left { text-align: right; }

.income-item .bar {
    width: 120px;
    height: 8px;
    border-radius: 999px;
    background: rgba(255,255,255,0.03);
    overflow: hidden;
}
.income-item .bar > i {
    display: block;
    height: 100%;
    border-radius: 999px;
    background: linear-gradient(90deg,#F8E0C1,#D9B1AB);
    box-shadow: 0 6px 20px rgba(217,177,171,0.06);
}

/* profit breakdown */
.profit-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-top: 10px;
}
.badge {
    font-size: 12px;
    padding: 6px 10px;
    border-radius: 999px;
    background: rgba(255,255,255,0.02);
    color: var(--muted);
    border: 1px solid rgba(255,255,255,0.03);
}

/* responsive */
@media (max-width: 980px) {
    .income-profit-scene { gap: 18px; }
    .energy-line { height: 300px; }
}

@media (max-width: 640px) {
    .income-profit-scene { flex-direction: column-reverse; }
    .energy-wrap { transform: rotate(90deg); width:100%; }
    .energy-line { height:6px; width:100%; border-radius:12px; }
}

    </style>


<div class="income-profit-scene">

  {{-- RIGHT: Income details --}}
  <div class="cinema-card float">
    <div style="display:flex; justify-content:space-between; align-items:center;">
      <div>
        <div style="font-weight:800; font-size:18px; margin-top:6px">تفصيل الدخل حسب نوع الخدمة</div>
      </div>
    </div>

    {{-- compute total to show relative bars --}}
    @php
      $totalIncomeSum = array_reduce($incomeDetails, function($carry, $val){ return $carry + (float)$val; }, 0);
      // avoid division by zero
      if($totalIncomeSum <= 0) { $totalIncomeSum = 1; }
    @endphp

    <div class="income-list">
      @foreach($incomeDetails as $label => $value)
        @php
          $num = (float) $value;
          $pct = round(($num / $totalIncomeSum) * 100, 1);
          $barWidth = max(4, ($num / $totalIncomeSum) * 100);
        @endphp

        <div class="income-item">
          <div class="left">
            <div style="font-weight:700">{{ $label }}</div>
            <div class="muted-sm">{{ number_format($num,2) }} ج.م · <small class="muted-sm">{{ $pct }}%</small></div>
          </div>

          <div style="display:flex; flex-direction:column; align-items:flex-start; gap:6px;">
            <div class="bar" title="{{ $pct }}%">
              <i style="width: {{ $barWidth }}%;"></i>
            </div>
            <div style="font-size:12px; color:var(--muted); text-align:left;">@if($label === 'إجمالي الحجز (ساعات + مقدم)') يشمل الساعات والمقدم @endif</div>
          </div>
        </div>
      @endforeach
    </div>

    <div style="margin-top:18px; border-top:1px dashed rgba(255,255,255,0.03); padding-top:12px; display:flex; justify-content:space-between; align-items:center;">
      <div>
        <div class="muted-sm">اجمالي الدخل</div>
        <div class="big-num">{{ $totalIncome }} ج.م</div>
      </div>
      <div style="text-align:left">
      </div>
    </div>
  </div>


<div class="income-profit-scene">

  {{-- Product cost card --}}
  <div class="cinema-card float">
    <div style="font-weight:800; font-size:18px; margin-bottom:12px;">صافي الربح</div>
    <div class="big-num">{{ number_format($netProfit,2) }} ج.م</div>
  </div>

  {{-- Expenses breakdown card --}}
  <div class="cinema-card float">
    <div style="font-weight:800; font-size:18px; margin-bottom:12px;">المصروفات</div>
    <div class="income-list">
      @php
        $totalExpenseSum = 0;
      @endphp
      @foreach($expenseList as $expense)
        @php $totalExpenseSum += $expense['total']; @endphp
        @php
          $pct = ($expense['total'] / max($totalExpenses,1)) * 100;
          $barWidth = max(4, $pct);
        @endphp
        <div class="income-item">
          <div class="left">
            <div style="font-weight:700">{{ $expense['name'] }}</div>
            <div class="muted-sm">{{ number_format($expense['total'],2) }} ج.م · <small class="muted-sm">{{ round($pct,1) }}%</small></div>
          </div>
          <div class="bar" title="{{ round($pct,1) }}%">
            <i style="width: {{ $barWidth }}%;"></i>
          </div>
        </div>
      @endforeach
    </div>

    {{-- Total Expenses --}}
    <div class="profit-row">
      <div class="muted-sm">إجمالي المصروف</div>
      <div class="big-num">{{ number_format($totalExpenses,2) }} ج.م</div>
    </div>

    {{-- Net Profit --}}
    <div class="profit-row" style="margin-top:8px;">
      <div class="muted-sm">اجمالي تكلفة المنتجات</div>
      <div class="big-num">{{ number_format($productInvoiceItems,2) }} ج.م</div>
    </div>
  </div>

</div>

@endsection
