<!doctype html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>برج مراقبة X Space</title>

    <!-- Tailwind Play CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <!-- lottie-player -->
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

    <style>
        :root {
            --bg-dark: #06060a;
            --primary-1: #7f5af0;
            --primary-2: #2cb67d;
            /* default fallback width for the typing box (will be overridden by JS) */
            --typewrite-width: 28ch;
            --typewrite-width-mobile: auto;
        }

        /* === Prevent horizontal nudges / overflow === */
        html,
        body {
            box-sizing: border-box;
            /* make sizing predictable */
            max-width: 100vw;
            /* never exceed viewport width */
            overflow-x: hidden !important;
            /* block horizontal scroll that shifts page */
            -webkit-overflow-scrolling: touch;
            overscroll-behavior-x: none;
        }

        /* ensure all elements follow the same box-sizing */
        *,
        *::before,
        *::after {
            box-sizing: inherit;
        }

        /* guard long strings (like typewrite or preformatted meta) from forcing width */
        .typewrite,
        pre,
        .td-notes,
        .card-body .val {
            word-break: break-word;
            overflow-wrap: anywhere;
        }

        /* starfield canvas safety */
        #starfield {
            left: 0;
            top: 0;
        }

        /* === Modal improvements: center + safe sizing on small screens === */
        /* add a wrapper class (modal-wrap) for consistent centering */
        .modal-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            /* small gap for mobile */
        }

        /* the modal content should never exceed viewport height and should scroll internally */
        .modal-content {
            max-width: 720px;
            /* same as max-w-2xl approx */
            width: 100%;
            margin: 0 auto;
            max-height: calc(100vh - 48px);
            /* keep some spacing from edges */
            overflow: auto;
            /* internal scrolling if content is tall */
            -webkit-overflow-scrolling: touch;
            box-shadow: 0 18px 60px rgba(0, 0, 0, 0.6);
        }

        /* a slightly tighter mobile size */
        @media (max-width: 640px) {
            .modal-content {
                padding: 14px;
                /* make inner padding smaller on phone */
                max-width: 96%;
                border-radius: 12px;
            }

            /* ensure hero/container padding won't push layout off-screen */
            .min-h-screen {
                padding-left: 12px;
                padding-right: 12px;
            }
        }

        /* If keyboard appears on mobile, center visually (flex center is more robust than top positioning) */
        @media (max-width: 880px) {
            .modal-wrap {
                align-items: center;
            }
        }

        body {


            margin: 0;
            font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
            background: var(--bg-dark);
            color: #e6eef5;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow-x: hidden;
        }

        .glass {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.02), rgba(255, 255, 255, 0.01));
            border: 1px solid rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(8px) saturate(120%);
        }

        /* starfield */
        #starfield {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            filter: drop-shadow(0 10px 30px rgba(0, 0, 0, 0.6));
        }

        /* blobs */
        .blob {
            position: fixed;
            z-index: 0;
            filter: blur(40px) saturate(140%);
            opacity: 0.22;
            pointer-events: none;
            mix-blend-mode: screen;
            animation: floatSlow linear infinite;
        }

        .blob.b1 {
            width: 560px;
            height: 420px;
            left: -120px;
            top: -80px;
            background: radial-gradient(circle at 30% 30%, rgba(127, 90, 240, 0.28), rgba(127, 90, 240, 0.08) 40%, transparent 60%);
            animation-duration: 18s;
        }

        .blob.b2 {
            width: 480px;
            height: 360px;
            right: -100px;
            bottom: -120px;
            background: radial-gradient(circle at 70% 70%, rgba(44, 182, 125, 0.22), rgba(44, 182, 125, 0.06) 40%, transparent 60%);
            animation-duration: 20s;
        }

        @keyframes floatSlow {
            0% {
                transform: translateY(0) translateX(0) rotate(0)
            }

            50% {
                transform: translateY(-18px) translateX(8px) rotate(2deg)
            }

            100% {
                transform: translateY(0) translateX(0) rotate(0)
            }
        }

        header {
            position: relative;
            z-index: 10;
        }

        .mini-lottie {
            width: 40px;
            height: 40px;
        }

        .accent-btn {
            background: linear-gradient(90deg, var(--primary-1), var(--primary-2));
            color: #fff;
        }

        .hero-wrap {
            position: relative;
            z-index: 10;
        }

        .back-btn {
            position: fixed;
            /* 👈 بدل absolute */
            top: 20px;
            left: 20px;
            background: #00000000;
            border-radius: 50%;
            padding: 13px;
            cursor: pointer;
            transition: transform 0.2s;
            z-index: 2000;
        }

        .back-btn:hover {
            transform: scale(1.1) rotate(-10deg);
        }

        .hero-title {
            font-weight: 800;
            font-size: 28px;
            letter-spacing: -0.6px;
            line-height: 1.05;
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .hero-sub {
            color: rgba(230, 238, 245, 0.85);
            font-size: 14px;
        }

        /* SAFE typewrite: reserve width so changing text doesn't reflow layout */
        .hero-title {
            position: relative;
        }

        /* anchor for absolute caret */
        .typewrite {
            display: inline-block;
            width: var(--typewrite-width, 28ch);
            /* reserved space (in ch = approx character width) */
            max-width: 100%;
            white-space: nowrap;
            overflow: hidden;
            /* يخفي النص الزائد داخل الصندوق */
            box-sizing: content-box;
            padding-right: 12px;
            /* مسافة للـ caret المطلق */
            vertical-align: middle;
            /* caret via pseudo-element (so it doesn't affect layout) */
        }

        /* caret moved to pseudo-element and blink via opacity */
        .typewrite::after {
            content: '';
            position: absolute;
            /* place caret inside the padded area at the right side of the reserved box */
            right: 0.6rem;
            top: 50%;
            transform: translateY(-50%);
            width: 2px;
            height: 1.05em;
            background: rgba(230, 238, 245, 0.95);
            border-radius: 1px;
            animation: blinkCaret 900ms steps(2, end) infinite;
            pointer-events: none;
        }

        @keyframes blinkCaret {
            50% {
                opacity: 0
            }
        }

        main {
            position: relative;
            z-index: 10;
        }

        .card-anim {
            transition: transform .28s cubic-bezier(.2, .9, .2, 1), box-shadow .28s;
        }

        .card-anim:hover {
            transform: translateY(-8px) scale(1.01);
            box-shadow: 0 18px 60px rgba(0, 0, 0, 0.6);
        }

        .timeline-dot {
            width: 12px;
            height: 12px;
            border-radius: 999px;
            border: 3px solid rgba(0, 0, 0, 0.35);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.6);
        }

        .modal-backdrop {
            background: linear-gradient(180deg, rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.55));
            backdrop-filter: blur(4px);
        }

        @media (max-width:880px) {
            .hero-title {
                font-size: 20px;
            }

            .mini-lottie {
                width: 34px;
                height: 34px;
            }
        }

        /* 1️⃣ اجعل الهيدر والـ hero-wrap مركزيين على الموبايل */
        @media (max-width: 640px) {
            .hero-wrap {
                justify-content: center;
                /* وسط المحتوى أفقيًا */
                text-align: center;
                /* وسط النصوص */
                flex-direction: column;
                /* لو فيه أي عناصر جنب بعض تتحول فوق بعض */
                gap: 6px;
                /* مسافة صغيرة بين العناصر */
            }

            .hero-title {
                font-size: 20px;
                /* تصغير الحجم */
                line-height: 1.2;
                /* ضبط الارتفاع */
            }

            .mini-lottie {
                width: 32px;
                height: 32px;
            }

            /* padding آمن من الجانبين */
            .min-h-screen {
                padding-left: 12px;
                padding-right: 12px;
            }
        }

        /* reduced motion: turn off animations if user prefers reduced motion */
        @media (prefers-reduced-motion: reduce) {

            .blob,
            .card-anim,
            .typewrite,
            .typewrite::after {
                animation: none !important;
                transition: none !important;
            }

            .typewrite::after {
                display: none !important;
            }
        }

        /* On small screens: stop typing loop effect and hide caret to avoid any flicker */
        @media (max-width:880px) {
            .typewrite {
                width: var(--typewrite-width-mobile, auto);
            }

            .typewrite::after {
                display: none;
            }
        }

        /* small chips look */
        .chip {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.03);
        }
    </style>
