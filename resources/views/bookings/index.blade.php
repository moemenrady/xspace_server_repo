{{-- @extends('layouts.app')

@section('page_title', 'قائمة الحجوزات')

@section('content')
<style>
/* زر الإضافة (لمعة) */
#addButton {
  width:100px; height:100px; border-radius:50%;
  display:flex; align-items:center; justify-content:center;
  text-decoration:none; background:#ffcb9a; font-size:48px;font-weight:600;
  color:#000; box-shadow:0 6px 18px rgba(0,0,0,.12);
  transition: transform .28s ease, box-shadow .28s ease, background .28s;
  margin:20px auto;
  position: relative; overflow: hidden;
}
#addButton::before {
  content:""; position:absolute; inset:-120% -30%;
  background: linear-gradient(120deg, transparent 35%, rgba(255,255,255,.65) 50%, transparent 65%);
  transform: translateX(-100%); transition: transform .6s ease;
  pointer-events:none;
}
#addButton:hover { transform: translateY(-4px) scale(1.03); background:#ffa94d; box-shadow:0 14px 30px rgba(0,0,0,.18); }
#addButton:hover::before { transform: translateX(100%); }

/* البحث */
.search-box { margin:20px auto; text-align:center; }
.search-box input {
  padding:14px 20px; width:450px; max-width:100%; border-radius:25px;
  border:1px solid #ddd; font-size:15px; outline:none; background:#fff;
  transition: box-shadow .22s ease, border-color .22s;
}
.search-box input:focus {
  border-color:#ffcb9a; box-shadow:0 0 8px rgba(255,170,80,0.28);
}

/* filters */
.filters-box { display:flex; justify-content:flex-end; flex-wrap:wrap; gap:20px; margin-bottom:18px; align-items:center; }

/* checkboxes */
.form-check-lg .form-check-input { width:1.4em; height:1.4em; margin-top:.1em; }
.form-check-lg .form-check-label { font-size:1em; margin-right:.4em; }

