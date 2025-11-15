@extends('layouts.app_page')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

@section('title', 'عرض الفاتورة')

@section('content')
    <div class="subscription-container">
        <div class="card">
            {{-- رأس الفاتورة --}}
            <div class="card-header">
                <h2>فاتورة رقم: {{ $invoice->invoice_number }}</h2>
                <span class="badge">{{ $invoice->created_at->format('d/m/Y') }}</span>
            </div>
            {{-- بيانات العميل إن لم تكن مشتريات فقط --}}
            @if (!in_array($invoiceType, ['product']))
                <div class="section">
                    <div class="box">
                        <p><strong>العميل:</strong> {{ $invoice->client->name ?? 'غير معروف' }}
                            {{ $invoice->client->id ?? '-' }}</p>
                    </div>
                </div>
            @endif
            {{-- حسب نوع الفاتورة --}}

            @if (in_array($invoiceType, ['subscription', 'mixed']))
                <div class="section">
                    <h3>فاتورة اشتراك</h3>
                    @foreach ($groupedItems['subscription'] as $item)
                        <div class="box">
                            <p>اسم الخطة: {{ $item->name }}</p>
                            <p>السعر: {{ $item->price }} ج</p>
                        </div>
                    @endforeach
                </div>
            @endif

            @if (in_array($invoiceType, ['booking', 'mixed']))
                <div class="section">
                    <h3>جلسة خاصة / حجز</h3>
                    @foreach ($groupedItems['booking'] as $item)
                        <div class="box">
                            <p>القاعة: {{ $bookingData->hall->name }} </p>
                            @if ($bookingData->real_start_at)
                                <p><strong>بداية :</strong>
                                    {{ \Carbon\Carbon::parse($bookingData->real_start_at)->format('Y-m-d h:i A') }}
                                </p>
                            @endif

                            @if ($bookingData->real_end_at)
                                <p><strong>نهاية :</strong>
                                    {{ \Carbon\Carbon::parse($bookingData->real_end_at)->format('Y-m-d h:i A') }}
                                </p>
                            @endif

                            <p>سعر الساعة: {{ $hourlyRate }} ج</p>
                            <p id="duration-text-{{ $loop->index }}">مدة الجلسة: ...</p>
                            <p>الإجمالي: {{ $item->total }} ج</p>
                        </div>

                        <script>
                            (function() {
                                const minutes = {{ $actualDurationMinutes ?? 0 }};

                                function formatDurationArabic(minutes) {
                                    if (minutes < 60) {
                                        return `${minutes} دقيقة`;
                                    }
                                    const hours = Math.floor(minutes / 60);
                                    const remainingMinutes = minutes % 60;
                                    let hourText = '';
                                    if (hours === 1) hourText = 'ساعة';
                                    else if (hours === 2) hourText = 'ساعتين';
                                    else hourText = `${hours} ساعات`;

                                    let minuteText = '';
                                    if (remainingMinutes === 15) {
                                        minuteText = 'وربع';
                                    } else if (remainingMinutes === 30) {
                                        minuteText = 'ونصف';
                                    } else if (remainingMinutes === 45) {
                                        minuteText = 'إلا ربع';
                                        // نزيد ساعة لأن "إلا ربع" يعني قبل الساعة التالية بربع
                                        if (hours === 1) hourText = 'ساعتين إلا ربع';
                                        else hourText = `${hours + 1} ساعات إلا ربع`;
                                        return hourText;
                                    } else if (remainingMinutes > 0) {
                                        minuteText = `و${remainingMinutes} دقيقة`;
                                    }
                                    return `${hourText} ${minuteText}`.trim();
                                }

                                const formatted = formatDurationArabic(minutes);
                                document.getElementById("duration-text-{{ $loop->index }}").textContent =
                                    `مدة الجلسة: ${formatted}`;
                            })
                            ();
                        </script>
                    @endforeach
                </div>
                @if ($groupedItems['deposit']->isNotEmpty())
                    <div class="section">
                        <h3>💰 الدفعات المقدمة</h3>

                        @foreach ($groupedItems['deposit'] as $item)
                            <div class="box deposit-box">
                                <p>المبلغ المدفوع مقدمًا: <strong>{{ number_format($item->total, 2) }} ج</strong></p>
                                @if (!empty($item->notes))
                                    <p>ملاحظات: {{ $item->notes }}</p>
                                @endif
                               <p>تاريخ الدفع: {{ $item->created_at->format('Y-m-d h:i A') }}</p>

                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- @if ($isHasPurchase == true)
                    <div class="section">
                        <h3>🛒 المشتريات</h3>
                        <div class="purchase-list">
                            <div class="purchase-header">
                                <span>المنتج</span>
                                <span>الكمية</span>
                                <span>السعر</span>
                                <span>الإجمالي</span>
                            </div>
                            @foreach ($groupedItems['product'] as $item)
                                <div class="purchase-row">
                                    <span>{{ $item->name }}</span>
                                    <span>{{ $item->qty }}</span>
                                    <span>{{ number_format($item->price, 2) }} ج</span>
                                    <span>{{ number_format($item->total, 2) }} ج</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif --}}

            @endif

            @if (in_array($invoiceType, ['session', 'mixed']))
                <div class="section">
                    <h3>جلسة</h3>
                    @if ($invoiceType === 'session' && $sessionData)
                        <div class="section">
                            <h3>🧘‍♀️ تفاصيل الجلسة</h3>
                            <div class="box">
                                <p><strong>🕒 وقت البداية:</strong>
                                    {{ $sessionData->start_time ? \Carbon\Carbon::parse($sessionData->start_time)->format('Y-m-d h:i A') : '-' }}
                                </p>
                                <p><strong>🏁 وقت النهاية:</strong>
                                    {{ $sessionData->end_time ? \Carbon\Carbon::parse($sessionData->end_time)->format('Y-m-d h:i A') : '-' }}
                                </p>
                                <p><strong>👥 عدد الأفراد:</strong> {{ $sessionData->persons ?? '-' }}</p>

                            </div>
                        </div>
                    @endif

                    @foreach ($groupedItems['session'] as $item)
                        <div class="box">
                            <p>سعر الساعة: {{ $item->price }} ج</p>
                            <p>عدد الساعات: {{ $item->qty }}</p>
                            <p>الإجمالي: {{ $item->total }} ج</p>
                        </div>
                    @endforeach



            @endif
            {{-- 🛒 قسم المشتريات الموحد --}}
            @if ($purchaseItems->isNotEmpty())
                <div class="section">
                    <h3>🛒 المشتريات</h3>
                    <div class="purchase-list">
                        <div class="purchase-header">
                            <span>المنتج</span>
                            <span>الكمية</span>
                            <span>السعر</span>
                            <span>الإجمالي</span>
                        </div>
                        @foreach ($purchaseItems as $item)
                            <div class="purchase-row">
                                <span>{{ $item->name }}</span>
                                <span>{{ $item->qty }}</span>
                                <span>{{ number_format($item->price, 2) }} ج</span>
                                <span>{{ number_format($item->total, 2) }} ج</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($invoiceType === 'deposit')
                <div class="section">
                    <h3>مقدم الحجز</h3>
                    <div class="box">
                        <p>اسم العميل: {{ $extraData['client_name'] }}</p>
                        <p>موعد الحجز: {{ $extraData['booking_date'] }}</p>
                        @foreach ($groupedItems['deposit'] as $item)
                            <p>المبلغ المدفوع: {{ $item->total }} ج</p>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- السعر الكلي --}}
            <div class="section">
                <h3>الإجمالي الكلي</h3>
                <div class="box price">{{ $totalAmount }} ج</div>
            </div>

            <div class="section">
                <p style="text-align:center; margin-top:20px;">خلينا نشوفك تاني 😊</p>
            </div>
        </div>
    </div>
