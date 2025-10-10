@extends('layouts.app_page')

@section('content')
    <div class="drafts-wrapper">
      
        <!-- فورم إضافة Draft -->
        <div class="draft-form">
            <h2>✏️ إضافة ملاحظة مصروف (Draft)</h2>
            <form action="{{ route('expense-drafts.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>الوصف</label>
                    <input type="text" name="note" placeholder="مثال: شراء ورق للطابعة..." >
                </div>
                <div class="form-group">
                    <label>نوع المصروف</label>
                    <select name="expense_type_id" class="styled-select">
                        <option value="">اختر النوع...</option>
                        @foreach ($types as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>المبلغ (تقديري)</label>
                    <input type="number" step="0.01" name="estimated_amount" placeholder="مثال: 200">
                </div>

                <button type="submit" class="btn-submit">💾 حفظ Draft</button>
            </form>
        </div>

        <!-- ليستة الملاحظات -->
        <div class="draft-list">
            <h3>📋 قائمة الملاحظات</h3>
            <div class="cards">
                @forelse($drafts as $draft)
                    <div class="card-content">
                        <h4>{{ $draft->note }}</h4>
                        <p class="amount">💰 {{ $draft->estimated_amount ?? 'غير محدد' }}</p>
                        <p class="type">🏷️ {{ $draft->expenseType->name ?? 'غير محدد' }}</p>
                        <span class="date">🕒 {{ $draft->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="empty">🚀 لا توجد ملاحظات حتى الآن</p>
                @endforelse
            </div>
        </div>

    </div>
@endsection
@section('style')
    <style>
        :root{
            --card-max-width: 980px;
            --body-padding-desktop: 40px;
            --body-padding-mobile: 12px;
            --base-font: "Cairo", sans-serif;
            --muted: #777;
            --accent-bg: #fff;
            --accent-border: #eee;
            --accent-color: #444;
            --accent-pill: #D9B1AB; /* حافظين اللون اللى طلبته */
        }

        html,body{ box-sizing:border-box; }
        *,*::before,*::after{ box-sizing:inherit; }

        body {
            font-family: var(--base-font);
            margin: 0;
            padding: var(--body-padding-desktop);
            background: #F2F2F2;
            color: #333;
            -webkit-font-smoothing:antialiased;
        }

        /* ===== Container: شبيه بصفحة الجلسة ===== */
        .drafts-wrapper{
            max-width: var(--card-max-width);
            margin: 18px auto;
            display: grid;
            gap: 28px;
            grid-template-columns: 1fr 420px; /* فورم + ليستة جانبية على الديسكتوب */
        }

        /* لو عايز العمودين يظهروا عموديًا على اللابتوب الصغير ممكن تعدل القيمة أعلاه */
        @media (max-width: 1024px){
            .drafts-wrapper{
                grid-template-columns: 1fr; /* عمود واحد على التابلت والموبايل */
                padding: 0 12px;
                gap: 20px;
            }
        }

        /* ===== الكروت / الفورم ===== */
        .draft-form,
        .draft-list {
            background: var(--accent-bg);
            padding: 22px; /* أقل من السابق لتقليل الإحساس بالـ "مفرود" */
            border-radius: 14px;
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.06);
            transition: transform .28s, box-shadow .28s;
            border: 1px solid var(--accent-border);
        }

        /* نخفف الحركة على الموبايل */
        @media (max-width: 420px){
            .draft-form,
            .draft-list {
                padding: 14px; /* padding أصغر للهواتف */
                border-radius: 12px;
            }
        }

        .draft-form:hover,
        .draft-list:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.08);
        }

        h2, h3 {
            margin: 0 0 14px 0;
            color: var(--accent-color);
            font-size: 18px;
        }

        /* ===== الفورم: الحقول ===== */
        .form-group { margin-bottom: 14px; }
        input[type="text"],
        input[type="number"],
        select.styled-select {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #e6e6e6;
            border-radius: 10px;
            outline: none;
            font-size: 15px;
            transition: box-shadow .2s, transform .15s;
            background: #fff;
        }
        input[type="text"]:focus,
        input[type="number"]:focus,
        select.styled-select:focus {
            box-shadow: 0 6px 18px rgba(217,177,171,0.12);
            transform: translateY(-1px);
            border-color: var(--accent-pill);
        }

        .btn-submit{
            background: var(--accent-pill);
            color: #fff;
            border: none;
            padding: 12px 16px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            box-shadow: 0 6px 18px rgba(217,177,171,0.18);
            transition: transform .18s, box-shadow .18s;
        }
        .btn-submit:hover{
            transform: translateY(-3px);
        }

        /* ===== ليستة الكروت ===== */
        .cards{
            display: grid;
            gap: 12px;
            align-content: start;
        }

        /* Card style — مهم: padding أصغر على الموبايل */
        .card-content{
            background: #fff;
            padding: 14px 16px; /* أقل padding علشان مايبقاش مفرود على الموبايل */
            border-radius: 12px;
            border-left: 6px solid var(--accent-pill);
            box-shadow: 0 6px 18px rgba(0,0,0,0.04);
            transition: transform .2s, box-shadow .2s;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .card-content:hover{
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(0,0,0,0.08);
        }

        .card-content h4{
            margin: 0;
            font-size: 16px;
            font-weight: 800;
            color: #333;
        }

        .card-content .amount{
            margin: 0;
            font-size: 14px;
            font-weight: 700;
            color: #28a745;
        }

        .card-content .type{
            margin: 0;
            font-size: 13px;
            color: #D9B1AB;
            font-weight: 700;
        }

        .card-content .date{
            margin-top: 6px;
            font-size: 13px;
            color: #888;
        }

        .empty {
            color: #999;
            font-style: italic;
            text-align: center;
            padding: 18px;
        }

        /* ===== سمارت adjustments للـ mobile: الكارت يملى العرض ويحافظ على راحة الحقول ===== */
        @media (max-width: 420px){
            .drafts-wrapper { padding: 0 10px; }
            .card-content { padding: 12px; border-radius: 10px; }
            .cards { gap: 10px; }
            .btn-submit { padding: 10px; }
            h2, h3 { font-size: 16px; }
        }

        /* ===== Accessibility / touch targets ===== */
        .btn-submit, input[type="text"], input[type="number"], .styled-select { min-height: 44px; }

        /* ==== Optional: if you want the draft-list to be sticky on wide screens (nice UX) ==== */
        @media (min-width: 1100px){
            .draft-list {
                position: sticky;
                top: 28px;
                height: calc(100vh - 56px);
                overflow: auto;
                padding-bottom: 32px;
            }
        }
    </style>
@endsection
