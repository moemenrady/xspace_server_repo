{{-- resources/views/daily/admin/calendar.blade.php --}}
@extends('layouts.app_page')

@section('content')

    <div class="container py-4">
        <div class="row g-4 justify-content-center">
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-3 p-3 animate__animated animate__fadeInRight">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <button id="prev-month" class="btn btn-sm theme-btn">&lt;</button>
                        <h5 id="calendar-title" class="mb-0 fw-bold"></h5>
                        <button id="next-month" class="btn btn-sm theme-btn">&gt;</button>
                    </div>

                    {{-- الكالندر بعرض الشاشة (أيام فقط) --}}
                    <div id="calendar" class="border p-2 rounded"></div>

                    <div class="mt-3 text-muted">
                        اضغط على أي يوم لفتح صفحة الشيفتات لذلك اليوم.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
document.addEventListener("DOMContentLoaded", function() {
    const calendarEl = document.getElementById('calendar');
    const titleEl = document.getElementById('calendar-title');
    let current = new Date();

    const fetchEnabled = false; // فعّل لو عايز جلب بيانات من السيرفر
    const fetchUrl = `{{ route('bookings.calendar') }}`;

    // أسماء الأيام بالعربي بحيث يكون السبت أولاً
    const weekdayNames = ['السبت','الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة'];

    function renderCalendar(year, month) {
        titleEl.textContent = `${year} / ${month+1}`;

        // حساب أول خانة للشهر مع بداية اسبوع السبت
        let firstDay = (new Date(year, month, 1).getDay() + 1) % 7;
        let daysInMonth = new Date(year, month + 1, 0).getDate();

        const loadData = fetchEnabled
            ? fetch(`${fetchUrl}?year=${year}&month=${month+1}`).then(res => res.json())
            : Promise.resolve({});

        loadData
            .then(data => {
                // هنا بنبني الجدول بدون هيدر لأسماء الأيام
                let html = `<table class="table text-center calendar-table"><tbody><tr>`;

                let day = 0;
                // مسافات قبل أول يوم
                for (let i = 0; i < firstDay; i++) {
                    html += `<td></td>`;
                    day++;
                }

                for (let d = 1; d <= daysInMonth; d++) {
                    if (day % 7 === 0) html += `</tr><tr>`;

                    let bookings = (data && data[d]) ? data[d] : [];
                    let dots = '';
                    if (bookings.length) {
                        bookings.forEach(b => {
                            let color = (typeof hallColor === 'function') ? hallColor(b.hall_id) : '#ccc';
                            dots += `<span class="booking-dot" style="background:${color};"></span>`;
                        });
                    }

                    let todayClass = "";
                    let now = new Date();
                    if (d === now.getDate() && year === now.getFullYear() && month === now.getMonth()) {
                        todayClass = "today";
                    }

                    // اسم اليوم للتاريخ ده (مرتّب من السبت بداية)
                    let weekdayIndex = (new Date(year, month, d).getDay() + 1) % 7;
                    let weekdayLabel = weekdayNames[weekdayIndex];

                    html += `<td class="${todayClass}" data-date="${year}-${String(month+1).padStart(2,'0')}-${String(d).padStart(2,'0')}" style="vertical-align:top;cursor:pointer">
                                <div style="display:flex;justify-content:space-between;align-items:center;">
                                    <strong>${d}</strong>
                                </div>
                                <div class="weekday-name-block">${weekdayLabel}</div>
                                <div class="dots-wrap">${dots}</div>
                             </td>`;
                    day++;
                }

                while (day % 7 !== 0) {
                    html += `<td></td>`;
                    day++;
                }

                html += `</tr></tbody></table>`;
                calendarEl.innerHTML = html;

                // ربط الكليك
                document.querySelectorAll('#calendar td[data-date]').forEach(td => {
                    td.addEventListener('click', function() {
                        let date = this.dataset.date;
                        window.location.href = `{{ route('admin.day_shifts') }}?date=${date}`;
                    });
                });
            })
            .catch(err => {
                calendarEl.innerHTML = `<div class="text-danger p-2">⚠ خطأ في تحميل الكالندر</div>`;
                console.error(err);
            });
    }

    // أزرار التنقل
    renderCalendar(current.getFullYear(), current.getMonth());
    document.getElementById('prev-month').onclick = () => {
        current.setMonth(current.getMonth() - 1);
        renderCalendar(current.getFullYear(), current.getMonth());
    };
    document.getElementById('next-month').onclick = () => {
        current.setMonth(current.getMonth() + 1);
        renderCalendar(current.getFullYear(), current.getMonth());
    };
});
    </script>
@endsection

@section('style')
    <style>
              body { background: #fff; }

        .theme-btn {
            background-color: #D9B1AB;
            color: #fff;
            border: none;
            transition: all 0.25s ease;
        }
        .theme-btn:hover { background-color: #c0958f; transform: scale(1.04); }

        .card .card-body, .card .p-3 { box-sizing: border-box; }

        /* الكالندر */
        #calendar { width: 100%; box-sizing: border-box; }
        #calendar table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            font-family: inherit;
        }

        /* الخلايا */
        #calendar td {
            min-width: 64px;
            height: 84px;
            vertical-align: top;
            border-radius: 10px;
            background: #fafafa;
            padding: 8px;
            position: relative;
            transition: transform 0.18s ease, background 0.18s ease;
            overflow: hidden;
            box-sizing: border-box;
        }

        @media (min-width: 769px) {
            #calendar td:hover { background: #f1e2df; transform: scale(1.03); cursor: pointer; }
        }

        /* أعلى الخلية: الرقم واسم اليوم صغير على اليمين */
        #calendar td > div:first-child { display:flex; justify-content:space-between; align-items:center; gap:8px; }
        #calendar td strong { font-size:14px; font-weight:700; color:#333; }
        .weekday-name-inline { font-size:12px; color:#666; white-space:nowrap; }

        /* اسم اليوم في سطر منفصل (أكبر قليلاً) */
        .weekday-name-block { font-size:12px; color:#666; margin-top:6px; }

        .dots-wrap { margin-top:8px; display:flex; align-items:center; flex-wrap:wrap; gap:4px; }
        .booking-dot { display:inline-block; width:8px; height:8px; border-radius:50%; margin:0 2px; }

        #calendar td.today {
            background: #D9B1AB;
            color: #fff;
            font-weight:700;
        }

        #calendar tr { height: 100px; }

        /* موبايل: نصغر العناصر ونجعل الاسم واضح */
        @media (max-width: 768px) {
            .card .p-3 { padding-left: 14px; padding-right: 14px; }

            #calendar { overflow-x: auto; -webkit-overflow-scrolling: touch; padding-bottom: 6px; }

            #calendar td {
                min-width: 48px;
                height: 72px;
                padding: 6px;
            }

            #calendar td strong { font-size:12px; margin-bottom:2px; }

            /* نُظهِر الاسم بشكل مناسب: السطر الصغير على اليمين ونبقي السطر المنفصل مرئي */
            .weekday-name-inline { font-size:11px; color:#666; }
            .weekday-name-block { display:block; font-size:11px; color:#666; margin-top:4px; }

            .booking-dot { width:6px; height:6px; margin:0 1px; }

            /* نحذف تأثير التكبير على اللمس */
            #calendar td { transform: none !important; }
        }

        /* شاشات صغيرة جدًا */
        @media (max-width: 420px) {
            #calendar td { min-width: 48px; height: 64px; padding: 5px; }
            #calendar tr { height: auto; }
            .weekday-name-block { font-size:10px; }
            .weekday-name-inline { font-size:10px; }
        }
    </style>
@endsection
