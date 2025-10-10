{{-- resources/views/clients/invoices.blade.php --}}
@extends('layouts.app_page')

@section('title', "فواتير — {$client->name}")

@section('content')
<div class="client-container">
    <div class="card">
        <div class="card-header">
            <div>
                <h2>🧾 فواتير {{ $client->name }}</h2>
                <div class="muted small">#{{ $client->id }} — {{ $client->phone }}</div>
            </div>

            <div class="header-actions">
                <button id="exportCsv" class="btn small">⬇︎ تصدير CSV</button>
                <button id="printBtn" class="btn small">🖨 طباعة</button>
            </div>
        </div>

        <!-- Summary stats -->
        <div class="section">
            <div class="box stats-grid">
                <div class="stat">
                    <div class="stat-label">إجمالي الفواتير</div>
                    <div class="stat-value">{{ $totalInvoices ?? 0 }}</div>
                </div>
                <div class="stat">
                    <div class="stat-label">مجموع الفواتير</div>
                    <div class="stat-value">{{ number_format($sumInvoices ?? 0,2) }} ج.م</div>
                </div>
                <div class="stat">
                    <div class="stat-label">المدفوع</div>
                    <div class="stat-value">{{ number_format($paidTotal ?? 0,2) }} ج.م</div>
                </div>
                <div class="stat">
                    <div class="stat-label">المتبقي</div>
                    <div class="stat-value">{{ number_format($dueTotal ?? 0,2) }} ج.م</div>
                </div>
                <div class="stat">
                    <div class="stat-label">مقدمات مرتبطة</div>
                    <div class="stat-value">{{ number_format($depositsTotal ?? 0,2) }} ج.م</div>
                </div>

                <div class="stat status-breakdown">
                    <div class="stat-label">تفصيل الحالات / النوع</div>
                    <div class="stat-value statuses-inline">
                        @if(!empty($countsByStatus))
                            @foreach($countsByStatus as $st => $cnt)
                                <span class="pill">{{ $st }}: {{ $cnt }}</span>
                            @endforeach
                        @else
                            <span class="muted">لا توجد حالات</span>
                        @endif
                        <br/>
                        @if(!empty($typesCount))
                            @foreach($typesCount as $type => $cnt)
                                <span class="pill" style="background:#fff; border:1px solid #eee;">{{ $type }}: {{ $cnt }}</span>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="section">
            <div class="box">
                <div class="filters-row">
                    <div class="filter-left" style="display:flex;flex-direction:column;gap:8px;">
                        <input id="searchInput" placeholder="ابحث برقم الفاتورة، الملاحظات، أو رقم الحجز..." />
                        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
                            <div>
                                <label class="small muted">حالة:</label>
                                <select id="statusSelect">
                                    <option value="all">كل الحالات</option>
                                    @if(!empty($countsByStatus))
                                        @foreach($countsByStatus as $st => $cnt)
                                            <option value="{{ $st }}">{{ $st }} ({{ $cnt }})</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div>
                                <label class="small muted">النوع:</label>
                                <select id="typeSelect">
                                    <option value="all">كل الأنواع</option>
                                    @if(!empty($typesCount))
                                        @foreach($typesCount as $type => $cnt)
                                            <option value="{{ $type }}">{{ $type }} ({{ $cnt }})</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="filter-right">
                        <label class="small muted">من:</label>
                        <input type="date" id="fromDate">
                        <label class="small muted">إلى:</label>
                        <input type="date" id="toDate">
                        <button id="clearFilters" class="btn small ghost">مسح</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="section">
            <div class="box table-wrap">
                <table id="invoicesTable" class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>رقم الفاتورة</th>
                            <th>التاريخ</th>
                            <th>النوع</th>
                            <th>الحالة</th>
                            <th>القيمة</th>
                            <th>مدفوع</th>
                            <th>متبقي</th>
                            <th>مقدم</th>
                            <th>بنود</th>
                            <th>حجز مرتبط</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($invoices as $inv)
                        @php
                            // مرونة: invoices قد تكون كائنات من موديل أو stdClass من query builder
                            $id = $inv->id ?? ($inv['id'] ?? null);
                            $number = $inv->number ?? $inv->id ?? ($inv['invoice_no'] ?? $id);
                            $date = isset($inv->created_at) ? \Carbon\Carbon::parse($inv->created_at)->format('Y-m-d') : (isset($inv->date) ? \Carbon\Carbon::parse($inv->date)->format('Y-m-d') : '-');
                            $type = $inv->type ?? ($inv['type'] ?? '—');
                            $status = $inv->status ?? ($inv['status'] ?? '—');
                            // قيمة الفاتورة: جرب حقول شائعة
                            $value = $inv->total ?? $inv->amount ?? $inv->grand_total ?? ($inv['total'] ?? 0);
                            $paid = $paymentsPerInvoice[$id] ?? ($inv->paid_amount ?? ($inv->paid ?? 0));
                            $deposit = $depositsPerInvoice[$id] ?? 0;
                            $items = $itemsPerInvoice[$id] ?? 0;
                            $due = max(0, (float)$value - (float)$paid);
                            $bookingRef = $inv->booking->id ?? ($inv->booking_id ?? ($inv->booking_id ?? '—'));
                        @endphp
                        <tr data-status="{{ $status }}" data-type="{{ $type }}" data-start="{{ $date }}">
                            <td>{{ $loop->iteration + (($invoices->currentPage() - 1) * $invoices->perPage()) }}</td>
                            <td class="title">#{{ $number }}</td>
                            <td>{{ $date }}</td>
                            <td>{{ $type }}</td>
                            <td><span class="status-badge status-{{ Str::slug($status, '_') }}">{{ $status }}</span></td>
                            <td>{{ number_format($value,2) }}</td>
                            <td>{{ number_format($paid,2) }}</td>
                            <td>{{ number_format($due,2) }}</td>
                            <td>{{ number_format($deposit,2) }}</td>
                            <td>{{ number_format($items,2) }}</td>
                            <td>{{ $bookingRef ? ('#' . $bookingRef) : '—' }}</td>
                            <td>
                                @if(isset($id))
                                    <a class="btn small" href="{{ route('invoices.show', $id) }}">🔍</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <div class="table-footer">
                    <div class="left">
                        عرض <strong>{{ $invoices->count() }}</strong> من <strong>{{ $totalInvoices ?? 0 }}</strong>
                    </div>
                    <div class="right">
                        {{ $invoices->links() }}
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<script>
    (function () {
        const table = document.getElementById('invoicesTable');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const searchInput = document.getElementById('searchInput');
        const statusSelect = document.getElementById('statusSelect');
        const typeSelect = document.getElementById('typeSelect');
        const fromDate = document.getElementById('fromDate');
        const toDate = document.getElementById('toDate');
        const clearBtn = document.getElementById('clearFilters');
        const exportBtn = document.getElementById('exportCsv');
        const printBtn = document.getElementById('printBtn');

        function matchesFilters(row) {
            // status
            const selStatus = statusSelect.value;
            const rowStatus = row.dataset.status || '';
            if (selStatus !== 'all' && rowStatus !== selStatus) return false;

            // type
            const selType = typeSelect.value;
            const rowType = row.dataset.type || '';
            if (selType !== 'all' && rowType !== selType) return false;

            // search
            const q = searchInput.value.trim().toLowerCase();
            if (q) {
                const txt = (row.querySelector('.title')?.textContent || '') + ' ' + (row.querySelector('td:nth-child(11)')?.textContent || '');
                if (!txt.toLowerCase().includes(q)) return false;
            }

            // date range
            const rowDate = row.dataset.start; // YYYY-MM-DD
            if (fromDate.value && rowDate < fromDate.value) return false;
            if (toDate.value && rowDate > toDate.value) return false;

            return true;
        }

        function applyFilters() {
            let visible = 0;
            rows.forEach(r => {
                if (matchesFilters(r)) {
                    r.style.display = '';
                    visible++;
                } else {
                    r.style.display = 'none';
                }
            });

            const left = document.querySelector('.table-footer .left');
            if (left) left.innerHTML = `عرض <strong>${visible}</strong> من <strong>{{ $totalInvoices ?? 0 }}</strong>`;
        }

        // events
        searchInput.addEventListener('input', debounce(applyFilters, 220));
        statusSelect.addEventListener('change', applyFilters);
        typeSelect.addEventListener('change', applyFilters);
        fromDate.addEventListener('change', applyFilters);
        toDate.addEventListener('change', applyFilters);
        clearBtn.addEventListener('click', function () {
            searchInput.value = '';
            fromDate.value = '';
            toDate.value = '';
            statusSelect.value = 'all';
            typeSelect.value = 'all';
            applyFilters();
        });

        // Export CSV
        exportBtn.addEventListener('click', function () {
            const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.innerText.trim());
            const visibleRows = rows.filter(r => r.style.display !== 'none');
            const csv = [];
            csv.push(headers.join(','));

            visibleRows.forEach(r => {
                const cells = Array.from(r.querySelectorAll('td')).map(td => {
                    let txt = td.innerText.replace(/\n/g,' ').replace(/,/g,'؛');
                    return '"' + txt.trim() + '"';
                });
                csv.push(cells.join(','));
            });

            const blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            const filename = 'invoices_{{ $client->id }}_{{ \Carbon\Carbon::now()->format('Ymd') }}.csv';
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            a.remove();
            URL.revokeObjectURL(url);
        });

        // Print
        printBtn.addEventListener('click', function () {
            const printContents = document.querySelector('.client-container').innerHTML;
            const w = window.open('', '', 'height=800,width=1200');
            w.document.write('<html><head><title>طباعة — فواتير {{ $client->name }}</title>');
            w.document.write('<style>body{font-family: Tahoma, sans-serif; direction:rtl; padding:20px;} table{width:100%;border-collapse:collapse;} td,th{padding:8px;border:1px solid #ddd;} .status-badge{padding:4px 8px;border-radius:6px;}</style>');
            w.document.write('</head><body dir="rtl">');
            w.document.write(printContents);
            w.document.write('</body></html>');
            w.document.close();
            w.focus();
            setTimeout(()=>{ w.print(); w.close(); }, 500);
        });

        // debounce
        function debounce(fn, wait) {
            let t;
            return function () {
                clearTimeout(t);
                t = setTimeout(() => fn.apply(this, arguments), wait);
            };
        }

        // initial
        applyFilters();
    })();
