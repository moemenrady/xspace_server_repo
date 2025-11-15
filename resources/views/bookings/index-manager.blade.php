@extends('layouts.app_page')

@section('title', 'إدارة الحجوزات')

@section('content')
    <div class="page-container">
    @section('page_title')
        <h1 class="title">📅 إدارة الحجوزات</h1>    @endsection
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
        <div class="page-actions">
            <a href="{{ route('bookings.create') }}" class="add-booking-btn" aria-label="اضافة حجز">اضافة حجز</a>
        </div>
        {{-- البحث والفلاتر --}}
        <div class="filters-box card shadow-sm mb-4 p-3">
            <div class="row g-3 align-items-end">
                {{-- البحث --}}
                <div class="col-md-3">
                    <label class="form-label">🔍 بحث</label>
                    <input type="text" id="searchBox" class="form-control"
                        placeholder="اسم الحجز / العميل / الهاتف / ID">
                </div>

                {{-- الفلاتر بالحالة --}}
                <div class="col-md-5">
                    <label class="form-label d-block">⚡ الحالة</label>
                    <div class="d-flex flex-wrap gap-3">
                        <div class="form-check">
                            <input class="form-check-input status-filter" type="checkbox" value="scheduled"
                                id="statusScheduled">
                            <label class="form-check-label" for="statusScheduled">ليس الآن</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input status-filter" type="checkbox" value="due" id="statusDue">
                            <label class="form-check-label" for="statusDue">لم يبدأ</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input status-filter" type="checkbox" value="in_progress"
                                id="statusInProgress">
                            <label class="form-check-label" for="statusInProgress">جاري</label>
                        </div>
                    </div>
                </div>

            {{-- التاريخ --}}
            <div class="col-md-4">
                <label class="form-label">📆 الفترة</label>
                <input type="text" id="dateRange" class="form-control" placeholder="اختر من - إلى">
            </div>
        </div>
    </div>

    {{-- الكروت --}}
    <div class="bookings-list" id="bookingsList">
        <p class="text-center p-3">⏳ جاري التحميل...</p>
    </div>ِ
</div>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ar.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let searchBox = document.getElementById("searchBox");
        let checkboxes = document.querySelectorAll(".status-filter");
        let bookingsList = document.getElementById("bookingsList");
        let fromDate = null,
            toDate = null;

        // route للـ show
        let showRoute = @json(route('bookings.show', ':id'));

        // Flatpickr لتحديد المدى الزمني
        flatpickr("#dateRange", {
            mode: "range",
            dateFormat: "Y-m-d",

            onChange: function(selectedDates) {
                if (selectedDates.length === 2) {
                    fromDate = selectedDates[0].toISOString().split('T')[0];
                    toDate = selectedDates[1].toISOString().split('T')[0];
                } else {
                    fromDate = toDate = null;
                }
                fetchBookings();
            }
        });

        function formatDateTime(dateStr) {
            if (!dateStr) return "-";
            let d = new Date(dateStr);
            return d.toLocaleString("ar-EG", {
                year: "numeric",
                month: "short",
                day: "2-digit",
                hour: "2-digit",
                minute: "2-digit",
            });
        }

        function fetchBookings() {
            let q = searchBox.value || '';
            let statuses = Array.from(checkboxes).filter(c => c.checked).map(c => c.value);

            let params = new URLSearchParams({
                q
            });
            if (fromDate) params.append("from", fromDate);
            if (toDate) params.append("to", toDate);
            statuses.forEach(s => params.append("statuses[]", s));

            // Debug: تحقق من الرابط قبل الطلب
            console.log("Fetching bookings with params:", params.toString());

            fetch("{{ route('bookings.ajaxSearchManager') }}?" + params.toString())
                .then(res => res.json())

                .then(data => {
                    console.log("Received data:", data);
                    bookingsList.innerHTML = "";

                    // إذا data مش array، حوّلها إلى array
                    let bookingsArray = Array.isArray(data) ? data : Object.values(data);

                    if (!bookingsArray.length) {
                        bookingsList.innerHTML = `<p class="no-results">❌ لا توجد نتائج</p>`;
                        if (data.error) console.error("Server error:", data.error);
                        return;
                    }

                    bookingsArray.forEach(b => {
                        let actionBtns = "";
                        if (b.status === "scheduled" || b.status === "due") {
                            actionBtns =
                                `<a href="/bookings/${b.id}/edit" class="btn btn-sm btn-outline-primary">✏️ تعديل</a>`;
                        }

                        // حساب يوم الأسبوع باللهجة المصرية (0 = الأحد)
                        const weekdayNames = ['الحد', 'الاتنين', 'التلات', 'الأربع', 'الخميس',
                            'الجمعة', 'السبت'
                        ];
                        const startDate = new Date(b.start_at);
                        const weekdayLabel = weekdayNames[startDate.getDay()];

                        bookingsList.innerHTML += `
        <div class="booking-card" onclick="window.location.href='${showRoute.replace(':id', b.id)}'" style="cursor:pointer;">
            <div class="info">
                <h3>${b.title}</h3>
                <p>👤 ${b.client_name || '-'} | 📞 ${b.client_phone || '-'}</p>
                <p>🏛️ ${b.hall_name || '-'} | 👥 ${b.attendees || 0}</p>
                <!-- هنا نعرض يوم الأسبوع -->
                <p class="weekday">📅 ${weekdayLabel}</p>
                <p>⏰ من: ${formatDateTime(b.start_at)} <br> إلى: ${formatDateTime(b.end_at)}</p>
            </div>
            <div class="meta">
                <span class="badge bg-${statusColor(b.status)}">${statusLabel(b.status)}</span>
                <p class="mt-2">💰 ${parseFloat(b.estimated_total).toFixed(2)}</p>
                <div class="actions mt-2">${actionBtns}</div>
            </div>
        </div>
    `;
                    });
                })

                .catch(err => {
                    console.error("Error fetching bookings:", err);
                    bookingsList.innerHTML = `<p class="no-results">❌ حدث خطأ أثناء جلب البيانات</p>`;
                });
        }

        function statusColor(status) {
            switch (status) {
                case "scheduled":
                    return "secondary";
                case "due":
                    return "warning";
                case "in_progress":
                    return "info";
                case "finished":
                    return "success";
                case "cancelled":
                    return "danger";
                default:
                    return "dark";
            }
        }

        function statusLabel(status) {
            switch (status) {
                case "scheduled":
                    return "ليس الآن";
                case "due":
                    return "لم يبدأ";
                case "in_progress":
                    return "جاري";
                case "finished":
                    return "منتهي";
                case "cancelled":
                    return "ملغي";
                default:
                    return "غير معروف";
            }
        }

        searchBox.addEventListener("keyup", fetchBookings);
        checkboxes.forEach(cb => cb.addEventListener("change", fetchBookings));

        fetchBookings(); // تحميل أولي
    });
