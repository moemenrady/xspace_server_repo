@extends('layouts.app_page')

@section('content')
    <div class="container py-4">
        <div class="row g-4">

            <!-- فورم الحجز -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0 rounded-3 animate__animated animate__fadeInUp">
                    <div class="card-body p-4">
                        <!-- ====== Estimate banner (hidden by default) ====== -->
                        <div id="estimateBanner" class="estimate-banner" aria-hidden="true" style="display:none;">
                            <div class="estimate-inner">
                                <div class="estimate-left">📊 السعر التقديري</div>
                                <div class="estimate-amount" id="estimateAmount">—</div>
                                <div class="estimate-small" id="estimatePerHour">—</div>
                            </div>
                        </div>

                        <h4 class="mb-4 text-center fw-bold">إضافة حجز جديد</h4>

                        <form action="{{ route('bookings.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">اسم الحجز</label>
                                <input type="text" name="title" class="form-control" value="{{ old('title') }}">
                                @error('title')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">رقم العميل / الاسم</label>
                                <input type="text" id="client_search" name="client_phone" placeholder="🔍 العميل"
                                    autocomplete="off" class="form-control" value="{{ old('client_phone') }}"
                                    maxlength="11">
                                <div id="client-results" class="border bg-white shadow-sm rounded mt-1"
                                    style="display:none; position:absolute; z-index:999; max-height:200px; overflow-y:auto;">
                                </div>
                                @error('client_phone')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">اسم العميل</label>
                                <input type="text" id="client_name" name="client_name" class="form-control"
                                    placeholder="الاسم" value="{{ old('client_name') }}">
                                @error('client_name')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <input type="hidden" id="client_id" name="client_id" value="{{ old('client_id') }}">

                            <div class="mb-3">
                                <label class="form-label">القاعة</label>
                                <select name="hall_id" id="hall_id" class="form-select" required>
                                    <option value=""> ..... اختر القاعه </option>
                                    @foreach ($halls as $hall)
                                        <option value="{{ $hall->id }}"
                                            {{ old('hall_id') == $hall->id ? 'selected' : '' }}>
                                            {{ $hall->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('hall_id')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">عدد الأفراد</label>
                                <input type="number" name="attendees" class="form-control"
                                    value="{{ old('attendees') }}"placeholder="ادخل عدد الافراد" min="1" required>
                                @error('attendees')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- الحقول -->
                            <div class="mb-3">
                                <label class="form-label">📅 اليوم</label>
                                <input type="text" id="day_picker" class="form-control" placeholder="اختر اليوم">
                                @error('day_picker')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>


                            <div class="mb-3">
                                <label class="form-label"> وقت البداية</label>

                                <input type="text" name="start_at_full" id="start_time" class="form-control"
                                    placeholder="اختر وقت البداية" readonly>
                                @error('start_at')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label"> وقت النهاية</label>

                                <input type="text" name="end_at_full" id="end_time" class="form-control"
                                    placeholder="اختر وقت النهاية" readonly>
                                @error('end_at')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">⏳ مدة الحجز</label>
                                <input type="text" id="duration_display" class="form-control" readonly>
                                <input type="hidden" name="duration_minutes" id="duration">
                                @error('duration_minutes')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <!-- تكرار الحجز -->
                            <div class="mb-3">
                                <label class="form-label">تكرار الحجز</label>
                                <select name="recurrence_type" id="recurrence_type" class="form-select">
                                    <option value="none" {{ old('recurrence_type') == 'none' ? 'selected' : '' }}>لا
                                        يوجد</option>
                                    <option value="weekly" {{ old('recurrence_type') == 'weekly' ? 'selected' : '' }}>كل
                                        أسبوع</option>
                                    <option value="biweekly" {{ old('recurrence_type') == 'biweekly' ? 'selected' : '' }}>
                                        كل أسبوعين</option>
                                    <option value="monthly" {{ old('recurrence_type') == 'monthly' ? 'selected' : '' }}>كل
                                        شهر</option>
                                    <option value="custom" {{ old('recurrence_type') == 'custom' ? 'selected' : '' }}>مخصص
                                        (كل N أسابيع)</option>
                                </select>
                            </div>

                            <div class="mb-3" id="custom_interval_wrapper" style="display: none;">
                                <label class="form-label">المسافة (بالأسابيع) — لو اخترت مخصص</label>
                                <input type="number" name="recurrence_interval" id="recurrence_interval"
                                    class="form-control" value="{{ old('recurrence_interval', 1) }}" min="1">
                            </div>

                            <!-- هنا عطيت الـ wrapper معرف عشان نتحكم فيه بالـ JS -->
                            <div class="mb-3" id="recurrence_end_wrapper" style="display: none;">
                                <label class="form-label">تاريخ انتهاء التكرار (اختياري — لو حابب)</label>
                                <input type="date" name="recurrence_end_date" id="recurrence_end_date"
                                    class="form-control" value="{{ old('recurrence_end_date') }}">
                                <small class="text-muted">اتركه فاضي لو مش عايز تكرر</small>
                            </div>

                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const typeSel = document.getElementById('recurrence_type');
                                    const customWrapper = document.getElementById('custom_interval_wrapper');
                                    const endWrapper = document.getElementById('recurrence_end_wrapper');
                                    const endInput = document.getElementById('recurrence_end_date');

                                    function updateUI() {
                                        const val = typeSel.value;

                                        // show custom interval only for 'custom'
                                        customWrapper.style.display = (val === 'custom') ? 'block' : 'none';

                                        // show end-date only when recurrence != 'none'
                                        if (val && val !== 'none') {
                                            // show
                                            endWrapper.style.display = 'block';
                                            endWrapper.setAttribute('aria-hidden', 'false');
                                            // make it required to push user to set it (optional — comment next line if not required)
                                            endInput.required = true;

                                            // focus + scroll to make sure user يركز عليه
                                            endInput.focus({
                                                preventScroll: false
                                            });
                                            endInput.scrollIntoView({
                                                behavior: 'smooth',
                                                block: 'center'
                                            });

                                            // ensure min date not before start date if start exists
                                            const startHidden = document.getElementById('start_at_full');
                                            if (startHidden && startHidden.value) {
                                                const startDate = startHidden.value.split(' ')[0]; // YYYY-MM-DD
                                                if (startDate) endInput.min = startDate;
                                            }
                                        } else {
                                            // hide and clear value + remove required
                                            endWrapper.style.display = 'none';
                                            endWrapper.setAttribute('aria-hidden', 'true');
                                            endInput.required = false;
                                            // don't clear if you want to preserve old value after form validation fail,
                                            // but to strictly clear: uncomment next line
                                            // endInput.value = '';
                                        }
                                    }

                                    // initial run (page load) — if old value exists, show accordingly
                                    updateUI();

                                    // on change
                                    typeSel.addEventListener('change', updateUI);
                                });
                            </script>


                            <div class="mb-3">
                                <label class="form-label">المقدم (اختياري)</label>
                                <input type="number" name="deposit" class="form-control" value="{{ old('deposit') }}"
                                    min="0" step="0.01" placeholder="0.00">
                                <small class="text-muted">اتركه فارغًا لو العميل مش دافع مقدم</small>
                                @error('deposit')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">ملاحظات</label>
                                <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                            </div>
                            <input type="hidden" name="start_at_full" id="start_at_full">
                            <input type="hidden" name="end_at_full" id="end_at_full">

                            <button type="submit" class="btn theme-btn w-100 py-2 fw-bold">💾 إضافة الحجز</button>
                        </form>
                    </div>
                </div>



                <!-- الحجوزات -->
                <div class="card shadow-sm border-0 rounded-3 mt-4 animate__animated animate__fadeIn">
                    <div class="card-body">
                        <h5 class="mb-3 fw-bold">الحجوزات في نفس التوقيت المختار</h5>
                        <div id="bookings-sidebar" class="p-2 border rounded bg-light text-muted">
                            لم يتم ادخال بيانات حجز
                        </div>
                    </div>
                </div>
            </div>

            <!-- الكالندر -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0 rounded-3 p-3 animate__animated animate__fadeInRight">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <button id="prev-month" class="btn btn-sm theme-btn">&lt;</button>
                        <h5 id="calendar-title" class="mb-0 fw-bold"></h5>
                        <button id="next-month" class="btn btn-sm theme-btn">&gt;</button>
                    </div>
                    <div id="calendar" class="border p-2 rounded"></div>

                    <div class="mt-3">
                        <strong>دليل الألوان:</strong>
                        <ul class="list-unstyled mt-2">
                            @foreach ($halls as $i => $hall)
                                <li class="mb-1 d-flex align-items-center">
                                    <span
                                        style="display:inline-block;width:18px;height:18px;
                                        background-color: {{ ['#FFD700', '#32CD32', '#1E90FF', '#FF69B4'][$i % 4] }};
                                        border-radius:4px; border:1px solid #999; margin-inline-end:8px;">
                                    </span>
                                    {{ $hall->name }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div id="day-bookings-box" class="mt-3">
                        <div class="card shadow-sm border-0 rounded-3 p-3">
                            <h5 class="fw-bold mb-2">📅 تفاصيل حجوزات اليوم</h5>
                            <div id="day-bookings-content" class="text-muted">اضغط على يوم في الكالندر لعرض التفاصيل.
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>




    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 🟢 اختيار اليوم فقط
            const dayPicker = flatpickr("#day_picker", {
                dateFormat: "Y-m-d",
                onChange: function(selectedDates) {
                    if (selectedDates.length > 0) {
                        // بعد اختيار اليوم افتح start time
                        document.getElementById("start_time").click();
                    }
                }
            });

            // ⏰ اختيار وقت البداية
            const startPicker = flatpickr("#start_time", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "h:i K", // ← AM/PM
                time_24hr: false,
                onChange: function() {
                    document.getElementById("end_time").click(); // بعد ما يختار البداية يفتح النهاية
                    calcDuration();
                }
            });

            // ⏰ اختيار وقت النهاية
            const endPicker = flatpickr("#end_time", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "h:i K", // ← AM/PM
                time_24hr: false,
                onChange: function() {
                    calcDuration();
                }
            });


            function calcDuration() {
                let day = document.getElementById("day_picker").value; // "2025-09-30"
                let start = document.getElementById("start_time").value; // "11:00 PM"
                let end = document.getElementById("end_time").value; // "10:00 AM"

                if (day && start && end) {
                    // تحويل لنماذج Date محلية
                    let startDate = new Date(day + " " + start);
                    let endDate = new Date(day + " " + end);

                    // لو نهاية اليوم أقل أو تساوي البداية => اعتبرها اليوم التالي
                    if (endDate <= startDate) {
                        endDate.setDate(endDate.getDate() + 1);
                    }

                    // فرق بالدقايق (الآن موجب دائماً)
                    let diff = (endDate - startDate) / (1000 * 60);

                    // اكتب القيمة للـ hidden duration
                    document.getElementById("duration").value = Math.round(diff);

                    // عرض المدة بصورة مقروءة
                    let hours = Math.floor(diff / 60);
                    let minutes = Math.round(diff % 60);
                    let text = (hours > 0 ? hours + " ساعة " : "") + (minutes > 0 ? minutes + " دقيقة" : "");
                    document.getElementById("duration_display").value = text || "0 دقيقة";

                    // ✅ مهم: ارسال التاريخ كامل + الوقت بصيغة Y-m-d H:i:s (بتوقيت المحلّي كما دخل المستخدم)
                    function fmt(d) {
                        return d.getFullYear() + "-" +
                            String(d.getMonth() + 1).padStart(2, '0') + "-" +
                            String(d.getDate()).padStart(2, '0') + " " +
                            String(d.getHours()).padStart(2, '0') + ":" +
                            String(d.getMinutes()).padStart(2, '0') + ":00";
                    }

                    document.getElementById("start_at_full").value = fmt(startDate);
                    document.getElementById("end_at_full").value = fmt(endDate);
                }
            }

            // لو في كود آخر بيستدعي calcDuration بشكل مباشر أو عند submit فمش حاجة إضافية هنا
            // لكن لو عندك listener على الفورم يغيّر start_time/end_time قبل الإرسال — تأكد إنه لا يغيّر hidden fields الخاصة start_at_full/end_at_full

        });
    </script>

    <!-- الكالندر -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const calendarEl = document.getElementById('calendar');
            const titleEl = document.getElementById('calendar-title');
            const prevBtn = document.getElementById('prev-month');
            const nextBtn = document.getElementById('next-month');

            let current = new Date();

            // ======= renderCalendar (مثل كودك الأصلي) =======
            function renderCalendar(year, month) {
    fetch(`{{ route('bookings.calendar') }}?year=${year}&month=${month+1}`)
        .then(res => res.json())
        .then(data => {
            titleEl.textContent = `${year} / ${month+1}`;
            let firstDay = new Date(year, month, 1).getDay();
            let daysInMonth = new Date(year, month + 1, 0).getDate();

            // أسماء الأيام بالعربي (0 = الأحد ... 6 = السبت) متوافقة مع getDay()
            const weekdayNames = ['الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس','الجمعة','السبت'];

            let html = `<table class="table text-center"><tr>`;
            let day = 0;

            for (let i = 0; i < firstDay; i++) {
                html += `<td></td>`;
                day++;
            }

            for (let d = 1; d <= daysInMonth; d++) {
                if (day % 7 == 0) html += `</tr><tr>`;
                let bookings = data[d] || [];
                let dots = '';
                bookings.forEach(b => {
                    let color = hallColor(b.hall_id);
                    dots += `<span class="booking-dot" style="background:${color}"></span>`;
                });

                let todayClass = "";
                let now = new Date();
                if (d === now.getDate() && year === now.getFullYear() && month === now.getMonth()) {
                    todayClass = "today";
                }

                // اسم اليوم للتاريخ ده
                let weekdayIndex = new Date(year, month, d).getDay(); // 0 = Sun ... 6 = Sat
                let weekdayLabel = weekdayNames[weekdayIndex];

                html += `<td class="${todayClass}" data-date="${year}-${String(month+1).padStart(2,'0')}-${String(d).padStart(2,'0')}">
                    <strong>${d}</strong>
                    <div class="weekday-name">${weekdayLabel}</div>
                    <div class="dots-wrap">${dots}</div>
                 </td>`;
                day++;
            }

            html += `</tr></table>`;
            calendarEl.innerHTML = html;

            // اربط الكليك بعد التوليد
            document.querySelectorAll('#calendar td[data-date]').forEach(td => {
                td.addEventListener('click', function() {
                    let date = this.dataset.date;
                    loadDayBookings(date);
                });
            });
        })
        .catch(err => {
            calendarEl.innerHTML = `<div class="text-danger p-2">⚠ خطأ في تحميل الكالندر</div>`;
            console.error(err);
        });
}

            // ======= loadDayBookings (مثل كودك الأصلي) =======
            function loadDayBookings(date) {
                const box = document.getElementById('day-bookings-content');
                box.innerHTML = '<p class="text-info">جارِ التحميل...</p>';

                fetch(`{{ route('bookings.byDate') }}?date=${date}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.length === 0) {
                            box.innerHTML =
                                `<div class="alert alert-warning mb-0">❌ لا يوجد حجوزات في هذا اليوم</div>`;
                        } else if (data.length === 1) {
                            let b = data[0];
                            const start = new Date(b.start_at).toLocaleTimeString([], {
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                            const end = new Date(b.end_at).toLocaleTimeString([], {
                                hour: '2-digit',
                                minute: '2-digit'
                            });

                            box.innerHTML = `
              <div class="card border-0 shadow-sm p-3 mb-0">
                <h6 class="fw-bold mb-2">🏛 ${b.hall.name}</h6>
                <p class="mb-1">📌 ${b.title}</p>
                <p class="mb-1">👤 ${b.client ? b.client.name : '---'}</p>
                <p class="mb-0">⏰ ${start} - ${end}</p>
              </div>
            `;
                        } else {
                            let html = `<table class="table table-sm table-bordered">
                      <thead>
                        <tr>
                          <th>🏛 القاعة</th>
                          <th>📌 الحجز</th>
                          <th>👤 العميل</th>
                          <th>⏰ من</th>
                          <th>إلى</th>
                        </tr>
                      </thead>
                      <tbody>`;
                            data.forEach(b => {
                                const start = new Date(b.start_at).toLocaleTimeString([], {
                                    hour: '2-digit',
                                    minute: '2-digit'
                                });
                                const end = new Date(b.end_at).toLocaleTimeString([], {
                                    hour: '2-digit',
                                    minute: '2-digit'
                                });
                                html += `<tr>
                        <td>${b.hall.name}</td>
                        <td>${b.title}</td>
                        <td>${b.client ? b.client.name : '---'}</td>
                        <td>${start}</td>
                        <td>${end}</td>
                     </tr>`;
                            });
                            html += `</tbody></table>`;
                            box.innerHTML = html;
                        }
                    })
                    .catch(err => {
                        box.innerHTML = `<p class="text-danger">⚠ خطأ أثناء جلب البيانات</p>`;
                        console.error(err);
                    });
            }

            // ======= hallColor (نفس تعريفك مع blade loop) =======
            function hallColor(hallId) {
                let colors = {
                    @foreach ($halls as $i => $hall)
                        {{ $hall->id }}: "{{ ['#FFD700', '#32CD32', '#1E90FF', '#FF69B4'][$i % 4] }}",
                    @endforeach
                };
                return colors[hallId] || '#ccc';
            }

            // ========= helper للتنقل بشهر واحد ==========
            function goMonth(delta) {
                // use setMonth on a copy to avoid weird DST/month glitches
                current = new Date(current.getFullYear(), current.getMonth() + delta, 1);
                renderCalendar(current.getFullYear(), current.getMonth());
            }

            // أحداث الأزرار (الأسهم)
            prevBtn.addEventListener('click', function(e) {
                e.preventDefault();
                goMonth(-1);
            });
            nextBtn.addEventListener('click', function(e) {
                e.preventDefault();
                goMonth(1);
            });

            // ================================
            // Swipe detection (موثوق، لا يكرر الحدث)
            // ================================
            let startX = 0,
                startY = 0,
                startTime = 0;
            let isTouching = false;
            const THRESHOLD = 50; // بكسلات أفقية لازمة لاعتبارها swipe
            const MAX_VERTICAL = 80; // لو كانت الحركة الرأسية أكبر من كده نعتبرها scroll
            const MAX_TIME = 700; // أقصى مدة للـ swipe بالمللي ثانية
            let lastSwipeAt = 0;
            const SWIPE_COOLDOWN = 600; // منع التكرار السريع

            function pointerStart(p) {
                startX = p.clientX;
                startY = p.clientY;
                startTime = Date.now();
                isTouching = true;
            }

            function pointerEnd(p) {
                if (!isTouching) return;
                isTouching = false;
                const dx = p.clientX - startX;
                const dy = p.clientY - startY;
                const dt = Date.now() - startTime;

                if (Date.now() - lastSwipeAt < SWIPE_COOLDOWN) return;
                if (Math.abs(dy) > MAX_VERTICAL) return;
                if (dt > MAX_TIME) return;

                if (Math.abs(dx) > THRESHOLD) {
                    lastSwipeAt = Date.now();
                    if (dx < 0) {
                        // سحب للشمال -> الشهر التالي
                        goMonth(1);
                    } else {
                        // سحب لليمين -> الشهر السابق
                        goMonth(-1);
                    }
                }
            }

            // استخدم Pointer Events إذا متاحة، وإلا استخدم touch كـ fallback
            if (window.PointerEvent) {
                calendarEl.addEventListener('pointerdown', function(e) {
                    if (e.isPrimary === false) return;
                    pointerStart(e);
                }, {
                    passive: true
                });
                calendarEl.addEventListener('pointerup', function(e) {
                    if (e.isPrimary === false) return;
                    pointerEnd(e);
                }, {
                    passive: true
                });
                calendarEl.addEventListener('pointercancel', function() {
                    isTouching = false;
                }, {
                    passive: true
                });
            } else {
                calendarEl.addEventListener('touchstart', function(e) {
                    const p = e.touches[0];
                    pointerStart(p);
                }, {
                    passive: true
                });
                calendarEl.addEventListener('touchend', function(e) {
                    const p = e.changedTouches[0];
                    pointerEnd(p);
                }, {
                    passive: true
                });
            }

            // دعم سحب بالماوس (اختياري)
            let isMouseDown = false;
            calendarEl.addEventListener('mousedown', function(e) {
                isMouseDown = true;
                pointerStart(e);
            });
            window.addEventListener('mouseup', function(e) {
                if (!isMouseDown) return;
                isMouseDown = false;
                pointerEnd(e);
            });

            // تحميل الشهر الحالي أول مرة
            renderCalendar(current.getFullYear(), current.getMonth());
        });
    </script>


    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const hallInput = document.getElementById('hall_id');
            const startInput = document.getElementById('start_at');
            const durationInput = document.getElementById('duration_minutes');
            const sidebar = document.getElementById('bookings-sidebar');

            function fetchFilteredBookings() {
                // لو البيانات ناقصة
                if (!hallInput.value || !startInput.value || !durationInput.value) {
                    sidebar.innerHTML = '<p class="text-muted">لم يتم ادخال بيانات حجز</p>';
                    return;
                }

                let url = "{{ route('bookings.sameDay') }}?";
                let params = [
                    "hall_id=" + hallInput.value,
                    "start_at=" + startInput.value,
                    "duration_minutes=" + durationInput.value
                ];

                url += params.join("&");

                sidebar.innerHTML = '<p class="text-info">جارِ التحميل...</p>';

                fetch(url)
                    .then(res => {
                        if (!res.ok) throw new Error("خطأ في جلب البيانات");
                        return res.json();
                    })
                    .then(data => {
                        if (data.length === 0) {
                            sidebar.innerHTML = '<p class="text-success fw-bold">✅ لا يوجد أي تعارضات</p>';
                            return;
                        }

                        let html = `
                  <table class="table table-sm table-bordered">
                    <thead>
                      <tr>
                        <th>القاعة</th>
                        <th>اسم الحجز</th>
                        <th>من</th>
                        <th>إلى</th>
                      </tr>
                    </thead>
                    <tbody>
                `;

                        data.forEach(b => {
                            const startDate = new Date(b.start_at);
                            const endDate = new Date(b.end_at);

                            html += `
                        <tr>
                          <td>${b.hall_name || (b.hall ? b.hall.name : '---')}</td>
                          <td>${b.title}</td>
                          <td>${b.date} - ${startDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</td>
                          <td>${b.date} - ${endDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</td>
                        </tr>
                    `;
                        });

                        html += `</tbody></table>`;
                        sidebar.innerHTML = html;
                    })
                    .catch(err => {
                        sidebar.innerHTML = '<p class="text-danger">⚠ حدث خطأ أثناء جلب الحجوزات</p>';
                        console.error(err);
                    });
            }

            // في الأول يظهر الرسالة الافتراضية
            sidebar.innerHTML = '<p class="text-muted">لم يتم ادخال بيانات حجز</p>';

            // نربط التغيير بالمدخلات
            hallInput.addEventListener('change', fetchFilteredBookings);
            startInput.addEventListener('change', fetchFilteredBookings);
            durationInput.addEventListener('change', fetchFilteredBookings);
        });
    </script>


    <script>
        $(document).ready(function() {
            console.log("jQuery جاهز والشيفرة تعمل"); // <-- هذا سيتأكد أن DOM جاهز و jQuery شغالة

            $('#client_search').on('keyup', function() {
                let query = $(this).val();
                console.log("تم كتابة شيء في البحث:", query); // <-- تحقق من الحدث

                if (query.length >= 1) {
                    $.ajax({
                        url: "{{ route('clients.search') }}",
                        type: "GET",
                        data: {
                            query: query
                        },
                        success: function(data) {
                            console.log("جاءت البيانات من السيرفر:", data); // <-- تحقق من الرد
                            let html = '';
                            if (data.length > 0) {
                                data.forEach(item => {
                                    html += `
                                <div class="result-item p-2 border-bottom"
                                     data-id="${item.id}"
                                     data-name="${item.name}"
                                     data-phone="${item.phone}">
                                  ${item.name} - ${item.phone}
                                </div>`;
                                });
                            } else {
                                html = '<div class="p-2 text-muted">لا توجد نتائج</div>';
                            }

                            $('#client-results').html(html).show();
                        },
                        error: function(err) {
                            console.error("خطأ في جلب البيانات:",
                                err); // <-- لو فيه مشكلة في AJAX
                        }
                    });
                } else {
                    $('#client-results').hide();
                }
            });
            $(document).on('click', '.result-item', function() {
                $('#client_id').val($(this).data('id'));
                $('#client_name').val($(this).data('name'));
                $('#client_search').val($(this).data('phone'));
                $('#client-results').hide();
            });

            $('#client_search').on('input', function() {
                $('#client_id').val('');
                $('#client_name').val('');
            });


            document.querySelector('form').addEventListener('submit', function(e) {
                let day = document.getElementById('day_picker').value;
                let start = document.getElementById('start_time').value;
                let end = document.getElementById('end_time').value;

                if (day && start && end) {
                    let startFull = day + ' ' + start;
                    let endFull = day + ' ' + end;

                    document.getElementById('start_time').value = startFull;
                    document.getElementById('end_time').value = endFull;
                }
            });

        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // عناصر
            const hallEl = document.getElementById('hall_id');
            const attendeesEl = document.querySelector('input[name="attendees"]');
            const durationHiddenEl = document.getElementById('duration'); // الدقايق
            const dayEl = document.getElementById('day_picker');
            const startEl = document.getElementById('start_time');
            const endEl = document.getElementById('end_time');

            const banner = document.getElementById('estimateBanner');
            const amountEl = document.getElementById('estimateAmount');
            const perHourEl = document.getElementById('estimatePerHour');

            // util
            function debounce(fn, delay = 300) {
                let t;
                return function(...args) {
                    clearTimeout(t);
                    t = setTimeout(() => fn.apply(this, args), delay);
                };
            }

            function showBanner() {
                if (!banner) return;
                banner.style.display = 'block';
                banner.setAttribute('aria-hidden', 'false');
                banner.classList.remove('estimate-hide');
                banner.classList.add('estimate-show');
            }

            function hideBanner() {
                if (!banner) return;
                banner.classList.remove('estimate-show');
                banner.classList.add('estimate-hide');
                setTimeout(() => {
                    banner.style.display = 'none';
                    banner.setAttribute('aria-hidden', 'true');
                }, 300);
            }

            function safeNumber(v) {
                if (v === null || v === undefined || v === '') return NaN;
                // remove non-digits except dot and minus
                const n = Number(String(v).replace(/[^\d\.\-]/g, ''));
                return isNaN(n) ? NaN : n;
            }

            // Debug helper: لو مش ظاهر اعمل console logs
            function debugLog(...args) {
                if (window && window.console) console.log('[estimate]', ...args);
            }

            async function fetchEstimate() {
                const hallId = hallEl?.value || '';
                const attendees = attendeesEl?.value || '';
                const duration = durationHiddenEl?.value || '';

                debugLog('fetchEstimate called', {
                    hallId,
                    attendees,
                    duration
                });

                // شروط العرض: الثلاث حقول موجودة والمدة >= 30
                const durNum = safeNumber(duration);
                const attNum = safeNumber(attendees);

                if (!hallId || !attNum || isNaN(attNum) || isNaN(durNum) || durNum < 30) {
                    debugLog('conditions not met -> hide banner', {
                        hallId,
                        attNum,
                        durNum
                    });
                    hideBanner();
                    return;
                }

                showBanner();
                amountEl.textContent = 'جارِ الحساب...';
                perHourEl.textContent = '';

                // بناء URL
                const params = new URLSearchParams({
                    hall_id: hallId,
                    attendees: attNum,
                    duration_minutes: Math.round(durNum)
                });

                try {
                    const url = "{{ route('bookings.estimate') }}?" + params.toString();
                    debugLog('fetch url', url);
                    const resp = await fetch(url, {
                        method: 'GET',
                        credentials: 'same-origin'
                    });

                    if (!resp.ok) {
                        const text = await resp.text();
                        console.error('[estimate] Resp not ok', resp.status, text);
                        amountEl.textContent = 'خطأ في الحساب';
                        perHourEl.textContent = '';
                        return;
                    }

                    const data = await resp.json();
                    debugLog('estimate response', data);

                    if (data && data.success) {
                        amountEl.textContent = `${data.estimated_formatted} ${data.currency}`;
                        perHourEl.textContent = `سعر الساعة: ${data.per_hour_formatted} ${data.currency}`;
                    } else if (data && data.error) {
                        amountEl.textContent = data.error;
                        perHourEl.textContent = '';
                    } else {
                        amountEl.textContent = 'لا توجد نتيجة';
                        perHourEl.textContent = '';
                    }
                } catch (err) {
                    console.error('[estimate] fetch failed', err);
                    amountEl.textContent = 'خطأ في الاتصال';
                    perHourEl.textContent = '';
                }
            }

            const debouncedFetch = debounce(fetchEstimate, 300);

            // ربط الأحداث: hall, attendees, and duration changes
            hallEl?.addEventListener('change', debouncedFetch);
            attendeesEl?.addEventListener('input', debouncedFetch);
            durationHiddenEl?.addEventListener('change', debouncedFetch);
            durationHiddenEl?.addEventListener('input', debouncedFetch);

            // أيضاً لو المستخدم يغيّر day/start/end — نتأكد نحسب المدة ثم ننادي التقدير
            function safeCalcDurationAndTrigger() {
                try {
                    const day = dayEl?.value || '';
                    const start = startEl?.value || '';
                    const end = endEl?.value || '';

                    debugLog('calcDuration called', {
                        day,
                        start,
                        end
                    });
                    if (!day || !start || !end) {
                        // لا تقدر تحسب
                        // لمسألة الـ time pickers: قد يكون الوقت بصيغة "h:i K" أو "HH:MM" — نحاول تحويله
                        durationHiddenEl.value = '';
                        debouncedFetch();
                        return;
                    }

                    // حاول تحويل "day + ' ' + start" و "day + ' ' + end" إلى Date
                    const s = new Date(day + ' ' + start);
                    const e = new Date(day + ' ' + end);
                    if (isNaN(s) || isNaN(e)) {
                        // جرب تنسيق بديل: إذا start/end بصيغة "HH:MM" أو "HH:MM AM/PM"
                        const s2 = new Date(day + ' ' + start.replace(/(AM|PM)/i, ''));
                        const e2 = new Date(day + ' ' + end.replace(/(AM|PM)/i, ''));
                        if (!isNaN(s2) && !isNaN(e2)) {
                            const diff = (e2 - s2) / (1000 * 60);
                            durationHiddenEl.value = diff < 0 ? diff + (24 * 60) : diff;
                            debouncedFetch();
                            return;
                        }
                        durationHiddenEl.value = '';
                        debouncedFetch();
                        return;
                    }

                    let diff = (e - s) / (1000 * 60);
                    if (diff < 0) diff += 24 * 60;
                    durationHiddenEl.value = Math.round(diff);
                    debugLog('calculated duration (minutes):', durationHiddenEl.value);
                    debouncedFetch();
                } catch (err) {
                    console.error('[estimate] calcDuration error', err);
                    durationHiddenEl.value = '';
                    debouncedFetch();
                }
            }

            // وصل الأحداث اللي بتغيّر الوقت
            dayEl?.addEventListener('change', safeCalcDurationAndTrigger);
            startEl?.addEventListener('change', safeCalcDurationAndTrigger);
            endEl?.addEventListener('change', safeCalcDurationAndTrigger);

            // لو تستخدم flatpickr callbacks موجودة — تأكد إنها تستدعي safeCalcDurationAndTrigger
            // (لو مسبقًا عندك onChange في flatpickr، ممكن تضيف call لـ safeCalcDurationAndTrigger بداخله)

            // نفاذ أولي لو فيه قيم محفوظة
            setTimeout(() => {
                safeCalcDurationAndTrigger();
            }, 300);
        });
    </script>
<!-- jQuery (لو مش محطوط بالفعل) -->
<!-- jQuery (لو مش محطوط بالفعل) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
(function($){
  // === CONFIG: عدل إذا غيرت أسماء الحقول في الـ Blade ===
  const CONFIG = {
    searchInput: '#client_search',         // حقل البحث عن العميل (موجود في Blade)
    resultsContainer: '#client-results',   // الصندوق الي هيظهر فيه النتائج
    nameField: '#client_name',
    idField: '#client_id',
    ajaxUrl: "{{ route('clients.search') }}", // تأكد أن هذا route يعيد JSON array من العملاء
    ajaxMethod: 'GET',
    ajaxDelay: 160,
    ignoreInputsSelector: 'input, textarea, select, [contenteditable="true"]',
    resultsItemClass: 'result-item',
    noResultsHtml: '<div style="padding:8px; color:#999;">لا توجد نتائج</div>',
    // الحقول التي ننتقل بينها عند الضغط Enter (ترتيب) — عدّل إذا لزم
    tabOrder: ['#client_search', '#client_name', 'input[name="attendees"]', '#day_picker', '#start_time', '#end_time']
  };

  // حالة محلية
  let state = {
    currentResults: [],
    highlightedIndex: -1,
    searchDebounceTimer: null,
  };

  // أمان النصوص
  function escapeHtml(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

  // render
  function renderResults(items){
    state.currentResults = items || [];
    const $c = $(CONFIG.resultsContainer);
    if (!state.currentResults.length){
      state.highlightedIndex = -1;
      $c.html(CONFIG.noResultsHtml).show();
      return;
    }
    let html = '';
    state.currentResults.forEach((it,i)=>{
      html += `<div id="client_res_${i}" class="${CONFIG.resultsItemClass}" data-index="${i}" data-id="${escapeHtml(it.id)}" data-name="${escapeHtml(it.name||'')}" data-phone="${escapeHtml(it.phone||'')}">
                <span>${escapeHtml(it.name)}${it.phone ? ' - ' + escapeHtml(it.phone) : ''}</span>
              </div>`;
    });
    $c.html(html).show();
    // لو عندنا highlighted index صالح نطبقه
    if (state.highlightedIndex >= 0 && state.highlightedIndex < state.currentResults.length){
      highlight(state.highlightedIndex, {scrollIntoView:true, keepFocusOnInput:true});
    } else {
      state.highlightedIndex = -1;
      $(`${CONFIG.resultsContainer} .${CONFIG.resultsItemClass}`).removeClass('active').attr('aria-selected','false');
    }
  }

  function clearResults(){
    state.currentResults = [];
    state.highlightedIndex = -1;
    $(CONFIG.resultsContainer).hide().empty();
  }

  function highlight(index, opts = {scrollIntoView:true, keepFocusOnInput:true}){
    const $items = $(`${CONFIG.resultsContainer} .${CONFIG.resultsItemClass}`);
    $items.removeClass('active').attr('aria-selected','false');
    if (index == null || index < 0 || index >= state.currentResults.length){
      state.highlightedIndex = -1;
      return;
    }
    state.highlightedIndex = index;
    const $el = $items.eq(index);
    $el.addClass('active').attr('aria-selected','true');

    if (opts.scrollIntoView){
      const container = $(CONFIG.resultsContainer)[0];
      if (container && $el.length){
        const item = $el[0];
        const cTop = container.scrollTop, cBottom = cTop + container.clientHeight;
        const itTop = item.offsetTop, itBottom = itTop + item.offsetHeight;
        if (itTop < cTop) container.scrollTop = itTop;
        if (itBottom > cBottom) container.scrollTop = itBottom - container.clientHeight;
      }
    }
    if (opts.keepFocusOnInput){
      try{ $(CONFIG.searchInput).focus(); }catch(e){}
    }
  }

  function pickResult(idx){
    const it = state.currentResults[idx];
    if (!it) return false;
    // عبّي الحقول
    $(CONFIG.searchInput).val(it.phone || it.id || '');
    if (CONFIG.nameField) $(CONFIG.nameField).val(it.name || '');
    if (CONFIG.idField) $(CONFIG.idField).val(it.id || '');
    clearResults();
    // نركّز على حقل الاسم ليستكمل المستخدم
    try{ $(CONFIG.nameField).focus(); }catch(e){}
    return true;
  }

  // debounce البحث
  function doSearch(query){
    if (!query || !query.trim()){ clearResults(); return; }
    if (!CONFIG.ajaxUrl) return;
    if (state.searchDebounceTimer) clearTimeout(state.searchDebounceTimer);
    state.searchDebounceTimer = setTimeout(()=>{
      $.ajax({
        url: CONFIG.ajaxUrl,
        type: CONFIG.ajaxMethod,
        data: { query: query },
        success: function(data){
          // نتوقع مصفوفة من العملاء
          renderResults(Array.isArray(data) ? data : []);
        },
        error: function(){
          $(CONFIG.resultsContainer).html('<div style="padding:8px; color:#999;">خطأ في البحث</div>').show();
          state.currentResults = []; state.highlightedIndex = -1;
        }
      });
    }, CONFIG.ajaxDelay);
  }

  // insert typed char into search when global typing
  function injectCharToSearch(ch){
    const $inp = $(CONFIG.searchInput);
    const inputEl = $inp.get(0);
    if (!inputEl) return false;
    try {
      inputEl.focus();
      const start = (typeof inputEl.selectionStart === 'number') ? inputEl.selectionStart : inputEl.value.length;
      const end = (typeof inputEl.selectionEnd === 'number') ? inputEl.selectionEnd : start;
      const val = inputEl.value || '';
      const newVal = val.slice(0,start) + ch + val.slice(end);
      inputEl.value = newVal;
      const caret = start + ch.length;
      inputEl.setSelectionRange(caret, caret);
      $inp.trigger('input');
      return true;
    } catch (e) {
      $inp.val(($inp.val()||'') + ch).trigger('input');
      $inp.focus();
      return true;
    }
  }

  // helper: focus next empty field in tabOrder
  function focusNextEmpty(){
    for (let sel of CONFIG.tabOrder){
      try {
        const el = document.querySelector(sel);
        if (!el) continue;
        const val = (el.value || '').toString().trim();
        if (!val){
          el.focus();
          if (typeof el.select === 'function') try{ el.select(); }catch(e){}
          return true;
        }
      } catch(e){}
    }
    return false;
  }

  // small toast
  function tinyToast(msg, ms=900){
    const ex = document.querySelector('.__tiny_toast'); if (ex) ex.remove();
    const d = document.createElement('div'); d.className='__tiny_toast'; d.textContent=msg;
    d.style.cssText='position:fixed;bottom:18px;right:18px;background:#222;color:#fff;padding:8px 12px;border-radius:6px;z-index:99999;opacity:0;transition:opacity .12s';
    document.body.appendChild(d);
    requestAnimationFrame(()=> d.style.opacity=1);
    setTimeout(()=> { d.style.opacity=0; setTimeout(()=> d.remove(),120); }, ms);
  }

  // DOM ready
  $(function(){
    const $search = $(CONFIG.searchInput);
    const $results = $(CONFIG.resultsContainer);
    const $name = $(CONFIG.nameField);

    $results.hide();

    // position results box under input (simple)
    try {
      const inp = $search.get(0);
      const box = $results.get(0);
      if (inp && box) {
        const rect = inp.getBoundingClientRect();
        box.style.minWidth = Math.max(260, rect.width) + 'px';
      }
    } catch(e){}

    // input event -> بحث
    $(document).on('input', CONFIG.searchInput, function(){
      const q = $(this).val() || '';
      if (CONFIG.nameField) $(CONFIG.nameField).val('');
      if (q.trim().length >= 1) doSearch(q.trim());
      else clearResults();
    });

    // click result -> pick
    $(document).on('click', `${CONFIG.resultsContainer} .${CONFIG.resultsItemClass}`, function(e){
      const idx = parseInt($(this).data('index'));
      if (!isNaN(idx)) pickResult(idx);
    });

    // keyboard when focus in search -> navigate results
    $(document).on('keydown', CONFIG.searchInput, function(e){
      const key = e.key;
      if ((key === 'ArrowDown' || key === 'ArrowUp') && state.currentResults.length > 0){
        e.preventDefault();
        if (key === 'ArrowDown'){
          if (state.highlightedIndex < state.currentResults.length - 1) highlight(state.highlightedIndex + 1);
          else highlight(state.currentResults.length - 1);
        } else {
          if (state.highlightedIndex > 0) highlight(state.highlightedIndex - 1);
          else highlight(0);
        }
        return;
      }

      if (key === 'Enter'){
        if (state.currentResults.length > 0){
          e.preventDefault();
          const pickIdx = state.highlightedIndex >= 0 ? state.highlightedIndex : 0;
          if (pickResult(pickIdx)) return;
        }
        if (!state.currentResults.length){
          // لا توجد نتائج -> انتقل للاسم
          if (CONFIG.nameField){ e.preventDefault(); $(CONFIG.nameField).focus().select(); return; }
        }
      }
    });

    // Enter in name -> انتقل للحقل الفاضي التالي
    $(document).on('keydown', CONFIG.nameField, function(e){
      if (e.key === 'Enter'){
        e.preventDefault();
        const ok = focusNextEmpty();
        if (!ok) tinyToast('لا يوجد حقول فارغة أخرى');
      }
    });

    // global key handling:
    // - printable chars when not focused inside editable -> inject to search
    // - arrows when not focused inside editable -> if results present navigate highlight
    $(document).on('keydown', function(e){
      const target = e.target;
      const isEditable = target && (target.matches && target.matches(CONFIG.ignoreInputsSelector));
      if (isEditable) return;

      // arrows navigate results if present
      if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
        if (state.currentResults.length > 0){
          e.preventDefault();
          if (e.key === 'ArrowDown'){
            if (state.highlightedIndex < state.currentResults.length - 1) highlight(state.highlightedIndex + 1);
            else highlight(state.currentResults.length - 1);
          } else {
            if (state.highlightedIndex > 0) highlight(state.highlightedIndex - 1);
            else highlight(0);
          }
          return;
        }
      }

      // Enter globally: pick highlighted if exists otherwise focus next empty
      if (e.key === 'Enter'){
        if (state.currentResults.length > 0){
          e.preventDefault();
          const pickIdx = state.highlightedIndex >= 0 ? state.highlightedIndex : 0;
          pickResult(pickIdx);
          return;
        } else {
          const ok = focusNextEmpty();
          if (ok){ e.preventDefault(); return; }
        }
      }

      // printable -> inject into search
      const key = e.key || '';
      if (e.ctrlKey || e.metaKey || e.altKey) return;
      if (key.length === 1){
        const code = key.charCodeAt(0);
        if (code >= 32){
          const ok = injectCharToSearch(key);
          if (ok){ e.preventDefault(); }
        }
      }
    });

    // click outside closes results
    $(document).on('click', function(e){
      if (!$(e.target).closest(CONFIG.resultsContainer + ', ' + CONFIG.searchInput + ', ' + CONFIG.nameField).length){
        clearResults();
      }
    });

    // init: if search has value on load, trigger search
    const initVal = $search.val() || '';
    if (initVal.trim().length >= 1) doSearch(initVal.trim());

    // small CSS for result active (in case not defined)
    const cssId = 'client-results-styles';
    if (!document.getElementById(cssId)){
      const style = document.createElement('style');
      style.id = cssId;
      style.innerHTML = `
        ${CONFIG.resultsContainer} { max-height:220px; overflow:auto; }
        ${CONFIG.resultsContainer} .${CONFIG.resultsItemClass} { padding:8px 12px 8px 36px; cursor:pointer; position:relative; }
        ${CONFIG.resultsContainer} .${CONFIG.resultsItemClass}.active { background:#e8f2ff; }
        ${CONFIG.resultsContainer} .${CONFIG.resultsItemClass}.active::before {
          content:""; position:absolute; left:12px; top:50%; transform:translateY(-50%); border-top:6px solid transparent; border-bottom:6px solid transparent; border-left:8px solid #007bff;
        }
      `;
      document.head.appendChild(style);
    }
  }); // ready
})(jQuery);
</script>
  <script>
        document.addEventListener('DOMContentLoaded', function() {
            // عنصر التقويم موجود؟ لو لا: ما يعملش حاجة
            const calendar = document.getElementById('calendar');
            if (!calendar) return;

            // selectors ممكن تعدّلهم لو عندك أسماء مختلفة
            const DAY_PICKER_SELECTOR = '#day_picker';
            const DAY_BOOKINGS_CONTENT_SEL = '#day-bookings-content'; // المكان اللي بتعرض فيه تفاصيل اليوم
            const DAY_BOOKINGS_SECTION_SEL = '#day-bookings-section'; // بديل محتمل لو موجود

            // حاول تشغيل دالة بحساب المدة لو موجودة
            function tryCalcDuration() {
                try {
                    if (typeof safeCalcDurationAndTrigger === 'function') safeCalcDurationAndTrigger();
                } catch (e) {
                    /* ignore */ }
            }

            // دالة تساعد على عمل scroll سلس وانتظار محتوى
            function scrollToBookingsAndHighlight(dateStr) {
                // أولوية: المحتوى داخل DAY_BOOKINGS_CONTENT_SEL ثم البديل
                const container = document.querySelector(DAY_BOOKINGS_CONTENT_SEL) || document.querySelector(
                    DAY_BOOKINGS_SECTION_SEL);
                if (!container) return;

                // scroll سلس بحيث العنوان/حافة فوق الشاشة مع 12px مسافة
                const top = container.getBoundingClientRect().top + window.pageYOffset - 12;
                window.scrollTo({
                    top,
                    behavior: 'smooth'
                });

                // لو المحتوى يتغيّر عبر AJAX، نتابع DOM changes ونضيف تمييز مؤقت للكروت بعد التحديث
                const applyHighlight = () => {
                    // نجرّب تمييز كروت الحجز (class .booking-card) إن وجدت
                    const cards = container.querySelectorAll('.booking-card');
                    if (cards.length > 0) {
                        cards.forEach(c => {
                            c.classList.remove('__scroll-highlight');
                            // force reflow then add to retrigger animation
                            void c.offsetWidth;
                            c.classList.add('__scroll-highlight');
                            // ازالة الكلاس بعد الوقت (سابقا 2 دور للأنيمي) — نحذف بعد 2200ms
                            setTimeout(() => c.classList.remove('__scroll-highlight'), 2200);
                        });
                        return true;
                    }
                    // لو مفيش كروت، نميّز الحاوية نفسها كبديل
                    container.classList.remove('__scroll-highlight-container');
                    void container.offsetWidth;
                    container.classList.add('__scroll-highlight-container');
                    setTimeout(() => container.classList.remove('__scroll-highlight-container'), 2200);
                    return true;
                };

                // لو المحتوى جاهز الآن، طبّق فوراً
                if (applyHighlight()) return;

                // وإلا نراقب التغييرات (مثلاً نتيجة استجابة AJAX)
                const mo = new MutationObserver((mutations, obs) => {
                    if (applyHighlight()) {
                        obs.disconnect();
                    }
                });
                mo.observe(container, {
                    childList: true,
                    subtree: true
                });
                // كسقطة أمان: بعد 2500ms ننفك المراقب لو لم يحدث شيء
                setTimeout(() => mo.disconnect(), 3000);
            }

            // استمع للنقرات على خلايا التقويم (لو renderCalendar بيربط أحداث، فإن هذا التكامل آمن أيضاً)
            calendar.addEventListener('click', function(e) {
                const td = e.target.closest('td[data-date]');
                if (!td) return;

                const date = td.dataset.date; // بصيغة yyyy-mm-dd كما في كودك
                if (!date) return;

                // 1) عبّي حقل اختيار اليوم في الفورم لو موجود
                const dayPicker = document.querySelector(DAY_PICKER_SELECTOR);
                if (dayPicker) {
                    try {
                        dayPicker.value = date;
                        // أطلق حدث تغيير لكي تُفعّل جميع الـ listeners المرتبطة
                        dayPicker.dispatchEvent(new Event('input', {
                            bubbles: true
                        }));
                        dayPicker.dispatchEvent(new Event('change', {
                            bubbles: true
                        }));
                    } catch (err) {}
                }

                // 2) استدعاء الوظيفة الموجودة لعرض تفاصيل اليوم (لو موجودة)
                try {
                    if (typeof loadDayBookings === 'function') {
                        loadDayBookings(date);
                    } else {
                        // لو الدالة مش موجودة، حاول النقر على الخلية الأصلي (في حال renderCalendar ربطها)
                        td.click();
                    }
                } catch (err) {
                    console.error('[calendar-integrate] loadDayBookings error', err);
                }

                // 3) شغّل محاولة حساب المدة (لو عندك)
                tryCalcDuration();

                // 4) اعمل scroll للمكان الخاص بتفاصيل اليوم واضف highlight بعد التحميل
                // ضيفْ تأخير خفيف علشان يعطي فرصة للـ AJAX يبدأ بالرد
                setTimeout(() => scrollToBookingsAndHighlight(date), 120);
            }, {
                passive: true
            });
        });
    </script>
@endsection

@section('style')
    <style>
        body {
            background: #fff;
        }

        .theme-btn {
            background-color: #D9B1AB;
            color: #fff;
            border: none;
            transition: all 0.3s ease;
        }

        .theme-btn:hover {
            background-color: #c0958f;
            transform: scale(1.05);
        }

        .form-control,
        .form-select,
        textarea {
            border-radius: 8px;
            padding: 10px 12px;
        }

        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
        }

        /* أنيميشن */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate__fadeInUp {
            animation: fadeInUp 0.6s ease-in-out;
        }

        .animate__fadeIn {
            animation: fadeInUp 0.8s ease-in-out;
        }

        .animate__fadeInRight {
            animation: fadeInUp 0.9s ease-in-out;
        }

        #prev-month,
        #next-month {
            min-width: 56px;
            min-height: 44px;
            padding: 10px 14px;
            font-size: 20px;
            border-radius: 10px;
        }

        /* مسافة بين الأزرار والعنوان */
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        /* يسمح للكالندر بالتقاط الحركات الأفقية دون منع التمرير العمودي */
        #calendar {
            touch-action: pan-y;
            -webkit-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }


        /* التقويم */
        #calendar table {
            width: 100%;
            border-collapse: collapse;
        }

        #calendar td {
            min-width: 80px;
            height: 90px;
            vertical-align: top;
            border-radius: 10px;
            background: #fafafa;
            padding: 6px;
            position: relative;
            transition: all 0.3s ease;
        }

        #calendar td:hover {
            background: #f1e2df;
            transform: scale(1.03);
            cursor: pointer;
        }

        #calendar td strong {
            display: inline-block;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 4px;
            color: #333;
        }


        /* اليوم الحالي */
        #calendar td.today {
            background: #D9B1AB;
            color: #fff;
            font-weight: bold;
        }

        /* دوائر البوكنج */
        .booking-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin: 0 2px;
            border: 1px solid #fff;
            box-shadow: 0 0 2px rgba(0, 0, 0, 0.2);
        }

        /* صفوف الجدول */
        #calendar tr {
            height: 100px;
        }

        /* تصغير في الموبايل */
        @media (max-width: 768px) {
            #calendar td {
                min-width: 40px;
                height: 60px;
                padding: 4px;
            }

            #calendar td strong {
                font-size: 12px;
            }
        }
  /* شوية ستايل صغير لاسم اليوم تحت التاريخ */
        #calendar .weekday-name {
            font-size: 0.75rem;
            color: #6c757d;
            /* bootstrap text-muted */
            margin-top: 4px;
            display: block;
        }

        /* إبراز اليوم الحالي */
        #calendar td.today {
            background: #fff8dc;
            border-radius: 6px;
        }

        /* نقط الحجز تبقى صف */
        .booking-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin: 0 2px;
        }
        /* Estimate banner styling */
        .estimate-banner {
            position: fixed;
            left: 50%;
            transform: translateX(-50%) translateY(-10px);
            top: 16px;
            z-index: 1200;
            width: min(860px, calc(100% - 32px));
            max-width: 980px;
            box-sizing: border-box;
            transition: transform .28s cubic-bezier(.2, .9, .2, 1), opacity .28s ease;
            opacity: 0;
            pointer-events: none;
        }

        .estimate-inner {
            background: #D9B1AB;
            /* theme */
            color: #fff;
            border-radius: 12px;
            padding: 10px 16px;
            display: flex;
            gap: 12px;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            font-weight: 700;
            font-size: 16px;
        }

        .estimate-left {
            font-size: 14px;
            opacity: 0.95;
        }

        .estimate-amount {
            font-size: 18px;
            font-weight: 900;
        }

        .estimate-small {
            font-size: 13px;
            opacity: 0.9;
        }

        /* show/hide animations */
        .estimate-show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
            pointer-events: auto;
        }

        .estimate-hide {
            opacity: 0;
            transform: translateX(-50%) translateY(-12px);
            pointer-events: none;
        }

        /* responsive tweaks */
        @media (max-width:420px) {
            .estimate-inner {
                padding: 10px;
                font-size: 14px;
                gap: 8px;
            }

            .estimate-amount {
                font-size: 16px;
            }

            .estimate-banner {
                top: 10px;
                width: calc(100% - 20px);
            }
        }
                /* تأثير تمييز مؤقت على كروت الحجز بعد الانتقال */
        .__scroll-highlight {
          animation: __flash 1s ease-in-out 0s 2;
          box-shadow: 0 6px 18px rgba(0,0,0,0.08);
          border-radius: 6px;
          transition: transform .12s;
        }
        @keyframes __flash {
          0% { transform: translateY(0); background-color: transparent; }
          30% { transform: translateY(-4px); background-color: #fff7cc; }
          100% { transform: translateY(0); background-color: transparent; }
        }
        
        /* لو عايز تمييز للصندوق كله بدل الكروت */
        .__scroll-highlight-container {
          animation: __flash 1s ease-in-out 0s 2;
        }
    </style>
@endsection
