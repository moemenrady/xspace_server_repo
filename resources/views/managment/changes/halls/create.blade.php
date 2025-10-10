@extends('layouts.app_page_admin')

@section('content')
    <div class="hall-wrapper">

        <!-- فورم إضافة قاعة -->
        <div class="hall-form">
            <h2>🏛️ إضافة قاعة جديدة</h2>

            @if(session('success'))
                <div class="alert-success" style="margin-bottom:12px;padding:10px;border-radius:8px;background:#ECFDF3;color:#065F46;">
                    {{ session('success') }}
                </div>
            @endif

            <form id="hallForm" action="{{ route('halls.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label>اسم القاعة</label>
                    <input type="text" name="name" placeholder="مثال: قاعة الاجتماعات" value="{{ old('name') }}" required>
                    @error('name') <div class="small text-danger">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>الحد الأدنى للسعة</label>
                    <input type="number" name="min_capacity" placeholder="مثال: 10" value="{{ old('min_capacity') }}" required>
                    @error('min_capacity') <div class="small text-danger">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>الحد الأقصى للسعة</label>
                    <input type="number" name="max_capacity" placeholder="مثال: 50" value="{{ old('max_capacity') }}" required>
                    @error('max_capacity') <div class="small text-danger">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>اسم المُنشئ</label>
                    <input type="text" name="setter_name" placeholder="مثال: الاستاذ كذا" value="{{ old('setter_name') }}" required>
                    @error('setter_name') <div class="small text-danger">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>الحالة</label>
                    <select name="is_active" class="styled-select">
                        <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>مفعلة ✅</option>
                        <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>غير مفعلة ❌</option>
                    </select>
                    @error('is_active') <div class="small text-danger">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn-submit mt-3">💾 حفظ القاعة</button>
            </form>
        </div>

        <!-- ليستة القاعات -->
        <div class="hall-list">
            <h3>📋 قائمة القاعات</h3>

            <div class="cards">
                @forelse($halls as $hall)
                    <div class="card-content {{ $hall->is_active ? '' : 'inactive-card' }}">
                        <div class="content-left">
                            <h4>🏛️ {{ $hall->name }}</h4>
                            <p class="meta small">👤 أضيف بواسطة: {{ $hall->setter_name }}</p>
                            <p class="capacity">👥 من {{ $hall->min_capacity }} لـ {{ $hall->max_capacity }} فرد</p>
                            <p class="meta small">
                                الحالة:
                                @if($hall->is_active)
                                    <span>✅ مفعلة</span>
                                @else
                                    <span>❌ غير مفعلة</span>
                                @endif
                            </p>
                        </div>

                        <div class="content-right">
                            <span class="date small">🕒 {{ $hall->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                @empty
                    <p class="empty">🚀 لا توجد قاعات حتى الآن</p>
                @endforelse
            </div>

            @if(method_exists($halls, 'links'))
                <div class="mt-3">{{ $halls->links() }}</div>
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

        * { box-sizing: border-box; }

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
            box-shadow: 0 10px 26px rgba(0,0,0,0.06);
            border: 1px solid #f1ecef;
            transition: transform .28s, box-shadow .28s;
        }

        .hall-form:hover,
        .hall-list:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 36px rgba(0,0,0,0.08);
        }

        h2, h3 {
            margin: 0 0 14px;
            font-size: 20px;
        }

        .form-group { margin-bottom: 14px; }

        label { display:block; margin-bottom:6px; font-weight:600; }

        input[type="text"],
        input[type="number"],
        select.styled-select {
            width:100%;
            padding:12px 14px;
            border-radius:10px;
            border:1px solid #e9e6e6;
            font-size:15px;
            background:#fff;
        }

        input:focus,
        select.styled-select:focus {
            outline:none;
            border-color:var(--accent);
            box-shadow:0 10px 30px rgba(230,199,255,0.12);
        }

        .btn-submit {
            display:block;
            width:100%;
            background:var(--accent);
            color:#fff;
            border:0;
            padding:12px 14px;
            border-radius:12px;
            font-weight:700;
            cursor:pointer;
            min-height:44px;
        }

        .btn-submit:hover { transform: translateY(-3px); }

        .cards {
            display:flex;
            flex-direction:column;
            gap:12px;
            margin-top:6px;
        }

        .card-content {
            background:#FBFBFF;
            padding:14px;
            border-radius:12px;
            border-left:6px solid var(--accent);
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:12px;
            box-shadow:0 6px 20px rgba(0,0,0,0.05);
            animation: slideUp .6s ease both;
            transition: transform .3s, box-shadow .3s;
        }

        .card-content:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
        }

        .card-content h4 {
            margin:0;
            font-size:16px;
            font-weight:800;
        }

        .card-content .capacity {
            color:#27ae60;
            font-weight:700;
            margin:6px 0;
        }

        .card-content .meta,
        .card-content .date {
            font-size:13px;
            color:var(--muted);
            margin:0;
        }

        .inactive-card {
            border-left-color:#F87171 !important;
            background:#FFF5F5 !important;
        }

        .empty {
            color:#999;
            text-align:center;
            padding:18px;
            font-style:italic;
        }

        @keyframes slideUp {
            from { opacity:0; transform: translateY(20px); }
            to   { opacity:1; transform: translateY(0); }
        }

        @media(min-width:900px) {
            .hall-wrapper { flex-direction:row; gap:28px; align-items:flex-start; }
            .hall-form { flex:0 0 380px; padding:22px; }
            .hall-list { flex:1; padding:22px; }
        }

        @media(min-width:700px) and (max-width:899px) {
            .hall-wrapper { padding:28px; }
            .hall-form { flex:0 0 360px; }
        }

        @media(max-width:420px) {
            .hall-wrapper { padding:16px; }
            .hall-form, .hall-list { padding:14px; border-radius:12px; }
            h2, h3 { font-size:18px; }
            input[type="text"], input[type="number"], select.styled-select { padding:10px 12px; font-size:14px; }
            .btn-submit { padding:10px; font-size:15px; min-height:40px; }
            .card-content { padding:12px; }
        }
    </style>
@endsection
