@extends('layouts.app')
@section('title', '📊 شيفتاتي اليومية')

<style>
    body {
        font-family: "Tahoma", sans-serif;
        background: linear-gradient(to bottom, #fff, #f0f9ff);
        margin: 0;
        padding: 0;
        color: #333;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 30px 20px;
    }

    /* ✅ فلتر التاريخ */
    .filters-box {
        display: flex;
        gap: 20px;
        justify-content: center;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }

    .filters-box input {
        padding: 10px 15px;
        border-radius: 8px;
        border: 1px solid #ccc;
        font-size: 15px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        background: transparent;
        border-radius: 12px;
        overflow: hidden;
        margin-top: 20px;
    }

    thead {
        background: rgba(255, 201, 125, 0.4);
    }

    thead th {
        padding: 14px 16px;
        text-align: center;
        font-size: 14px;
        font-weight: bold;
        color: #444;
    }

    tbody tr {
        border-bottom: 1px solid #eee;
        text-align: center;
        transition: background 0.2s;
    }

    tbody tr:hover {
        background: rgba(240, 248, 255, 0.6);
    }

    tbody td {
        padding: 12px 14px;
        font-size: 14px;
        color: #333;
    }

    /* ✅ موبايل -> كروت */
    @media (max-width: 768px) {

        table,
        thead,
        tbody,
        th,
        td,
        tr {
            display: block;
        }

        thead {
            display: none;
        }

        tbody tr {
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 12px;
            background: #fff;
            text-align: right;
        }

        tbody td {
            padding: 6px 10px;
            position: relative;
            font-size: 14px;
        }

        tbody td::before {
            content: attr(data-label);
            font-weight: bold;
            color: #666;
            position: absolute;
            left: 10px;
        }
    }
</style>

@section('content')
    <div class="container">
        <h1 class="mb-4 text-center">📊 شيفتاتي اليومية</h1>

        {{-- ✅ فلتر التاريخ --}}
        <div class="filters-box">
            <div>
                <label>من:</label>
                <input type="date" id="fromDate">
            </div>
            <div>
                <label>إلى:</label>
                <input type="date" id="toDate">
            </div>
        </div>

        {{-- ✅ جدول الشيفتات --}}
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>بداية </th>
                    <th>نهاية </th>
                    <th>المدة</th>
                    <th>الإيرادات</th>
                    <th>المصروفات</th>
                    <th>الصافي</th>
                </tr>
            </thead>
            <tbody id="shiftsTable">
                @forelse($shifts as $shift)
                    <tr>
                        <td data-label="#"> {{ $shift->id }} </td>
                        <td data-label="بداية">{{ $shift->created_at->format('Y-m-d H:i') }}</td>
                        <td data-label="نهاية">
                            {{ $shift->end_time ? \Carbon\Carbon::parse($shift->updated_at)->format('Y-m-d H:i') : '—' }}
                        </td>
                        <td data-label="المدة">
                            @php
                                $durationText = '—';
                                if (!empty($shift->end_time) && $shift->created_at && $shift->updated_at) {
                                    $minutes = $shift->created_at->diffInMinutes($shift->updated_at);
                                    $hours = intdiv($minutes, 60);
                                    $mins = $minutes % 60;
                                    $durationText =
                                        $hours > 0
                                            ? $hours . ' س ' . ($mins > 0 ? $mins . ' د' : '')
                                            : $mins . ' دقيقة';
                                }
                            @endphp
                            {{ $durationText }}
                        </td>
                        <td data-label="الإيرادات">{{ number_format($shift->total_amount, 2) }}</td>
                        <td data-label="المصروفات">{{ number_format($shift->total_expense, 2) }}</td>
                        <td data-label="الصافي">{{ number_format($shift->total_amount - $shift->total_expense, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center p-3">❌ لا توجد شيفتات</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let fromDate = document.getElementById("fromDate");
            let toDate = document.getElementById("toDate");
            let tableBody = document.getElementById("shiftsTable");

            function fetchShifts() {
                let from = fromDate.value;
                let to = toDate.value;

                let params = new URLSearchParams();
                if (from) params.append("from", from);
                if (to) params.append("to", to);

                fetch("{{ route('shift.index') }}?" + params.toString(), {
                        headers: {
                            "X-Requested-With": "XMLHttpRequest"
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        tableBody.innerHTML = "";
                        if (!data || data.length === 0) {
                            tableBody.innerHTML =
                                `<tr><td colspan="7" class="text-center p-3">❌ لا توجد نتائج</td></tr>`;
                        } else {
                            data.forEach(s => {
                                tableBody.innerHTML += `
<tr>
    <td data-label="#">${s.id}</td>
    <td data-label="بداية">${s.start_time}</td>
    <td data-label="نهاية">${s.end_time}</td>
    <td data-label="المدة">${s.duration}</td>
    <td data-label="الإيرادات">${s.total_amount}</td>
    <td data-label="المصروفات">${s.total_expense}</td>
    <td data-label="الصافي">${s.net_profit}</td>
</tr>`;
                            });
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        tableBody.innerHTML =
                            `<tr><td colspan="7" class="text-center p-3">❌ خطأ في جلب البيانات</td></tr>`;
                    });
            }

            fromDate.addEventListener("change", fetchShifts);
            toDate.addEventListener("change", fetchShifts);
        });
    </script>
@endsection
