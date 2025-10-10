@extends('layouts.app_page_admin')

@section('content')
    <div class="hall-wrapper">

        <!-- فورم إضافة نوع مصروف -->
        <div class="hall-form">
            <h2>💳 إضافة نوع مصروف</h2>

            @if (session('success'))
                <div class="alert-success"
                    style="margin-bottom:12px;padding:10px;border-radius:8px;background:#ECFDF3;color:#065F46;">
                    {{ session('success') }}
                </div>
            @endif

            <form id="expenseTypeForm" action="{{ route('expense-type.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label>اسم النوع (مثال: مواد - صيانة)</label>
                    <input type="text" name="name" placeholder="مثال: مياه تشغيل" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="small text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>اسم واضع النوع</label>
                    <input type="text" name="setter_name" placeholder="مثال: أ. محمد" value="{{ old('setter_name') }}"
                        required>
                    @error('setter_name')
                        <div class="small text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>الظهور للمستخدمين أو للإدارة فقط</label>
                    <select name="user_appearance" required class="styled-select">
                        <option value="1" {{ old('user_appearance', '1') == '1' ? 'selected' : '' }}>يظهر للمستخدمين
                            (عام)</option>
                        <option value="0" {{ old('user_appearance') === '0' ? 'selected' : '' }}>خاص بالإدارة فقط
                        </option>
                    </select>
                    @error('user_appearance')
                        <div class="small text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn-submit mt-3">💾 حفظ نوع المصروف</button>
            </form>
        </div>

        <!-- ليستة أنواع المصروف مع فلتر -->
        <div class="hall-list">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                <h3>📋 أنواع المصروف</h3>

                <!-- فلتر: كل او الادارة فقط -->
                @php
                    $currentFilter = request()->query('appearance', 'all');
                @endphp
                <div class="filter-group" style="display:flex;gap:8px;align-items:center;">
                    <a href="{{ request()->fullUrlWithQuery(['appearance' => 'all']) }}"
                        class="filter-btn {{ $currentFilter === 'all' ? 'active-filter' : '' }}">الكل</a>
                    <a href="{{ request()->fullUrlWithQuery(['appearance' => 'admin']) }}"
                        class="filter-btn {{ $currentFilter === 'admin' ? 'active-filter' : '' }}">الإدارة فقط</a>
                </div>
            </div>

            <div class="cards">
                @forelse($records as $rec)
                    <div class="card-content {{ $rec->user_appearance ? '' : 'admin-only-card' }}">
                        <div class="content-left">
                            <h4>💠 {{ $rec->name }}</h4>
                            <p class="meta small">أضيف بواسطة: {{ $rec->setter_name }}</p>
                            <p class="meta small">الظهور:
                                @if ($rec->user_appearance)
                                    <span>🔓 عام - يظهر للمستخدمين</span>
                                @else
                                    <span>🔒 للإدارة فقط</span>
                                @endif
                            </p>
                        </div>

                        <div class="content-right" style="text-align:right;">
                            <span class="date small">🕒 {{ $rec->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                @empty
                    <p class="empty">🚀 لا توجد أنواع مصروف حتى الآن</p>
                @endforelse
            </div>

            {{-- لو انت تستخدم pagination --}}
            @if (method_exists($records, 'links'))
                <div class="mt-3">{{ $records->appends(request()->query())->links() }}</div>
            @endif
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

        * {
            box-sizing: border-box;
        }

        body {
            font-family: "Cairo", sans-serif;
            margin: 0;
            color: #333;
            background: var(--bg);
        }

        .hall-wrapper {
            max-width: var(--max-width);
            margin: 0 auto;
            padding: 28px;
            display: flex;
            flex-direction: column;
            gap: 22px;
            padding-bottom: 40px;
        }

        .hall-form,
        .hall-list {
            background: var(--card);
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 10px 26px rgba(0, 0, 0, 0.06);
            border: 1px solid #f1ecef;
            transition: transform .28s, box-shadow .28s;
        }

        .hall-form:hover,
        .hall-list:hover {
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
        input[type="number"],
        select.styled-select {
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid #e9e6e6;
            font-size: 15px;
            background: #fff;
        }

        input:focus,
        select.styled-select:focus {
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

        .admin-only-card {
            border-left-color: #F59E0B !important;
            background: #FFF8ED !important;
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

        /* فلتر الأزرار */
        .filter-btn {
            padding: 8px 12px;
            border-radius: 10px;
            border: 1px solid #eee;
            font-size: 14px;
            text-decoration: none;
            color: #333;
            background: #fff;
            transition: all .18s;
        }

        .filter-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
        }

        .active-filter {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
            font-weight: 700;
        }

        @media(min-width:900px) {
            .hall-wrapper {
                flex-direction: row;
                gap: 28px;
                align-items: flex-start;
            }

            .hall-form {
                flex: 0 0 380px;
                padding: 22px;
            }

            .hall-list {
                flex: 1;
                padding: 22px;
            }
        }

        @media(min-width:700px) and (max-width:899px) {
            .hall-wrapper {
                padding: 28px;
            }

            .hall-form {
                flex: 0 0 360px;
            }
        }

        @media(max-width:420px) {
            .hall-wrapper {
                padding: 16px;
            }

            .hall-form,
            .hall-list {
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
        }
    </style>
@endsection

@section('scripts')
    <script>
        // سلوك بسيط: لو في أخطاء فالصفحة ستبقى على الفورم مع إظهار الرسائل (handled by blade)
        // لا حاجة لجافاسكربت إضافي هنا لكن خليت الـ section جاهز لو حبيت تضيف dynamic behavior لاحقًا
    </script>
@endsection
