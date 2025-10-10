@extends('layouts.app_page')

@section('title', 'إضافة مشترك')

@section('content')

    <div class="container animate__animated animate__fadeInUp">
        <h2 class="page-title">إضافة مشترك جديد</h2>

        <form action="{{ route('clients.subscribe') }}" method="POST">
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

            {{-- اسم العميل --}}
            <div class="form-group">
                <label for="name">اسم العميل</label>
                <input type="text" id="name" name="name" class="input-box" placeholder="اسم العميل"
                    value="{{ old('name') }}" required>
                @error('name')
                    <span class="error-msg">{{ $message }}</span>
                @enderror
            </div>

            {{-- اختيار الخطة --}}
            <div class="form-group">
                <label for="plan-select">اختر الخطة</label>
                <select class="dropdown" name="plan_id" id="plan-select" required>
                    <option value="" disabled selected>اختر الخطة</option>
                    @foreach ($plans as $plan)
                        <option value="{{ $plan->id }}" data-price="{{ $plan->price }}">
                            {{ $plan->name }} - {{ $plan->visits_count }} زيارة / {{ $plan->duration_days }} يوم
                        </option>
                    @endforeach
                </select>
                @error('plan_id')
                    <span class="error-msg">{{ $message }}</span>
                @enderror
            </div>

            {{-- السعر --}}
            <div class="form-group">
                <label for="plan-price">سعر الاشتراك</label>
                <input type="text" id="plan-price" placeholder="سعر الاشتراك" class="input-box" readonly>
            </div>

            <button type="submit" class="btn-submit">إتمام الاشتراك</button>
        </form>
    </div>

    {{-- ✅ JS --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // عرض السعر حسب الخطة
        document.getElementById('plan-select').addEventListener('change', function() {
            let price = this.options[this.selectedIndex].dataset.price;
            document.getElementById('plan-price').value = price + " ج.م";
        });

        // Auto Complete للعميل
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
                                    html += `<div class="result-item"
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

            $(document).on('click', '.result-item', function() {
                $('#phone').val($(this).data('phone'));
                $('#name').val($(this).data('name'));
                $('#phone-results').hide();
            });

            $('#phone').on('input', function() {
                $('#name').val('');
            });
        });

        // Snackbar function
        function showSnackbar(message, type = "success") {
            let snackbar = document.createElement("div");
            snackbar.className = `snackbar ${type}`;
            snackbar.innerText = message;
            document.body.appendChild(snackbar);
            setTimeout(() => snackbar.classList.add("show"), 50);
            setTimeout(() => snackbar.classList.remove("show"), 3000);
            setTimeout(() => snackbar.remove(), 3500);
        }
    </script>


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
    planSelect: '#plan-select',
    formSelector: 'form[action="{{ route('clients.subscribe') }}"]',
    ignoreInputsSelector: 'input, textarea, [contenteditable="true"]',
    resultsItemClass: 'result-item',
    noResultsHtml: '<div style="padding:8px; color:#999;">لا توجد نتائج</div>',
    useAriaActiveDescendant: true,
    resultIdPrefix: 'phone_result_'
  };

  let state = {
    currentResults: [],
    highlightedIndex: -1,
    searchDebounceTimer: null
  };

  function escapeHtml(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

  function renderResults(items){
    state.currentResults = items || [];
    const $c = $(CONFIG.resultsContainer);
    if (!state.currentResults.length){
      state.highlightedIndex = -1;
      $c.html(CONFIG.noResultsHtml).hide().show();
      return;
    }
    let html = '';
    state.currentResults.forEach((it,i)=>{
      const id = CONFIG.resultIdPrefix + i;
      html += `<div id="${id}" class="${CONFIG.resultsItemClass}" data-index="${i}" data-id="${escapeHtml(it.id)}" data-name="${escapeHtml(it.name||'')}" data-phone="${escapeHtml(it.phone||'')}"><span>${escapeHtml(it.name)}${it.phone ? ' - ' + escapeHtml(it.phone) : ''}</span></div>`;
    });
    $c.html(html).show();
    if (state.highlightedIndex >= 0 && state.highlightedIndex < state.currentResults.length){
      highlight(state.highlightedIndex, {scrollIntoView:true, keepFocusOnInput:true});
    } else {
      state.highlightedIndex = -1;
      $(`${CONFIG.resultsContainer} .${CONFIG.resultsItemClass}`).removeClass('active').attr('aria-selected','false');
      if (CONFIG.useAriaActiveDescendant) $(CONFIG.searchInput).removeAttr('aria-activedescendant');
    }
  }

  function clearResults(){
    state.currentResults = [];
    state.highlightedIndex = -1;
    $(CONFIG.resultsContainer).hide().empty();
    if (CONFIG.useAriaActiveDescendant) $(CONFIG.searchInput).removeAttr('aria-activedescendant');
  }

  function highlight(index, opts = {scrollIntoView:true, keepFocusOnInput:true}){
    const $items = $(`${CONFIG.resultsContainer} .${CONFIG.resultsItemClass}`);
    $items.removeClass('active').attr('aria-selected','false');
    if (index == null || index < 0 || index >= state.currentResults.length){
      state.highlightedIndex = -1;
      if (CONFIG.useAriaActiveDescendant) $(CONFIG.searchInput).removeAttr('aria-activedescendant');
      return;
    }
    state.highlightedIndex = index;
    const $el = $items.eq(index);
    $el.addClass('active').attr('aria-selected','true');
    if (CONFIG.useAriaActiveDescendant){
      try{ $(CONFIG.searchInput).attr('aria-activedescendant', $el.attr('id')); }catch(e){}
    }
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
    $(CONFIG.searchInput).val(it.phone || it.id || '');
    if (CONFIG.nameField) $(CONFIG.nameField).val(it.name || '');
    clearResults();
    try{ $(CONFIG.nameField).focus(); }catch(e){}
    return true;
  }

  function doSearch(query){
    if (!query || !query.trim()){ clearResults(); return; }
    if (!CONFIG.ajaxUrl) return;
    if (state.searchDebounceTimer) clearTimeout(state.searchDebounceTimer);
    state.searchDebounceTimer = setTimeout(()=>{
      $.ajax({
        url: CONFIG.ajaxUrl,
        type: CONFIG.ajaxMethod,
        data: { query: query },
        success: function(data){ renderResults(Array.isArray(data) ? data : []); },
        error: function(){ $(CONFIG.resultsContainer).html('<div style="padding:8px; color:#999;">خطأ في البحث</div>').show(); state.currentResults=[]; state.highlightedIndex=-1; }
      });
    }, CONFIG.ajaxDelay);
  }

  function changePlanBy(delta){
    const $sel = $(CONFIG.planSelect);
    if (!$sel.length) return false;
    const sel = $sel.get(0);
    const options = sel.options;
    if (!options || options.length === 0) return false;
    let idx = sel.selectedIndex;
    if (idx < 0) idx = 0;
    idx = Math.max(0, Math.min(options.length - 1, idx + delta));
    sel.selectedIndex = idx;
    $sel.trigger('change');
    try{ sel.focus(); }catch(e){}
    return true;
  }

  function trySubmitFormIfValid(){
    const $form = $(CONFIG.formSelector).first();
    if (!$form.length) return false;
    const formEl = $form.get(0);
    try{
      if (formEl.checkValidity && formEl.checkValidity()){
        $form.submit();
        return true;
      }
    }catch(e){}
    return false;
  }

  function injectCharToPhone(ch){
    const $inp = $(CONFIG.searchInput);
    const inputEl = $inp.get(0);
    if (!inputEl) return false;
    try{
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
    }catch(err){
      $inp.val(($inp.val()||'') + ch).trigger('input');
      $inp.focus();
      return true;
    }
  }

  function showToast(msg, ms = 900){
    const ex = document.querySelector('.__shortcuts_toast'); if (ex) ex.remove();
    const d = document.createElement('div'); d.className = '__shortcuts_toast'; d.textContent = msg; document.body.appendChild(d);
    requestAnimationFrame(()=> d.classList.add('show'));
    setTimeout(()=> { d.classList.remove('show'); setTimeout(()=> d.remove(),160); }, ms);
  }

  // utility: get submit button element
  function getSubmitBtn(){
    const $form = $(CONFIG.formSelector).first();
    if (!$form.length) return null;
    const $btn = $form.find('[type="submit"]').first();
    return $btn.length ? $btn : null;
  }

  // ---- ready ----
  $(function(){
    const $phone = $(CONFIG.searchInput);
    const $results = $(CONFIG.resultsContainer);
    const $plan = $(CONFIG.planSelect);
    const $name = $(CONFIG.nameField);
    const $submitBtn = getSubmitBtn();

    $results.hide();

    // input typing/search
    $(document).on('input', CONFIG.searchInput, function(){
      const q = $(this).val() || '';
      if (CONFIG.nameField) $(CONFIG.nameField).val('');
      if (q.trim().length >= 1) doSearch(q.trim());
      else clearResults();
    });

    // click result
    $(document).on('click', `${CONFIG.resultsContainer} .${CONFIG.resultsItemClass}`, function(){
      const idx = parseInt($(this).data('index'));
      if (!isNaN(idx)) pickResult(idx);
    });

    // keyboard when focus in phone -> navigate results
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
        // no results -> go to name
        if (!state.currentResults.length){
          const next = CONFIG.nextFieldIfNoResults;
          if (next){ e.preventDefault(); $(next).focus().select(); return; }
        }
      }
    });

    // when focus on name -> Enter moves to plan select
    $(document).on('keydown', CONFIG.nameField, function(e){
      if (e.key === 'Enter'){
        e.preventDefault();
        try {
          $plan.focus();
          // if you want to open select dropdown in some browsers, dispatch mousedown
          try { $plan.get(0).dispatchEvent(new MouseEvent('mousedown', {bubbles:true})); } catch(e){}
        } catch(e){}
      }
    });

    // when focus on plan-select -> Enter confirms plan and focuses submit button
    $(document).on('keydown', CONFIG.planSelect, function(e){
      // if user types a character while on select -> redirect typing to phone
      const key = e.key || '';
      if (key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey){
        // move typing to phone input
        e.preventDefault();
        injectCharToPhone(key);
        return;
      }

      if (e.key === 'Enter'){
        e.preventDefault();
        // ensure selection remains, then focus submit button
        const $btn = getSubmitBtn();
        if ($btn) {
          try { $btn.focus(); showToast('الخطة تم اختيارها — اضغط Enter لإتمام الاشتراك', 1000); } catch(e){}
        } else {
          showToast('تم اختيار الخطة',700);
        }
      }
    });

    // global keydown: arrows when not focused inside inputs -> change plan-select
    $(document).on('keydown', function(e){
      const target = e.target;
      const isEditable = target && (target.matches && target.matches(CONFIG.ignoreInputsSelector));
      // special: if focus is on the plan-select and user types printable char, handled in planSelect handler above
      if (isEditable){
        // if focus is on select but it's our plan-select, let plan handler handle chars
        return;
      }

      // arrows -> change plan
      if (e.key === 'ArrowDown' || e.key === 'ArrowUp'){
        const delta = e.key === 'ArrowDown' ? 1 : -1;
        const ok = changePlanBy(delta);
        if (ok){ e.preventDefault(); return; }
      }

      // Enter -> if form valid submit
      if (e.key === 'Enter'){
        const submitted = trySubmitFormIfValid();
        if (submitted) e.preventDefault();
        return;
      }

      // printable -> inject to phone (global typing)
      const key = e.key || '';
      if (e.ctrlKey || e.metaKey || e.altKey) return;
      if (key.length === 1){
        const code = key.charCodeAt(0);
        if (code >= 32){
          const ok = injectCharToPhone(key);
          if (ok) { e.preventDefault(); return; }
        }
      }
    });

    // change plan updates price
    $plan.on('change', function(){
      const price = $(this).find(':selected').data('price') || '';
      $('#plan-price').val(price ? (price + " ج.م") : '');
    });

    // click outside closes results
    $(document).on('click', function(e){
      if (!$(e.target).closest(CONFIG.resultsContainer + ', ' + CONFIG.searchInput).length){
        clearResults();
      }
    });

    // backup: Enter on highlighted result triggers pick
    $(document).on('keydown', function(e){
      if (e.key === 'Enter' && state.highlightedIndex >= 0 && $(document.activeElement).is(CONFIG.searchInput)){
        e.preventDefault();
        pickResult(state.highlightedIndex);
      }
    });

    // init plan price if preselected
    const $selInit = $plan;
    if ($selInit.length && $selInit.val()){
      const p = $selInit.find(':selected').data('price') || '';
      if (p) $('#plan-price').val(p + " ج.م");
    }
  });
})(jQuery);
</script>
@endsection
@section('style')
    <style>
        body {
            font-family: "Cairo", sans-serif;
            background: #ffffff;
            margin: 0;
            padding: 40px;
            color: #333;
        }

        .page-title {
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
            color: #444;
        }

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

        /* container */
        #phone-results {
            max-height: 240px;
            overflow: auto;
            padding: 6px;
            border: 1px solid #eee;
            border-radius: 8px;
            background: #fff;
            display: none;
        }

        .result-item {
            padding: 8px 12px 8px 36px;
            cursor: pointer;
            border-radius: 6px;
            position: relative;
            transition: background .12s, transform .08s;
        }

        .result-item:hover {
            background: #f7fbff;
        }

        /* active style: ثابت وواضح */
        .result-item.active {
            background: #e8f2ff !important;
            box-shadow: inset 0 0 0 1px rgba(0, 123, 255, 0.06);
            transform: translateX(0);
        }

        /* السهم العصري على اليسار */
        .result-item.active::before {
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
        .result-item span {
            display: inline-block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: calc(100% - 12px);
        }
    
        .dropdown {
            width: 100%;
            padding: 12px;
            border-radius: 12px;
            border: 1px solid #b98c86;
            background: #ffffff;
            /* الخلفية بيضاء */
            color: #2b2b2b;
            /* النص بالأسود */
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }

        .dropdown:focus {
            outline: none;
            border-color: #d9b2ad;
            box-shadow: 0 0 8px rgba(217, 178, 173, 0.4);
        }

        .dropdown option:hover {
            background: #f0f0f0;
            color: #000;
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
            left: 50%;
            transform: translateX(-50%);
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
