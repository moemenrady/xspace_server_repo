@extends('layouts.app_page')

@section('content')
    <div class="add-expense-wrapper">

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

        <div class="page-actions">
            <a href="{{ route('admin_draft.create') }}" class="start-session-btn" aria-label="بدء جلسه">مصروف الموظفين</a>
        </div>
        <h2 class="page-title">إضافة مصروف جديد</h2>

        <div class="form-container animate__animated animate__fadeInUp">
            <form action="{{ route('expense.store') }}" method="POST" class="expense-form">
                @csrf

                <div class="form-group">
                    <label for="expense_type_id">نوع المصروف</label>
                    <select name="expense_type_id" id="expense_type_id" class="styled-select" required>
                        <option value="">اختر النوع</option>
                        @foreach ($types as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="amount">المبلغ</label>
                    <input type="number" step="0.01" name="amount" id="amount" placeholder="أدخل قيمة المصروف"
                        required>
                </div>

                <div class="form-group">
                    <label for="note">الوصف (اختياري)</label>
                    <textarea name="note" id="note" rows="3" placeholder="اكتب ملاحظة..."></textarea>
                </div>

                <button type="submit" class="btn-submit">💰 إضافة المصروف</button>
            </form>
        </div>
    </div>

    <style>
        body {
            font-family: "Cairo", sans-serif;
            background: #F2F2F2;
            margin: 0;
            padding: 90px;
            color: #333;
        }

        .page-title {
            font-size: 28px;
            margin-bottom: 25px;
            color: #444;
            text-align: center;
        }

        .form-container {
            background: #fff;
            padding: 35px;
            border-radius: 18px;
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.1);
            max-width: 550px;
            margin: auto;
        }

        .form-group {
            margin-bottom: 18px;
            text-align: right;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }

        .styled-select,
        input[type="number"],
        textarea {
            width: 100%;
            padding: 12px 15px;
            border-radius: 12px;
            border: 1px solid #ccc;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .styled-select:focus,
        input[type="number"]:focus,
        textarea:focus {
            border-color: #D9B1AB;
            box-shadow: 0 0 10px rgba(217, 177, 171, 0.4);
            outline: none;
        }

        .btn-submit {
            background: linear-gradient(135deg, #D9B1AB, #c48c85);
            border: none;
            padding: 14px 28px;
            border-radius: 12px;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: transform 0.25s, box-shadow 0.25s;
        }

        .btn-submit:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(217, 177, 171, 0.5);
        }

        .start-session-btn {
            position: relative;
            display: inline-block;
            padding: 12px 18px;
            background: var(--btn-bg);
            color: var(--btn-text);
            font-weight: 800;
            /* Bold */
            font-size: 15px;
            border: 1px solid var(--btn-border);
            border-radius: 14px;
            text-decoration: none;
            letter-spacing: .2px;
            box-shadow: 0 6px 14px rgba(0, 0, 0, .12), inset 0 -2px 0 rgba(0, 0, 0, .05);
            transition: transform .25s ease, box-shadow .25s ease, background-color .25s ease, border-color .25s ease;
            overflow: hidden;
            /* لإخفاء الوميض أثناء الحركة */
            -webkit-tap-highlight-color: transparent;
        }

        /* لمعان عصري يمر على الزر */
        .start-session-btn::before {
            content: "";
            position: absolute;
            inset: -120% -30%;
            background: linear-gradient(120deg, transparent 35%, rgba(255, 255, 255, .65) 50%, transparent 65%);
            transform: translateX(-100%);
            transition: transform .6s ease;
            pointer-events: none;
        }

        .start-session-btn:hover {
            background-color: var(--btn-bg-hover);
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 10px 22px rgba(0, 0, 0, .16), inset 0 -2px 0 rgba(0, 0, 0, .05);
            border-color: #e9c94e;
        }

        .start-session-btn:hover::before {
            transform: translateX(100%);
        }

        /* تأثير ضغط خفيف */
        .start-session-btn:active {
            transform: translateY(0) scale(0.99);
            box-shadow: 0 6px 14px rgba(0, 0, 0, .12), inset 0 -2px 0 rgba(0, 0, 0, .08);
        }

        /* وضيح لليوزرز باستخدام الكيبورد */
        .start-session-btn:focus {
            outline: none;
            box-shadow:
                0 0 0 3px rgba(255, 228, 131, .6),
                0 10px 22px rgba(0, 0, 0, .16),
                inset 0 -2px 0 rgba(0, 0, 0, .05);
        }

        /* احترام إعدادات تقليل الحركة */
        @media (prefers-reduced-motion: reduce) {

            .start-session-btn,
            .start-session-btn::before {
                transition: none;
            }

            .start-session-btn:hover {
                transform: none;
            }
        }

        /* المسافة الإضافية للموبايل فقط */
        @media (max-width: 768px) {
            .add-expense-wrapper {
                padding-top: 90px !important;
                /* زودنا المسافة من فوق */
            }
        }
    </style>
@endsection