/* الجدول (الاجمالي) */
table { width:100%; border-collapse:collapse; border-radius:12px; overflow:hidden; margin-top:12px; background:transparent; }
thead { background: rgba(255,224,178,0.85); }
thead th { padding:14px 12px; text-align:center; color:#444;font-size:15px; }
tbody { background: transparent; } /* tbody شفاف كما طلبت */
tbody tr { border-bottom:1px solid rgba(0,0,0,0.03); text-align:center; transition: background .18s ease, transform .18s ease; background: transparent; }
tbody td { padding:12px 10px; text-align:center; color:#333; }

/* hover effect on desktop rows */
tbody tr:hover { transform: translateY(-3px); background: rgba(255,247,240,0.6); box-shadow: 0 6px 18px rgba(0,0,0,0.05); cursor:pointer; }

/* badge styles */
.badge { padding:6px 10px; border-radius:10px;  display:inline-block; font-size:13px; }
.badge.scheduled { background:#fff4d6; color:#a46e00; }
.badge.due { background:#ffe8e0; color:#9b3b20; }
.badge.in_progress { background:#dbf7e9; color:#11683e; }
.badge.finished { background:#e9eef7; color:#1f3f7a; }
.badge.cancelled { background:#f5e9ee; color:#8a3350; }

/* card style on small screens (when table elements become block) */
@media (max-width:768px) {
  table, thead, tbody, th, td, tr { display:block; width:100%; }
  thead { display:none; }
  tbody tr {
    margin-bottom:15px; border:1px solid #eee; border-radius:10px; padding:12px; background:#fff;
    box-shadow: 0 6px 18px rgba(0,0,0,0.06);
    transform:none; /* no lift on mobile for consistency */
    animation: cardIn .28s ease both;
  }
  tbody td { text-align:right; padding:8px 10px; position:relative; font-size:14px; }
  tbody td::before { content: attr(data-label); position:absolute; left:12px;  color:#666; }
}

/* subtle animations */
@keyframes fadeUp {
  from { opacity:0; transform: translateY(8px); }
  to { opacity:1; transform: translateY(0); }
}
@keyframes cardIn {
  from { opacity:0; transform: translateY(8px); }
  to { opacity:1; transform: translateY(0); }
}
.row-animate { animation: fadeUp .32s ease both; }

/* accessibility - reduced motion */
@media (prefers-reduced-motion: reduce) {
  #addButton, #addButton::before, .row-animate { transition: none; animation: none; transform: none !important; }
}

/* nice spacing for container */
.container h1 { margin-bottom:8px;  }

/* small helper for no-results */
.no-results { text-align:center; color:#999; padding:18px 0; }
</style>

<div class="container">

  <a href="{{ route('bookings.create') }}" id="addButton" title="إضافة حجز">+</a>

  <div class="search-box">
    <input type="text" id="searchBox" placeholder="🔍 بحث (العنوان / اسم العميل / القاعة / التاريخ)">
  </div>

  <div class="filters-box">
    <div>
      <label class="form-label d-block mb-2">⚡ الحالة</label>
      <div class="d-flex gap-3">
        <div class="form-check form-check-lg">
          <input class="form-check-input status-filter" type="checkbox" value="scheduled" id="statusScheduled">
          <label class="form-check-label" for="statusScheduled">Scheduled</label>
        </div>
        <div class="form-check form-check-lg">
          <input class="form-check-input status-filter" type="checkbox" value="due" id="statusDue">
          <label class="form-check-label" for="statusDue">Due</label>
        </div>
        <div class="form-check form-check-lg">
          <input class="form-check-input status-filter" type="checkbox" value="in_progress" id="statusInProgress">
          <label class="form-check-label" for="statusInProgress">In Progress</label>
        </div>
        <div class="form-check form-check-lg">
          <input class="form-check-input status-filter" type="checkbox" value="finished" id="statusFinished">
          <label class="form-check-label" for="statusFinished">Finished</label>
        </div>
        <div class="form-check form-check-lg">
          <input class="form-check-input status-filter" type="checkbox" value="cancelled" id="statusCancelled">
          <label class="form-check-label" for="statusCancelled">Cancelled</label>
        </div>
      </div>
    </div>

    <div>
      <label class="form-label d-block mb-2">🏛️ القاعة</label>
      <select id="hallFilter" class="form-select" style="min-width:160px;">
        <option value="">كل القاعات</option>
        @foreach ($halls as $h)
          <option value="{{ $h->id }}">{{ $h->name }}</option>
        @endforeach
      </select>
    </div>
  </div>

  <table aria-describedby="booking-list">
    <thead>
      <tr>
        <th>العنوان</th>
        <th>القاعة</th>
        <th>العميل</th>
        <th>من</th>
        <th>إلى</th>
        <th>التاريخ</th>
        <th>الحالة</th>
      </tr>
    </thead>
    <tbody id="bookingTable">
      <tr><td colspan="7" class="text-center p-3">⏳ جاري التحميل...</td></tr>
    </tbody>
  </table>
</div>

<script>document.addEventListener('DOMContentLoaded', function() {
            const searchBox = document.getElementById('searchBox');
            const statusCheckboxes = Array.from(document.querySelectorAll('.status-filter'));
            const hallFilter = document.getElementById('hallFilter');
            const tableBody = document.getElementById('bookingTable');
            const showRouteTemplate = @json(route('bookings.show', ['id' => ':id']));

            function escapeHtml(unsafe) {
                if (unsafe == null) return '';
                return String(unsafe)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function badgeClass(status) {
                switch (status) {
                    case 'scheduled':
                        return 'scheduled';
                    case 'due':
                        return 'due';
                    case 'in_progress':
                        return 'in_progress';
                    case 'finished':
                        return 'finished';
                    case 'cancelled':
                        return 'cancelled';
                    default:
                        return '';
                }
            }

            // أسماء الأيام بالعربية، getDay() => 0 = الأحد ... 6 = السبت
            const weekdayNames = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];

            function formatDateWithWeekday(isoDateStr) {
                if (!isoDateStr) return '';
                const d = new Date(isoDateStr);
                if (isNaN(d)) return '';
                const weekday = weekdayNames[d.getDay()] || '';
                // تاريخ بصيغة أرقام لاتينية DD/MM/YYYY
                const dd = String(d.getDate()).padStart(2, '0');
                const mm = String(d.getMonth() + 1).padStart(2, '0');
                const yyyy = d.getFullYear();
                const datePart = `${dd}/${mm}/${yyyy}`;
                return `${weekday} ${datePart}`;
            }

            function renderRows(data) {
                tableBody.innerHTML = '';
                if (!data || data.length === 0) {
                    tableBody.innerHTML = `<tr><td colspan="7" class="no-results">❌ لا توجد نتائج</td></tr>`;
                    return;
                }

                data.forEach((b, i) => {
                    const tr = document.createElement('tr');
                    tr.classList.add('row-animate');

                    // حساب الوقت والتاريخ بصيغة مناسبة
                    const startTime = (b.start_at) ? new Date(b.start_at).toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit'
                    }) : '-';
                    const endTime = (b.end_at) ? new Date(b.end_at).toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit'
                    }) : '-';
                    const dateWithWeekday = formatDateWithWeekday(b
                    .start_at); // مثال: "الثلاثاء ١٤/١٠/٢٠٢٥" أو بصيغة ar-EG

                    tr.innerHTML = `
        <td data-label="العنوان">${escapeHtml(b.title)}</td>
        <td data-label="القاعة">${escapeHtml(b.hall_name)}</td>
        <td data-label="العميل">${escapeHtml(b.client_name)}</td>
        <td data-label="من">${startTime}</td>
        <td data-label="إلى">${endTime}</td>
        <td data-label="التاريخ">${escapeHtml(dateWithWeekday)}</td>
        <td data-label="الحالة"><span class="badge ${badgeClass(b.status)}">${escapeHtml(b.status)}</span></td>
      `;

                    tr.addEventListener('click', () => {
                        const url = showRouteTemplate.replace(':id', encodeURIComponent(b.id));
                        window.location.href = url;
                    });

                    // تأخير خفيف للانتقال لتأثير متدرج على السطر
                    tr.style.animationDelay = `${i * 25}ms`;

                    tableBody.appendChild(tr);
                });
            }

            function fetchBookings() {
                const q = searchBox.value.trim();
                const selectedStatuses = statusCheckboxes.filter(cb => cb.checked).map(cb => cb.value);
                const hall = hallFilter.value;

                const params = new URLSearchParams();
                if (q) params.append('q', q);
                selectedStatuses.forEach(s => params.append('statuses[]', s));
                if (hall) params.append('halls[]', hall);

                tableBody.innerHTML = `<tr><td colspan="7" class="text-center p-3">⏳ جاري التحميل...</td></tr>`;

                fetch("{{ route('bookings.ajaxSearch') }}?" + params.toString())
                    .then(res => {
                        if (!res.ok) throw new Error('Network error');
                        return res.json();
                    })
                    .then(data => renderRows(data))
                    .catch(err => {
                        console.error(err);
                        tableBody.innerHTML =
                            `<tr><td colspan="7" class="text-center p-3">⚠️ حدث خطأ، حاول مرة أخرى</td></tr>`;
                    });
            }

            // debounce
            let timer = null;
            searchBox.addEventListener('keyup', function() {
                clearTimeout(timer);
                timer = setTimeout(fetchBookings, 250);
            });

            statusCheckboxes.forEach(cb => cb.addEventListener('change', fetchBookings));
            hallFilter.addEventListener('change', fetchBookings);

            // initial load
            fetchBookings();
        });
</script>
@endsection --}}
