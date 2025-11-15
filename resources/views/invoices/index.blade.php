@extends('layouts.app_page')
@section('title', 'إدارة الفواتير')

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

        {{-- صفحة عنوان --}}
    @section('page_title')
        <h1 class="title">إدارة الفواتير</h1>
    @endsection

    {{-- صندوق البحث --}}
    <div class="search-box" style="margin-bottom:15px;">
        <input type="text" id="invoiceSearch" placeholder="ابحث بالعميل أو رقم الفاتورة" style="width:100%; padding:8px;"
            value="{{ request('search') ?? '' }}">
    </div>

    {{-- فلتر النوع --}}
    <div class="filters" style="margin-bottom:15px; display:flex; flex-wrap:wrap; gap:12px;">
        @php
            $types = ['product', 'subscription', 'booking', 'session', 'deposit', 'mixed'];
        @endphp
        @foreach ($types as $type)
            <label style="display:flex; align-items:center; gap:4px;">
                <input type="checkbox" class="filter-type" value="{{ $type }}">
                <span>{{ $type }}</span>
            </label>
        @endforeach
    </div>

    {{-- فلتر التاريخ --}}
    <div class="date-filters" style="margin-bottom:20px; display:flex; gap:8px; flex-wrap:wrap;">
        <input type="date" id="fromDate" placeholder="من">
        <input type="date" id="toDate" placeholder="إلى">
    </div>

    {{-- قائمة الفواتير --}}
    <div class="invoice-list" id="invoiceList">
        <p class="text-center p-3">⏳ جاري التحميل...</p>
    </div>

</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById('invoiceSearch');
        const invoiceList = document.getElementById('invoiceList');
        const typeCheckboxes = document.querySelectorAll('.filter-type');
        const fromDate = document.getElementById('fromDate');
        const toDate = document.getElementById('toDate');

        const searchRoute = @json(route('invoices.ajaxSearch'));
        const showRoute = @json(route('invoices.client.show', ':id'));

        function debounce(fn, delay = 300) {
            let t;
            return function(...args) {
                clearTimeout(t);
                t = setTimeout(() => fn.apply(this, args), delay);
            };
        }

        function safeText(s) {
            return String(s ?? '').replace(/[&<>"]/g, function(c) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;'
                } [c];
            });
        }

        function renderInvoiceCard(inv) {
            const client = safeText(inv.client_name ?? 'عميل غير معروف');
            const number = safeText(inv.invoice_number);
            const type = safeText(inv.type);
            const total = inv.total ?? 0;
            const date = inv.updated_at ? new Date(inv.updated_at).toLocaleDateString() : '-';
            const url = showRoute.replace(':id', inv.id);

            return `
        <div class="session-card" role="button" onclick="window.location.href='${url}'">
            <div class="info" style="text-align:right;">
                <h3>#${number}</h3>
                <p>العميل: ${client}</p>
                <p>النوع: ${type}</p>
                <p>التاريخ: ${date}</p>
            </div>
            <div class="persons">
    <div class="total-amount">الإجمالي: ${total} ج</div>
</div>

        </div>
        `;
        }

        function showLoading() {
            invoiceList.innerHTML = `<p class="text-center p-3">⏳ جاري التحميل...</p>`;
        }

        function showNoResults() {
            invoiceList.innerHTML = `<p class="no-results">❌ لا توجد فواتير</p>`;
        }

      async function fetchInvoices() {
    const q = searchInput.value.trim();
    const types = Array.from(typeCheckboxes).filter(c => c.checked).map(c => c.value);
    const from = fromDate.value;
    const to = toDate.value;

    showLoading();
    try {
        const url = new URL(searchRoute, location.origin);
        if (q) url.searchParams.append('q', q);
        if (types.length > 0) url.searchParams.append('types', types.join(','));
        if (from) url.searchParams.append('from', from);
        if (to) url.searchParams.append('to', to);

        const res = await fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        if (!res.ok) throw new Error('Network response was not ok');
        const data = await res.json();
        const items = Array.isArray(data) ? data : (data.data ?? data.items ?? data);

        if (!items || items.length === 0) {
            showNoResults();
            hideClientsSnackbar();
            return;
        }

        invoiceList.innerHTML = '';
        items.forEach(i => {
            invoiceList.innerHTML += renderInvoiceCard(i);
        });

        // ✅ بعد عرض النتائج، احسب عدد الفواتير لكل عميل واعرض Snackbar
        showClientsSnackbar(items);

    } catch (err) {
        console.error(err);
        invoiceList.innerHTML = `<p class="no-results">حدث خطأ أثناء جلب الفواتير</p>`;
        hideClientsSnackbar();
    }
}
// دالة تعرض Snackbar بقائمة العملاء وعدد فواتيرهم
function showClientsSnackbar(invoices) {
    // احسب عدد الفواتير لكل عميل
    const clientCounts = {};
    invoices.forEach(inv => {
        const name = inv.client_name || 'عميل غير معروف';
        clientCounts[name] = (clientCounts[name] || 0) + 1;
    });

    // لو مفيش عملاء واضحين مفيش داعي للسناكبار
    const entries = Object.entries(clientCounts);
    if (entries.length === 0) {
        hideClientsSnackbar();
        return;
    }

    // احذف أي Snackbar سابق
    $('#clientsSnackbar').remove();

    // أنشئ قائمة صغيرة بالعملاء وعدد الفواتير
    const listHtml = entries.map(([name, count]) => `
        <li style="padding:4px 0; border-bottom:1px solid #eee;">
            <strong>${name}</strong> = ${count}
        </li>
    `).join('');

    // أنشئ الـ Snackbar
    const $snackbar = $(`
        <div id="clientsSnackbar" style="
            position:fixed;
            bottom:15px;
            left:50%;
            transform:translateX(-50%);
            background:#333;
            color:#fff;
            padding:12px 16px;
            border-radius:10px;
            z-index:9999;
            box-shadow:0 2px 10px rgba(0,0,0,0.25);
            max-width:320px;
            text-align:right;
        ">
            <div style="font-weight:bold; margin-bottom:6px;">📋 العملاء اللي ليهم فواتير:</div>
            <ul style="list-style:none; margin:0; padding:0; max-height:140px; overflow:auto;">
                ${listHtml}
            </ul>
        </div>
    `);

    $('body').append($snackbar);

    // يختفي بعد 8 ثواني
    setTimeout(() => hideClientsSnackbar(), 8000);
}

