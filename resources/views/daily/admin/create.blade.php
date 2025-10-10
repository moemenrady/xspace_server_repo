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

            function renderCalendar(year, month) {
                titleEl.textContent = `${year} / ${month+1}`;
                let firstDay = new Date(year, month, 1).getDay();
                let daysInMonth = new Date(year, month + 1, 0).getDate();

                let html = `<table class="table text-center"><tr>`;
                let day = 0;

                for (let i = 0; i < firstDay; i++) {
                    html += `<td></td>`;
                    day++;
                }

                for (let d = 1; d <= daysInMonth; d++) {
                    if (day % 7 == 0) html += `</tr><tr>`;

                    let todayClass = "";
                    let now = new Date();
                    if (d === now.getDate() && year === now.getFullYear() && month === now.getMonth()) {
                        todayClass = "today";
                    }

                    html += `<td class="${todayClass}" data-date="${year}-${String(month+1).padStart(2,'0')}-${String(d).padStart(2,'0')}">
                                <strong>${d}</strong>
                             </td>`;
                    day++;
                }

                html += `</tr></table>`;
                calendarEl.innerHTML = html;

                // عند الكليك نوجّه لصفحة الشيفتات لليوم
                document.querySelectorAll('#calendar td[data-date]').forEach(td => {
                    td.addEventListener('click', function() {
                        let date = this.dataset.date;
                        window.location.href = `{{ route('admin.day_shifts') }}?date=${date}`;
                    });
                });
            }

            // أزرار الشهر
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
        .theme-btn { background-color: #D9B1AB; color: #fff; border: none; transition: all 0.3s ease; }
        .theme-btn:hover { background-color: #c0958f; transform: scale(1.05); }

        /* الكالندر */
        #calendar table { width:100%; border-collapse:collapse; }
        #calendar td {
            min-width: 80px;
            height: 90px;
            vertical-align: top;
            border-radius: 10px;
            background: #fafafa;
            padding: 8px;
            position: relative;
            transition: all 0.18s ease;
        }
        #calendar td:hover { background:#f1e2df; transform: scale(1.03); cursor:pointer; }
        #calendar td strong { display:inline-block; font-size:14px; font-weight:bold; margin-bottom:6px; color:#333; }
        #calendar td.today { background:#D9B1AB; color:#fff; font-weight:bold; }

        #calendar tr { height:100px; }

        @media (max-width:768px) {
            #calendar td { min-width:40px; height:60px; padding:6px; }
            #calendar td strong { font-size:12px; }
        }
    </style>
@endsection


