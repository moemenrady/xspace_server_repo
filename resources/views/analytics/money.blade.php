@extends('layouts.analytics')
@section('title', 'التحليل المالي')

@section('content')

    <div class="analytics-header">
        <div>
            <div class="page-title">التحليل المالي المتقدم</div>
            <div class="sub-muted">لوحة العائدات · الإحصاءات المتقدمة</div>
        </div>
    </div>

    {{-- ======= STAT CARDS ======= --}}
    <div class="stats-grid">

        <div class="card">
            <div class="label">إجمالي الدخل</div>
            <div class="num text-success" style="cursor:pointer"
                onclick="window.location='{{ route('analytics.totalIncomeAndProfit') }}'">
                {{ number_format($totalIncome, 2) }} جنيه
            </div>
        </div>

        <div class="card">
            <div class="label">إجمالي المصاريف</div>
            <div class="num text-danger">
                {{ number_format($totalExpenses, 2) }} جنيه
            </div>
        </div>

        <div class="card">
            <div class="label">صافي الربح</div>
            <div class="num {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                {{ number_format($netProfit, 2) }} جنيه
            </div>
        </div>

        <div class="card">
            <div class="label">الهامش الربحي</div>
            <div class="num">{{ $profitMargin }}%</div>
        </div>

        <div class="card">
            <div class="label">نسبة النمو هذا الشهر</div>
            <div class="num {{ $growthRate >= 0 ? 'text-success' : 'text-danger' }}">
                {{ $growthRate }}%
            </div>
        </div>

        <div class="card">
            <div class="label">أفضل يوم دخل</div>
            <div class="num">
                @if ($topIncomeDay)
                    {{ $topIncomeDay->day }} — {{ number_format($topIncomeDay->sum, 2) }} جنيه
                @else
                    لا يوجد بيانات
                @endif
            </div>
        </div>

        <div class="card">
            <div class="label">أعلى خدمة جابت دخل</div>
            <div class="num">
                @if ($topService)
                    @php
                        $serviceTypes = [
                            'session' => 'الجلسات',
                            'booking' => 'الحجوزات',
                            'subscription' => 'الاشتراكات',
                            'product' => 'المبيعات',
                            'deposit' => 'المقدم',
                        ];
                        $serviceName = $serviceTypes[$topService->item_type] ?? $topService->item_type;
                    @endphp
                    {{ $serviceName }} — {{ number_format($topService->sum, 2) }} جنيه
                @else
                    لا يوجد بيانات
                @endif
            </div>
        </div>


    </div>


    {{-- ======= TREND & TABLE ======= --}}
    <div class="content-row">

        {{-- LEFT: Trend Chart placeholder --}}
        <div class="glass-box">
            <h5 class="mb-3">منحنى الدخل خلال آخر 30 يوم</h5>
            <div class="chart-placeholder">📈 سيتم إضافة الرسم هنا قريبًا</div>
        </div>

        {{-- RIGHT: Monthly Comparison --}}
        <div class="glass-box">
            <h5 class="mb-3">مقارنة بين الشهور</h5>

            <table class="analytics-table">
                <thead>
                    <tr>
                        <th>الفترة</th>
                        <th>القيمة</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>هذا الشهر</td>
                        <td>{{ number_format($thisMonth, 2) }} جنيه</td>
                    </tr>
                    <tr>
                        <td>الشهر السابق</td>
                        <td>{{ number_format($lastMonth, 2) }} جنيه</td>
                    </tr>
                    <tr>
                        <td>الفرق</td>
                        <td class="{{ $growthRate >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $growthRate }}%
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>

    </div>

@endsection
