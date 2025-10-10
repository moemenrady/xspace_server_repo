@extends('layouts.app_page')

@section('content')
    <div class="drafts-wrapper">

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

        <h2 class="page-title">ملاحظات الموظفين (Drafts)</h2>

        <div class="table-container" role="region" aria-label="Drafts list">
            <!-- TABLE: يظهر على الشاشات الكبيرة -->
            <table class="styled-table" aria-hidden="false">
                <thead>
                    <tr>
                        <th>الوصف</th>
                        <th>المبلغ التقديري</th>
                        <th>الموظف</th>
                        <th>إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($drafts as $draft)
                        <tr>
                            <td class="td-note">{{ $draft->note }}</td>
                            <td>{{ $draft->estimated_amount ?? 'غير محدد' }}</td>
                            <td>{{ $draft->user->name ?? 'غير معروف' }}</td>
                            <td>
                                <form action="{{ route('expense-drafts.convert', $draft->id) }}" method="POST"
                                    class="action-form">
                                    @csrf
                                    <select name="expense_type_id" class="styled-select" required>
                                        <option value="">اختر النوع</option>
                                        @foreach ($types as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>

                                    <input type="number" step="0.01" name="amount" placeholder="القيمة الرسمية"
                                        required aria-label="Amount">
                                    <button type="submit" class="btn-submit">تحويل لمصروف</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- CARDS GRID: يظهر على الموبايل -->
            <div class="cards-grid" aria-hidden="true">
                @foreach ($drafts as $draft)
                    <article class="draft-card" role="article" aria-label="ملاحظة {{ $draft->id }}">
                        <div class="card-header">
                            <h3 class="card-user">{{ $draft->user->name ?? 'غير معروف' }}</h3>
                            <span class="card-amount">{{ $draft->estimated_amount ?? 'غير محدد' }}</span>
                        </div>
                        <p class="card-note">{{ $draft->note }}</p>

                        <form action="{{ route('expense-drafts.convert', $draft->id) }}" method="POST"
                            class="card-action-form">
                            @csrf
                            <select name="expense_type_id" class="styled-select" required>
                                <option value="">اختر النوع</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>

                            <input type="number" step="0.01" name="amount" placeholder="القيمة الرسمية" required
                                aria-label="Amount">
                            <button type="submit" class="btn-submit">تحويل</button>
                        </form>
                    </article>
                @endforeach
            </div>
        </div>
    </div>

    <style>
        /* عام */
        :root{
            --bg:#F2F2F2;
            --card-bg:#ffffff;
            --accent:#D9B1AB;
            --muted:#777;
            --text:#333;
            --radius:14px;
        }

        body {
            font-family: "Cairo", sans-serif;
            background: var(--bg);
            margin: 0;
            padding: 28px;
            color: var(--text);
            -webkit-font-smoothing:antialiased;
            -moz-osx-font-smoothing:grayscale;
        }

        .page-title {
            font-size: 24px;
            margin-bottom: 18px;
            color: #333;
            font-weight: 600;
        }

        .table-container {
            background: var(--card-bg);
            padding: 20px;
            border-radius: var(--radius);
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        /* TABLE (desktop) */
        .styled-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 15px;
        }

        .styled-table thead {
            background: var(--accent);
            color: #fff;
        }

        .styled-table th,
        .styled-table td {
            padding: 14px 12px;
            text-align: center;
            vertical-align: middle;
        }

        .td-note {
            max-width: 420px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            direction: rtl; /* يحافظ على اتجاه النص العربي */
        }

        .styled-table tbody tr {
            border-bottom: 1px solid #f3f3f3;
            transition: background 0.18s ease;
        }

        .styled-table tbody tr:hover {
            background: #fff6f5;
        }

        /* inputs & selects */
        .styled-select,
        input[type="number"] {
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid #ddd;
            margin: 6px;
            font-size: 14px;
            min-width: 140px;
            background: #fff;
        }

        .styled-select:focus,
        input[type="number"]:focus {
            border-color: var(--accent);
            box-shadow: 0 0 8px rgba(217, 177, 171, 0.28);
            outline: none;
        }

        .btn-submit {
            background: var(--accent);
            border: none;
            padding: 10px 16px;
            border-radius: 10px;
            color: #fff;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.18s, box-shadow 0.18s;
            margin-top: 6px;
        }

        .btn-submit:hover,
        .btn-submit:focus{
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(217, 177, 171, 0.32);
            outline: none;
        }

        .action-form {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* CARDS GRID (mobile) */
        .cards-grid {
            display: none; /* يبدأ مخفيًا على الديسكتوب */
            gap: 14px;
            margin-top: 6px;
        }

        .draft-card {
            background: linear-gradient(180deg, #fff, #fff);
            border-radius: 12px;
            padding: 14px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.06);
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .card-header {
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:10px;
        }

        .card-user {
            font-size:16px;
            margin:0;
            color:#222;
            font-weight:600;
        }

        .card-amount {
            font-size:14px;
            color:var(--muted);
            background: #faf0ef;
            padding:6px 10px;
            border-radius:8px;
        }

        .card-note {
            margin:0;
            color:#444;
            font-size:14px;
            white-space:pre-wrap;
        }

        .card-action-form{
            display:flex;
            gap:8px;
            flex-wrap:wrap;
            align-items:center;
            justify-content:space-between;
        }

        /* RESPONSIVE BREAKPOINTS */
        @media (max-width: 900px) {
            /* إخفاء الجدول وإظهار الكروت */
            .styled-table {
                display: none;
            }
            .cards-grid {
                display: grid;
                grid-template-columns: 1fr;
            }

            .table-container {
                padding: 16px;
            }

            body {
                padding: 16px;
            }

            .page-title {
                font-size: 20px;
                margin-bottom: 12px;
            }

            .styled-select, input[type="number"] {
                min-width: 100%;
                margin: 0;
            }

            .card-action-form .btn-submit {
                min-width: 110px;
                margin-left: 8px;
            }
        }

        @media (min-width: 901px) and (max-width: 1200px) {
            .td-note { max-width: 300px; }
        }

        /* Accessibility helpers */
        .styled-select:focus-visible, input[type="number"]:focus-visible, .btn-submit:focus-visible {
            outline: 3px solid rgba(217,177,171,0.25);
            outline-offset: 2px;
        }
    </style>
@endsection