</script>
@endsection

@section('style')
<style>
    /* Base (مطابق لستايل صفحة العميل) */
    body { background: #fafafa; font-family: "Tahoma", sans-serif; }
    .client-container { max-width: 1100px; margin: 36px auto; padding: 20px; }
    .card { background: #fff; border-radius: 20px; box-shadow: 0 6px 26px rgba(0,0,0,0.06); padding: 22px; animation: fadeInUp .5s ease; }
    .card-header { display:flex; justify-content:space-between; align-items:flex-start; gap:12px; border-bottom:2px solid #f5f5f5; padding-bottom:12px; margin-bottom:14px; }
    .card-header h2 { font-size:22px; margin:0; color:#2b2b2b; }
    .header-actions { display:flex; gap:8px; align-items:center; }

    .btn { border: none; cursor: pointer; font-weight:700; border-radius:10px; padding:10px 14px; background:#D9B1AB; color:#fff; box-shadow: 0 6px 18px rgba(0,0,0,0.06); transition: transform .14s ease; }
    .btn:hover { transform: translateY(-3px); background:#a86f68; }
    .btn.ghost { background:transparent; color:#555; box-shadow:none; border:1px solid #eee; padding:8px 10px; }
    .btn.small { padding:6px 10px; font-size:13px; border-radius:9px; }
    .btn.small.ghost { background:transparent; color:#555; border:1px solid #eee; }

    .section { margin-top:14px; }
    .box { background:#fafafa; padding:14px; border-radius:12px; box-shadow: inset 0 2px 6px rgba(0,0,0,0.03); }

    /* stats grid */
    .stats-grid { display:grid; grid-template-columns: repeat(3,1fr); gap:12px; align-items:center; }
    .stat { padding:12px; background:#fff; border-radius:10px; box-shadow:0 4px 14px rgba(0,0,0,0.04); }
    .stat-label { color:#7a7a7a; font-size:13px; }
    .stat-value { font-size:18px; font-weight:800; margin-top:6px; color:#2b2b2b; }
    .statuses-inline .pill { display:inline-block; margin:4px 6px 0 0; padding:6px 8px; background:#f5f5f5; border-radius:999px; font-weight:700; font-size:13px; }

    /* filters */
    .filters-row { display:flex; justify-content:space-between; gap:10px; align-items:center; flex-wrap:wrap; }
    .filters-row input[type="date"], #searchInput, #statusSelect, #typeSelect { padding:8px 10px; border-radius:8px; border:1px solid #ececec; min-width:180px; }
    #searchInput { width:320px; }

    /* table */
    .table-wrap { overflow: auto; }
    table.table { width:100%; border-collapse:collapse; min-width:1100px; }
    table.table th, table.table td { padding:10px 12px; text-align:left; border-bottom:1px solid #f0f0f0; font-size:14px; color:#333; }
    table.table thead th { background:transparent; color:#6b6b6b; font-weight:800; font-size:13px; position:sticky; top:0; backdrop-filter: blur(2px); z-index:2; }
    .status-badge { padding:6px 8px; border-radius:8px; font-weight:800; text-transform:capitalize; display:inline-block; }
    .status-paid { background:#e9ffef; color:#1f7a3a; }
    .status-unpaid { background:#ffeef0; color:#b31d2b; }
    .status-partial { background:#fff7e9; color:#b26a00; }

    .table-footer { display:flex; justify-content:space-between; align-items:center; margin-top:12px; }
    .muted { color:#7a7a7a; }
    .small { font-size:13px; }

    @keyframes fadeInUp {
        from { opacity:0; transform: translateY(14px); }
        to { opacity:1; transform: translateY(0); }
    }

    @media (max-width:1000px) {
        .stats-grid { grid-template-columns: repeat(2,1fr); }
        #searchInput { width:180px; }
        .card-header { flex-direction:column; align-items:flex-start; gap:8px; }
    }
    @media (max-width:700px) {
        .stats-grid { grid-template-columns: 1fr; }
        table.table { min-width:900px; }
    }
</style>
@endsection
