@extends('layouts.app_page')

@section('content')

    @php
        // خريطة action_type => label عربي
        $actionLabels = [
            'new_subscription' => 'اشتراك جديد',
            'renew_subscription' => 'تجديد اشتراك',
            'end_session' => 'إنهاء جلسة',
            'separate_sale' => 'عملية بيع منفصلة',
            'add_booking' => 'إضافة حجز',
            'end_booking' => 'إنهاء حجز',
            'expense_note' => 'إضافة مصروف',
        ];
    @endphp
    <div class="shift-wrapper">

        {{-- Alerts --}}
        @if (session('success'))
            <script>
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: "{{ session('success') }}",
                    showConfirmButton: false,
                    timer: 3500,
                    timerProgressBar: true,
                    background: '#fff',
                    color: '#333',
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp'
                    }
                });
            </script>
        @endif

        @if (session('error'))
            <script>
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: "{{ session('error') }}",
                    showConfirmButton: false,
                    timer: 3500,
                    timerProgressBar: true,
                    background: '#fff',
                    color: '#333',
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp'
                    }
                });
            </script>
        @endif

        @if (!$shift)
            {{-- مفيش شيفت مفتوح --}}
            <div class="form-container animate__animated animate__fadeInUp" style="text-align:center;">
                <h2 class="page-title">لم تبدأ أي شيفت بعد</h2>
                <form action="{{ route('shift.start') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-start-shift">🚀 بدء الشيفت</button>
                </form>
            </div>
        @else
            {{-- في شيفت مفتوح --}}
            <h2 class="page-title">الشيفت الحالي</h2>

            <div class="form-container animate__animated animate__fadeInUp shift-summary">
                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="summary-label">وقت البدء</div>
                        <div class="summary-value">{{ $shift->start_time }}</div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">إجمالي الإيرادات</div>
                        <div class="summary-value">{{ number_format($shift->total_amount, 2) }} ج.م</div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">إجمالي المصروفات</div>
                        <div class="summary-value">{{ number_format($shift->total_expense, 2) }} ج.م</div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">الصافي</div>
                        <div class="summary-value">{{ number_format($shift->net_profit, 2) }} ج.م</div>
                    </div>
                </div>

                <form action="{{ route('shift.end') }}" method="POST"
                    onsubmit="return confirm('هل أنت متأكد من إنهاء الشيفت؟');" class="end-form">
                    @csrf
                    <button type="submit" class="btn-end-shift">⛔ إنهاء الشيفت</button>
                </form>
            </div>

            <h3 class="page-title">العمليات خلال الشيفت</h3>

            <div class="form-container animate__animated animate__fadeInUp actions-wrapper" aria-live="polite">
                <!-- Desktop Table -->
                <div class="table-responsive">
                    <table class="actions-table" role="table" aria-label="قائمة العمليات">
                        <thead>
                            <tr>
                                <th>العملية</th>
                                <th>المبلغ</th>
                                <th>المصروف</th>
                                <th>الوصف</th>
                                <th>التاريخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($shift->actions as $action)
                                <tr>
                                    <td>
                                        {{ $actionLabels[$action->action_type] ?? $action->action_type }}
                                    </td>
                                    <td>{{ $action->amount > 0 ? number_format($action->amount, 2) : '-' }}</td>
                                    <td>{{ $action->expense_amount ? number_format($action->expense_amount, 2) : '-' }}
                                    </td>
                                    <td class="td-notes">{{ $action->notes ?? '-' }}</td>
                                    <td>{{ $action->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align:center;">لا توجد عمليات مسجلة</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="cards-grid" aria-hidden="true">
                    @forelse ($shift->actions as $action)
                        <article class="action-card" role="article" aria-label="عملية {{ $action->id }}">
                            <div class="card-top">
                                <div class="card-type">
                                    {{ $actionLabels[$action->action_type] ?? $action->action_type }}
                                </div>
                                <div class="card-date">{{ $action->created_at->format('Y-m-d H:i') }}</div>
                            </div>

                            <div class="card-body">
                                <div class="card-row"><span class="meta">المبلغ:</span>
                                    <span
                                        class="val">{{ $action->amount > 0 ? number_format($action->amount, 2) : '-' }}</span>
                                </div>
                                <div class="card-row"><span class="meta">المصروف:</span>
                                    <span
                                        class="val">{{ $action->expense_amount ? number_format($action->expense_amount, 2) : '-' }}</span>
                                </div>
                                <div class="card-row note"><span class="meta">الوصف:</span>
                                    <div class="val">{{ $action->notes ?? '-' }}</div>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="no-cards">لا توجد عمليات مسجلة</div>
                    @endforelse
                </div>
            </div>
        @endif
    </div>

    <style>
        :root {
            --bg: #F2F2F2;
            --card-bg: #fff;
            --accent-start: #4CAF50;
            --accent-end: #2e7d32;
            --danger-start: #e53935;
            --danger-end: #b71c1c;
            --text: #333;
            --muted: #666;
            --radius: 18px;
        }

        body {
            font-family: "Cairo", sans-serif;
            background: var(--bg);
            margin: 0;
            padding: 32px;
            color: var(--text);
            -webkit-font-smoothing: antialiased;
        }

        .page-title {
            font-size: 22px;
            margin-bottom: 16px;
            color: #444;
            text-align: center;
            font-weight: 600;
        }

        .form-container {
            background: var(--card-bg);
            padding: 22px;
            border-radius: var(--radius);
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }

        /* summary grid */
        .shift-summary .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            align-items: center;
            margin-bottom: 16px;
        }

        .summary-item {
            background: #fff;
            padding: 12px;
            border-radius: 12px;
            border: 1px solid #f0f0f0;
            text-align: center;
        }

        .summary-label {
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 6px;
        }

        .summary-value {
            font-size: 15px;
            font-weight: 700;
            color: #222;
        }

        .end-form {
            display: flex;
            justify-content: flex-end;
            margin-top: 8px;
        }

        .btn-start-shift,
        .btn-end-shift {
            border: none;
            padding: 12px 22px;
            border-radius: 12px;
            color: #fff;
            font-weight: 700;
            cursor: pointer;
            font-size: 16px;
            transition: transform 0.18s, box-shadow 0.18s;
        }

        .btn-start-shift {
            background: linear-gradient(135deg, var(--accent-start), var(--accent-end));
            width: 100%;
            max-width: 320px;
        }

        .btn-end-shift {
            background: linear-gradient(135deg, var(--danger-start), var(--danger-end));
            min-width: 160px;
        }

        .btn-start-shift:hover,
        .btn-end-shift:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
        }

        /* table */
        .table-responsive {
            overflow-x: auto;
        }

        .actions-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            font-size: 14px;
        }

        .actions-table th,
        .actions-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .actions-table th {
            background: #fafafa;
            font-weight: 700;
            color: #444;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .td-notes {
            max-width: 280px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            direction: rtl;
        }

        /* mobile cards */
        .cards-grid {
            display: none;
            gap: 12px;
        }

        .action-card {
            background: linear-gradient(180deg, #fff, #fff);
            border-radius: 12px;
            padding: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .card-top {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            align-items: center;
        }

        .card-type {
            font-weight: 700;
            color: #222;
        }

        .card-date {
            font-size: 12px;
            color: var(--muted);
        }

        .card-body .card-row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            align-items: center;
            padding: 6px 0;
            border-top: 1px dashed #f3f3f3;
        }

        .card-body .card-row:first-of-type {
            border-top: none;
        }

        .card-body .note .val {
            white-space: normal;
            text-align: left;
        }

        .meta {
            color: var(--muted);
            font-size: 13px;
        }

        .val {
            font-weight: 600;
            color: #222;
        }

        .no-cards {
            text-align: center;
            color: var(--muted);
            padding: 14px 0;
        }

        /* responsiveness */
        @media (max-width: 900px) {
            body {
                padding: 16px;
            }

            .shift-summary .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .actions-wrapper .table-responsive {
                display: none;
            }

            .cards-grid {
                display: grid;
                grid-template-columns: 1fr;
            }

            .end-form {
                justify-content: center;
            }

            .btn-end-shift {
                width: 100%;
                max-width: none;
            }

            .summary-item {
                text-align: left;
                padding: 10px;
            }

            .summary-label {
                text-align: left;
            }

            .summary-value {
                text-align: left;
            }

            .page-title {
                font-size: 20px;
            }
        }

        @media (min-width: 901px) and (max-width: 1200px) {
            .shift-summary .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .td-notes {
                max-width: 200px;
            }
        }

        /* accessibility focus */
        button:focus,
        a:focus {
            outline: 3px solid rgba(0, 0, 0, 0.06);
            outline-offset: 2px;
        }
    </style>
@endsection
