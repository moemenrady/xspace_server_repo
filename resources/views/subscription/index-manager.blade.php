@extends('layouts.app_page')
@section('title', 'إدارة الاشتراكات')

@section('content')
    @if (session('success'))
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                showSnackbar("{{ session('success') }}", "success");
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                showSnackbar("{{ session('error') }}", "error");
            });
        </script>
    @endif

    <div class="page-container">

        {{-- زر الرجوع / إضافة مشترك --}}
        <h1 class="title">إدارة الاشتراكات</h1>
        <div class="page-actions">
            <a href="{{ route('subscriptions.create') }}" class="start-subscription-btn" aria-label="اضافة مشترك">اضافة مشترك</a>
        </div>

        {{-- صندوق البحث --}}
        <div class="search-box" style="margin-bottom:20px;">
            <input type="text" id="subscriptionSearch" placeholder="ابحث بالاسم أو رقم الهاتف" style="width:100%; padding:8px;"
                value="{{ request('search') ?? '' }}">
        </div>

        {{-- قائمة الاشتراكات (سيظهر هنا المحتوى المحمَّل عبر AJAX) --}}
        <div class="subscription-list" id="subscriptionList">
            <p class="text-center p-3">⏳ جاري التحميل...</p>
        </div>
    </div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById('subscriptionSearch');
    const list = document.getElementById('subscriptionList');

    const showRoute = @json(route('subscriptions.show', ':id'));
    const searchRoute = @json(route('subscriptions.ajaxSearch'));

    function debounce(fn, delay = 300) {
        let t;
        return function(...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    function safeText(s) {
        return String(s ?? '').replace(/[&<>"]/g, function(c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
        });
    }

    function renderSubscriptionCard(sub) {
        const name = safeText(sub.client_name || 'مشترك غير معروف');
        const phone = safeText(sub.client_phone || '-');
        const plan = safeText(sub.plan_name || '-');
        const startDate = sub.start_date ? safeText(new Date(sub.start_date).toLocaleDateString()) : '-';
        const endDate = sub.end_date ? safeText(new Date(sub.end_date).toLocaleDateString()) : '-';
        const remaining = sub.remaining_visits ?? 0;
        const statusText = safeText(sub.is_active || '-');
        const color = statusText === 'فعال' ? 'green' : 'red';
        const url = showRoute.replace(':id', sub.id);

        return `
            <div class="session-card" role="button" onclick="window.location.href='${url}'">
                <div class="info" style="text-align:right;">
                    <h3>${name}</h3>
                    <p>📞 ${phone}</p>
                    <p style="margin-top:6px; font-size:13px;">الخطة: ${plan}</p>
                    <p style="margin-top:6px; font-size:13px;">من ${startDate} إلى ${endDate}</p>
                </div>
                <div class="persons" style="text-align:left;">
                    <div>متبقي: ${remaining} زيارة</div>
                    <div style="margin-top:4px; font-weight:700; color:${color};">${statusText}</div>
                </div>
            </div>
        `;
    }

    function showLoading() {
        list.innerHTML = `<p class="text-center p-3">⏳ جاري التحميل...</p>`;
    }

    function showNoResults() {
        list.innerHTML = `<p class="no-results">❌ لا توجد اشتراكات</p>`;
    }

    async function fetchSubscriptions() {
        const q = searchInput.value.trim();
        showLoading();
        try {
            const url = new URL(searchRoute, location.origin);
            if (q) url.searchParams.append('q', q);

            const res = await fetch(url.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!res.ok) throw new Error('Network response was not ok');

            const data = await res.json();
            const items = Array.isArray(data) ? data : (data.data ?? data.items ?? data);

            if (!items || items.length === 0) {
                showNoResults();
                return;
            }

            list.innerHTML = '';
            items.forEach(i => {
                list.innerHTML += renderSubscriptionCard(i);
            });
        } catch (err) {
            console.error(err);
            list.innerHTML = `<p class="no-results">حدث خطأ أثناء جلب الاشتراكات</p>`;
        }
    }

    const debouncedFetch = debounce(fetchSubscriptions, 250);
    searchInput.addEventListener('input', debouncedFetch);

    // Enter => نفّذ البحث مباشرة
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            fetchSubscriptions();
        }
    });

    fetchSubscriptions();
});
</script>

