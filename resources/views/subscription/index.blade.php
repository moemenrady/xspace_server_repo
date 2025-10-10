@extends('layouts.app')
@section('title', 'إدارة الاشتراكات')

<style>
    body {
        font-family: "Tahoma", sans-serif;
        background: linear-gradient(to bottom, #fff, #fce9d9);
        margin: 0;
        padding: 0;
        color: #333;
    }

    /* الزرار نفسه */
    #addButton {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        /* يشيل الخط الأزرق */
        background: #ffcb9a;
        font-size: 48px;
        font-weight: bold;
        color: #000;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        transition: 0.3s;
        margin: 20px auto;
        /* يخليه وسط الصفحة */
    }

    #addButton:hover {
        background: #ffa94d;
        transform: scale(1.05);
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 30px 20px;
    }

    /* مربع البحث */
    .search-box {
        margin: 20px auto;
        text-align: center;
    }

    .search-box input {
        padding: 14px 20px;
        width: 450px;
        max-width: 100%;
        border-radius: 25px;
        border: 1px solid #ddd;
        font-size: 15px;
        outline: none;
        transition: 0.2s;
        background: #fff;
    }

    .search-box input:focus {
        border-color: #ffcb9a;
        box-shadow: 0 0 6px rgba(255, 170, 80, 0.5);
    }

    /* الفلاتر */
    .filters-box {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 25px;
    }

    .form-check-lg .form-check-input {
        width: 1.5em;
        height: 1.5em;
        margin-top: .2em;
    }

    .form-check-lg .form-check-label {
        font-size: 1.05em;
        margin-right: .3em;
    }

    /* الجدول */
    table {
        width: 100%;
        border-collapse: collapse;
        background: transparent;
        border-radius: 12px;
        overflow: hidden;
        margin-top: 20px;
    }

    thead {
        background: rgba(255, 224, 178, 0.8);
    }

    thead th {
        padding: 16px 20px;
        text-align: center;
        font-size: 15px;
        font-weight: bold;
        color: #444;
    }

    tbody tr {
        border-bottom: 1px solid #eee;
        text-align: center;
        transition: background 0.2s;
    }

    tbody tr:hover {
        background: rgba(255, 247, 240, 0.7);
    }

    tbody td {
        padding: 14px 18px;
        font-size: 15px;
        color: #333;
    }

    /* الموبايل */
    @media (max-width: 768px) {
        .filters-box {
            flex-direction: column;
            align-items: flex-start;
        }

        .search-box input {
            width: 100%;
        }

        table,
        thead,
        tbody,
        th,
        td,
        tr {
            display: block;
            width: 100%;
        }

        thead {
            display: none;
        }

        tbody tr {
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.9);
        }

        tbody td {
            text-align: right;
            padding: 8px 10px;
            position: relative;
            font-size: 14px;
        }

        tbody td::before {
            content: attr(data-label);
            position: absolute;
            left: 10px;
            font-weight: bold;
            color: #666;
        }
    }
</style>

