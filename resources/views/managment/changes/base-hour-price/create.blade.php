@extends('layouts.app_page_admin')

@section('content')
    <div class="records-wrapper">

        <!-- فورم إضافة سعر ساعة -->
        <div class="record-form">
            <h2>💰 إضافة سعر ساعة جديد</h2>
            <form action="{{ route('records.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label>سعر الساعة</label>
                    <input type="number" name="hour_price" placeholder="مثال: 100 جنيه" value="{{ old('hour_price') }}"
                        required>
                    @error('hour_price')
                        <div class="small text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>اسم الشخص الذي أضاف السعر</label>
                    <input type="text" name="setter_name" placeholder="الاستاذ كذا" value="{{ old('setter_name') }}"
                        required>
                    @error('setter_name')
                        <div class="small text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn-submit mt-2">💾 حفظ السعر</button>
            </form>
        </div>

        <!-- ليستة أسعار الساعات -->
        <div class="record-list">
            <h3>📋 سجل أسعار الساعة</h3>
            <div class="cards">
                @forelse($records as $record)
                    <div class="card-content {{ $record->is_active ? 'active-card' : '' }}">
                        <h4>📌 السعر: {{ $record->base_hour_price }} جنيه</h4>
                        <p class="meta small">👤 أضيف بواسطة: {{ $record->setter_name }}</p>
                        <span class="date small">
                            🕒 تمت الإضافة {{ $record->created_at->diffForHumans() }}
                        </span>
                    </div>
                @empty
                    <p class="empty">🕒 لا توجد أسعار حتى الآن</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@section('style')
    <style>
        :root {
            --accent: #E6C7FF;
            --bg: #F6F7FB;
            --card: #FFFFFF;
            --muted: #777;
            --max-width: 1100px;
        }

        body {
            font-family: "Cairo", sans-serif;
            margin: 0;
            color: #333;
            background: var(--bg);
        }

        .records-wrapper {
            max-width: var(--max-width);
            margin: 0 auto;
            padding: 28px;
            display: flex;
            flex-direction: column;
            gap: 22px;
            padding-bottom: 40px;
        }

        .record-form,
        .record-list {
            background: var(--card);
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 10px 26px rgba(0, 0, 0, 0.06);
            border: 1px solid #f1ecef;
            transition: transform .28s, box-shadow .28s;
        }

        .record-form:hover,
        .record-list:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.08);
        }

        h2,
        h3 {
            margin: 0 0 14px;
            font-size: 20px;
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
        input[type="number"] {
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid #e9e6e6;
            font-size: 15px;
            background: #fff;
        }

        input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 10px 30px rgba(230, 199, 255, 0.12);
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
            min-height: 44px;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
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
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
            animation: slideUp .6s ease both;
        }

        .card-content h4 {
            margin: 0;
            font-size: 16px;
            font-weight: 800;
        }

        .card-content .meta,
        .card-content .date {
            font-size: 13px;
            color: var(--muted);
            margin: 0;
        }

        .active-card {
            border-left-color: #27ae60 !important;
            background: #F4FFF8 !important;
        }

        .empty {
            color: #999;
            text-align: center;
            padding: 18px;
            font-style: italic;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media(min-width:900px) {
            .records-wrapper {
                flex-direction: row;
                gap: 28px;
                align-items: flex-start;
            }

            .record-form {
                flex: 0 0 380px;
                padding: 22px;
            }

            .record-list {
                flex: 1;
                padding: 22px;
            }
        }

        @media(min-width:700px) and (max-width:899px) {
            .records-wrapper {
                padding: 28px;
            }

            .record-form {
                flex: 0 0 360px;
            }
        }

        @media(max-width:420px) {
            .records-wrapper {
                padding: 16px;
            }

            .record-form,
            .record-list {
                padding: 14px;
                border-radius: 12px;
            }

            h2,
            h3 {
                font-size: 18px;
            }

            input[type="text"],
            input[type="number"] {
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
        }
    </style>
@endsection
