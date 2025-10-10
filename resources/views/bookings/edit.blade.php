@extends('layouts.app_page')

@section('title', 'تعديل الحجز')

@section('content')
<div class="subscription-container">
    

    @if (session('success'))
        <script>document.addEventListener("DOMContentLoaded", ()=> showSnackbar("{{ session('success') }}","success"));</script>
    @endif
    @if (session('error'))
        <script>document.addEventListener("DOMContentLoaded", ()=> showSnackbar("{{ session('error') }}","error"));</script>
    @endif

    <div class="card">
        <div class="card-header">
            <h2>✏️ تعديل الحجز</h2>
            <span class="badge">#{{ $booking->id }}</span>
        </div>

        <form id="bookingEditForm" action="{{ route('bookings.update', $booking) }}" method="POST" autocomplete="off">
            @csrf
            @method('PUT')

            <div class="section">
                <h3>👤 بيانات العميل</h3>
                <div class="box" style="position:relative;">
                    <div class="mb-3" style="position:relative;">
                        <label class="form-label">رقم العميل / الاسم</label>
                        <input type="text" id="client_search" name="client_phone"
                            placeholder="🔍 ابحث بالاسم أو الرقم" autocomplete="off" class="form-control"
                            value="{{ old('client_phone', $booking->client->phone ?? '') }}" maxlength="11">
                        <div id="client-results" class="border bg-white shadow-sm rounded mt-1"
                            style="display:none; position:absolute; left:15px; right:15px; z-index:9999; max-height:260px; overflow-y:auto;">
                        </div>
                        @error('client_phone') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3" style="margin-top:12px;">
                        <label class="form-label">اسم العميل</label>
                        <input type="text" id="client_name" name="client_name" class="form-control"
                            value="{{ old('client_name', $booking->client->name ?? '') }}">
                        @error('client_name') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                    </div>

                    <input type="hidden" id="client_id" name="client_id" value="{{ old('client_id', $booking->client_id ?? '') }}">
                    <small class="text-muted">اختَر نتيجة من البحث لربط العميل بالحجز، أو اكتب اسم جديد ليُسجَّل كعميل جديد عند الحفظ.</small>
                </div>
            </div>

            <div class="section">
                <h3>🏛️ القاعة و التفاصيل</h3>
                <div class="box">
                    <label>القاعة</label>
                    <select name="hall_id" id="hall_id" class="form-control" required>
                        @foreach ($halls as $h)
                            <option value="{{ $h->id }}" {{ $booking->hall_id == $h->id ? 'selected' : '' }}>
                                {{ $h->name }} (min: {{ $h->min_capacity }} - max: {{ $h->max_capacity }})
                            </option>
                        @endforeach
                    </select>

                    <label style="margin-top:10px;">العنوان</label>
                    <input type="text" name="title" value="{{ old('title', $booking->title) }}" class="form-control" />

                    <label style="margin-top:10px;">عدد الحضور</label>
                    <input type="number" name="attendees" id="attendees" min="1"
                        value="{{ old('attendees', $booking->attendees) }}" class="form-control" />

                    <div style="display:flex; gap:8px; margin-top:10px; flex-wrap:wrap;">
                        <div style="flex:1;">
                            <label>تاريخ ووقت البداية</label>
                            <input type="datetime-local" name="start_at" id="start_at"
                                value="{{ old('start_at', \Carbon\Carbon::parse($booking->start_at)->format('Y-m-d\TH:i')) }}"
                                class="form-control" required />
                        </div>

                        <div style="width:180px;">
                            <label>المدة (دقائق)</label>
                            <input type="number" name="duration_minutes" id="duration_minutes" min="30" max="720"
                                value="{{ old('duration_minutes', $booking->duration_minutes) }}" class="form-control" required />
                        </div>
                    </div>

                    <label style="margin-top:10px;">الحالة</label>
                    <select name="status" id="status" class="form-control">
                        <option value="scheduled" {{ $booking->status == 'scheduled' ? 'selected' : '' }}>⏳ scheduled</option>
                        <option value="due" {{ $booking->status == 'due' ? 'selected' : '' }}>📌 due</option>
                    </select>

                    <div id="conflictWarning" style="margin-top:10px; color:#a94442; display:none;">
                        ⚠️ تعارض مع حجز آخر في نفس القاعة — راجع التواريخ أو اختر قاعة أخرى.
                    </div>
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn yellow">حفظ التعديلات</button>
                <a href="{{ route('bookings.show', $booking->id) }}" class="btn">إلغاء</a>
            </div>
        </form>
    </div>
</div>

{{-- jQuery (لو بالفعل محمل في layout فلا داعي له، لكن إن لم يكن فالسطر هذا يضمن وجوده) --}}
<script>
if (typeof jQuery === 'undefined') {
  document.write('<script src="https://code.jquery.com/jquery-3.7.1.min.js"><\/script>');
}
</script>

