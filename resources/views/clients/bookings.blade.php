{{-- resources/views/client/bookings.blade.php --}}
@extends('layouts.app_page')

@section('title', "حجوزات — {$client->name}")

@section('content')
    <div class="client-container">
        <div class="card">
            <div class="card-header">
                <div>
                    <h2>📋 حجوزات {{ $client->name }}</h2>
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
                        <div class="stat-label">إجمالي الحجوزات</div>
                        <div class="stat-value">{{ $totalBookings }}</div>
                    </div>
                    <div class="stat">
                        <div class="stat-label">مقدمات</div>
                        <div class="stat-value">{{ number_format($depositsTotal, 2) }} ج.م</div>
                    </div>
                    <div class="stat">
                        <div class="stat-label">المستلم فعليًا</div>
                        <div class="stat-value">{{ number_format($receivedTotal, 2) }} ج.م</div>
                    </div>
                    <div class="stat">
                        <div class="stat-label">تقديرات</div>
                        <div class="stat-value">{{ number_format($estimatedTotal, 2) }} ج.م</div>
                    </div>
                    <div class="stat">
                        <div class="stat-label">مشتريات الحجوزات</div>
                        <div class="stat-value">{{ number_format($purchasesTotal, 2) }} ج.م</div>
                    </div>

                    <div class="stat status-breakdown">
                        <div class="stat-label">تفصيل الحالات</div>
                        <div class="stat-value statuses-inline">
                            <span class="pill">مجدولة: {{ $countsByStatus['scheduled'] ?? 0 }}</span>
                            <span class="pill">مستحقة: {{ $countsByStatus['due'] ?? 0 }}</span>
                            <span class="pill">جارية: {{ $countsByStatus['in_progress'] ?? 0 }}</span>
                            <span class="pill">منتهية: {{ $countsByStatus['finished'] ?? 0 }}</span>
                            <span class="pill">ملغاة: {{ $countsByStatus['cancelled'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="section">
                <div class="box">
                    <div class="filters-row">
                        <div class="filter-left">
                            <input id="searchInput" placeholder="ابحث بالعنوان أو القاعة..." />
                            <div class="status-filters">
                                <label><input type="checkbox" class="status-filter" value="scheduled" checked>
                                    مجدولة</label>
                                <label><input type="checkbox" class="status-filter" value="due" checked> مستحقة</label>
                                <label><input type="checkbox" class="status-filter" value="in_progress" checked>
                                    جارية</label>
                                <label><input type="checkbox" class="status-filter" value="finished" checked> منتهية</label>
                                <label><input type="checkbox" class="status-filter" value="cancelled" checked> ملغاة</label>
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
                    <table id="bookingsTable" class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>العنوان</th>
                                <th>القاعة</th>
                                <th>الموعد</th>
                                <th>المدة</th>
                                <th>الحالة</th>
                                <th>تقدير</th>
                                <th>حقيقي</th>
                                <th>مقدم</th>
                                <th>مشتريات</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bookings as $b)
                                @php
                                    $deposit = $depositsPerBooking[$b->id] ?? 0;
                                    $purchases = $purchasesPerBooking[$b->id] ?? 0;
                                @endphp
                                <tr data-status="{{ $b->status }}"
                                    data-start="{{ \Carbon\Carbon::parse($b->start_at)->format('Y-m-d') }}">
                                    <td>{{ $b->id }}</td>
                                    <td class="title">{{ $b->title }}</td>
                                    <td class="hall">{{ $b->hall->name ?? '—' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($b->start_at)->format('Y-m-d H:i') }}</td>
                                    <td>{{ intdiv($b->duration_minutes, 60) }}س {{ $b->duration_minutes % 60 }}د</td>
                                    <td><span class="status-badge status-{{ $b->status }}">{{ $b->status }}</span>
                                    </td>
                                    <td>{{ number_format($b->estimated_total, 2) }}</td>
                                    <td>{{ number_format($b->real_total ?? 0, 2) }}</td>
                                    <td>{{ number_format($deposit, 2) }}</td>
                                    <td>{{ number_format($purchases, 2) }}</td>
                                    <td>
                                        <a class="btn small" href="{{ route('bookings.show', $b->id) }}">🔍</a>
                                        <a class="btn small ghost" href="{{ route('bookings.edit', $b->id) }}">✏️</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="table-footer">
                        <div class="left">
                            عرض <strong>{{ $bookings->count() }}</strong> من <strong>{{ $totalBookings }}</strong>
                        </div>
                        <div class="right">
                            {{ $bookings->links() }}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <script>
        (function() {
            const table = document.getElementById('bookingsTable');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const statusCheckboxes = Array.from(document.querySelectorAll('.status-filter'));
            const searchInput = document.getElementById('searchInput');
            const fromDate = document.getElementById('fromDate');
            const toDate = document.getElementById('toDate');
            const clearBtn = document.getElementById('clearFilters');
            const exportBtn = document.getElementById('exportCsv');
            const printBtn = document.getElementById('printBtn');

            function getActiveStatuses() {
                return statusCheckboxes.filter(c => c.checked).map(c => c.value);
            }

            function matchesFilters(row) {
                const active = getActiveStatuses();
                const status = row.dataset.status;
                if (!active.includes(status)) return false;

                const txt = (row.querySelector('.title').textContent + ' ' + row.querySelector('.hall').textContent)
                    .toLowerCase();
                const q = searchInput.value.trim().toLowerCase();
                if (q && !txt.includes(q)) return false;

                const rowDate = row.dataset.start; // YYYY-MM-DD
                if (fromDate.value) {
                    if (rowDate < fromDate.value) return false;
                }
                if (toDate.value) {
                    if (rowDate > toDate.value) return false;
                }

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
                // update footer count (left text)
                const left = document.querySelector('.table-footer .left');
                if (left) left.innerHTML = `عرض <strong>${visible}</strong> من <strong>{{ $totalBookings }}</strong>`;
            }

            // events
            statusCheckboxes.forEach(c => c.addEventListener('change', applyFilters));
            searchInput.addEventListener('input', debounce(applyFilters, 250));
            fromDate.addEventListener('change', applyFilters);
            toDate.addEventListener('change', applyFilters);
            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                fromDate.value = '';
                toDate.value = '';
                statusCheckboxes.forEach(c => c.checked = true);
                applyFilters();
            });

            // Export CSV of visible rows
            exportBtn.addEventListener('click', function() {
                const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.innerText.trim());
                const visibleRows = rows.filter(r => r.style.display !== 'none');
                const csv = [];
                csv.push(headers.join(','));

                visibleRows.forEach(r => {
                    const cells = Array.from(r.querySelectorAll('td')).map(td => {
                        let txt = td.innerText.replace(/\n/g, ' ').replace(/,/g,
                        '؛'); // avoid comma conflicts — use Arabic semicolon
                        return '"' + txt.trim() + '"';
                    });
                    csv.push(cells.join(','));
                });

                const blob = new Blob([csv.join('\n')], {
                    type: 'text/csv;charset=utf-8;'
                });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                const filename =
                'bookings_{{ $client->id }}_{{ \Carbon\Carbon::now()->format('Ymd') }}.csv';
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                a.remove();
                URL.revokeObjectURL(url);
            });

            // Print (print only the card area)
            printBtn.addEventListener('click', function() {
                const printContents = document.querySelector('.client-container').innerHTML;
                const w = window.open('', '', 'height=800,width=1200');
                w.document.write('<html><head><title>طباعة — حجوزات {{ $client->name }}</title>');
                w.document.write(
                    '<style>body{font-family: Tahoma, sans-serif; direction:rtl; padding:20px;} table{width:100%;border-collapse:collapse;} td,th{padding:8px;border:1px solid #ddd;} .status-badge{padding:4px 8px;border-radius:6px;}</style>'
                    );
                w.document.write('</head><body dir="rtl">');
                w.document.write(printContents);
                w.document.write('</body></html>');
                w.document.close();
                w.focus();
                setTimeout(() => {
                    w.print();
                    w.close();
                }, 500);
            });

            // debounce helper
            function debounce(fn, wait) {
                let t;
                return function() {
                    clearTimeout(t);
                    t = setTimeout(() => fn.apply(this, arguments), wait);
                };
            }

            // initial apply
            applyFilters();
        })();
    </script>
