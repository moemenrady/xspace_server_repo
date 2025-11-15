@extends('layouts.app_page')

@section('title', 'إدارة الجلسات')

@section('style')
    <style>
        :root {
            --theme-primary: #d9b2ad;
            --btn-bg: #ffe483;
            --btn-border: #f2d35e;
            --btn-text: #111;
        }

        /* صفحة كاملة */
        body {
            font-family: "Cairo", sans-serif;
            background: #ffffff;
            color: #333;
            margin: 0;
            padding: 18px;
        }

        .page-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 18px;
        }

        .title {
            text-align: center;
            margin: 8px 0 18px;
            color: var(--theme-primary);
            font-size: 22px;
            font-weight: 800;
            padding: 12px 18px;
            border-radius: 12px;
            background: linear-gradient(180deg, rgba(217, 178, 173, 0.06), rgba(217, 178, 173, 0.02));
            border: 1px solid rgba(217, 178, 173, 0.10);
            box-shadow: 0 8px 22px rgba(217, 178, 173, 0.06);
        }

        .counts-container {
            display: flex;
            align-items: center;
            gap: 10px;
            /* مسافة بينهم */
            margin-bottom: 15px;
        }

        .count-box {
            font-size: 15px;
            font-weight: 600;
            padding: 6px 10px;
            border-radius: 8px;
            border: 2px solid #555;
            width: fit-content;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        /* لون جلسات عادية */
        .sessions-box {
            background-color: #D9B1AB;
            color: #2b2b2b;
        }

        /* لون جلسات خاصة */
        .private-box {
            background-color: #7b61ff;
            color: #fff;
        }

        /* الريبّة اليسار/اليمين */
        .split {
            flex-direction: row-reverse;

            display: flex;
            gap: 20px;
            align-items: flex-start;
            animation: fadeUp .38s ease;
        }

        /* اللوحة اليسرى: قائمة الجلسات */
        .left {
            flex: 1 1 0%;
            min-width: 260px;
        }

        .sessions-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
            padding: 12px;
        }

        .session-card {
            width: 100%;
            background: linear-gradient(180deg, #ffffff, #fffafa);
            min-height: 72px;
            border-radius: 12px;
            padding: 12px 14px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
            border-top: 4px solid rgba(217, 178, 173, 0.18);
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            transition: transform .25s ease, box-shadow .25s ease;
        }

        .space {
            height: 50px;
        }

        .session-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 34px rgba(0, 0, 0, 0.10);
        }

        .session-card .info h3 {
            margin: 0;
            font-size: 15px;
            color: #222;
        }

        .session-card .info p {
            margin: 6px 0 0;
            font-size: 13px;
            color: #666;
        }

        .session-card .persons {
            font-weight: 700;
            font-size: 14px;
            color: #333;
            margin-left: 12px;
            white-space: nowrap;
        }

        /* اللوحة اليمنى: بطاقة الإضافة المصغّرة */
        .right {
            width: 360px;
            max-width: 38%;
            min-width: 260px;
            background: rgba(255, 255, 255, 0.98);
            padding: 18px;
            border-radius: 14px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(217, 178, 173, 0.06);
            align-self: flex-start;
            animation: fadeUp .38s ease .06s both;
        }

        @media (max-width:980px) {
            .right {
                max-width: 360px;
                width: 42%;
            }
        }

        @media (max-width:820px) {
            .split {
                flex-direction: column-reverse;
            }

            /* على الموبايل: الإضافة فوق أو تحت حسب رغبتك - هنا تحت */
            .right {
                width: 100%;
                max-width: 100%;
            }

            .left {
                width: 100%;
            }
        }

        /* form inside right */
        .form-group {
            margin-bottom: 14px;
            text-align: right;
            margin-bottom: 22px;

        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .input-box {
            width: 100%;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid #ddd;
            background: #fafafa;
        }

        .input-box:focus {
            outline: none;
            box-shadow: 0 8px 18px rgba(217, 178, 173, 0.08);
            border-color: rgba(217, 178, 173, 0.20);
        }

        .counter-box {
            display: flex;
            justify-content: center;
            gap: 12px;
            align-items: center;
            padding: 8px;
            border-radius: 10px;
            background: #fff;
            border: 1px solid #eee;
        }

        .counter-box button {
            background: var(--theme-primary);
            border: none;
            color: #fff;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            cursor: pointer;
        }

        .counter-box span {
            font-weight: 700;
            min-width: 28px;
            text-align: center;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            background: linear-gradient(135deg, #D9B1AB, #c48c85);
            color: #fff;
            font-weight: 700;
            border: none;
            cursor: pointer;
        }

        .no-results {
            text-align: center;
            color: #999;
            padding: 18px 8px;
        }

        /* phone results dropdown (same as before) */
        #phone-results {
            position: absolute;
            top: calc(100% + 6px);
            /* 6px gap below the input */
            left: 0;
            right: 0;
            width: auto;
            /* controlled by left/right */
            box-sizing: border-box;
            max-width: 100%;
            /* never exceed parent width */
            min-width: 220px;
            /* optional minimal width on wide screens */
            max-height: 320px;
            /* limit height (scroll if overflow) */
            overflow-y: auto;
            padding: 1px;
            border-radius: 10px;
            background: #fff;
            border: 1px solid #eee;
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.08);
            display: none;
            z-index: 9999;
        }


        .result-item {
            padding: 10px 12px;
            cursor: pointer;
            border-radius: 6px;
        }

        .result-item:hover {
            background: #f7fbff;
        }

        .result-item.active {
            background: #e8f2ff;
            box-shadow: inset 0 0 0 1px rgba(0, 123, 255, 0.06);
        }

        .result-item span {
            display: inline-block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            * {
                transition: none !important;
                animation: none !important;
            }
        }

        /* fancy select */
        .fancy-select-wrapper {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #dcdcff;
            background: linear-gradient(180deg, #ffffff, #fbfbff);
        }

        .fancy-select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            width: 100%;
            padding: 12px 40px 12px 14px;
            border: none;
            background: transparent;
            font-size: 15px;
            outline: none;
            cursor: pointer;
        }

        .fancy-select-wrapper::after {
            content: '▾';
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: #5b5b8a;
            font-weight: 700;
        }

        /* modal refinements */
        #estimateBanner {
            transition: all .18s ease;
        }

        .alert-warning {
            background: #fff4e5;
            border-color: #f7d8b5;
            color: #7a4b00;
        }

        @media (max-width:600px) {
            .container {
                padding: 18px;
            }

            #phone-results {
                width: calc(100% - 30px);
                left: 15px;
            }
        }

        #phone-results .new-client {
            padding: 12px;
            border-radius: 8px;
            background: linear-gradient(90deg, #eefaf0, #f4fff6);
            border: 1px solid #dbf5df;
            color: #115e2b;
            font-weight: 600;
            display: flex;
            flex-direction: column;
            gap: 6px;
            align-items: flex-start;
        }

        #phone-results .new-client .badge-new {
            background: #1db954;
            color: #fff;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 13px;
            box-shadow: 0 4px 10px rgba(29, 185, 84, 0.14);
        }

        #phone-results .new-client .new-client-msg {
            font-weight: 500;
            color: #0b3f1b;
        }

        #phone-results .new-client .new-client-id {
            font-family: monospace;
            padding: 2px 6px;
            background: #ffffff;
            border-radius: 6px;
            margin-left: 6px;
            color: #0b3f1b;
        }

        /* result item layout with id badge */
        .result-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 12px;
        }

        .result-item .result-main {
            display: flex;
            gap: 8px;
            align-items: center;
            overflow: hidden;
        }

        .result-item .result-name {
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 220px;
        }

        .result-item .result-phone {
            color: #666;
            font-size: 13px;
        }

        .result-item .result-meta {
            margin-left: 10px;
        }

        .result-item .result-id {
            background: #f2f4fb;
            color: #333;
            padding: 6px 8px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            border: 1px solid #e6e9f8;
        }
    </style>