<script>
(function(){
  // debounce
  function debounce(fn, delay){ let t; return function(){ clearTimeout(t); t = setTimeout(()=> fn.apply(this, arguments), delay); }; }

  // client search: uses route('clients.search') GET?query=...
  const clientsSearchUrl = "{{ route('clients.search') }}";
  const checkConflictUrl = "{{ route('bookings.check_conflict') }}";
  const bookingId = "{{ $booking->id }}";
  const csrfToken = "{{ csrf_token() }}";

  $(function(){
    // elements
    const $clientSearch = $('#client_search');
    const $clientResults = $('#client-results');
    const $clientId = $('#client_id');
    const $clientName = $('#client_name');

    // search handler
    const doSearch = debounce(function(){
      const q = $clientSearch.val().trim();
      $clientId.val(''); // typing clears selected id
      if (!q) { $clientResults.hide().empty(); return; }

      $.ajax({
        url: clientsSearchUrl,
        method: 'GET',
        data: { query: q },
        success(data){
          let html = '';
          if (Array.isArray(data) && data.length){
            data.forEach(item => {
              const phone = item.phone ? item.phone : 'بدون هاتف';
              html += `<div class="result-item p-2 border-bottom" style="cursor:pointer;"
                        data-id="${item.id}" data-name="${escapeHtml(item.name)}" data-phone="${escapeHtml(item.phone||'')}">
                        ${escapeHtml(item.name)} — ${escapeHtml(phone)}
                      </div>`;
            });
          } else {
            html = '<div class="p-2 text-muted">لا توجد نتائج</div>';
          }
          $clientResults.html(html).show();
        },
        error(err){
          console.error('clients.search error', err);
          $clientResults.hide();
        }
      });
    }, 220);

    $clientSearch.on('input', doSearch);

    // click result
    $(document).on('click', '.result-item', function(){
      const $el = $(this);
      $clientId.val($el.data('id'));
      $clientName.val($el.data('name'));
      $clientSearch.val($el.data('phone'));
      $clientResults.hide();
    });

    // clear selected id if user types after selecting
    $clientSearch.on('keydown', function(e){
      if (e.key === 'Backspace' || e.key === 'Delete') $clientId.val('');
    });

    // hide results when clicking outside
    $(document).on('click', function(e){
      if (!$(e.target).closest('#client-results, #client_search').length) {
        $clientResults.hide();
      }
    });

    // conflict check (reuse existing logic)
    const $hall = $('#hall_id'), $start = $('#start_at'), $duration = $('#duration_minutes'), $conflictWarning = $('#conflictWarning');

    const checkConflict = debounce(function(){
      const hall_id = $hall.val(), start_at = $start.val(), duration_minutes = $duration.val();
      if (!hall_id || !start_at || !duration_minutes) { $conflictWarning.hide(); return; }

      fetch(checkConflictUrl, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept':'application/json' },
        body: JSON.stringify({ hall_id, start_at, duration_minutes, exclude_booking_id: bookingId })
      })
      .then(r => r.json())
      .then(data => {
        if (data && data.conflict === true) $conflictWarning.show(); else $conflictWarning.hide();
      })
      .catch(()=> $conflictWarning.hide());
    }, 200);

    $hall.on('change', checkConflict);
    $start.on('change', checkConflict);
    $duration.on('input change', checkConflict);

    // prevent submit if conflict visible (client-side only)
    $('#bookingEditForm').on('submit', function(e){
      if ($conflictWarning.is(':visible')) {
        e.preventDefault();
        showSnackbar('يوجد تعارض؛ عدّل التوقيت أو الغِ التغييرات.', 'error');
        return false;
      }
      // else submit normally -- server-side update() will validate/create client as needed
    });

    // escape helper
    function escapeHtml(text) {
      if (!text) return '';
      return String(text)
        .replace(/&/g, "&amp;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");
    }
  });
})();
</script>
@endsection

@section('style')
<style>
/* same styles as before (kept concise) */
body { background: #fafafa; font-family: "Tahoma", sans-serif; }
.subscription-container { max-width: 820px; margin: 40px auto; padding: 20px; position:relative; }
.card { background:#fff; border-radius:20px; padding:30px; box-shadow:0 5px 20px rgba(0,0,0,0.1); animation: fadeInUp .6s ease; }
.card-header { display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #f1f1f1; margin-bottom:20px; padding-bottom:10px; }
.card-header h2 { font-size:26px; margin:0; }
.badge { background:#D9B1AB; color:#fff; padding:6px 15px; border-radius:30px; font-weight:bold; }
.section h3 { color:#a86f68; font-size:20px; margin-bottom:10px; }
.box { background:#fafafa; padding:15px 20px; border-radius:12px; margin-bottom:25px; display:block; position:relative; }
.form-control { width:100%; padding:10px; border-radius:8px; border:1px solid #ddd; box-sizing:border-box; }
.actions { display:flex; gap:10px; flex-wrap:wrap; margin:20px 0; }
.btn { border:none; padding:12px 18px; border-radius:12px; font-weight:bold; cursor:pointer; transition:.3s; font-size:15px; }
.btn.yellow { background:#ffe483; border:1px solid #f2d35e; }
.result-item:hover { background:#f7f7f7; }
@keyframes fadeInUp { from { opacity:0; transform:translateY(20px);} to { opacity:1; transform:translateY(0);} }
</style>
@endsection
