{{--
 @extends('layouts.app_page')
@section('content')
    <div class="invoice-page" style="padding:28px 18px;">

        <div class="form-container animate__animated animate__fadeInUp" style="max-width:1100px; margin:0 auto 18px;">
            <div style="display:flex; gap:12px; align-items:center; justify-content:space-between; flex-wrap:wrap;">
                <div>
                    <h2 style="margin:0; font-size:22px;">فاتورة #{{ $invoice->id }}</h2>
                    <div style="color:#666; margin-top:6px;">
                        <span>التاريخ:
                            <strong>{{ \Carbon\Carbon::parse($invoice->created_at)->format('Y-m-d H:i') }}</strong></span>
                        &nbsp; • &nbsp;
                        <span>الموظف: <strong>{{ $invoice->user?->name ?? '—' }}</strong></span>
                    </div>
                </div>

                {{-- <div style="display:flex; gap:10px; align-items:center;">
                    <button id="printBtn" class="btn">طباعة</button>
                    <button id="exportCsv" class="btn">تصدير CSV</button>
                    <a href="{{ route('invoices.duplicate', $invoice->id) ?? '#' }}" class="btn btn-outline">نسخ
                        الفاتورة</a>
                </div> 
            </div>
        </div>

        <div class="form-container animate__animated animate__fadeInUp" style="max-width:1100px; margin:0 auto 18px;">
            <div style="display:flex; gap:18px; flex-wrap:wrap;">
                <div style="flex:1; min-width:240px;">
                    <h4 style="margin:0 0 10px;">بيانات الفاتورة</h4>
                    <div class="info-row"><strong>الرقم:</strong> #{{ $invoice->id }}</div>
                    <div class="info-row"><strong>تاريخ الإصدار:</strong>
                        {{ \Carbon\Carbon::parse($invoice->created_at)->format('Y-m-d') }}</div>
                    <div class="info-row"><strong>الحالة:</strong> {{ $invoice->status ?? 'مدفوعة/غير مدفوعة' }}</div>
                </div>

                <div style="flex:1; min-width:240px;">
                    <h4 style="margin:0 0 10px;">العميل / المستفيد</h4>
                    <div class="info-row"><strong>الاسم:</strong>
                        {{ $invoice->customer_name ?? ($invoice->user?->name ?? '—') }}</div>
                    <div class="info-row"><strong>هاتف:</strong> {{ $invoice->customer_phone ?? '—' }}</div>
                    <div class="info-row"><strong>ملاحظة:</strong> {{ $invoice->notes ?? '—' }}</div>
                </div>

                <div style="flex:1; min-width:220px;">
                    <h4 style="margin:0 0 10px;">ملخص</h4>
                    <div class="summary-pill small" style="margin-bottom:8px;">
                        <div class="small-title">الإجمالي</div>
                        <div class="big-num">{{ number_format($total, 2) }} ج.م</div>
                    </div>
                    <div class="summary-pill small" style="margin-bottom:8px;">
                        <div class="small-title">تكلفة</div>
                        <div class="big-num">{{ number_format($cost, 2) }} ج.م</div>
                    </div>
                    <div class="summary-pill small" style="background:linear-gradient(90deg,#8FD3A6,#66C2A2); color:#fff;">
                        <div class="small-title">الربح</div>
                        <div class="big-num">{{ number_format($profit, 2) }} ج.م</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-container animate__animated animate__fadeInUp" style="max-width:1100px; margin:0 auto 16px;">
            <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                <input id="searchInput" placeholder="ابحث عن اسم البند، النوع، أو الوصف..."
                    style="flex:1; min-width:200px; padding:10px 12px; border-radius:10px; border:1px solid #e6e6e6;">
                <select id="filterType" style="padding:10px 12px; border-radius:10px; border:1px solid #e6e6e6;">
                    <option value="">كل الأنواع</option>
                    <option value="product">منتج</option>
                    <option value="subscription">اشتراك</option>
                    <option value="booking">حجز</option>
                    <option value="session">جلسة</option>
                    <option value="deposit">إيداع</option>
                </select>

                <div style="margin-left:auto;">
                    <button id="toggleDetails" class="btn btn-outline">طي/توسيع الوصف</button>
                </div>
            </div>
        </div>

        <div class="form-container animate__animated animate__fadeInUp" style="max-width:1100px; margin:0 auto 40px;">
            @if ($invoice->items->isEmpty())
                <div class="alert alert-warning">لا توجد بنود لهذه الفاتورة</div>
            @else
                <div id="printableArea">
                    <table class="invoice-table" style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>البند</th>
                                <th>النوع</th>
                                <th>الكمية</th>
                                <th>سعر الوحدة</th>
                                <th>التكلفة للوحدة</th>
                                <th>الإجمالي</th>
                                <th class="hide-mobile">الوصف</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTbody">
                            @foreach ($invoice->items as $item)
                                <tr class="invoice-item-row" data-item-type="{{ $item->item_type }}"
                                    data-search="{{ implode(' ', [$item->name, $item->description, $item->item_type]) }}">
                                    <td>{{ $item->id }}</td>
                                    <td>
                                        <div style="font-weight:700;">{{ $item->name }}</div>
                                        <div style="color:#888; font-size:13px;">
                                            @if ($item->product_id)
                                                منتج#{{ $item->product_id }}
                                            @elseif($item->subscription_id)
                                                اشتراك#{{ $item->subscription_id }}
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ humanizeItem($item->item_type) }}</td>
                                    <td>{{ $item->qty }}</td>
                                    <td>{{ number_format($item->price, 2) }}</td>
                                    <td>{{ number_format($item->cost, 2) }}</td>
                                    <td>{{ number_format($item->total, 2) }}</td>
                                    <td class="hide-mobile">
                                        <div class="item-desc">{{ $item->description ? e($item->description) : '—' }}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" style="text-align:right; font-weight:700;">المجموع</td>
                                <td style="font-weight:800;">{{ number_format($total, 2) }} ج.م</td>
                                <td class="hide-mobile"></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="text-align:right; color:#666;">مجموع التكلفة</td>
                                <td style="color:#666;">{{ number_format($cost, 2) }} ج.م</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="text-align:right; font-weight:800;">الربح</td>
                                <td style="font-weight:800;">{{ number_format($profit, 2) }} ج.م</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>

    </div>

    <style>
        body {
            font-family: "Cairo", sans-serif;
            background: #F6F6F6;
            color: #333;
        }

        .form-container {
            background: #fff;
            padding: 18px;
            border-radius: 14px;
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.06);
        }

        .btn {
            background: #D9B1AB;
            border: none;
            color: #fff;
            padding: 8px 12px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 700;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid #ddd;
            padding: 8px 12px;
            border-radius: 10px;
            cursor: pointer;
        }

        .summary-pill.small {
            display: block;
            padding: 8px 10px;
            border-radius: 10px;
            margin-bottom: 8px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.04);
        }

        .summary-pill .small-title {
            font-size: 12px;
            color: #666;
        }

        .summary-pill .big-num {
            font-weight: 800;
            font-size: 16px;
            margin-top: 6px;
        }

        .invoice-table thead th {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-weight: 800;
            color: #444;
        }

        .invoice-table td,
        .invoice-table th {
            padding: 12px;
            vertical-align: middle;
        }

        .invoice-table tbody tr {
            border-bottom: 1px solid #f3f3f3;
        }

        .invoice-table tfoot td {
            padding: 12px;
            border-top: 2px solid #f1f1f1;
        }

        .hide-mobile {
            display: table-cell;
        }

        /* responsive: convert to cards on small screens */
        @media (max-width:767px) {

            .invoice-table,
            .invoice-table thead,
            .invoice-table tbody,
            .invoice-table th,
            .invoice-table td,
            .invoice-table tr {
                display: block;
            }

            .invoice-table thead {
                display: none;
            }

            .invoice-item-row {
                background: #fff;
                border-radius: 10px;
                padding: 10px;
                margin-bottom: 10px;
                box-shadow: 0 6px 18px rgba(0, 0, 0, 0.03);
            }

            .invoice-item-row td {
                display: flex;
                justify-content: space-between;
                padding: 6px 8px;
            }

            .hide-mobile {
                display: none;
            }
        }

        /* print styles */
        @media print {
            body * {
                visibility: hidden;
            }

            #printableArea,
            #printableArea * {
                visibility: visible;
            }

            #printableArea {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
        }
    </style>

    @php
        function humanizeItem($type)
        {
            return match ($type) {
                'product' => 'منتج',
                'subscription' => 'اشتراك',
                'booking' => 'حجز',
                'session' => 'جلسة',
                'deposit' => 'إيداع',
                default => $type,
            };
        }
    @endphp

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const filterType = document.getElementById('filterType');
            const toggleDetails = document.getElementById('toggleDetails');
            const exportCsv = document.getElementById('exportCsv');
            const printBtn = document.getElementById('printBtn');

            const rows = Array.from(document.querySelectorAll('.invoice-item-row'));

            function applyFilter() {
                const q = (searchInput.value || '').trim().toLowerCase();
                const t = (filterType.value || '').trim();

                rows.forEach(r => {
                    const text = (r.dataset.search || '').toLowerCase();
                    const type = (r.dataset.itemType || '');
                    const matchesSearch = q === '' || text.includes(q);
                    const matchesType = t === '' || type === t;
                    r.style.display = (matchesSearch && matchesType) ? '' : 'none';
                });
            }

            searchInput.addEventListener('input', applyFilter);
            filterType.addEventListener('change', applyFilter);

            // toggle description visibility (for mobile where descriptions hidden)
            let descVisible = true;
            toggleDetails.addEventListener('click', () => {
                descVisible = !descVisible;
                document.querySelectorAll('.item-desc').forEach(d => {
                    d.style.display = descVisible ? '' : 'none';
                });
                toggleDetails.textContent = descVisible ? 'طي/توسيع الوصف' : 'عرض الوصف';
            });

            // export CSV
            exportCsv.addEventListener('click', () => {
                const rowsCsv = [];
                rowsCsv.push(['id', 'name', 'type', 'qty', 'price', 'cost', 'total', 'description']);

                document.querySelectorAll('.invoice-item-row').forEach(r => {
                    if (r.style.display === 'none') return;
                    const id = r.querySelector('td:nth-child(1)')?.textContent.trim() ?? '';
                    const name = r.querySelector('td:nth-child(2)')?.textContent.trim() ?? '';
                    const type = r.dataset.itemType ?? '';
                    const qty = r.querySelector('td:nth-child(4)')?.textContent.trim() ?? '';
                    const price = r.querySelector('td:nth-child(5)')?.textContent.trim() ?? '';
                    const cost = r.querySelector('td:nth-child(6)')?.textContent.trim() ?? '';
                    const total = r.querySelector('td:nth-child(7)')?.textContent.trim() ?? '';
                    const desc = r.querySelector('.item-desc')?.textContent.trim() ?? '';
                    rowsCsv.push([id, name, type, qty, price, cost, total, desc]);
                });

                const csvContent = rowsCsv.map(r => r.map(c => `"${String(c).replace(/"/g,'""')}"`).join(
                    ',')).join('\n');
                const blob = new Blob([csvContent], {
                    type: 'text/csv;charset=utf-8;'
                });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `invoice_{{ $invoice->id }}.csv`;
                document.body.appendChild(a);
                a.click();
                a.remove();
                URL.revokeObjectURL(url);
            });

            // print
            printBtn.addEventListener('click', () => {
                window.print();
            });
        });
    </script>
@endsection --}}
