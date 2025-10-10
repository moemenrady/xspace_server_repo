@extends('layouts.app_page_admin')

@section('content')
    <div class="expenses-wrapper">

        <!-- فورم إضافة ساعات اليوم الكامل -->
        <div class="expense-form">
            <h2>✚ إضافة عدد ساعات اليوم الكامل</h2>

            {{-- رسائل الفلاش --}}
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('full-day-hours.store') }}" method="POST" novalidate>
                @csrf

                <div class="form-group">
                    <label>عدد الساعات <small class="muted">(مثال: 8)</small></label>
                    <input type="number" name="hours_count" placeholder="عدد الساعات" value="{{ old('hours_count') }}"
                        min="1" required>
                    @error('hours_count')
                        <div class="small text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>اسم المُسجل <small class="muted">(اختياري)</small></label>
                    <input type="text" name="setter_name" placeholder="اسم المُسجل" value="{{ old('setter_name') }}">
                    @error('setter_name')
                        <div class="small text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>حالة التفعيل</label>
                    <select name="is_active" class="styled-select" required>
                        <option value="1" {{ old('is_active') == '1' ? 'selected' : '' }}>مفعل</option>
                        <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>غير مفعل</option>
                    </select>
                    @error('is_active')
                        <div class="small text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn-submit">💾 حفظ عدد الساعات</button>
            </form>
        </div>

        <!-- ليستة ساعات اليوم الكامل -->
        <div class="expense-list">
            <div class="list-header">
                <h3>📋 قائمة ساعات اليوم الكامل</h3>
            </div>

            <div class="cards">
                @forelse($fullDayHours as $hour)
                    <div class="card-content">
                        <div class="content-left">
                            <h4>✴ {{ $hour->hours_count }} ساعة</h4>
                            <p class="meta small">بواسطة: {{ $hour->setter_name ?? '---' }}</p>
                            <p class="meta small">الحالة: {{ $hour->is_active ? 'مفعل' : 'غير مفعل' }}</p>
                        </div>

                        <div class="content-right">
                            <span class="date small">🕒 {{ $hour->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                @empty
                    <p class="empty">🚀 لا توجد ساعات حتى الآن</p>
                @endforelse
            </div>
        </div>

    </div>
@endsection

@section('style')
    <style>
        /* نفس CSS صفحة اشتراكات مع الاحتفاظ بالستايل الأصلي */
        :root {
            --accent: #E6C7FF;
            --bg: #F6F7FB;
            --card: #FFFFFF;
            --muted: #777;
            --max-width: 1100px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: "Cairo", sans-serif;
            margin: 0;
            color: #333;
            background: var(--bg);
        }

        .expenses-wrapper {
            max-width: var(--max-width);
            margin: 0 auto;
            padding: 28px;
            display: flex;
            flex-direction: column;
            gap: 22px;
            padding-bottom: 40px;
        }

        .expense-form,
        .expense-list {
            background: var(--card);
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 10px 26px rgba(0, 0, 0, 0.06);
            border: 1px solid #f1ecef;
            transition: transform .28s, box-shadow .28s;
        }

        .expense-form:hover,
        .expense-list:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.08);
        }

        h2,
        h3 {
            margin: 0 0 14px;
            font-size: 20px;
        }

        .muted {
            color: var(--muted);
            font-weight: 500;
            font-size: 13px;
        }

        .form-group {
            margin-bottom: 14px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
        }

        input[type="text"],
        input[type="number"],
        select.styled-select {
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid #e9e6e6;
            font-size: 15px;
            background: #fff;
            transition: box-shadow .16s, transform .12s, border-color .12s;
            appearance: none;
        }

        input:focus,
        select.styled-select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 10px 30px rgba(230, 199, 255, 0.12);
            transform: translateY(-1px);
        }

        .btn-submit {
            display: block;
            width: 100%;
            background: var(--accent);
            color: #fff;
            border: 0;
            padding: 12px 14px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 8px 22px rgba(217, 177, 171, 0.12);
            transition: transform .18s ease, box-shadow .18s ease;
            min-height: 44px;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
        }

        .list-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
        }

        .cards {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 6px;
        }

        .card-content {
            background: #FBFBFF;
            padding: 14px;
            border-radius: 12px;
            border-left: 6px solid var(--accent);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
            transition: transform .22s, box-shadow .22s;
        }

        .card-content:hover {
            transform: translateY(-6px);
            box-shadow: 0 14px 34px rgba(0, 0, 0, 0.08);
        }

        .card-content h4 {
            margin: 0;
            font-size: 16px;
            font-weight: 800;
        }

        .card-content .meta {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: 13px;
        }

        .content-left {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .content-right {
            text-align: right;
            font-size: 13px;
            color: var(--muted);
            min-width: 86px;
        }

        .empty {
            color: #999;
            text-align: center;
            padding: 18px;
            font-style: italic;
        }

        @media(min-width:900px) {
            .expenses-wrapper {
                flex-direction: row;
                gap: 28px;
                align-items: flex-start;
            }

            .expense-form {
                flex: 0 0 380px;
                padding: 22px;
            }

            .expense-list {
                flex: 1;
                padding: 22px;
            }
        }

        @media(min-width:700px) and (max-width:899px) {
            .expenses-wrapper {
                padding: 28px;
            }

            .expense-form {
                flex: 0 0 360px;
            }
        }

        @media(max-width:420px) {
            .expenses-wrapper {
                padding: 16px;
            }

            .expense-form,
            .expense-list {
                padding: 14px;
                border-radius: 12px;
            }

            h2,
            h3 {
                font-size: 18px;
            }

            input[type="text"],
            input[type="number"],
            select.styled-select {
                padding: 10px 12px;
                font-size: 14px;
            }

            .btn-submit {
                padding: 10px;
                font-size: 15px;
                min-height: 40px;
            }

            .card-content {
                padding: 12px;
            }

            .content-right {
                min-width: 70px;
                font-size: 12px;
            }
        }
    </style>
@endsection
