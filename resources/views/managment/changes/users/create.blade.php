
@extends('layouts.app_page_admin')

@section('page_title', 'المستخدمين')

@section('content')
<style>
    /* Base */
    :root{
        --accent: #26a69a;
        --accent-dark: #145a32;
        --success: #2ecc71;
        --bg-start: #ffffff;
        --bg-end: #f2fff0;
        --card-bg: #fbfffb;
        --muted: #6b7a73;
    }

    body { font-family: "Tahoma", sans-serif; margin:0; color:#233; background: linear-gradient(to bottom, var(--bg-start), var(--bg-end)); -webkit-font-smoothing:antialiased; -moz-osx-font-smoothing:grayscale; }

    .page-container {
        max-width: 1100px;
        margin: 28px auto;
        padding: 20px;
    }

    /* header row & stats */
    .header-row { display:flex; gap:16px; align-items:center; justify-content:flex-start; flex-wrap:wrap; margin-bottom:16px; }
    .stats-box {
        background: var(--card-bg);
        padding: 12px 16px;
        border-radius:12px;
        box-shadow:0 2px 10px rgba(0,0,0,0.04);
        min-width:150px;
        text-align:center;
    }
    .stats-box p:first-child { margin:0; font-weight:700; color:var(--accent-dark); font-size:13px; }
    .stats-box p:last-child { margin:6px 0 0; font-size:20px; color:#0e6b3b; }

    .title { text-align:center; margin:6px 0 18px; color:var(--accent-dark); font-size:20px; font-weight:700; }

    /* search */
    .search-box {
        display:flex;
        gap:0;
        justify-content:center;
        align-items:center;
        margin-bottom:16px;
    }
    .search-box .search-input {
        padding:10px 14px;
        border:2px solid var(--accent);
        border-right:0;
        border-radius:25px 0 0 25px;
        outline:none;
        transition: box-shadow .18s, transform .12s;
        width:60%;
        min-width:200px;
    }
    .search-box .search-input:focus { box-shadow:0 8px 24px rgba(38,166,154,.10); transform:translateY(-1px); }
    .search-box .search-btn {
        padding:10px 18px;
        border:none;
        background:var(--accent);
        color:#fff;
        border-radius:0 25px 25px 0;
        cursor:pointer;
        font-weight:700;
        min-width:92px;
    }

    /* table (desktop) */
    .table-wrap { width:100%; overflow: auto; border-radius:12px; }
    table { width:100%; border-collapse:collapse; font-size:14px; background:transparent; min-width:700px; }
    thead { background: rgba(38,166,154,0.06); }
    thead th { padding:12px 12px; text-align:center; color:var(--accent-dark); font-weight:700; white-space:nowrap; }
    tbody td { padding:12px 12px; text-align:center; border-bottom:1px solid #f0f4f3; vertical-align:middle; }

    tbody tr:hover { background: #f7fffe; cursor:pointer; transform: translateY(-2px); transition: all .15s ease; }

    /* small text & muted */
    .muted { color: var(--muted); font-size:13px; }

    /* add button */
    .page-actions { position: fixed; top: 18px; right: 18px; z-index: 1000; }
    .add-user-btn{
        position: relative;
        display: inline-block;
        padding: 12px 18px;
        background: var(--success);
        color: #042a10;
        font-weight: 800;
        font-size: 15px;
        border: 1px solid #2bbf66;
        border-radius: 14px;
        letter-spacing: .2px;
        box-shadow: 0 6px 14px rgba(43, 122, 66, .12), inset 0 -2px 0 rgba(0,0,0,.03);
        transition: transform .25s ease, box-shadow .25s ease;
    }
    .add-user-btn::before{
        content: "";
        position: absolute;
        inset: -120% -30%;
        background: linear-gradient(120deg, transparent 35%, rgba(255,255,255,.65) 50%, transparent 65%);
        transform: translateX(-100%);
        transition: transform .6s ease;
        pointer-events: none;
    }
    .add-user-btn:hover { transform: translateY(-2px) scale(1.02); box-shadow: 0 10px 26px rgba(39, 139, 84, .16); }
    .add-user-btn:hover::before { transform: translateX(100%); }

    /* responsive: mobile card style for table rows */
    @media (max-width: 840px) {
        .page-container { padding: 14px; margin: 18px auto; }
        .search-box .search-input { width: calc(100% - 100px); } /* space for button */
        .search-box { align-items:center; gap:8px; }

        /* change table behaviour: hide thead, show rows as cards */
        thead { display: none; }
        table { min-width:unset; width:100%; }
        tbody { display:block; width:100%; }
        tbody tr {
            display: block;
            background: #fff;
            border-radius: 12px;
            padding: 10px;
            margin-bottom: 12px;
            box-shadow: 0 6px 16px rgba(12, 66, 33, 0.04);
            border: 1px solid #f0f4f3;
            transform: none !important;
        }
        tbody td {
            display: flex;
            justify-content: space-between;
            padding: 8px 10px;
            text-align: left;
            border: none;
        }
        tbody td[data-label]::before {
            content: attr(data-label) ": ";
            color: var(--accent-dark);
            font-weight: 700;
            display: inline-block;
            min-width: 110px;
        }

        /* move add button to bottom-right on mobile for easier thumb reach */
        .page-actions { top: auto; bottom: 18px; right: 18px; left: auto; }
    }

    /* small screens (very small) tweak */
    @media (max-width: 420px) {
        .search-box .search-input { width: 100%; }
        .search-box .search-btn { width: 100%; margin-top: 8px; border-radius: 10px; }
        .search-box { flex-direction: column; align-items: stretch; }
    }

    /* accessibility focus */
    button:focus, a:focus, input:focus {
        outline: 3px solid rgba(38,166,154,0.14);
        outline-offset: 2px;
    }
</style>

<div class="page-container">
    <div class="header-row">
        <div class="stats-box">
            <p>إجمالى الحسابات</p>
            <p id="totalUsersCount">{{ $totalUsers ?? '—' }}</p>
        </div>
        <div class="stats-box">
            <p>عدد الأدمن</p>
            <p id="adminsCount">{{ $adminsCount ?? '—' }}</p>
        </div>
    </div>

    <h2 class="title">قائمة الحسابات</h2>

    <div class="search-box" role="search" aria-label="بحث المستخدمين">
        <input class="search-input" type="text" id="searchBox" placeholder="🔍 بحث باسم المستخدم أو البريد أو ID" aria-label="حقل البحث">
    </div>

    <div class="table-wrap" role="region" aria-label="قائمة المستخدمين">
        <table aria-describedby="usersTableDesc">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الاسم</th>
                    <th>البريد</th>
                    <th>الصلاحية</th>
                </tr>
            </thead>
            <tbody id="usersTable" aria-live="polite">
                <tr><td colspan="4" class="text-center p-3">⏳ جاري التحميل...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div class="page-actions">
    <a href="{{ route('register') }}" class="add-user-btn" aria-label="إضافة مستخدم">إضافة حساب</a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const searchBox = document.getElementById('searchBox');
    const tbody = document.getElementById('usersTable');
    const totalUsersEl = document.getElementById('totalUsersCount');
    const adminsCountEl = document.getElementById('adminsCount');

    function renderRows(data){
        tbody.innerHTML = '';
        if (!data || data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center p-3">❌ لا توجد نتائج</td></tr>`;
            return;
        }

        data.forEach(u => {
            const tr = document.createElement('tr');
            tr.dataset.id = u.id;

            tr.innerHTML = `
                <td data-label="ID">${escapeHtml(u.id)}</td>
                <td data-label="الاسم">${escapeHtml(u.name)}</td>
                <td data-label="البريد">${escapeHtml(u.email)}</td>
                <td data-label="الصلاحية">${escapeHtml(u.role)}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    function showLoading() {
        tbody.innerHTML = `<tr><td colspan="4" class="text-center p-3">⏳ جاري التحميل...</td></tr>`;
    }

    function showError() {
        tbody.innerHTML = `<tr><td colspan="4" class="text-center p-3">⚠️ حدث خطأ أثناء جلب البيانات</td></tr>`;
    }

    function fetchUsers(q = '') {
        showLoading();
        const params = new URLSearchParams({ q });
        fetch("{{ route('users.ajaxSearch') }}?" + params.toString(), {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
            .then(res => {
                if (!res.ok) throw new Error('Network response was not ok');
                return res.json();
            })
            .then(data => {
                // data متوقع array من المستخدمين
                renderRows(data);

                // لو تحب تحدث الأرقام العلوية لو backend أعادها:
                // if (data.meta) { totalUsersEl.textContent = data.meta.total ?? totalUsersEl.textContent; }
            })
            .catch(err => {
                console.error('fetchUsers error', err);
                showError();
            });
    }

    // debounce بسيط
    let debounceTimer = null;
    searchBox.addEventListener('input', function(e){
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => fetchUsers(this.value.trim()), 250);
    });

    // load initial automatically
    fetchUsers(); // <-- هذا يجعل الصفحة تعرض "جاري التحميل" ثم يملأ الجدول أول ما يعود JSON
});

/* escape to prevent XSS */
function escapeHtml(unsafe) {
    if (unsafe === null || unsafe === undefined) return '';
    return String(unsafe)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
}
</script>
@endsection
