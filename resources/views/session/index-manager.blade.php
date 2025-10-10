 @extends('layouts.app_page')
 @section('title', 'إدارة الجلسات')
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

  
    <h1 class="title">إدارة الجلسات</h1>

    <div class="page-actions">
        <a href="{{ route('session.create') }}" class="start-session-btn" aria-label="بدء جلسه">بدء جلسه</a>
    </div>

    {{-- البحث --}}
    <div class="search-box" style="margin-bottom:20px;">
        <input type="text" id="searchInput" placeholder="ابحث بالاسم أو رقم الهاتف أو ID" style="width:100%; padding:8px;">
    </div>

    {{-- الكروت --}}
    <div class="sessions-list" id="sessionsList">
        <p class="text-center p-3">⏳ جاري التحميل...</p>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById('searchInput');
    const sessionsList = document.getElementById('sessionsList');

    // route للـ show — تأكد إن اسم الراوت مضبوط (session.show)
    const showRoute = @json(route('session.show', ':id'));

    // اسم راوت البحث — لو عندك اسم مختلف عدله هنا
    const searchRoute = @json(route('sessions.search'));

    // debounce عشان ما يبقاش كل حرف يطلب
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

    function renderSessionCard(session) {
        const clientName = session.client ? safeText(session.client.name) : 'عميل غير معروف';
        const clientPhone = session.client ? safeText(session.client.phone) : '-';
        const persons = session.persons ?? 0;
        // نبني الـ HTML بحيث الضغط على البطاقة يفتح show بالشكل الصحيح
        return `
            <div class="session-card" role="button" onclick="window.location.href='${showRoute.replace(':id', session.id)}'" style="cursor:pointer;">
                <div class="info" style="text-align:right;">
                    <h3>${clientName}</h3>
                    <p>📞 ${clientPhone}</p>
                </div>
                <div class="persons">
                    الاشخاص : ${persons}
                </div>
            </div>
        `;
    }

    function showLoading() {
        sessionsList.innerHTML = `<p class="text-center p-3">⏳ جاري التحميل...</p>`;
    }

    function showNoResults() {
        sessionsList.innerHTML = `<p class="no-results">❌ لا توجد جلسات</p>`;
    }

    async function fetchSessions() {
        const q = searchInput.value || '';
        showLoading();
        try {
            const url = new URL(searchRoute, location.origin);
            if (q) url.searchParams.append('query', q);
            const res = await fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
            if (!res.ok) throw new Error('Network response was not ok');
            const data = await res.json();

            // بعض APIs ترجع كائن يحتوي على data، بعضهم يرجع مصفوفة مباشرة
            const items = Array.isArray(data) ? data : (data.data ?? []);

            if (!items || items.length === 0) {
                showNoResults();
                return;
            }

            // render
            sessionsList.innerHTML = '';
            items.forEach(s => {
                sessionsList.innerHTML += renderSessionCard(s);
            });
        } catch (err) {
            console.error(err);
            sessionsList.innerHTML = `<p class="no-results">حدث خطأ أثناء جلب الجلسات</p>`;
        }
    }

    // استعمل debounce عشان يقلل الطلبات
    const debouncedFetch = debounce(fetchSessions, 250);
    searchInput.addEventListener('keyup', debouncedFetch);

    // تحميل أولي
    fetchSessions();
});
</script>
@endsection


@section('style')
    <style>
        :root{
            --theme-primary: #d9b2ad; /* اللون المطلوب */
            --accent-2: #ffe8ee;
            --btn-bg: #ffe483; /* زر أصفر زي القديم */
            --btn-bg-hover: #ffec9e;
            --btn-border: #f2d35e;
            --btn-text: #111;
        }

        body{
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial;
            background: #faf7f9;
            color: #222;
            margin: 0;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
.page-container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 18px;

    /* علشان يوسطن العنوان */

    justify-content: center;  /* أفقي */
    align-items: center;      /* عمودي */
    height: 100vh;            /* ياخد طول الشاشة كلها */
}

  .title {
    display: block;            /* ياخد عرض كامل */
    margin: 20px auto;         /* يوسطنه أفقي */
    text-align: center;        /* يوسطن النص */
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

        /* Snackbar */
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
            transition: opacity 0.35s ease, transform 0.35s ease;
            box-shadow: 0 6px 18px rgba(0,0,0,0.12);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .snackbar.show { opacity: 1; transform: translateX(0); }
        .snackbar.success { background: #28a745; }
        .snackbar.error { background: #dc3545; }

        /* زر بدء جلسة (القديم بالفشيخ) */
        .page-actions {
            position: fixed;
            top: 16px;
            right: 16px;
            z-index: 1000;
        }

        .start-session-btn {
            position: relative;
            display: inline-block;
            padding: 12px 18px;
            background: var(--btn-bg);
            color: var(--btn-text);
            font-weight: 800;
            font-size: 15px;
            border: 1px solid var(--btn-border);
            border-radius: 14px;
            text-decoration: none;
            letter-spacing: .2px;
            box-shadow: 0 6px 14px rgba(0, 0, 0, .12), inset 0 -2px 0 rgba(0, 0, 0, .05);
            transition: transform .25s ease, box-shadow .25s ease, background-color .25s ease, border-color .25s ease;
            overflow: hidden;
            -webkit-tap-highlight-color: transparent;
        }

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

        .start-session-btn:hover::before { transform: translateX(100%); }
        .start-session-btn:active {
            transform: translateY(0) scale(0.99);
            box-shadow: 0 6px 14px rgba(0, 0, 0, .12), inset 0 -2px 0 rgba(0, 0, 0, .08);
        }
        .start-session-btn:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 228, 131, .6),
                        0 10px 22px rgba(0, 0, 0, .16),
                        inset 0 -2px 0 rgba(0, 0, 0, .05);
        }

        /* Search */
        .search-box {
            display: flex;
            justify-content: center;
            margin: 12px auto 26px;
            padding: 0 12px;
        }
        .search-box input{
            padding: 12px 14px;
            width: 100%;
            max-width: 820px;
            border: 2px solid rgba(217,178,173,0.18);
            border-radius: 999px;
            background: #fff;
            font-size: 14px;
            box-shadow: 0 6px 18px rgba(217,178,173,0.08);
            transition: box-shadow .18s ease, transform .12s ease;
        }
        .search-box input:focus{
            box-shadow: 0 6px 26px rgba(217,178,173,0.12);
            transform: translateY(-1px);
        }

        /* الكروت */
        .sessions-list{
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
            .start-session-btn { padding:10px 14px; font-size:13px; }
            .page-actions{ top:10px; right:10px; }
        }

        @media (prefers-reduced-motion: reduce){
            .start-session-btn, .start-session-btn::before, .session-card, .title {
                transition: none !important; animation: none !important;
            }
        }

        @keyframes slideDown {
            from { transform: translateY(-20px); opacity:0; }
            to { transform: translateY(0); opacity:1; }
        }
    </style>
@endsection