</script>



@endsection

@section('style')
<style>
    .page-container {
        max-width: 1000px;
        margin: auto;
        padding: 20px;
    }


    /* ===== Snackbar ===== */
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
        transition: opacity 0.4s ease, transform 0.4s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .snackbar.show {
        opacity: 1;
        transform: translateX(0);
    }

    .snackbar.success {
        background: #28a745;
    }

    .snackbar.error {
        background: #dc3545;
    }

    .snackbar i {
        font-size: 16px;
    }

    .page-actions {
        position: fixed;
        top: 16px;
        right: 16px;
        /* ثابت في أقصى اليمين */
        z-index: 1000;
    }

    .add-booking-btn {
        position: relative;
        display: inline-block;
        padding: 12px 18px;
        background: var(--btn-bg);
        color: var(--btn-text);
        font-weight: 800;
        /* Bold */
        font-size: 15px;
        border: 1px solid var(--btn-border);
        border-radius: 14px;
        text-decoration: none;
        letter-spacing: .2px;
        box-shadow: 0 6px 14px rgba(0, 0, 0, .12), inset 0 -2px 0 rgba(0, 0, 0, .05);
        transition: transform .25s ease, box-shadow .25s ease, background-color .25s ease, border-color .25s ease;
        overflow: hidden;
        /* لإخفاء الوميض أثناء الحركة */
        -webkit-tap-highlight-color: transparent;
    }

    /* لمعان عصري يمر على الزر */
    .add-booking-btn::before {
        content: "";
        position: absolute;
        inset: -120% -30%;
        background: linear-gradient(120deg, transparent 35%, rgba(255, 255, 255, .65) 50%, transparent 65%);
        transform: translateX(-100%);
        transition: transform .6s ease;
        pointer-events: none;
    }

    .add-booking-btn:hover {
        background-color: var(--btn-bg-hover);
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 10px 22px rgba(0, 0, 0, .16), inset 0 -2px 0 rgba(0, 0, 0, .05);
        border-color: #e9c94e;
    }

    .booking-card .weekday {
        font-weight: 600;
        color: #6c757d;
        margin: 4px 0;
    }

    .add-booking-btn:hover::before {
        transform: translateX(100%);
    }

    /* تأثير ضغط خفيف */
    .add-booking-btn:active {
        transform: translateY(0) scale(0.99);
        box-shadow: 0 6px 14px rgba(0, 0, 0, .12), inset 0 -2px 0 rgba(0, 0, 0, .08);
    }

    /* وضيح لليوزرز باستخدام الكيبورد */
    .add-booking-btn:focus {
        outline: none;
        box-shadow:
            0 0 0 3px rgba(255, 228, 131, .6),
            0 10px 22px rgba(0, 0, 0, .16),
            inset 0 -2px 0 rgba(0, 0, 0, .05);
    }

    /* احترام إعدادات تقليل الحركة */
    @media (prefers-reduced-motion: reduce) {

        .add-booking-btn,
        .add-booking-btn::before {
            transition: none;
        }

        .add-booking-btn:hover {
            transform: none;
        }
    }

    .bookings-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .booking-card {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        background: #fff;
        border-radius: 10px;
        padding: 15px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        border-top: 4px solid #d9b2ad;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .booking-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    }

    .booking-card .info {
        flex: 2;
        font-size: 14px;
    }

        .booking-card .info h3 {
            margin: 0 0 5px;
            font-size: 16px;
            color: #333;
        }

        .booking-card .meta {
            flex: 1;
            text-align: right;
            font-size: 13px;
        }

        .booking-card .actions a {
            display: block;
            margin-bottom: 4px;
        }

        .no-results {
            text-align: center;
            color: #888;
        }
    </style>
@endsection