</head>

<body>
    <form action="{{ route('managment.create') }}">
        <button class="back-btn">خروج</button>

    </form>
    <canvas id="starfield" aria-hidden="true"></canvas>
    <div class="blob b1" aria-hidden="true"></div>
    <div class="blob b2" aria-hidden="true"></div>

    <div x-data="systemActionsDemo()" x-init="init()" class="min-h-screen px-4 py-10 relative">

        <header class="max-w-6xl mx-auto mb-6 flex items-center justify-between gap-4">
            <div class="flex items-center gap-4 hero-wrap">
                <div class="w-12 h-12 rounded-lg glass flex items-center justify-center shadow-lg">
                    <lottie-player src="https://assets5.lottiefiles.com/packages/lf20_jcikwtux.json"
                        background="transparent" speed="1" loop autoplay class="mini-lottie"></lottie-player>
                </div>

                <div>
                    <div class="hero-title text-white">
                        <span>مراقبة النظام</span>
                        <span class="typewrite" x-text="typed" aria-live="polite"></span>
                    </div>
                    <p class="hero-sub mt-1" x-text="subtitle"></p>
                </div>
            </div>


        </header>

        <main class="max-w-6xl mx-auto relative">
            <div class="glass rounded-xl p-4 mb-4 flex flex-col md:flex-row md:items-center gap-3 card-anim">
                <div class="flex-1">
                    <input x-model="query" @input.debounce.250="applyFilters"
                        placeholder="ابحث بأي شيء: اسم، action، رقم فاتورة، IP، ملاحظة..."
                        class="w-full p-3 rounded-lg bg-transparent border border-white/10 placeholder-gray-300 text-white" />
                </div>

                <!-- Desktop filters (hidden on small screens) -->
                <div class="hidden md:flex items-center gap-2 flex-wrap">
                    <template x-for="t in visibleChips" :key="t">
                        <button @click="toggleChip(t)"
                            :class="selectedChips.includes(t) ? 'bg-indigo-500/25 ring-1 ring-indigo-400/30 text-white' :
                                'chip text-gray-200/90'"
                            class="px-3 py-2 rounded-full text-sm transition">
                            <span x-text="humanize(t)"></span>
                        </button>
                    </template>

                    <div class="ml-2 flex items-center gap-2">
                        <input type="date" x-model="dateFrom" @change="applyFilters"
                            class="p-2 rounded border border-white/10 bg-transparent text-white" />
                        <input type="date" x-model="dateTo" @change="applyFilters"
                            class="p-2 rounded border border-white/10 bg-transparent text-white" />
                        <!-- Clear button stays visible on desktop here -->
                        <button @click="clearFilters" class="px-3 py-2 rounded-md chip">مسح</button>
                    </div>
                </div>


            </div>

            <!-- Mobile control card: يظهر فقط على الشاشات الصغيرة -->
            <div class="max-w-6xl mx-auto mt-4 md:hidden">
                <div class="glass rounded-xl p-3 card-anim">
                    <div class="flex items-center justify-between mb-3">
                        <div class="text-sm text-gray-300 font-medium">تحكم الفلاتر</div>
                        <button @click="clearFilters" class="px-3 py-1 rounded bg-red-600/15 text-red-200 text-sm">مسح
                            الفلاتر</button>
                    </div>

                    <div class="space-y-3">
                        <div class="flex flex-wrap gap-2">
                            <template x-for="t in visibleChips" :key="t">
                                <button @click="toggleChip(t)"
                                    :class="selectedChips.includes(t) ?
                                        'bg-indigo-500/25 ring-1 ring-indigo-400/30 text-white' :
                                        'chip text-gray-200/90'"
                                    class="px-3 py-2 rounded-full text-sm transition">
                                    <span x-text="humanize(t)"></span>
                                </button>
                            </template>
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="date" x-model="dateFrom" @change="applyFilters"
                                class="flex-1 p-2 rounded border border-white/10 bg-transparent text-white" />
                            <input type="date" x-model="dateTo" @change="applyFilters"
                                class="flex-1 p-2 rounded border border-white/10 bg-transparent text-white" />
                        </div>

                        <div class="text-xs text-gray-400">اضغط على أي فلتر لتفعيله/تعطيله. ثم انقر "مسح الفلاتر" لإعادة
                            تعيين.</div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between gap-4 mb-4">
                <div class="flex items-center gap-3 text-sm text-gray-300">
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-green-400"></span> معاملات
                    </div>
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-red-400"></span> مصروفات
                    </div>
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-yellow-400"></span> جلسات
                    </div>
                </div>

                <div x-show="realtimeToast" x-transition
                    class="text-sm text-white bg-indigo-600/90 px-3 py-2 rounded shadow">
                    <span x-text="realtimeToast"></span>
                </div>
            </div>

            <section class="space-y-4 relative z-10">
                <div x-show="loading" class="text-center text-gray-300 py-4">جاري التحميل...</div>
                <div x-show="error" class="text-center text-red-400 py-2" x-text="error"></div>

                <div x-show="view==='timeline'">
                    <div class="space-y-3">
                        <template x-for="act in filtered" :key="act.id">
                            <div class="relative">
                                <div class="flex items-start gap-4 p-4 rounded-xl glass card-anim">
                                    <div class="flex flex-col items-center">
                                        <div :class="dotColor(act.action)" class="timeline-dot"></div>
                                        <div class="h-full w-px bg-white/6 ml-0" style="height:40px"></div>
                                    </div>

                                    <div class="flex-1">
                                        <div class="flex items-center justify-between gap-2">
                                            <div>
                                                <div class="font-bold text-white" x-text="humanize(act.action)"></div>
                                                <div class="text-xs text-gray-300 mt-1" x-text="formatMetaLine(act)">
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <div class="text-sm text-gray-200">#<span x-text="act.id"></span>
                                                </div>
                                                <button @click="openDetails(act)"
                                                    class="px-3 py-1 rounded-md bg-indigo-500/20 text-indigo-200 text-sm">التفاصيل</button>
                                            </div>
                                        </div>

                                        <div class="mt-2 text-sm text-gray-300" x-text="shortNote(act.note)"></div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div x-show="filtered.length===0 && !loading" class="text-center text-gray-400 py-8">لا توجد
                            نتائج</div>
                    </div>
                </div>

                <div x-show="view==='table'">
                    <div class="overflow-auto rounded-xl glass card-anim">
                        <table class="min-w-full text-sm">
                            <thead class="border-b border-white/6">
                                <tr class="text-left text-gray-300">
                                    <th class="p-3">الوقت</th>
                                    <th class="p-3">المستخدم</th>
                                    <th class="p-3">الحدث</th>
                                    <th class="p-3">مبلغ</th>
                                    <th class="p-3">ملاحظات</th>
                                    <th class="p-3">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="act in filtered" :key="act.id">
                                    <tr class="border-b border-white/4 hover:bg-white/2 transition">
                                        <td class="p-3 text-gray-200" x-text="act.created_at"></td>
                                        <td class="p-3" x-text="act.user.name"></td>
                                        <td class="p-3" x-text="humanize(act.action)"></td>
                                        <td class="p-3" x-text="act.amount ?? '-'"></td>
                                        <td class="p-3" x-text="shortNote(act.note)"></td>
                                        <td class="p-3"><button @click="openDetails(act)"
                                                class="text-indigo-300">عرض</button></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        <div class="p-4 text-gray-400" x-show="filtered.length===0 && !loading">لا توجد نتائج</div>
                    </div>
                </div>

                <div class="flex items-center justify-center gap-2 mt-4" x-show="meta.last_page > 1">
                    <button @click="goToPage(meta.current_page - 1)" :disabled="meta.current_page <= 1"
                        class="px-3 py-1 rounded chip">السابق</button>

                    <template x-for="p in pagesToShow()" :key="p">
                        <button @click="goToPage(p)"
                            :class="p === meta.current_page ? 'bg-indigo-500 text-white px-3 py-1 rounded' :
                                'px-3 py-1 rounded chip'"
                            x-text="p"></button>
                    </template>

                    <button @click="goToPage(meta.current_page + 1)" :disabled="meta.current_page >= meta.last_page"
                        class="px-3 py-1 rounded chip">التالي</button>
                </div>

            </section>

        </main>

        <div x-show="detailOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div @click.self="detailOpen=false" class="absolute inset-0 modal-backdrop"></div>
            <div class="relative max-w-2xl w-full rounded-xl glass p-6 z-10">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-bold" x-text="selected.action ? humanize(selected.action) : ''"></h3>
                        <div class="text-sm text-gray-300 mt-1" x-text="selected.created_at"></div>
                    </div>
                    <div>
                        <button @click="detailOpen=false" class="px-3 py-2 chip rounded">اغلاق</button>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs text-gray-300">المستخدم</div>
                        <div class="font-medium" x-text="selected.user?.name ?? '-'"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-300">المبلغ</div>
                        <div class="font-medium" x-text="selected.amount ? selected.amount+' EGP' : '-'"></div>
                    </div>

                    <div class="md:col-span-2">
                        <div class="text-xs text-gray-300">ملاحظة</div>
                        <div class="mt-1 text-sm text-gray-200" x-text="selected.note ?? '-'"></div>
                    </div>

                    <div class="md:col-span-2">
                        <div class="text-xs text-gray-300">meta (JSON)</div>
                        <pre class="mt-2 text-xs p-3 bg-black/20 rounded text-gray-100 overflow-auto" x-text="prettyJson(selected.meta)"></pre>
                    </div>
                </div>
            </div>
        </div>

    </div> {{-- end alpine root --}}

    <script>
        /* ---------------- starfield (canvas) ---------------- */
        (function starfield() {
            const canvas = document.getElementById('starfield');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            let w = 0,
                h = 0,
                stars = [];

            function resize() {
                w = canvas.width = innerWidth;
                h = canvas.height = innerHeight;
                initStars();
            }

            function initStars() {
                stars = [];
                const density = Math.max(80, Math.floor((w * h) / 90000));
                for (let i = 0; i < density; i++) {
                    stars.push({
                        x: Math.random() * w,
                        y: Math.random() * h,
                        r: Math.random() * 1.6,
                        alpha: 0.2 + Math.random() * 0.9,
                        dx: (Math.random() - 0.5) * 0.2,
                        dy: (Math.random() - 0.5) * 0.2
                    });
                }
            }

            function update() {
                ctx.clearRect(0, 0, w, h);
                const g = ctx.createRadialGradient(w * 0.2, h * 0.2, 10, w * 0.8, h * 0.8, Math.max(w, h));
                g.addColorStop(0, 'rgba(127,90,240,0.02)');
                g.addColorStop(1, 'transparent');
                ctx.fillStyle = g;
                ctx.fillRect(0, 0, w, h);

                for (const s of stars) {
                    s.x += s.dx;
                    s.y += s.dy;
                    if (s.x < 0) s.x = w;
                    if (s.x > w) s.x = 0;
                    if (s.y < 0) s.y = h;
                    if (s.y > h) s.y = 0;
                    s.alpha += (Math.random() - 0.5) * 0.02;
                    s.alpha = Math.max(0.1, Math.min(1, s.alpha));
                    ctx.beginPath();
                    ctx.globalAlpha = s.alpha * 0.9;
                    ctx.fillStyle = '#fff';
                    ctx.arc(s.x, s.y, s.r, 0, Math.PI * 2);
                    ctx.fill();
                }
                ctx.globalAlpha = 1;
                requestAnimationFrame(update);
            }
            addEventListener('resize', resize);
            resize();
            update();
        })();

        /* -------------- Alpine app -------------- */
        function systemActionsDemo() {
            return {
                // state
                view: 'timeline',
                query: '',
                selectedChips: [],
                visibleChips: ['login', 'logout', 'sale_process', 'add_expense', 'start_session', 'session_checkout'],
                dateFrom: '',
                dateTo: '',
                realtimeToast: '',
                detailOpen: false,
                selected: {},

                // headline typing
                phrases: [
                    '— بحث فائق، فلتر سريع، وعرض أنيق.',
                    '— عرض Live وTimeline تفاعلي.',
                    '— افتح أي حدث واعرف كل التفاصيل.'
                ],
                typed: '',
                subtitle: 'راقب كل الـ system actions',
                _typeIndex: 0,
                _charIndex: 0,
                _typing: true,

                // data + pagination
                all: [],
                meta: {
                    total: 0,
                    per_page: 20,
                    current_page: 1,
                    last_page: 0
                },
                loading: false,
                error: null,

                /* computed (client-side final filtering is minimal; main filtering by API) */
                get filtered() {
                    // The API already filters/paginates. This getter only applies small client-side search fallback
                    let q = this.query?.toString().toLowerCase().trim();
                    let arr = this.all.slice();

                    if (this.selectedChips.length) arr = arr.filter(a => this.selectedChips.includes(a.action));
                    if (this.dateFrom) arr = arr.filter(a => a.created_at >= this.dateFrom);
                    if (this.dateTo) arr = arr.filter(a => a.created_at <= (this.dateTo + ' 23:59:59'));

                    if (q) {
                        arr = arr.filter(a => {
                            if (a.id && a.id.toString().includes(q)) return true;
                            if (a.action && a.action.toLowerCase().includes(q)) return true;
                            if (a.user?.name && a.user.name.toLowerCase().includes(q)) return true;
                            if (a.note && a.note.toLowerCase().includes(q)) return true;
                            if (a.ip && a.ip.toLowerCase().includes(q)) return true;
                            if (a.amount && a.amount.toString().includes(q)) return true;
                            if (a.meta && JSON.stringify(a.meta).toLowerCase().includes(q)) return true;
                            return false;
                        });
                    }
                    return arr.sort((x, y) => y.id - x.id);
                },

                /* helpers */
                humanize(key) {
                    const map = {
                        login: 'تسجيل دخول',
                        logout: 'تسجيل خروج',
                        sale_process: 'معاملة بيع',
                        add_expense: 'اضافة مصروف',
                        start_session: 'بداية جلسة',
                        session_checkout: 'انهاء جلسة',
                        add_session_punchment: 'اضافة مشتريات جلسه'
                    };
                    return map[key] ?? key.replaceAll('_', ' ');
                },
                shortNote(n) {
                    if (!n) return '';
                    return n.length > 100 ? n.slice(0, 100) + '...' : n;
                },
                prettyJson(j) {
                    try {
                        return JSON.stringify(j, null, 2)
                    } catch (e) {
                        return j
                    }
                },
                formatMetaLine(a) {
                    let parts = [];
                    if (a.user?.name) parts.push('بواسطة: ' + a.user.name);
                    if (a.amount) parts.push(a.amount + ' EGP');
                    if (a.created_at) parts.push(a.created_at);
                    return parts.join(' · ');
                },
                dotColor(action) {
                    if (!action) return 'timeline-dot bg-gray-400';
                    if (action.includes('sale')) return 'timeline-dot bg-green-400';
                    if (action.includes('expense')) return 'timeline-dot bg-red-400';
                    if (action.includes('session')) return 'timeline-dot bg-yellow-400';
                    return 'timeline-dot bg-gray-400';
                },

                /* ====== API fetching & pagination ====== */
                async fetchActions(page = 1) {
                    this.loading = true;
                    this.error = null;

                    try {
                        const params = new URLSearchParams();

                        // ensure numeric page & per_page to avoid 422
                        const pageNum = Number(page) || 1;
                        params.append('page', pageNum);

                        const per = Number(this.meta.per_page) || 20;
                        params.append('per_page', per);

                        if (this.query && String(this.query).trim() !== '') params.append('q', String(this.query)
                            .trim());

                        // send action filter only if present
                        if (this.selectedChips && this.selectedChips.length) {
                            // append action[] multiple times so backend gets an array
                            this.selectedChips.forEach(a => params.append('action[]', a));
                        }


                        if (this.dateFrom) params.append('date_from', this.dateFrom);
                        if (this.dateTo) params.append('date_to', this.dateTo);

                        const url = `/api/system-actions?${params.toString()}`;

                        const res = await fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            credentials: 'same-origin' // important if route requires session auth (breeze)
                        });

                        if (!res.ok) {
                            // try parse JSON error, else text
                            let txt;
                            try {
                                txt = await res.json();
                                txt = JSON.stringify(txt);
                            } catch (e) {
                                txt = await res.text();
                            }
                            throw new Error(`HTTP ${res.status} — ${txt}`);
                        }

                        const json = await res.json();

                        // map items defensively
                        this.all = (json.data || []).map(d => ({
                            id: d.id,
                            action: d.action,
                            created_at: d.created_at,
                            user: d.user ?? {
                                id: null,
                                name: '-'
                            },
                            amount: d.amount,
                            note: d.note,
                            ip: d.ip,
                            meta: d.meta,
                        }));

                        // update pagination meta (robust)
                        if (json.meta) {
                            this.meta = {
                                total: Number(json.meta.total ?? json.total ?? 0),
                                per_page: Number(json.meta.per_page ?? json.per_page ?? per),
                                current_page: Number(json.meta.current_page ?? json.current_page ?? pageNum),
                                last_page: Number(json.meta.last_page ?? json.last_page ?? 0),
                            };
                        } else {
                            this.meta = {
                                total: Number(json.total ?? 0),
                                per_page: Number(json.per_page ?? per),
                                current_page: Number(json.current_page ?? pageNum),
                                last_page: Number(json.last_page ?? 0)
                            };
                        }

                    } catch (err) {
                        console.error('fetchActions error', err);
                        // show friendly message (server validation error included)
                        this.error = err.message || 'خطأ أثناء جلب البيانات';
                        this.all = [];
                        this.meta = {
                            total: 0,
                            per_page: this.meta.per_page || 20,
                            current_page: 1,
                            last_page: 0
                        };
                    } finally {
                        this.loading = false;
                    }
                },

                goToPage(page) {
                    if (!page || page < 1) return;
                    this.fetchActions(page);
                },

                pagesToShow() {
                    const total = this.meta.last_page || 0;
                    if (total <= 7) return Array.from({
                        length: total
                    }, (_, i) => i + 1);
                    const half = 3;
                    let start = Math.max(1, this.meta.current_page - half);
                    if (start + 6 > total) start = Math.max(1, total - 6);
                    return Array.from({
                        length: 7
                    }, (_, i) => start + i);
                },

                applyFilters() {
                    // when search / date / chips change we reload page 1 from API
                    this.fetchActions(1);
                },

                toggleChip(t) {
                    if (this.selectedChips.includes(t)) this.selectedChips = this.selectedChips.filter(x => x !== t);
                    else this.selectedChips.push(t);
                    this.applyFilters();
                },

                clearFilters() {
                    this.query = '';
                    this.selectedChips = [];
                    this.dateFrom = '';
                    this.dateTo = '';
                    this.applyFilters();
                },

                openDetails(act) {
                    this.selected = JSON.parse(JSON.stringify(act));
                    this.detailOpen = true;
                },

                /* typing loop with small delays (non-blocking) */
                _typeLoop() {
                    const phrase = this.phrases[this._typeIndex % this.phrases.length];
                    if (this._typing) {
                        if (this._charIndex < phrase.length) {
                            this._charIndex++;
                            this.typed = phrase.slice(0, this._charIndex);
                            setTimeout(() => this._typeLoop(), 40 + Math.random() * 40);
                        } else {
                            this._typing = false;
                            setTimeout(() => this._typeLoop(), 900 + Math.random() * 400);
                        }
                    } else {
                        if (this._charIndex > 0) {
                            this._charIndex--;
                            this.typed = phrase.slice(0, this._charIndex);
                            setTimeout(() => this._typeLoop(), 25 + Math.random() * 30);
                        } else {
                            this._typing = true;
                            this._typeIndex++;
                            setTimeout(() => this._typeLoop(), 250);
                        }
                    }
                },

                init() {
                    // 1) Compute safe reserved width for the typewrite box (in ch)
                    try {
                        const phrases = this.phrases || [];
                        let maxLen = 0;
                        for (const p of phrases) {
                            const len = String(p).length;
                            if (len > maxLen) maxLen = len;
                        }
                        if (maxLen < 10) maxLen = 10;
                        if (maxLen > 60) maxLen = 60; // safety cap
                        document.documentElement.style.setProperty('--typewrite-width', maxLen + 'ch');

                        // 2) Mobile behavior: on narrow screens disable typing loop and show first phrase statically
                        const isNarrow = window.innerWidth <= 880;
                        const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)')
                            .matches;

                        if (isNarrow || prefersReduced) {
                            // show first phrase and don't start the typing loop (prevents any layout jitter)
                            this.typed = this.phrases && this.phrases.length ? this.phrases[0] : '';
                            this._typing = false;
                            // hide caret via CSS var for small screens (CSS media query already hides it)
                        } else {
                            // start typing headline on larger screens
                            this._typeLoop();
                        }
                    } catch (e) {
                        // fallback: if anything fails, start typing loop (original behavior)
                        try {
                            this._typeLoop();
                        } catch (e2) {
                            /* ignore */
                        }
                    }

                    // load first page from API
                    this.fetchActions(1);

                    // note: no live pushes by default (you said no websockets)
                }
            };
        }

        /* reduced motion respect (no-op: CSS handles it) */
        (function respectReducedMotion() {
            if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                // CSS @media already handles it
            }
        })();
    </script>

</body>

</html>