// دالة لإخفاء Snackbar لو موجود
function hideClientsSnackbar() {
    $('#clientsSnackbar').fadeOut(300, function() {
        $(this).remove();
    });
}


        const debouncedFetch = debounce(fetchInvoices, 250);
        searchInput.addEventListener('input', debouncedFetch);
        typeCheckboxes.forEach(cb => cb.addEventListener('change', fetchInvoices));
        fromDate.addEventListener('change', fetchInvoices);
        toDate.addEventListener('change', fetchInvoices);

        fetchInvoices();
    });
</script>

@endsection
@section('style')
<style>
    :root {
        --theme-primary: #d9b2ad;
        --accent-2: #ffe8ee;
        --btn-bg: #ffe483;
        --btn-bg-hover: #ffec9e;
        --btn-border: #f2d35e;
        --btn-text: #111;
    }

    body {
        font-family: "Cairo", sans-serif;
        background: #faf7f9;
        color: #222;
        margin: 0;
    }

    .page-container {
        max-width: 1100px;
        margin: 0 auto;
        padding: 18px;
    }

    .filters label {
        cursor: pointer;
        font-size: 14px;
    }

    .date-filters input {
        padding: 8px;
        border-radius: 8px;
        border: 1px solid #ddd;
    }
    .session-card {
    background: #fff;
    border: 1px solid #f3e7ea;
    border-radius: 16px;
    padding: 16px 18px;
    margin-bottom: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.25s ease-in-out;
    box-shadow: 0 2px 6px rgba(217, 178, 173, 0.1);
}

.session-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 10px rgba(217, 178, 173, 0.25);
}

.session-card .info h3 {
    font-size: 18px;
    color: #222;
    margin-bottom: 6px;
}

.session-card .info p {
    margin: 2px 0;
    color: #555;
    font-size: 14px;
}

.session-card .persons {
    text-align: center;
}

.session-card .total-amount {
    font-size: 18px;
    font-weight: bold;
    color: #198754; /* أخضر لطيف */
    background: #e8f8ec;
    padding: 8px 14px;
    border-radius: 10px;
    border: 1px solid #c9ebd1;
    display: inline-block;
}

</style>
@endsection