@endsection



@section('style')


    <style>
        body {
            background: #fafafa;
            font-family: "Tahoma", sans-serif;
        }

        .space {
            height: 30px;
        }

        .subscription-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }

        .card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            animation: fadeInUp 0.6s ease;
        }


        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #f1f1f1;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }

        .card-header h2 {
            font-size: 26px;
            color: #2b2b2b;
            margin: 0;
        }

        .badge {
            background: #D9B1AB;
            color: #fff;
            padding: 6px 15px;
            border-radius: 30px;
            font-weight: bold;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
        }

        .section h3 {
            color: #a86f68;
            font-size: 20px;
            margin-bottom: 10px;
        }

        .box {
            background: #fafafa;
            padding: 5px 10px;
            border-radius: 12px;
            box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 5px;
            font-size: 12px;
            line-height: 1.7;
        }

        .price {
            font-weight: bold;
            font-size: 18px;
            color: #2b2b2b;
        }

        .remaining {
            font-weight: bold;
            font-size: 22px;
            color: #008000;
        }

        /* Progress Bar */
        .progress-section {
            margin: 25px 0;
        }

        .progress-bar {
            background: #eaeaea;
            border-radius: 12px;
            height: 20px;
            overflow: hidden;
            box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .progress-fill {
            background: linear-gradient(90deg, #D9B1AB, #a86f68);
            height: 100%;
            width: 0;
            border-radius: 12px;
            transition: width 0.6s ease-in-out;
        }

        .form-btn {
            text-align: center;
            margin-top: 20px;
        }

        .btn {
            background: #D9B1AB;
            color: #fff;
            padding: 12px 30px;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            cursor: pointer;
            transition: 0.3s;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .btn:hover {
            background: #a86f68;
            transform: scale(1.05);
        }

        /* ====== تصميم ذكي لقائمة المشتريات ====== */
        .purchase-list {
            background: #fafafa;
            border-radius: 12px;
            box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.05);
            padding: 10px;
            font-size: 13px;
        }

        .purchase-header,
        .purchase-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            align-items: center;
            text-align: center;
            padding: 8px 0;
        }

        .purchase-header {
            font-weight: bold;
            border-bottom: 1px solid #e0e0e0;
            color: #a86f68;
        }

        .purchase-row {
            border-bottom: 1px dashed #e5e5e5;
            color: #333;
        }

        .purchase-row:last-child {
            border-bottom: none;
        }

        .purchase-row span {
            padding: 3px 5px;
        }

        /* استجابة ممتازة للموبايل */
        @media (max-width: 600px) {

            .purchase-header,
            .purchase-row {
                grid-template-columns: 1.5fr 0.8fr 0.8fr 0.8fr;
                font-size: 12px;
            }
        }

        /* Snackbar style */
        .snackbar {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #333;
            color: #fff;
            padding: 12px 18px;
            border-radius: 10px;
            font-size: 14px;
            z-index: 9999;
            opacity: 0;
            transform: translateX(120%);
            transition: opacity 0.4s ease, transform 0.4s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }


        .snackbar.show {
            opacity: 1;
            transform: translateX(0);
            /* 👈 تتحرك للداخل */
        }

        .snackbar.success {
            background: #28a745;
        }

        .snackbar.error {
            background: #dc3545;
        }

        /* أيقونة صغيرة */
        .snackbar i {
            font-size: 16px;
        }

        .used {
            font-weight: bold;
            font-size: 22px;
            color: #c40000;
            /* أحمر قوي */
            transition: transform 0.25s ease, color 0.25s ease;
        }

        /* تأثير بسيط عند التحديث */
        .used.updated {
            transform: scale(1.12);
        }


        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endsection
