{{-- resources/views/clients/invoices.blade.php --}}
@extends('layouts.app_page')

@section('title', "فواتير — {$client->name}")

@section('content')
    <div class="client-container">
        <div class="card">
            <div class="card-header">
                <div>
                    <h2>🧾 فواتير العميل : {{ $client->name }} </h2>
                </div>

                <div class="header-actions">
                    <button id="printBtn" class="btn small">🖨 طباعة</button>
                </div>
            </div>

            <!-- Summary stats -->
            <div class="section">
                <div class="box stats-grid">
                    <div class="stat stat-money">
                        <div class="stat-label">💰 إجمالي الفواتير</div>
                        <div class="stat-money-value">
                            {{ number_format($invoiceTotal ?? 0) }} ج.م
                        </div>
                    </div>

                    <div class="stat">
                        <div class="stat-label">مجموع الفواتير</div>
                        <div class="stat-value">{{ $invoiceCount }}</div>
                    </div>
                </div>
            </div>
            @foreach ($invoices as $inv)
                @php
                    $typeLabel =
                        [
                            'product' => 'مشتريات',
                            'subscription' => 'اشتراك',
                            'booking' => 'جلسة خاصة',
                            'session' => 'جلسة',
                            'deposit' => 'مقدم حجز',
                        ][$inv->type] ?? $inv->type;
                @endphp

                <div class="invoice-card" onclick="window.location.href='{{ route('invoices.client.show', $inv->id) }}'">
                    <div class="info">
                        <h3>🧾 فاتورة رقم: {{ $inv->invoice_number }}</h3>
                        <p>النوع: <strong>{{ $typeLabel }}</strong></p>
                    </div>

                    <div class="total-box">
                        <span class="total-amount">💰 {{ number_format($inv->total, 2) }}</span>
                    </div>
                </div>
            @endforeach




        </div>
    </div>

@endsection

@section('style')
    <style>
        body {
            background: #fafafa;
            font-family: "Tahoma", sans-serif;
        }

        .client-container {
            max-width: 1100px;
            margin: 36px auto;
            padding: 20px;
        }

        .card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 6px 26px rgba(0, 0, 0, 0.06);
            padding: 22px;
            animation: fadeInUp .5s ease;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            border-bottom: 2px solid #f5f5f5;
            padding-bottom: 12px;
            margin-bottom: 14px;
        }

        .card-header h2 {
            font-size: 22px;
            margin: 0;
            color: #2b2b2b;
        }

        .header-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .btn {
            border: none;
            cursor: pointer;
            font-weight: 700;
            border-radius: 10px;
            padding: 10px 14px;
            background: #D9B1AB;
            color: #fff;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
            transition: transform .14s ease;
        }

        .btn:hover {
            transform: translateY(-3px);
            background: #a86f68;
        }

        .btn.ghost {
            background: transparent;
            color: #555;
            box-shadow: none;
            border: 1px solid #eee;
            padding: 8px 10px;
        }

        .btn.small {
            padding: 6px 10px;
            font-size: 13px;
            border-radius: 9px;
        }

        .btn.small.ghost {
            background: transparent;
            color: #555;
            border: 1px solid #eee;
        }

        .section {
            margin-top: 14px;
        }

        .box {
            background: #fafafa;
            padding: 14px;
            border-radius: 12px;
            box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.03);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 14px;
        }


        /* ---------- تحسين كروت الإحصائيات ---------- */
        .stat {
            padding: 18px;
            background: linear-gradient(135deg, #ffffff, #faf7f8);
            border-radius: 14px;
            border: 1px solid #f3e7ea;
            box-shadow: 0 4px 16px rgba(217, 178, 173, 0.1);
            transition: .25s ease;
        }

        .stat:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(217, 178, 173, 0.15);
        }

        /* ---------- كروت الفواتير ---------- */
        .invoice-card {
            background: #fff;
            border: 1px solid #f3e7ea;
            border-radius: 16px;
            padding: 18px 20px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: 0.25s ease;
            box-shadow: 0 3px 8px rgba(217, 178, 173, 0.08);
            cursor: pointer;
        }

        .invoice-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 16px rgba(217, 178, 173, 0.22);
        }

        .invoice-card .info h3 {
            font-size: 19px;
            color: #2b2b2b;
            margin-bottom: 6px;
            font-weight: 800;
        }

        .invoice-card .info p {
            margin: 2px 0;
            color: #555;
            font-size: 14px;
        }

        /* ---------- إجمالي الفاتورة ---------- */
        .total-amount {
            font-size: 17px;
            font-weight: bold;
            color: #198754;
            background: #e8f8ec;
            padding: 8px 14px;
            border-radius: 12px;
            border: 1px solid #c9ebd1;
            display: inline-block;
            min-width: 90px;
            text-align: center;
        }

        /* === Money Highlight Box (Gain Style) === */
        .stat-money {
            background: #e9f9ee;
            /* أخضر باهت لطيف */
            border: 1px solid #c8ebd1;
            padding: 18px;
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(0, 128, 0, 0.06);
        }

        .stat-money .stat-label {
            font-size: 14px;
            font-weight: 700;
            color: #1a7f37;
            margin-bottom: 8px;
        }

        .stat-money-value {
            font-size: 22px;
            font-weight: 800;
            color: #198754;
            /* الأخضر الرسمي (Bootstrap success) */
            background: #ffffff;
            padding: 10px 14px;
            display: inline-block;
            border-radius: 10px;
            border: 1px solid #c8ebd1;
            min-width: 140px;
            text-align: center;
            box-shadow: 0 2px 6px rgba(0, 128, 0, 0.05);
        }
    </style>
@endsection