@endsection

@section('style')
    <style>
        /* Base (مطابق لستايل صفحة العميل) */
        body {
            background: #fafafa;
            font-family: "Tahoma", sans-serif;
        }

        .client-container {
            max-width: 1100px;
            margin: 36px auto;
            padding: 20px;
        }

        .card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 6px 26px rgba(0, 0, 0, 0.06);
            padding: 22px;
            animation: fadeInUp .5s ease;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            border-bottom: 2px solid #f5f5f5;
            padding-bottom: 12px;
            margin-bottom: 14px;
        }

        .card-header h2 {
            font-size: 22px;
            margin: 0;
            color: #2b2b2b;
        }

        .header-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .btn {
            border: none;
            cursor: pointer;
            font-weight: 700;
            border-radius: 10px;
            padding: 10px 14px;
            background: #D9B1AB;
            color: #fff;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
            transition: transform .14s ease;
        }

        .btn:hover {
            transform: translateY(-3px);
            background: #a86f68;
        }

        .btn.ghost {
            background: transparent;
            color: #555;
            box-shadow: none;
            border: 1px solid #eee;
            padding: 8px 10px;
        }

        .btn.small {
            padding: 6px 10px;
            font-size: 13px;
            border-radius: 9px;
        }

        .btn.small.ghost {
            background: transparent;
            color: #555;
            border: 1px solid #eee;
        }

        .section {
            margin-top: 14px;
        }

        .box {
            background: #fafafa;
            padding: 14px;
            border-radius: 12px;
            box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.03);
        }

        /* stats grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            align-items: center;
        }

        .stat {
            padding: 12px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
        }

        .stat-label {
            color: #7a7a7a;
            font-size: 13px;
        }

        .stat-value {
            font-size: 18px;
            font-weight: 800;
            margin-top: 6px;
            color: #2b2b2b;
        }

        .statuses-inline .pill {
            display: inline-block;
            margin: 4px 6px 0 0;
            padding: 6px 8px;
            background: #f5f5f5;
            border-radius: 999px;
            font-weight: 700;
            font-size: 13px;
        }

        /* filters */
        .filters-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filters-row input[type="date"],
        #searchInput {
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid #ececec;
            min-width: 180px;
        }

        #searchInput {
            width: 320px;
        }

        .status-filters label {
            margin-right: 12px;
            font-weight: 600;
        }

        .status-filters input {
            transform: scale(1.05);
            margin-left: 6px;
            margin-right: 6px;
        }

        /* table */
        .table-wrap {
            overflow: auto;
        }

        table.table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
        }

        table.table th,
        table.table td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
            color: #333;
        }

        table.table thead th {
            background: transparent;
            color: #6b6b6b;
            font-weight: 800;
            font-size: 13px;
            position: sticky;
            top: 0;
            backdrop-filter: blur(2px);
            z-index: 2;
        }

        .status-badge {
            padding: 6px 8px;
            border-radius: 8px;
            font-weight: 800;
            text-transform: capitalize;
            display: inline-block;
        }

        .status-scheduled {
            background: #fff4e5;
            color: #b26a00;
        }

        .status-due {
            background: #fff7e9;
            color: #b26a00;
        }

        .status-in_progress {
            background: #e8f7ff;
            color: #176fb7;
        }

        .status-finished {
            background: #e9ffef;
            color: #1f7a3a;
        }

        .status-cancelled {
            background: #ffeef0;
            color: #b31d2b;
        }

        .table-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 12px;
        }

        .muted {
            color: #7a7a7a;
        }

        .small {
            font-size: 13px;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(14px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width:1000px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            #searchInput {
                width: 180px;
            }

            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
        }

        @media (max-width:700px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            table.table {
                min-width: 900px;
            }
        }
    </style>

@endsection
