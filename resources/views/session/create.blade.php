@extends('layouts.app_page')

@section('title', 'إنشاء جلسة')

@section('content')
    {{-- ✅ تنبيهات Toast باستخدام SweetAlert --}}
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

  

    <div class="container animate__animated animate__fadeInUp">
      
        <h2 class="page-title">إنشاء جلسة جديدة</h2>

        <form action="{{ route('session.store') }}" method="POST">
            @csrf

            {{-- البحث بالهاتف --}}
            <div class="form-group">
                <label for="phone">رقم الهاتف</label>
                <input type="text" id="phone" name="phone" class="input-box" placeholder="🔍 العميل"
                    value="{{ old('phone') }}" maxlength="11" autocomplete="off" required>
                <div id="phone-results"></div>
                @error('phone')
                    <span class="error-msg">{{ $message }}</span>
                @enderror
            </div>

            {{-- الاسم --}}
            <div class="form-group">
                <label for="name">اسم العميل</label>
                <input type="text" id="name" name="name" class="input-box" placeholder="اسم العميل"
                    value="{{ old('name') }}" required>
                @error('name')
                    <span class="error-msg">{{ $message }}</span>
                @enderror
            </div>

            {{-- عدد الأشخاص (Counter) --}}
            <div class="form-group">
                <label>عدد الأشخاص</label>
                <div class="counter-box">
                    <button type="button" id="decreasePersons">➖</button>
                    <span id="personsCount">1</span>
                    <button type="button" id="increasePersons">➕</button>
                </div>
                <input type="hidden" id="personsInput" name="persons" value="1">
            </div>

            <button type="submit" class="btn-submit">🚀 بدء الجلسة</button>
        </form>
    </div>

    {{-- ✅ JS (AJAX + Counter) --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // 📌 البحث بالهاتف
        $(document).ready(function() {
            $('#phone').on('keyup', function() {
                let query = $(this).val();
                if (query.length >= 1) {
                    $.ajax({
                        url: "{{ route('clients.search') }}",
                        type: "GET",
                        data: {
                            query: query
                        },
                        success: function(data) {
                            let html = '';
                            if (data.length > 0) {
                                data.forEach(item => {
                                    html += `
                                        <div class="result-item"
                                             data-id="${item.id}"
                                             data-name="${item.name}"
                                             data-phone="${item.phone}">
                                            <span>${item.name} - ${item.phone}</span>
                                        </div>`;
                                });
                            } else {
                                html =
                                    '<div style="padding:8px; color:#999;">لا توجد نتائج</div>';
                            }
                            $('#phone-results').html(html).show();
                        }
                    });
                } else {
                    $('#phone-results').hide();
                }
            });

            // عند اختيار نتيجة
            $(document).on('click', '.result-item', function() {
                $('#phone').val($(this).data('phone'));
                $('#name').val($(this).data('name'));
                $('#phone-results').hide();
            });

            // لو كتب رقم جديد → يفضي الاسم
            $('#phone').on('input', function() {
                $('#name').val('');
            });
        });

        // 📌 عداد الأشخاص
        let persons = 1,
            maxPersons = 30;
        $('#increasePersons').on('click', function() {
            if (persons < maxPersons) {
                persons++;
                $('#personsCount').text(persons);
                $('#personsInput').val(persons);
            }
        });
        $('#decreasePersons').on('click', function() {
            if (persons > 1) {
                persons--;
                $('#personsCount').text(persons);
                $('#personsInput').val(persons);
            }
        });
    </script>
    
<!-- CSS: ضع هذا في <head> أو قبل السكربت -->
<style>
  /* container */
  #phone-results { max-height:240px; overflow:auto; padding:6px; border:1px solid #eee; border-radius:8px; background:#fff; display:none; }
  .result-item { padding:8px 12px 8px 36px; cursor:pointer; border-radius:6px; position:relative; transition:background .12s, transform .08s; }
  .result-item:hover { background:#f7fbff; }
  /* active style: ثابت وواضح */
  .result-item.active {
    background:#e8f2ff !important;
    box-shadow: inset 0 0 0 1px rgba(0,123,255,0.06);
    transform:translateX(0);
  }
  /* السهم العصري على اليسار */
  .result-item.active::before{
    content: "";
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    width: 0;
    height: 0;
    border-top: 6px solid transparent;
    border-bottom: 6px solid transparent;
    border-left: 8px solid #007bff;
    opacity: 0.98;
  }
  /* ترك مسافة لليمين لو محتوى طويل */
  .result-item span { display:inline-block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:calc(100% - 12px); }
</style>

<!-- JS: استبدل السكربت القديم بهذا -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
(function($){
  const CONFIG = {
    searchInput: '#phone',
    resultsContainer: '#phone-results',
    nameField: '#name',
    ajaxUrl: "{{ route('clients.search') }}",
    ajaxMethod: 'GET',
    ajaxDelay: 160,
    nextFieldIfNoResults: '#name',
    increaseBtn: '#increasePersons',
    decreaseBtn: '#decreasePersons',
    countDisplay: '#personsCount',
    countInputHidden: '#personsInput',
    minPersons: 1,
    maxPersons: 30,
    ignoreInputsSelector: 'input, textarea, [contenteditable="true"]',
    resultsItemClass: 'result-item',
    noResultsHtml: '<div style="padding:8px; color:#999;">لا توجد نتائج</div>',
    // لو حبيت تتحكم: هل نستخدم aria-activedescendant (تحسين وصول)
    useAriaActiveDescendant: true,
    // ID prefix للنتايج
    resultIdPrefix: 'phone_result_'
  };

  let state = {
    currentResults: [],
    highlightedIndex: -1,
    searchDebounceTimer: null,
    persons: parseInt($(CONFIG.countInputHidden).val() || $(CONFIG.countDisplay).text() || CONFIG.minPersons, 10) || CONFIG.minPersons
  };

  function escapeHtml(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

  function renderResults(items){
    // لا نغيّر highlightedIndex هنا — نعيده إلى -1 فقط لو لا توجد نتائج
    state.currentResults = items || [];
    const $c = $(CONFIG.resultsContainer);
    if (!state.currentResults.length){
      state.highlightedIndex = -1;
      $c.html(CONFIG.noResultsHtml).show();
      return;
    }
    let html = '';
    state.currentResults.forEach((it, i)=>{
      const id = CONFIG.resultIdPrefix + i;
      html += `<div id="${id}" class="${CONFIG.resultsItemClass}" data-index="${i}" data-id="${escapeHtml(it.id)}" data-name="${escapeHtml(it.name||'')}" data-phone="${escapeHtml(it.phone||'')}">
                <span>${escapeHtml(it.name)} ${it.phone ? ' - ' + escapeHtml(it.phone) : ''}</span>
              </div>`;
    });
    $c.html(html).show();
    // لو كان هناك highlightedIndex سابق صالح، نعيد تطبيق الستايل عليه (يبقى ثابت)
    if (state.highlightedIndex >= 0 && state.highlightedIndex < state.currentResults.length) {
      highlight(state.highlightedIndex, {scrollIntoView: true, keepFocusOnInput: true});
    } else {
      // لو ما فيش تمييز سابق، خلي ما فيش highlight حتى يختار المستخدم
      state.highlightedIndex = -1;
      $(`${CONFIG.resultsContainer} .${CONFIG.resultsItemClass}`).removeClass('active').attr('aria-selected', 'false');
      if (CONFIG.useAriaActiveDescendant) $(CONFIG.searchInput).removeAttr('aria-activedescendant');
    }
  }

  function clearResults(){ state.currentResults = []; state.highlightedIndex = -1; $(CONFIG.resultsContainer).hide().empty(); if (CONFIG.useAriaActiveDescendant) $(CONFIG.searchInput).removeAttr('aria-activedescendant'); }

  function highlight(index, opts = {scrollIntoView:true, keepFocusOnInput:true}){
    const $items = $(`${CONFIG.resultsContainer} .${CONFIG.resultsItemClass}`);
    $items.removeClass('active').attr('aria-selected','false');
    if (index == null || index < 0 || index >= state.currentResults.length) { state.highlightedIndex = -1; if (CONFIG.useAriaActiveDescendant) $(CONFIG.searchInput).removeAttr('aria-activedescendant'); return; }
    state.highlightedIndex = index;
    const $el = $items.eq(index);
    $el.addClass('active').attr('aria-selected','true');
    if (CONFIG.useAriaActiveDescendant){
      try { $(CONFIG.searchInput).attr('aria-activedescendant', $el.attr('id')); } catch(e){/* ignore */ }
    }
    // scroll to view but DON'T change focus — هذا يضمن الـ highlight ثابت
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
    // حافظ على فوكس الـ input لو طُلب (هنا نريده ثابت)
    if (opts.keepFocusOnInput){
      try { $(CONFIG.searchInput).focus(); } catch(e){/* ignore */ }
    }
  }

  function blurActiveElementSafely(){
    try {
      setTimeout(()=> {
        if (document.activeElement && typeof document.activeElement.blur === 'function'){
          document.activeElement.blur();
        }
        if (window.getSelection) {
          const sel = window.getSelection();
          if (sel && sel.removeAllRanges) sel.removeAllRanges();
        }
      }, 0);
    } catch (e) {}
  }

  function pickResult(idx){
    const it = state.currentResults[idx];
    if (!it) return false;
    $(CONFIG.searchInput).val(it.phone || it.id || '');
    if (CONFIG.nameField) $(CONFIG.nameField).val(it.name || '');
    // بعد الاختيار ننظف النتائج
    clearResults();
    // عند الاختيار نقدر نعمل blur أو نخلي الفوكس في أي مكان — نعمل blur هنا لأن المستخدم اختار
    blurActiveElementSafely();
    return true;
  }

  function doSearch(query){
    if (!query || !query.trim()){ clearResults(); return; }
    if (!CONFIG.ajaxUrl){ return; }
    if (state.searchDebounceTimer) clearTimeout(state.searchDebounceTimer);
    state.searchDebounceTimer = setTimeout(()=>{
      $.ajax({
        url: CONFIG.ajaxUrl,
        type: CONFIG.ajaxMethod,
        data: { query: query },
        success: function(data){
          renderResults(Array.isArray(data) ? data : []);
        },
        error: function(){
          $(CONFIG.resultsContainer).html('<div style="padding:8px; color:#999;">خطأ في البحث</div>').show();
          state.currentResults = [];
          state.highlightedIndex = -1;
        }
      });
    }, CONFIG.ajaxDelay);
  }

  function updatePersonsDisplay(){
    $(CONFIG.countDisplay).text(state.persons);
    $(CONFIG.countInputHidden).val(state.persons);
  }
  function incPersons(){
    if (state.persons < CONFIG.maxPersons){
      state.persons++;
      updatePersonsDisplay();
    } else {
      const el = $(CONFIG.countDisplay);
      el.addClass('shake');
      setTimeout(()=> el.removeClass('shake'), 250);
    }
  }
  function decPersons(){
    if (state.persons > CONFIG.minPersons){
      state.persons--;
      updatePersonsDisplay();
    } else {
      const el = $(CONFIG.countDisplay);
      el.addClass('min-reached');
      setTimeout(()=> el.removeClass('min-reached'), 250);
    }
  }

  $(function(){
    $(CONFIG.resultsContainer).hide();
    updatePersonsDisplay();

    $(document).on('input', CONFIG.searchInput, function(){
      const q = $(this).val() || '';
      if (CONFIG.nameField) $(CONFIG.nameField).val('');
      if (q.trim().length >= 1) doSearch(q.trim());
      else clearResults();
    });

    // click on result: pick and submit? (keeps previous behavior)
    $(document).on('click', `${CONFIG.resultsContainer} .${CONFIG.resultsItemClass}`, function(e){
      const idx = parseInt($(this).data('index'));
      if (!isNaN(idx)) {
        pickResult(idx);
      }
      $(CONFIG.searchInput).focus();
    });

    // keyboard navigation only affects highlight (لا يغيّر الفوكس)
    $(document).on('keydown', CONFIG.searchInput, function(e){
      const key = e.key;
      const q = $(this).val() || '';
      // Arrow navigation: حافظ على highlight ثابت
      if ((key === 'ArrowDown' || key === 'ArrowUp') && state.currentResults.length > 0){
        e.preventDefault();
        if (key === 'ArrowDown'){
          if (state.highlightedIndex < state.currentResults.length - 1) highlight(state.highlightedIndex + 1);
          else highlight(state.currentResults.length - 1);
        } else {
          if (state.highlightedIndex > 0) highlight(state.highlightedIndex - 1);
          else highlight(0);
        }
        // لا نغير الفوكس — نحتفظ به في الـ input
        return;
      }

      // Enter: نختار العنصر المظلّل (أو أول عنصر إذا لم يكن هناك ظل)
      if (key === 'Enter'){
        if (state.currentResults.length > 0){
          e.preventDefault();
          const pickIdx = state.highlightedIndex >= 0 ? state.highlightedIndex : 0;
          if (pickResult(pickIdx)){
            // بعد الاختيار يمكن ارسال الفورم من مكان آخر إن رغبت
            return;
          }
        }
        if (!state.currentResults.length){
          const next = CONFIG.nextFieldIfNoResults;
          if (next){
            e.preventDefault();
            $(next).focus().select();
            return;
          }
        }
      }

      // left/right for persons
      if ((key === 'ArrowLeft' || key === 'ArrowRight') && state.currentResults.length === 0){
        if ($(CONFIG.countDisplay).length){
          e.preventDefault();
          if (key === 'ArrowLeft') incPersons();
          else decPersons();
          return;
        }
      }
    });

    // global typing shortcuts and other handlers (لا يغيّرون الـ highlight)
    $(document).on('keydown.globalType', function(e){
      const target = e.target;
      if ($(target).is(CONFIG.ignoreInputsSelector)) return;

      if ((e.ctrlKey || e.metaKey) && (e.key && e.key.toLowerCase() === 'k')){
        e.preventDefault();
        $(CONFIG.searchInput).focus().select();
        return;
      }

      if (e.key === 'Escape'){
        clearResults();
        $(CONFIG.searchInput).blur();
        return;
      }

      if (e.key === 'Enter'){
        const qVal = $(CONFIG.searchInput).val() || '';
        if (qVal.trim().length >= 1){
          if (state.currentResults.length > 0){
            e.preventDefault();
            const pickIdx = state.highlightedIndex >= 0 ? state.highlightedIndex : 0;
            if (pickResult(pickIdx)){
              return;
            }
          } else {
            if (CONFIG.nextFieldIfNoResults){
              e.preventDefault();
              $(CONFIG.nextFieldIfNoResults).focus().select();
              return;
            }
          }
        } else {
          // do nothing
        }
      }

      const key = e.key;
      if (key && key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey){
        const code = key.charCodeAt(0);
        if (code >= 32){
          e.preventDefault();
          const $inp = $(CONFIG.searchInput);
          const inputEl = $inp.get(0);
          if (!inputEl) return;
          try {
            const start = (typeof inputEl.selectionStart === 'number') ? inputEl.selectionStart : $inp.val().length;
            const end = (typeof inputEl.selectionEnd === 'number') ? inputEl.selectionEnd : start;
            const val = $inp.val() || '';
            const newVal = val.slice(0,start) + key + val.slice(end);
            $inp.val(newVal).trigger('input');
            const caret = start + 1;
            inputEl.setSelectionRange(caret, caret);
            $inp.focus();
          } catch (err) {
            $inp.val(($inp.val()||'') + key).trigger('input');
            $inp.focus();
          }
          clearResults();
          return;
        }
      }

      if (e.key === 'ArrowDown' || e.key === 'ArrowUp'){
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

      if (e.key === 'ArrowLeft' || e.key === 'ArrowRight'){
        if ($(CONFIG.countDisplay).length){
          e.preventDefault();
          if (e.key === 'ArrowLeft') incPersons();
          else decPersons();
          return;
        }
      }
    });

    // click outside closes results
    $(document).on('click', function(e){
      if (!$(e.target).closest(CONFIG.resultsContainer + ', ' + CONFIG.searchInput).length){
        clearResults();
      }
    });

    // small UX: Enter on highlighted result triggers pick (backup)
    $(document).on('keydown', function(e){
      if (e.key === 'Enter' && state.highlightedIndex >= 0 && $(document.activeElement).is(CONFIG.searchInput)){
        e.preventDefault();
        pickResult(state.highlightedIndex);
      }
    });

  }); // ready
})(jQuery);
</script>

@endsection

@section('style')
    <style>
        body {
            font-family: "Cairo", sans-serif;
            background: #ffffff;
            /* 🔥 أبيض بدل المتدرج */
            margin: 0;
            padding: 40px;
            color: #333;
        }

        .snackbar {
            position: fixed;
            top: 20px;
            right: 20px;
            /* 👈 بدل left خليها right */
            background: #333;
            color: #fff;
            padding: 12px 18px;
            border-radius: 10px;
            font-size: 14px;
            z-index: 9999;
            opacity: 0;
            transform: translateX(120%);
            /* 👈 هتبدأ برة الشاشة */
            transition: opacity 0.4s ease, transform 0.4s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .snackbar.show {
            opacity: 1;
            transform: translateX(0);
            /* 👈 تتحرك للداخل */
        }

        .snackbar.success {
            background: #28a745;
        }

        .snackbar.error {
            background: #dc3545;
        }

        /* أيقونة صغيرة */
        .snackbar i {
            font-size: 16px;
        }

        .page-title {
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
            color: #444;
        }
 #phone-results { max-height:240px; overflow:auto; padding:6px; border:1px solid #eee; border-radius:8px; background:#fff; display:none; }
  .result-item { padding:8px 12px 8px 36px; cursor:pointer; border-radius:6px; position:relative; transition:background .12s, transform .08s; }
  .result-item:hover { background:#f7fbff; }
  /* active style: ثابت وواضح */
  .result-item.active {
    background:#e8f2ff !important;
    box-shadow: inset 0 0 0 1px rgba(0,123,255,0.06);
    transform:translateX(0);
  }
  /* السهم العصري على اليسار */
  .result-item.active::before{
    content: "";
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    width: 0;
    height: 0;
    border-top: 6px solid transparent;
    border-bottom: 6px solid transparent;
    border-left: 8px solid #007bff;
    opacity: 0.98;
  }
  /* ترك مسافة لليمين لو محتوى طويل */
  .result-item span { display:inline-block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:calc(100% - 12px); }

        .container {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 18px;
            max-width: 500px;
            margin: auto;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(6px);
        }

        .form-group {
            margin-bottom: 18px;
            text-align: right;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }

        .input-box {
            width: 100%;
            padding: 12px;
            border-radius: 12px;
            border: 1px solid #ccc;
            background: #fafafa;
            transition: all 0.3s;
        }

        .input-box:focus {
            border-color: #d9b2ad;
            box-shadow: 0 0 8px rgba(217, 178, 173, 0.6);
            outline: none;
        }

        .counter-box {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fafafa;
            border-radius: 12px;
            border: 1px solid #ccc;
            padding: 8px;
            gap: 12px;
        }

        .counter-box button {
            background: #D9B1AB;
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            font-size: 18px;
            color: #fff;
            cursor: pointer;
            transition: all 0.3s;
        }

        .counter-box button:hover {
            background: #c48c85;
            transform: scale(1.1);
        }

        .btn-submit {
            background: linear-gradient(135deg, #D9B1AB, #c48c85);
            border: none;
            padding: 14px;
            border-radius: 12px;
            color: #fff;
            font-weight: bold;
            font-size: 16px;
            width: 100%;
            cursor: pointer;
            transition: transform 0.25s, box-shadow 0.25s;
        }

        .btn-submit:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(217, 177, 171, 0.5);
        }



        

        .error-msg {
            color: red;
            font-size: 13px;
        }

        #phone-results {
            position: absolute;
            width: 90%;
            /* 👈 قللت العرض بدل 100% */
            left: 50%;
            /* 👈 يتحرك للنص */
            transform: translateX(-50%);
            /* 👈 يوسّطه تحت الانبوت */
            max-height: 250px;
            overflow-y: auto;
            border: 1px solid #ccc;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            display: none;
            z-index: 1000;
        }

        #phone-results .result-item {
            padding: 10px;
            cursor: pointer;
            transition: background 0.2s;
        }

        #phone-results .result-item:hover {
            background: #f5f5f5;
        }
    </style>
@endsection