@endsection

@section('content')
    <div class="page-container">

        <h1 class="title">إدارة الجلسات</h1>
        <div class="counts-container">
            <div class="count-box sessions-box">
                الأفراد في الجلسات: {{ $sessions_count }}
            </div>

            <div class="count-box private-box">
                الجلسات الخاصة: {{ $private_sessions_count }}
            </div>
        </div>
        <div class="split">
            <!-- ====== اليسار: قائمة الجلسات ====== -->
            <div class="left">
                <div class="search-box" style="margin-bottom:12px;">
                    <input type="text" id="searchInput" placeholder="ابحث بالاسم أو رقم الهاتف أو ID"
                        style="width:100%; padding:10px; border-radius:10px; border:1px solid #eee;">
                </div>

                <div class="sessions-list" id="sessionsList">
                    <p class="text-center p-3">⏳ جاري التحميل...</p>
                </div>
            </div>

            <!-- ====== اليمين: بطاقة إنشاء الجلسة مصغّرة ====== -->
            <div class="right">
                <h3 style="margin:0 0 10px; text-align:center; color:#333;">بدء جلسة جديدة</h3>

                <form id="miniSessionForm" action="{{ route('session.store.manager') }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="form-group" style="position:relative; margin-bottom: 18px;">
                        <label for="clientIdInput">معرّف العميل (ID)</label>
                        <input type="text" id="clientIdInput" name="client_id" class="input-box"
                            placeholder="🔎 أدخل المعرّف (4 أرقام)" maxlength="4" inputmode="numeric" pattern="\d*">
                        <div id="id-results"
                            style="display:none; position:absolute; left:0; right:0; z-index:50; background:#fff; border:1px solid #eee; border-radius:8px; max-height:220px; overflow:auto;">
                        </div>
                    </div>
                    {{-- البحث بالهاتف --}}
                    <div class="form-group" style="position:relative; margin-bottom: 22px;">
                        <label for="phone">رقم الهاتف</label>
                        <input type="text" id="phone" name="phone" class="input-box" placeholder="📞 العميل"
                            maxlength="11" required>
                        <div id="phone-results"></div>
                        @error('phone')
                            <div class="error-msg">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="space"></div>
                    {{-- الاسم --}}
                    <div class="form-group">
                        <label for="name">اسم العميل</label>
                        <input type="text" id="name" name="name" class="input-box" placeholder="اسم العميل"
                            required>
                        @error('name')
                            <div class="error-msg">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- عدد الأشخاص --}}
                    <div class="form-group">
                        <label>عدد الأشخاص</label>
                        <div class="counter-box" style="justify-content:center;">
                            <button type="button" id="decreasePersons">➖</button>
                            <span id="personsCount">1</span>
                            <button type="button" id="increasePersons">➕</button>
                        </div>
                        <input type="hidden" id="personsInput" name="persons" value="1">
                    </div>
                    <input type="hidden" name="age" value="">
                    <input type="hidden" name="specialization_id" value="">
                    <input type="hidden" name="education_stage_id" value="">

                    <div class="d-flex gap-2" style="flex-wrap:wrap;">
                        <button type="submit" class="btn-submit" style="flex:1; min-width:140px;">🚀 بدء الجلسة</button>

                        <!-- زر يفتح المودال — لا يرسل الفورم -->
                        <button type="button" id="openPrivateBtn" class="btn-submit"
                            style="background: linear-gradient(135deg,#7b61ff,#5e3bff); flex:1; min-width:140px;"
                            data-bs-toggle="modal" data-bs-target="#startBookingModal">
                            🔒 بدء جلسة خاصة
                        </button>
                    </div>
                </form>

                <!-- إضافة ملاحظة صغيرة أسفل الكارت -->
                <p style="font-size:12px; color:#666; margin-top:10px; text-align:center;">يمكنك البحث عن عميل موجود أو
                    إضافة بيانات جديدة ثم بدء الجلسة.</p>
            </div>
        </div>
    </div>
    @include('session.modal.new_client')

    @include('session.modal.start-booking')
    <!-- ====== السكربت: دمج وظائف البحث والعداد مع جلب الجلسات ====== -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        const ID_CONFIG = {
            input: '#clientIdInput',
            resultsContainer: '#id-results',
            ajaxUrl: "{{ route('clients.search.id') }}", // route: clients.search.id
            ajaxMethod: 'GET',
            ajaxDelay: 180,
            resultsItemClass: 'result-item-id',
            maxLength: 4


        };

        let stateId = {
            currentResults: [],
            highlightedIndex: -1,
            searchDebounceTimer: null,
            latestRequestId: 0
        };

        function _escapeHtml(s) {
            return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        /**
         * renderIdResults: يعرض نتائج البحث بالمعرّف
         * - لا يغيّر stateId.latestRequestId هنا (فقط يعرض)
         * - تتلقى items (Array) و query (string) و requestId (number) للفحص إذا لزم
         */
        function renderIdResults(items, query, requestId) {
            // إذا وصل رد قديم — تجاهل (requestId قد يكون أقل من الأحدث)
            if (typeof requestId === 'number' && requestId !== stateId.latestRequestId) {
                // stale response -> ignore
                return;
            }

            stateId.currentResults = Array.isArray(items) ? items : [];
            const $c = $(ID_CONFIG.resultsContainer);

            if (!$c.length) {
                console.warn('renderIdResults: results container not found:', ID_CONFIG.resultsContainer);
                return;
            }

            if (!stateId.currentResults.length) {
                stateId.highlightedIndex = -1;

                if (query && String(query).length === ID_CONFIG.maxLength) {
                    // لو حقل الـ id كامل لكن ما فيش نتائج -> نعرض "لا توجد نتائج"
                    $c.html(`<div class="no-results" role="status" aria-live="polite" style="padding:10px;color:#555;">
                        <div style="font-weight:700;color:#c94b3c;margin-bottom:6px;">لا توجد نتائج</div>
                        <div style="font-size:13px;">المعرّف: <strong>${_escapeHtml(String(query))}</strong></div>
                      </div>`).show();
                } else if (!query || String(query).trim().length === 0) {
                    $c.html('<div style="padding:8px;color:#999;">اكتب المعرّف (4 أرقام)</div>').show();
                } else {
                    $c.html('<div style="padding:8px;color:#999;">لا توجد نتائج</div>').show();
                }
                return;
            }

            // بناء الـ HTML للنتايج
            let html = '';
            stateId.currentResults.forEach((it, i) => {
                const id = _escapeHtml(it.id);
                const phonePart = it.phone ? ' - ' + _escapeHtml(it.phone) : '';
                html += `<div id="id_res_${i}" class="${ID_CONFIG.resultsItemClass}" data-index="${i}" data-id="${_escapeHtml(it.id)}" data-name="${_escapeHtml(it.name||'')}" data-phone="${_escapeHtml(it.phone||'')}">
            <div style="display:flex;justify-content:space-between;gap:8px;padding:8px;">
              <div><strong>${_escapeHtml(it.name||'-')}</strong> ${phonePart}</div>
              <div style="opacity:0.7">#${id}</div>
            </div>
          </div>`;
            });
            $c.html(html).show();

            // reset highlight index safely
            stateId.highlightedIndex = (stateId.highlightedIndex >= 0 && stateId.highlightedIndex < stateId.currentResults
                .length) ? stateId.highlightedIndex : -1;
            if (stateId.highlightedIndex >= 0) {
                $(`${ID_CONFIG.resultsContainer} .${ID_CONFIG.resultsItemClass}`).removeClass('active').eq(stateId
                    .highlightedIndex).addClass('active');
            }
        }



        // clear id results
        function clearIdResults() {
            stateId.currentResults = [];
            stateId.highlightedIndex = -1;
            $(ID_CONFIG.resultsContainer).hide().empty();
        }

        // pick نتيجة من id results واملأ الحقول (phone + name + id)
        function pickIdResult(idx) {
            const it = stateId.currentResults[idx];
            if (!it) return false;
            $('#phone').val(it.phone || '');
            $('#name').val(it.name || '');
            $('#clientIdInput').val(it.id || '');
            clearIdResults();
            // لو عايز تغلق الفوكس:
            try {
                document.getElementById('clientIdInput').blur();
            } catch (e) {
                /*ignore*/
            }
            return true;
        }

        // doSearchId: يستدعي الراوت clients.search.id
        function doSearchId(query) {
            if (!query || !query.trim()) {
                clearIdResults();
                return;
            }
            // نمنع non-digits
            if (!/^\d*$/.test(query)) {
                // منع الأحرف الخاطئة
                return;
            }
            if (stateId.searchDebounceTimer) clearTimeout(stateId.searchDebounceTimer);
            stateId.searchDebounceTimer = setTimeout(() => {
                $.ajax({
                    url: ID_CONFIG.ajaxUrl,
                    type: ID_CONFIG.ajaxMethod,
                    data: {
                        query: query
                    },
                    success: function(data) {
                        renderIdResults(Array.isArray(data) ? data : [], query);
                    },
                    error: function() {
                        $(ID_CONFIG.resultsContainer).html(
                            '<div style="padding:8px; color:#999;">خطأ في البحث</div>').show();
                        stateId.currentResults = [];
                        stateId.highlightedIndex = -1;
                    }
                });
            }, ID_CONFIG.ajaxDelay);
        }

        // bind events for the id input
        $(document).on('input', ID_CONFIG.input, function(e) {
            const q = $(this).val() || '';
            // allow only digits and max length
            const digits = q.replace(/\D/g, '').slice(0, ID_CONFIG.maxLength);
            if (digits !== q) $(this).val(digits);
            if (digits.length >= 1) doSearchId(digits);
            else clearIdResults();
        });

        // click on id-result item
        $(document).on('click', `${ID_CONFIG.resultsContainer} .${ID_CONFIG.resultsItemClass}`, function(e) {
            const idx = parseInt($(this).data('index'));
            if (!isNaN(idx)) {
                pickIdResult(idx);
            }
            $(ID_CONFIG.input).focus();
        });

        // keyboard navigation for id input (arrow / enter)
        $(document).on('keydown', ID_CONFIG.input, function(e) {
            const key = e.key;
            const items = $(`${ID_CONFIG.resultsContainer} .${ID_CONFIG.resultsItemClass}`);
            if ((key === 'ArrowDown' || key === 'ArrowUp') && items.length) {
                e.preventDefault();
                if (key === 'ArrowDown') stateId.highlightedIndex = Math.min(stateId.highlightedIndex + 1, items
                    .length - 1);
                else stateId.highlightedIndex = Math.max(stateId.highlightedIndex - 1, 0);
                items.removeClass('active').eq(stateId.highlightedIndex).addClass('active');
                return;
            }
            if (key === 'Enter') {
                if (stateId.currentResults.length > 0) {
                    e.preventDefault();
                    const pickIdx = stateId.highlightedIndex >= 0 ? stateId.highlightedIndex : 0;
                    if (pickIdResult(pickIdx)) return;
                } else {
                    // اذا مفيش نتايج وادخل id كامل -> نركز على اسم العميل لتعبئته
                    const val = $(this).val() || '';
                    if (val.length === ID_CONFIG.maxLength) {
                        e.preventDefault();
                        $('#name').focus().select();
                        return;
                    }
                }
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- عناصر الواجهة ---
            const searchInput = document.getElementById('searchInput');
            const sessionsList = document.getElementById('sessionsList');

            // روابط الراوتس
            const showRoute = @json(route('session.show', ':id'));
            const searchRoute = @json(route('sessions.search'));
            const storeRoute = @json(route('session.store.manager'));

            // ---- وظائف جلب وعرض الجلسات (يسار) ----
            function safeText(s) {
                return String(s ?? '').replace(/[&<>"]/g, c => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;'
                } [c]));
            }

            function renderSessionCard(session) {
                const clientName = session.client ? safeText(session.client.name) : 'عميل غير معروف';
                const clientPhone = session.client ? safeText(session.client.phone) : '-';
                const persons = session.persons ?? 0;
                return `
            <div class="session-card" role="button" data-id="${session.id}">
                <div class="info" style="text-align:right;">
                    <h3>${clientName}</h3>
                    <p>📞 ${clientPhone}</p>
                </div>
                <div class="persons">الأشخاص: ${persons}</div>
            </div>
        `;
            }

            function showLoading() {
                sessionsList.innerHTML = `<p class="text-center p-3">⏳ جاري التحميل...</p>`;
            }

            function showNoResults() {
                sessionsList.innerHTML = `<p class="no-results">❌ لا توجد جلسات</p>`;
            }

            async function fetchSessions(q = '') {
                showLoading();
                try {
                    const url = new URL(searchRoute, location.origin);
                    if (q) url.searchParams.append('query', q);
                    const res = await fetch(url.toString(), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (!res.ok) throw new Error('Network response was not ok');
                    const data = await res.json();
                    const items = Array.isArray(data) ? data : (data.data ?? []);

                    if (!items || items.length === 0) {
                        showNoResults();
                        return;
                    }
                    sessionsList.innerHTML = '';
                    items.forEach(s => {
                        sessionsList.insertAdjacentHTML('beforeend', renderSessionCard(s));
                    });

                    // ربط النقر على البطاقة للانتقال للصفحة التفصيلية
                    sessionsList.querySelectorAll('.session-card').forEach(card => {
                        card.addEventListener('click', () => {
                            const id = card.dataset.id;
                            if (!id) return;
                            window.location.href = showRoute.replace(':id', id);
                        });
                    });
                } catch (err) {
                    console.error(err);
                    sessionsList.innerHTML = `<p class="no-results">حدث خطأ أثناء جلب الجلسات</p>`;
                }
            }

            // debounce
            function debounce(fn, delay = 300) {
                let t;
                return function(...a) {
                    clearTimeout(t);
                    t = setTimeout(() => fn.apply(this, a), delay);
                };
            }
            const debouncedFetch = debounce((e) => fetchSessions(e ? e.target.value : ''), 250);
            searchInput.addEventListener('keyup', debouncedFetch);

            // initial load
            fetchSessions();

            // ---- وظائف بطاقة الإضافة (يمين) ----
            const CONFIG = {
                searchInput: '#phone',
                resultsContainer: '#phone-results',
                nameField: '#name',
                ajaxUrl: @json(route('clients.search')),
                ajaxMethod: 'GET',
                ajaxDelay: 160,
                resultsItemClass: 'result-item'
            };

            let state = {
                currentResults: [],
                highlightedIndex: -1,
                searchDebounceTimer: null,
                persons: 1
            };

            function escapeHtml(s) {
                return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            }

            function renderResults(items) {
                state.currentResults = items || [];
                const $c = $(CONFIG.resultsContainer);
                if (!state.currentResults.length) {
                    state.highlightedIndex = -1;
                    $c.html('<div style="padding:8px;color:#999;">عميل جديد id : </div>').show();
                    return;
                }
                let html = '';
                state.currentResults.forEach((it, i) => {
                    html +=
                        `<div id="phone_res_${i}" class="${CONFIG.resultsItemClass}" data-index="${i}" data-id="${escapeHtml(it.id)}" data-name="${escapeHtml(it.name||'')}" data-phone="${escapeHtml(it.phone||'')}"><span>${escapeHtml(it.name)} ${it.phone ? ' - ' + escapeHtml(it.phone) : ''}</span></div>`;
                });
                $c.html(html).show();
            }

            function clearResults() {
                state.currentResults = [];
                state.highlightedIndex = -1;
                $(CONFIG.resultsContainer).hide().empty();
            }

            function pickResult(idx) {
                const it = state.currentResults[idx];
                if (!it) return false;
                $(CONFIG.searchInput).val(it.phone || it.id || '');
                if (CONFIG.nameField) $(CONFIG.nameField).val(it.name || '');
                clearResults();
                return true;
            }

            function doSearch(query) {
                if (!query || !query.trim()) {
                    clearResults();
                    return;
                }
                if (state.searchDebounceTimer) clearTimeout(state.searchDebounceTimer);
                state.searchDebounceTimer = setTimeout(() => {
                    $.ajax({
                        url: CONFIG.ajaxUrl,
                        type: CONFIG.ajaxMethod,
                        data: {
                            query: query
                        },
                        success: function(data) {
                            renderResults(Array.isArray(data) ? data : []);
                        },
                        error: function() {
                            $(CONFIG.resultsContainer).html(
                                    '<div style="padding:8px;color:#999;">خطأ في البحث</div>')
                                .show();
                            state.currentResults = [];
                        }
                    });
                }, CONFIG.ajaxDelay);
            }

            // bind search input (phone)
            $(document).on('input', CONFIG.searchInput, function() {
                const q = $(this).val() || '';
                if (CONFIG.nameField) $(CONFIG.nameField).val('');
                if (q.trim().length >= 1) doSearch(q.trim());
                else clearResults();
            });

            $(document).on('click', `${CONFIG.resultsContainer} .${CONFIG.resultsItemClass}`, function() {
                const idx = parseInt($(this).data('index'));
                if (!isNaN(idx)) pickResult(idx);
                $(CONFIG.searchInput).focus();
            });

            // keyboard + global handlers simplified (enter picks, arrows navigate)
            $(document).on('keydown', CONFIG.searchInput, function(e) {
                const key = e.key;
                const items = $(CONFIG.resultsContainer + ' .' + CONFIG.resultsItemClass);
                if ((key === 'ArrowDown' || key === 'ArrowUp') && items.length) {
                    e.preventDefault();
                    if (key === 'ArrowDown') state.highlightedIndex = Math.min(state.highlightedIndex + 1,
                        items.length - 1);
                    else state.highlightedIndex = Math.max(state.highlightedIndex - 1, 0);
                    items.removeClass('active').eq(state.highlightedIndex).addClass('active');
                    return;
                }
                if (key === 'Enter') {
                    if (state.currentResults.length > 0) {
                        e.preventDefault();
                        const pickIdx = state.highlightedIndex >= 0 ? state.highlightedIndex : 0;
                        if (pickResult(pickIdx)) return;
                    }
                }
            });

            // clear on click outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest(CONFIG.resultsContainer + ', ' + CONFIG.searchInput + ', ' + CONFIG
                        .nameField).length) {
                    clearResults();
                }
            });

            // counter persons
            const increaseBtn = document.getElementById('increasePersons');
            const decreaseBtn = document.getElementById('decreasePersons');
            const personsCount = document.getElementById('personsCount');
            const personsInput = document.getElementById('personsInput');
            let persons = 1,
                maxPersons = 30,
                minPersons = 1;

            function updatePersons() {
                personsCount.textContent = persons;
                personsInput.value = persons;
            }
            increaseBtn.addEventListener('click', () => {
                if (persons < maxPersons) {
                    persons++;
                    updatePersons();
                }
            });
            decreaseBtn.addEventListener('click', () => {
                if (persons > minPersons) {
                    persons--;
                    updatePersons();
                }
            });

            miniForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const formData = new FormData(miniForm);
                const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                const headers = tokenMeta ? {
                    'X-CSRF-TOKEN': tokenMeta.content,
                    'X-Requested-With': 'XMLHttpRequest'
                } : {
                    'X-Requested-With': 'XMLHttpRequest'
                };

                // optional: loading snackbar
                if (typeof showSnackbar === 'function') showSnackbar('جارٍ معالجة الطلب...', 'info');

                try {
                    const res = await fetch(storeRoute, {
                        method: 'POST',
                        headers,
                        body: formData,
                        credentials: 'same-origin'
                    });

                    // حاول parse JSON لو موجود
                    let data = null;
                    const contentType = res.headers.get('content-type') || '';
                    if (contentType.includes('application/json')) {
                        data = await res.json();
                    } else {
                        // لو رجع HTML أو نص، احتفظ بالنص للـ fallback
                        const txt = await res.text();
                        data = {
                            _rawText: txt,
                            message: txt
                        };
                    }

                    // ===== حالة Validation (Laravel 422) =====
                    if (res.status === 422) {
                        const errors = data && data.errors ? data.errors : null;
                        const first = errors ? Object.values(errors).flat()[0] :
                            'خطأ في التحقق من البيانات';
                        if (typeof showSnackbar === 'function') showSnackbar(first, 'error');
                        else alert(first);
                        return;
                    }

                    // ===== حالة Conflict: العميل لديه جلسة (409) =====
                    if (res.status === 409) {
                        const msg = (data && (data.error || data.message)) ? (data.error || data
                            .message) : 'هذا العميل لديه جلسة حالية';
                        if (typeof showSnackbar === 'function') showSnackbar(msg, 'error');
                        else alert(msg);
                        return;
                    }

                    // ===== حالة نجاح 201 أو 200 =====
                    if (res.ok) {
                        const successMessage = (data && (data.message || (data.success && typeof data
                            .success === 'string' ? data.success : null))) || 'تم بدء الجلسة';
                        // تفريغ الفورم وتحديث الواجهة
                        miniForm.reset();
                        persons = 1;
                        updatePersons();
                        clearResults();
                        if (typeof fetchSessions === 'function') fetchSessions();

                        if (typeof showSnackbar === 'function') showSnackbar(successMessage, 'success');
                        else alert(successMessage);

                        // لو السيرفر أعاد object session ونريد التحويل لصفحة show:
                        // if (data && data.session && data.session.id) window.location.href = showRoute.replace(':id', data.session.id);
                        return;
                    }

                    // ===== أخطاء غير متوقعة (مثل 500) =====
                    const fallback = (data && (data.error || data.message)) ? (data.error || data
                        .message) : 'حدث خطأ، حاول مرة أخرى';
                    if (typeof showSnackbar === 'function') showSnackbar(fallback, 'error');
                    else alert(fallback);
                } catch (err) {
                    console.error('Submit error:', err);
                    if (typeof showSnackbar === 'function') showSnackbar(
                        'خطأ في الاتصال، تحقق من الاتصال أو حاول لاحقًا', 'error');
                    else alert('خطأ في الاتصال، تحقق من الاتصال أو حاول لاحقًا');
                }
            });
        }); // DOMContentLoaded
    </script>

    <!-- JS: استبدل السكربت القديم بهذا -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        (function($) {
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
                noResultsHtml: '<div style="padding:8px; color:#999;">عميل جديد id : </div>',
                // لو حبيت تتحكم: هل نستخدم aria-activedescendant (تحسين وصول)
                useAriaActiveDescendant: true,
                // ID prefix للنتايج
                resultIdPrefix: 'phone_result_'
            };

            let state = {
                currentResults: [],
                highlightedIndex: -1,
                searchDebounceTimer: null,
                persons: parseInt($(CONFIG.countInputHidden).val() || $(CONFIG.countDisplay).text() || CONFIG
                    .minPersons, 10) || CONFIG.minPersons
            };

            function escapeHtml(s) {
                return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            }

            // وضع في نفس السكربت — state موجود عندك، نضيف حقل لإدارة الطلبات المتسلسلة
            state.latestRequestId = 0;


            async function fetchNextClientId(requestId) {
                try {
                    const url = "{{ route('clients.next_id') }}";
                    const resp = await fetch(url, {
                        credentials: 'same-origin'
                    });

                    // لو الرد مش للنسخة الحالية من الطلب → نتجاهل
                    if (requestId !== state.latestRequestId) return {
                        ok: false,
                        stale: true
                    };

                    if (!resp.ok) return {
                        ok: false
                    };
                    const data = await resp.json();
                    if (data && data.success && typeof data.last_id !== 'undefined') {
                        return {
                            ok: true,
                            nextId: Number(data.last_id) + 1
                        };
                    }
                    return {
                        ok: false
                    };
                } catch (err) {
                    console.error('[fetchNextClientId] ', err);
                    return {
                        ok: false
                    };
                }
            }

            /**
             * renderResults: يعرض النتائج أو رسالة "عميل جديد" مع الحذر من استجابة طلبات قديمة
             */
            function renderResults(items) {
                state.currentResults = items || [];
                const $c = $(CONFIG.resultsContainer);

                const myRequestId = ++state.latestRequestId;

                if (!state.currentResults.length) {
                    state.highlightedIndex = -1;

                    // عرض فوري للمعلومة الخضراء المؤقتة
                    $c.html(`
            <div class="no-results new-client" role="status" aria-live="polite">
                <div class="badge-new">عميل جديد</div>
                <div class="new-client-msg">المعرف الجديد : <span class="new-client-id loading">جاري الحساب...</span></div>
            </div>
        `).show();

                    fetchNextClientId(myRequestId).then(res => {
                        if (res && res.stale) return;

                        if (res && res.ok) {
                            const id = res.nextId;
                            if (myRequestId !== state.latestRequestId) return;

                            $c.html(`
                    <div class="no-results new-client" role="status" aria-live="polite">
                        <div class="badge-new">عميل جديد</div>
                        <div class="new-client-msg">المعرف الجديد : <span class="new-client-id">${escapeHtml(String(id))}</span></div>
                    </div>
                `).show();

                            // هنا نضيف Snackbar مع زر لإكمال البيانات
                            showSnackbarForNewClient(id);

                        } else {
                            if (myRequestId !== state.latestRequestId) return;
                            $c.html(
                                    `<div class="no-results" style="padding:8px; color:#999;">لا توجد نتائج</div>`
                                )
                                .show();
                        }
                    }).catch(() => {
                        if (myRequestId !== state.latestRequestId) return;
                        $c.html(`<div class="no-results" style="padding:8px; color:#999;">لا توجد نتائج</div>`)
                            .show();
                    });

                    return;
                }

                // لو في نتائج — نعرضها كالمعتاد
                let html = '';
                state.currentResults.forEach((it, i) => {
                    const id = CONFIG.resultIdPrefix + i;
                    const clientId = escapeHtml(it.id);
                    const phonePart = it.phone ? ' - ' + escapeHtml(it.phone) : '';
                    html += `<div id="${id}" class="${CONFIG.resultsItemClass}" data-index="${i}" data-id="${escapeHtml(it.id)}" data-name="${escapeHtml(it.name||'')}" data-phone="${escapeHtml(it.phone||'')}">
            <div class="result-main">
                <span class="result-name">${escapeHtml(it.name)}</span>
                <span class="result-phone">${phonePart}</span>
            </div>
            <div class="result-meta">
                <span class="result-id">#${clientId}</span>
            </div>
        </div>`;
                });

                $c.html(html).show();

                if (state.highlightedIndex >= 0 && state.highlightedIndex < state.currentResults.length) {
                    highlight(state.highlightedIndex, {
                        scrollIntoView: true,
                        keepFocusOnInput: true
                    });
                } else {
                    state.highlightedIndex = -1;
                    $(`${CONFIG.resultsContainer} .${CONFIG.resultsItemClass}`).removeClass('active').attr(
                        'aria-selected', 'false');
                    if (CONFIG.useAriaActiveDescendant) $(CONFIG.searchInput).removeAttr('aria-activedescendant');
                }
            }

            function openClientModalForExtraData(clientData = {}) {
                const modal = document.getElementById('clientDataModal');
                modal.style.display = 'flex';

                const form = modal.querySelector('#clientDataForm');
                form.age.value = clientData.age || '';
                form.specialization_id.value = clientData.specialization_id || '';
                form.education_stage_id.value = clientData.education_stage_id || '';

                modal.querySelector('#closeClientModal').onclick = () => {
                    modal.style.display = 'none';
                }

                form.onsubmit = function(e) {
                    e.preventDefault();

                    // نسخ البيانات للفورم الرئيسي
                    const mainForm = document.getElementById('miniSessionForm');
                    mainForm.querySelector('input[name="age"]').value = form.age.value;
                    mainForm.querySelector('input[name="specialization_id"]').value = form.specialization_id.value;
                    mainForm.querySelector('input[name="education_stage_id"]').value = form.education_stage_id
                    .value;

                    // اغلاق المودال
                    modal.style.display = 'none';

                    // إظهار Snackbar للمستخدم
                    showCompletionSnackbar();
                }
            }

            // دالة Snackbar جديدة لرسالة "تم إكمال البيانات"
            function showCompletionSnackbar() {
                $('#customSnackbarCompletion').remove();

                const $snackbar = $(`
        <div id="customSnackbarCompletion" style="
            position:fixed; bottom:20px; right:20px; background:#4caf50; color:#fff;
            border-radius:12px; padding:12px 18px; box-shadow:0 4px 12px rgba(0,0,0,0.2);
            min-width:280px; z-index:99999; display:flex; justify-content:center; align-items:center; gap:12px;
        ">
            تم إكمال البيانات، يمكنك الآن بدء الجلسة للعميل الجديد
        </div>
    `);

                $('body').append($snackbar);

                // اختفاء تلقائي بعد 5 ثواني
                setTimeout(() => $snackbar.fadeOut(300, () => $snackbar.remove()), 5000);
            }




            function showSnackbarForNewClient(clientData = {}) {
                $('#customSnackbar').remove();

                const $snackbar = $(`
        <div id="customSnackbar" style="
            position:fixed; bottom:20px; right:20px; background:#fff; color:#333; border:1px solid #d9b2ad;
            border-radius:12px; padding:12px 18px; box-shadow:0 4px 12px rgba(0,0,0,0.2); min-width:280px; z-index:99999;
            display:flex; justify-content:space-between; align-items:center; gap:12px;
        ">
            عميل جديد: #${clientData.id||''}
            <button id="fillClientBtn" style="
                background:#d9b2ad; color:#fff; border:none; padding:6px 12px; border-radius:8px; cursor:pointer; font-weight:bold;
            ">إكمال البيانات</button>
        </div>
    `);

                $('body').append($snackbar);

                $('#fillClientBtn').on('click', () => {
                    openClientModalForExtraData(clientData);
                    $snackbar.remove();
                });

                setTimeout(() => $snackbar.fadeOut(300, () => $snackbar.remove()), 10000);
            }





            function clearResults() {
                state.currentResults = [];
                state.highlightedIndex = -1;
                $(CONFIG.resultsContainer).hide().empty();
                if (CONFIG.useAriaActiveDescendant) $(CONFIG.searchInput).removeAttr('aria-activedescendant');
            }

            function highlight(index, opts = {
                scrollIntoView: true,
                keepFocusOnInput: true
            }) {
                const $items = $(`${CONFIG.resultsContainer} .${CONFIG.resultsItemClass}`);
                $items.removeClass('active').attr('aria-selected', 'false');
                if (index == null || index < 0 || index >= state.currentResults.length) {
                    state.highlightedIndex = -1;
                    if (CONFIG.useAriaActiveDescendant) $(CONFIG.searchInput).removeAttr('aria-activedescendant');
                    return;
                }
                state.highlightedIndex = index;
                const $el = $items.eq(index);
                $el.addClass('active').attr('aria-selected', 'true');
                if (CONFIG.useAriaActiveDescendant) {
                    try {
                        $(CONFIG.searchInput).attr('aria-activedescendant', $el.attr('id'));
                    } catch (e) {
                        /* ignore */
                    }
                }
                // scroll to view but DON'T change focus — هذا يضمن الـ highlight ثابت
                if (opts.scrollIntoView) {
                    const container = $(CONFIG.resultsContainer)[0];
                    if (container && $el.length) {
                        const item = $el[0];
                        const cTop = container.scrollTop,
                            cBottom = cTop + container.clientHeight;
                        const itTop = item.offsetTop,
                            itBottom = itTop + item.offsetHeight;
                        if (itTop < cTop) container.scrollTop = itTop;
                        if (itBottom > cBottom) container.scrollTop = itBottom - container.clientHeight;
                    }
                }
                // حافظ على فوكس الـ input لو طُلب (هنا نريده ثابت)
                if (opts.keepFocusOnInput) {
                    try {
                        $(CONFIG.searchInput).focus();
                    } catch (e) {
                        /* ignore */
                    }
                }
            }

            function blurActiveElementSafely() {
                try {
                    setTimeout(() => {
                        if (document.activeElement && typeof document.activeElement.blur === 'function') {
                            document.activeElement.blur();
                        }
                        if (window.getSelection) {
                            const sel = window.getSelection();
                            if (sel && sel.removeAllRanges) sel.removeAllRanges();
                        }
                    }, 0);
                } catch (e) {}
            }

            function pickResult(idx) {
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

            function doSearch(query) {
                if (!query || !query.trim()) {
                    clearResults();
                    return;
                }
                if (!CONFIG.ajaxUrl) {
                    return;
                }
                if (state.searchDebounceTimer) clearTimeout(state.searchDebounceTimer);
                state.searchDebounceTimer = setTimeout(() => {
                    $.ajax({
                        url: CONFIG.ajaxUrl,
                        type: CONFIG.ajaxMethod,
                        data: {
                            query: query
                        },
                        success: function(data) {
                            renderResults(Array.isArray(data) ? data : []);
                        },
                        error: function() {
                            $(CONFIG.resultsContainer).html(
                                    '<div style="padding:8px; color:#999;">خطأ في البحث</div>')
                                .show();
                            state.currentResults = [];
                            state.highlightedIndex = -1;
                        }
                    });
                }, CONFIG.ajaxDelay);
            }

            function updatePersonsDisplay() {
                $(CONFIG.countDisplay).text(state.persons);
                $(CONFIG.countInputHidden).val(state.persons);
            }

            function incPersons() {
                if (state.persons < CONFIG.maxPersons) {
                    state.persons++;
                    updatePersonsDisplay();
                } else {
                    const el = $(CONFIG.countDisplay);
                    el.addClass('shake');
                    setTimeout(() => el.removeClass('shake'), 250);
                }
            }

            function decPersons() {
                if (state.persons > CONFIG.minPersons) {
                    state.persons--;
                    updatePersonsDisplay();
                } else {
                    const el = $(CONFIG.countDisplay);
                    el.addClass('min-reached');
                    setTimeout(() => el.removeClass('min-reached'), 250);
                }
            }
            (function() {
                const hallEl = document.getElementById('hallSelect');
                // durationSelect removed intentionally; we use fixed duration = 60 minutes
                const FIXED_DURATION = 60;

                const personsDisplayEl = document.getElementById('personsDisplayInModal');
                const estimateBanner = document.getElementById('estimateBanner');
                const estimateMessage = document.getElementById('estimateMessage');
                const estimateAmount = document.getElementById('estimateAmount');
                const estimatePerHour = document.getElementById('estimatePerHour');
                const ongoingWarning = document.getElementById('ongoingWarning');
                const ongoingText = document.getElementById('ongoingText');
                const startNowBtn = document.getElementById('startNowBtn');

                // when modal opens, copy current client info & persons
                $('#startBookingModal').on('shown.bs.modal', function() {
                    $('#modal_phone').val($('#phone').val() || '');
                    $('#modal_name').val($('#name').val() || '');
                    $('#modal_persons').val($('#personsInput').val() || '1');
                    personsDisplayEl.textContent = $('#personsInput').val() || '1';
                    // reset UI
                    estimateBanner.style.display = 'none';
                    ongoingWarning.style.display = 'none';
                    estimateAmount.textContent = '';
                    estimatePerHour.textContent = '';
                    estimateMessage.textContent = 'اختَر القاعة لاظهار التقدير (المدة: ساعة واحدة)';
                    startNowBtn.disabled = true;

                    // إذا كانت القاعة محددة بالفعل — نفذ الحساب تلقائيًا
                    if (hallEl && hallEl.value) {
                        fetchEstimate();
                    }
                });

                // helper to safely parse number
                function safeNumber(v) {
                    const n = Number(String(v || '').replace(/,/g, ''));
                    return isNaN(n) ? 0 : n;
                }

                async function checkOngoing(hallId) {
                    try {
                        const url = "{{ route('bookings.check_ongoing') }}?hall_id=" + encodeURIComponent(
                            hallId);
                        const resp = await fetch(url, {
                            credentials: 'same-origin'
                        });
                        if (!resp.ok) return {
                            error: 'خطأ في التحقق'
                        };
                        const data = await resp.json();
                        return data;
                    } catch (err) {
                        console.error('[checkOngoing] ', err);
                        return {
                            error: 'خطأ في الاتصال'
                        };
                    }
                }

                async function fetchEstimate() {
                    const hallId = hallEl?.value || '';
                    const attendees = $('#personsInput').val() || '';

                    const durNum = FIXED_DURATION; // use fixed 60 minutes
                    const attNum = safeNumber(attendees);

                    // سلوك التحقق: مدة ثابتة 60 دقيقة => فقط نتحقق من hallId و attendees
                    if (!hallId || !attNum || isNaN(attNum)) {
                        estimateBanner.style.display = 'none';
                        startNowBtn.disabled = true;
                        return;
                    }

                    // عرض الـbanner والتحقق من الحجز الجاري
                    ongoingWarning.style.display = 'none';
                    estimateBanner.style.display = 'block';
                    estimateMessage.textContent = 'جارِ التحقق...';
                    estimateAmount.textContent = '';
                    estimatePerHour.textContent = '';
                    startNowBtn.disabled = true;

                    const ongoingResp = await checkOngoing(hallId);
                    if (ongoingResp && ongoingResp.error) {
                        ongoingWarning.style.display = 'block';
                        ongoingText.textContent = ongoingResp.error;
                        estimateMessage.textContent = '';
                        startNowBtn.disabled = true;
                        return;
                    }
                    if (ongoingResp && ongoingResp.ongoing) {
                        ongoingWarning.style.display = 'block';
                        ongoingText.textContent = ongoingResp.message ||
                            'القاعة محجوزة حالياً. اختر قاعة أخرى.';
                        estimateMessage.textContent = '';
                        estimateAmount.textContent = '';
                        startNowBtn.disabled = true;
                        return;
                    }

                    // طلب التقدير من السيرفر مع duration_minutes = 60
                    estimateMessage.textContent = 'جارِ الحساب...';
                    try {
                        const params = new URLSearchParams({
                            hall_id: hallId,
                            attendees: attNum,
                            duration_minutes: Math.round(durNum)
                        });
                        const url = "{{ route('bookings.estimate') }}?" + params.toString();
                        const resp = await fetch(url, {
                            method: 'GET',
                            credentials: 'same-origin'
                        });
                        if (!resp.ok) {
                            estimateMessage.textContent = 'خطأ في الحساب';
                            startNowBtn.disabled = true;
                            return;
                        }
                        const data = await resp.json();
                        if (data && data.success) {
                            estimateMessage.textContent = `التقدير (المدة: ساعة واحدة)`;
                            estimateAmount.textContent = `${data.estimated_formatted} ${data.currency || ''}`;
                            estimatePerHour.textContent =
                                `سعر الساعة: ${data.per_hour_formatted || ''} ${data.currency || ''}`;
                            // فعل زر البدء
                            startNowBtn.disabled = false;
                        } else if (data && data.error) {
                            estimateMessage.textContent = data.error;
                            estimateAmount.textContent = '';
                            estimatePerHour.textContent = '';
                            startNowBtn.disabled = true;
                        } else {
                            estimateMessage.textContent = 'لا توجد نتيجة';
                            estimateAmount.textContent = '';
                            estimatePerHour.textContent = '';
                            startNowBtn.disabled = true;
                        }
                    } catch (err) {
                        console.error('[estimate] ', err);
                        estimateMessage.textContent = 'خطأ في الاتصال';
                        estimateAmount.textContent = '';
                        estimatePerHour.textContent = '';
                        startNowBtn.disabled = true;
                    }
                }

                // مستمع على تغيير القاعة — عند التغيير نحسب تلقائيًا
                if (hallEl) hallEl.addEventListener('change', fetchEstimate);

                // لو العدد تغيّر (من العدّاد) يجب تحديث العرض
                $(document).on('click', '#increasePersons, #decreasePersons', function() {
                    $('#modal_persons').val($('#personsInput').val());
                    $('#personsDisplayInModal').text($('#personsInput').val());
                    // لو القاعة محددة → نعيد الحساب
                    if (hallEl && hallEl.value) fetchEstimate();
                });

                // قبل إرسال الفورم النهائي (startNow) نملأ القيم النهائية من الفورم الرئيسي (اسم/تليفون/عدد)
                document.getElementById('startBookingForm').addEventListener('submit', function(e) {
                    // تأكد من وجود بيانات العميل وإلا نمنع الإرسال
                    const phone = $('#phone').val() || $('#modal_phone').val();
                    const name = $('#name').val() || $('#modal_name').val();

                    if (!phone || !name) {
                        e.preventDefault();
                        alert('مطلوب: اسم العميل ورقم الهاتف قبل بدء الجلسة.');
                        return false;
                    }

                    // copy values (safety)
                    $('#modal_phone').val(phone);
                    $('#modal_name').val(name);
                    $('#modal_persons').val($('#personsInput').val());

                    // تأكد أن الحقل المخفي للمدة موجود ومملوء (مدة ثابتة ساعة)
                    $('input[name="duration_minutes"]').val(FIXED_DURATION);

                    // الفورم سيُرسل للطريق bookings.start-now (POST)
                });

            })();

            $(function() {
                $(CONFIG.resultsContainer).hide();
                updatePersonsDisplay();

                $(document).on('input', CONFIG.searchInput, function() {
                    const q = $(this).val() || '';
                    if (CONFIG.nameField) $(CONFIG.nameField).val('');
                    if (q.trim().length >= 1) doSearch(q.trim());
                    else clearResults();
                });

                // click on result: pick and submit? (keeps previous behavior)
                $(document).on('click', `${CONFIG.resultsContainer} .${CONFIG.resultsItemClass}`, function(e) {
                    const idx = parseInt($(this).data('index'));
                    if (!isNaN(idx)) {
                        pickResult(idx);
                    }
                    $(CONFIG.searchInput).focus();
                });

                // keyboard navigation only affects highlight (لا يغيّر الفوكس)
                $(document).on('keydown', CONFIG.searchInput, function(e) {
                    const key = e.key;
                    const q = $(this).val() || '';
                    // Arrow navigation: حافظ على highlight ثابت
                    if ((key === 'ArrowDown' || key === 'ArrowUp') && state.currentResults.length > 0) {
                        e.preventDefault();
                        if (key === 'ArrowDown') {
                            if (state.highlightedIndex < state.currentResults.length - 1) highlight(
                                state.highlightedIndex + 1);
                            else highlight(state.currentResults.length - 1);
                        } else {
                            if (state.highlightedIndex > 0) highlight(state.highlightedIndex - 1);
                            else highlight(0);
                        }
                        // لا نغير الفوكس — نحتفظ به في الـ input
                        return;
                    }

                    // Enter: نختار العنصر المظلّل (أو أول عنصر إذا لم يكن هناك ظل)
                    if (key === 'Enter') {
                        if (state.currentResults.length > 0) {
                            e.preventDefault();
                            const pickIdx = state.highlightedIndex >= 0 ? state.highlightedIndex : 0;
                            if (pickResult(pickIdx)) {
                                // بعد الاختيار يمكن ارسال الفورم من مكان آخر إن رغبت
                                return;
                            }
                        }
                        if (!state.currentResults.length) {
                            const next = CONFIG.nextFieldIfNoResults;
                            if (next) {
                                e.preventDefault();
                                $(next).focus().select();
                                return;
                            }
                        }
                    }

                    // left/right for persons
                    if ((key === 'ArrowLeft' || key === 'ArrowRight') && state.currentResults.length ===
                        0) {
                        if ($(CONFIG.countDisplay).length) {
                            e.preventDefault();
                            if (key === 'ArrowLeft') incPersons();
                            else decPersons();
                            return;
                        }
                    }
                });

                // مستمع موحّد: اختصارات عالمية + توجيه الكتابة حسب أول رقم (0 -> phone, 1 -> clientIdInput)
                // يحترم CONFIG.ignoreInputsSelector حتى لا يتداخل مع الحقول النصية الحقيقية
                $(document).on('keydown.globalTypeMerged', function(e) {
                    const target = e.target;

                    // لو التركيز داخل حقل نصي أو عنصر قابل للتحرير أو select — لا نتدخل
                    if ($(target).is(CONFIG.ignoreInputsSelector)) return;

                    // اختصارات: Ctrl/Cmd + K => تركيز على حقل البحث الرئيسي
                    if ((e.ctrlKey || e.metaKey) && (e.key && e.key.toLowerCase() === 'k')) {
                        e.preventDefault();
                        $(CONFIG.searchInput).focus().select();
                        return;
                    }

                    // Escape => إخفاء النتائج وازالة الفوكس من البحث
                    if (e.key === 'Escape') {
                        clearResults();
                        $(CONFIG.searchInput).blur();
                        return;
                    }

                    // Enter => سلوك إرسال/اختيار عام (يحاكي السلوك السابق)
                    if (e.key === 'Enter') {
                        const qVal = $(CONFIG.searchInput).val() || '';
                        if (qVal.trim().length >= 1) {
                            if (state.currentResults.length > 0) {
                                e.preventDefault();
                                const pickIdx = state.highlightedIndex >= 0 ? state.highlightedIndex :
                                    0;
                                if (pickResult(pickIdx)) {
                                    return;
                                }
                            } else {
                                if (CONFIG.nextFieldIfNoResults) {
                                    e.preventDefault();
                                    $(CONFIG.nextFieldIfNoResults).focus().select();
                                    return;
                                }
                            }
                        } else {
                            // لا تفعل شيئاً إن الحقل فاضي
                        }
                        return;
                    }

                    // السلوك الخاص بالمفاتيح المفردة غير المعدّلة (حروف/أرقام)
                    const key = e.key;

                    // — نعامل الأرقام المفردة بشكل خاص إذا لم تُضغط أي modifier
                    if (key && key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
                        // لو رقم
                        if (/^[0-9]$/.test(key)) {
                            // تمنع السلوك الافتراضي لأننا سنتحكم بإدخال الحرف في الحقل الصحيح
                            e.preventDefault();

                            // لو الرقم '0' — اكتب في حقل الهاتف وأعطه الفوكس
                            if (key === '0') {
                                const $phone = $('#phone');
                                // ضع الرقم في موضع الكيرسور الحالي داخل الحقل (أو النهاية إذا لا يدعم)
                                try {
                                    const el = $phone.get(0);
                                    const start = (typeof el.selectionStart === 'number') ? el
                                        .selectionStart : $phone.val().length;
                                    const end = (typeof el.selectionEnd === 'number') ? el
                                        .selectionEnd : start;
                                    const val = $phone.val() || '';
                                    const newVal = val.slice(0, start) + key + val.slice(end);
                                    $phone.val(newVal).trigger('input');
                                    const caret = start + 1;
                                    el.setSelectionRange(caret, caret);
                                    $phone.focus();
                                } catch (err) {
                                    $phone.val(($phone.val() || '') + key).trigger('input').focus();
                                }
                                return;
                            }

                            // لو الرقم '1' — اكتب في حقل المعرّف (ID) وأعطه الفوكس
                            if (key === '1') {
                                const $id = $('#clientIdInput');
                                try {
                                    const el = $id.get(0);
                                    const start = (typeof el.selectionStart === 'number') ? el
                                        .selectionStart : $id.val().length;
                                    const end = (typeof el.selectionEnd === 'number') ? el
                                        .selectionEnd : start;
                                    const val = $id.val() || '';
                                    const newVal = (val.slice(0, start) + key + val.slice(end)).slice(0,
                                        ID_CONFIG.maxLength);
                                    $id.val(newVal).trigger('input');
                                    const caret = Math.min(start + 1, ID_CONFIG.maxLength);
                                    el.setSelectionRange(caret, caret);
                                    $id.focus();
                                } catch (err) {
                                    $id.val(($id.val() || '') + key);
                                    $id.trigger('input');
                                    $id.focus();
                                }
                                return;
                            }

                            // لرقم آخر — افتراضي: أدخله في حقل البحث الرئيسي (CONFIG.searchInput)
                            // هذا يحاكي السلوك السابق الذي كان يدخل أي حرف الى حقل البحث.
                            (function() {
                                const $inp = $(CONFIG.searchInput);
                                const inputEl = $inp.get(0);
                                if (!inputEl) return;
                                try {
                                    const start = (typeof inputEl.selectionStart === 'number') ?
                                        inputEl.selectionStart : $inp.val().length;
                                    const end = (typeof inputEl.selectionEnd === 'number') ? inputEl
                                        .selectionEnd : start;
                                    const val = $inp.val() || '';
                                    const newVal = val.slice(0, start) + key + val.slice(end);
                                    $inp.val(newVal).trigger('input');
                                    const caret = start + 1;
                                    inputEl.setSelectionRange(caret, caret);
                                    $inp.focus();
                                } catch (err) {
                                    $inp.val(($inp.val() || '') + key).trigger('input');
                                    $inp.focus();
                                }
                                clearResults();
                            })();

                            return;
                        }

                        // لو حرف عادي (غير رقم) — نعيد سلوك إدخال الحرف في حقل البحث الرئيسي (كما كان)
                        const code = key.charCodeAt(0);
                        if (code >= 32) {
                            e.preventDefault();
                            const $inp = $(CONFIG.searchInput);
                            const inputEl = $inp.get(0);
                            if (!inputEl) return;
                            try {
                                const start = (typeof inputEl.selectionStart === 'number') ? inputEl
                                    .selectionStart : $inp.val().length;
                                const end = (typeof inputEl.selectionEnd === 'number') ? inputEl
                                    .selectionEnd : start;
                                const val = $inp.val() || '';
                                const newVal = val.slice(0, start) + key + val.slice(end);
                                $inp.val(newVal).trigger('input');
                                const caret = start + 1;
                                inputEl.setSelectionRange(caret, caret);
                                $inp.focus();
                            } catch (err) {
                                $inp.val(($inp.val() || '') + key).trigger('input');
                                $inp.focus();
                            }
                            clearResults();
                            return;
                        }
                    }

                    // — الآن التعامل مع تنقّل الأسهم وعداد الأشخاص كما في السابق
                    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                        if (state.currentResults.length > 0) {
                            e.preventDefault();
                            if (e.key === 'ArrowDown') {
                                if (state.highlightedIndex < state.currentResults.length - 1) highlight(
                                    state.highlightedIndex + 1);
                                else highlight(state.currentResults.length - 1);
                            } else {
                                if (state.highlightedIndex > 0) highlight(state.highlightedIndex - 1);
                                else highlight(0);
                            }
                            return;
                        }
                    }

                    if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
                        if ($(CONFIG.countDisplay).length) {
                            e.preventDefault();
                            if (e.key === 'ArrowLeft') incPersons();
                            else decPersons();
                            return;
                        }
                    }

                    // لا تغيّر أي سلوك آخر
                });


                // click outside closes results
                $(document).on('click', function(e) {
                    if (!$(e.target).closest(CONFIG.resultsContainer + ', ' + CONFIG.searchInput)
                        .length) {
                        clearResults();
                    }
                });

                // small UX: Enter on highlighted result triggers pick (backup)
                $(document).on('keydown', function(e) {
                    if (e.key === 'Enter' && state.highlightedIndex >= 0 && $(document.activeElement)
                        .is(CONFIG.searchInput)) {
                        e.preventDefault();
                        pickResult(state.highlightedIndex);
                    }
                });

            }); // ready
        })(jQuery);
    </script>

@endsection