@endsection

@section('style')
    <style>
        :root{
            --theme-primary: #d9b2ad;
            --accent-2: #ffe8ee;
            --btn-bg: #ffe483;
            --btn-bg-hover: #ffec9e;
            --btn-border: #f2d35e;
            --btn-text: #111;
        }
        body{
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial;
            background: #faf7f9;
            color: #222;
            margin: 0;
        }
        .page-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 18px;
            height: 100vh;
            justify-content: center;
            align-items: center;
        }
        .title {
            display: block;
            margin: 20px auto;
            text-align: center;
            color: var(--theme-primary);
            animation: slideDown 0.9s ease;
            font-size: 22px;
            font-weight: 800;
            padding: 12px 20px;
            border-radius: 12px;
            background: linear-gradient(180deg, rgba(217,178,173,0.08), rgba(217,178,173,0.03));
            border: 1px solid rgba(217,178,173,0.12);
            box-shadow: 0 6px 18px rgba(217,178,173,0.08);
        }

        /* page action button */
        .page-actions {
            position: fixed;
            top: 16px;
            right: 16px;
            z-index: 1000;
        }
        .start-subscription-btn {
            display: inline-block;
            padding: 12px 18px;
            background: var(--btn-bg);
            color: var(--btn-text);
            font-weight: 800;
            font-size: 15px;
            border: 1px solid var(--btn-border);
            border-radius: 14px;
            text-decoration: none;
            box-shadow: 0 6px 14px rgba(0,0,0,0.12);
        }
        .start-subscription-btn:hover { background: var(--btn-bg-hover); }

        .search-box { display:flex; justify-content:center; margin:12px auto 26px; padding:0 12px; }
        .search-box input{
            padding: 12px 14px;
            width: 100%;
            max-width: 820px;
            border: 2px solid rgba(217,178,173,0.18);
            border-radius: 999px;
            background: #fff;
            font-size: 14px;
            box-shadow: 0 6px 18px rgba(217,178,173,0.08);
        }

        .subscription-list{
            display: flex;
            flex-direction: column;
            gap: 14px;
            padding: 0 12px;
            margin-bottom: 40px;
        }
        .session-card{
            width: 100%;
            background: linear-gradient(180deg, #ffffff, #fffafa);
            min-height: 76px;
            border-radius: 12px;
            padding: 12px 14px;
            font-size: 14px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.06);
            border-top: 4px solid rgba(217,178,173,0.18);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }
        .session-card:hover{
            transform: translateY(-4px);
            box-shadow: 0 14px 34px rgba(0,0,0,0.10);
        }
        .session-card .info h3{ margin:0; font-size:15px; color:#222; }
        .session-card .info p{ margin:6px 0 0; font-size:13px; color:#666; }
        .session-card .persons{ font-weight:700; font-size:14px; color:#333; margin-left:12px; white-space:nowrap; }

        .no-results{ text-align:center; color:#999; padding:18px 12px; }

        @media (max-width:720px){
            .page-container { padding:12px; margin:8px auto; }
            .title { font-size:18px; padding:10px 14px; }
            .search-box { margin-bottom:18px; }
            .session-card { min-height:70px; padding:10px 12px; }
            .session-card .info h3{ font-size:14px; }
            .session-card .info p{ font-size:12px; }
            .session-card .persons{ font-size:13px; }
            .start-subscription-btn { padding:10px 14px; font-size:13px; }
            .page-actions{ top:10px; right:10px; }
        }

        @keyframes slideDown {
            from { transform: translateY(-20px); opacity:0; }
            to { transform: translateY(0); opacity:1; }
        }

    </style>
@endsection