@section('content')
    <div class="container">
        {{-- زرار الإضافة --}}
        <a href="{{ route('subscriptions.create') }}" id="addButton">+</a>
        {{-- البحث --}}
        <div class="search-box">
            <input type="text" id="searchBox" placeholder="🔍 بحث (اسم العميل / الهاتف / ID)">
        </div>

        {{-- الفلاتر --}}
        <div class="filters-box">
            {{-- الحالة --}}
            <div>
                <label class="form-label d-block mb-2">⚡ الحالة</label>
                <div class="d-flex gap-3">
                    <div class="form-check form-check-lg">
                        <input class="form-check-input status-filter" type="checkbox" value="1" id="statusActive">
                        <label class="form-check-label" for="statusActive">فعال</label>
                    </div>
                    <div class="form-check form-check-lg">
                        <input class="form-check-input status-filter" type="checkbox" value="0" id="statusEnded">
                        <label class="form-check-label" for="statusEnded">منتهي</label>
                    </div>
                </div>
            </div>

            {{-- الخطط --}}
            <div>
                <label class="form-label d-block mb-2">📦 الخطط</label>
                <div class="d-flex flex-wrap gap-3">
                    @foreach ($plans as $plan)
                        <div class="form-check form-check-lg">
                            <input class="form-check-input plan-filter" type="checkbox" value="{{ $plan->id }}"
                                id="plan{{ $plan->id }}">
                            <label class="form-check-label" for="plan{{ $plan->id }}">{{ $plan->name }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>العميل</th>
                    <th>الهاتف</th>
                    <th>الخطة</th>
                    <th>من</th>
                    <th>إلى</th>
                    <th>المتبقي</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody id="subsTable">
                <tr>
                    <td colspan="9" class="text-center p-3">⏳ جاري التحميل...</td>
                </tr>
            </tbody>
        </table>
      
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // عناصر البحث والتصفية
            let searchBox = document.getElementById("searchBox");
            let statusFilters = document.querySelectorAll(".status-filter");
            let planFilters = document.querySelectorAll(".plan-filter");
            let tableBody = document.getElementById("subsTable");

            // المودال
            let renewModal = new bootstrap.Modal(document.getElementById("renewModal"));
            let renewForm = document.getElementById("renewForm");
            let renewClient = document.getElementById("renewClient");
            let renewPlan = document.getElementById("renewPlan");
            let renewRoute = "{{ route('subscriptions.renew', ['subscription' => ':id']) }}";

            // الراوت الخاص بالعرض
            let showRoute = @json(route('subscriptions.show', ['id' => ':id']));

            // فورمات التاريخ
            function formatDateTime(dateStr) {
                let d = new Date(dateStr);
                let options = {
                    year: "numeric",
                    month: "short",
                    day: "2-digit",
                };
                return d.toLocaleString("en-US", options);
            }

            // الفetch
            function fetchSubs() {
                let q = searchBox.value;
                let statuses = Array.from(statusFilters).filter(c => c.checked).map(c => c.value);
                let plans = Array.from(planFilters).filter(c => c.checked).map(c => c.value);

                let params = new URLSearchParams({
                    q
                });
                statuses.forEach(s => params.append("statuses[]", s));
                plans.forEach(p => params.append("plans[]", p));

                fetch("{{ route('subscriptions.ajaxSearch') }}?" + params.toString())
                    .then(res => res.json())
                    .then(data => {
                        tableBody.innerHTML = "";
                        if (data.length === 0) {
                            tableBody.innerHTML =
                                `<tr><td colspan="9" class="text-center p-3">❌ لا توجد نتائج</td></tr>`;
                        } else {
                            data.forEach(s => {
                                tableBody.innerHTML += `
                                <tr class="subscription-row" data-id="${s.id}" style="cursor: pointer;">
                                    <td>${s.id}</td>
                                    <td>${s.client_name}</td>
                                    <td>${s.client_phone}</td>
                                    <td>${s.plan_name}</td>
                                    <td>${formatDateTime(s.start_date) ?? '-'}</td>
                                    <td>${formatDateTime(s.end_date) ?? '-'}</td>
                                    <td>${s.remaining_visits}</td>
                                    <td>
                                        ${s.is_active === "فعال"
                                            ? `<span class="badge bg-success">فعال</span>`
                                            : `<span class="badge bg-secondary">منتهي</span>`}
                                    </td>
                                    
                                </tr>`;
                            });

                            attachRowClick();
                        }
                    });
            }

            // ربط الكلاينت show
            function attachRowClick() {
                document.querySelectorAll(".subscription-row").forEach(row => {
                    row.addEventListener("click", function() {
                        let id = this.dataset.id;
                        window.location.href = showRoute.replace(':id', id);
                    });
                });
            }

            // تشغيل البحث أول مرة
            searchBox.addEventListener("keyup", fetchSubs);
            statusFilters.forEach(cb => cb.addEventListener("change", fetchSubs));
            planFilters.forEach(cb => cb.addEventListener("change", fetchSubs));

            fetchSubs();
        });
    </script>


    <!-- ✅ Modal تجديد الاشتراك -->
    <div class="modal fade animate__animated animate__fadeInDown" id="renewModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 shadow">

                <!-- الهيدر -->
                <div class="modal-header bg-success text-white rounded-top-4">
                    <h5 class="modal-title">🔄 تجديد الاشتراك</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- الفورم -->
                <form id="renewForm" method="POST" class="p-4">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">👤 العميل</label>
                            <input type="text" id="renewClient" class="form-control form-control-lg" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">📦 الخطة الحالية</label>
                            <input type="text" id="renewPlan" class="form-control form-control-lg" readonly>
                        </div>

                        <div class="col-12">
                            <label for="plan_id" class="form-label">🔄 اختيار خطة أخرى (اختياري)</label>
                            <select name="plan_id" id="plan_id" class="form-select form-select-lg">
                                <option value="">-- نفس الخطة --</option>
                                @foreach ($plans as $plan)
                                    <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- الأزرار -->
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-success btn-lg px-5 fw-bold">✅ إتمام التجديد</button>
                        <button type="button" class="btn btn-outline-secondary btn-lg px-4 ms-2"
                            data-bs-dismiss="modal">إلغاء</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@endsection
