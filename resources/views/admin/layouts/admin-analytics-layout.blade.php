{{-- resources/views/admin/layouts/admin-analytics-layout.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>@yield('title','Admin')</title>

  {{-- Tailwind via Vite (لو عندك) --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  {{-- Chart.js CDN (لو مش مركب عبر NPM) --}}
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    :root{
      --bg: #ffffff;
      --text: #0b0b0c;
      --muted: rgba(11,11,12,0.6);
      --prime: #E6C7FF;
      --card-bg: #ffffff;
      --glass: rgba(230,199,255,0.08);
      --radius: 12px;
      --shadow-sm: 0 6px 20px rgba(11,11,12,0.06);
    }

    /* global */
    html,body{height:100%}
    body{
      margin:0;
      font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      background: var(--bg);
      color: var(--text);
      -webkit-font-smoothing:antialiased;
      -moz-osx-font-smoothing:grayscale;
    }

    /* layout */
    .admin-root{min-height:100vh; display:flex; flex-direction:column;}
    .topbar{
      height:64px;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:12px;
      padding:0 20px;
      border-bottom: 1px solid rgba(11,11,12,0.04);
      background: linear-gradient(180deg, rgba(230,199,255,0.03), transparent);
      position:sticky; top:0; z-index:60;
    }
    .brand { display:flex; align-items:center; gap:12px; }
    .brand .logo{width:44px;height:44px;border-radius:10px; overflow:hidden; box-shadow:var(--shadow-sm); }
    .brand h1{font-size:18px;margin:0;font-weight:700; color:var(--text);}

    .main-body{display:flex; gap:20px; padding:22px; width:100%; box-sizing:border-box;}
    /* sidebar */
    .sidebar{
      width:260px;
      border-radius:var(--radius);
      background:var(--card-bg);
      box-shadow:var(--shadow-sm);
      padding:12px;
      flex-shrink:0;
      height: calc(100vh - 128px);
      overflow:auto;
    }
    .sidebar .nav-item{
      display:flex; align-items:center; gap:12px; padding:10px; border-radius:10px;
      color:var(--muted); text-decoration:none; margin-bottom:6px;
      transition: background .15s cubic-bezier(.2,.9,.2,1), transform .12s;
    }
    .sidebar .nav-item:hover{ background: linear-gradient(90deg, rgba(230,199,255,0.06), rgba(230,199,255,0.03)); transform: translateY(-2px); color: var(--text); }
    .sidebar .nav-item.active{ border-right: 4px solid var(--prime); background: linear-gradient(90deg, rgba(230,199,255,0.08), rgba(230,199,255,0.03)); color:var(--text); font-weight:600; }

    /* content */
    .content{
      flex:1;
      min-height: calc(100vh - 128px);
    }

    /* cards */
    .card{
      background: var(--card-bg);
      border-radius:12px;
      padding:16px;
      box-shadow: var(--shadow-sm);
      border:1px solid rgba(11,11,12,0.03);
    }

    /* metric */
    .metric { display:flex; align-items:center; justify-content:space-between; gap:12px; }
    .metric .val { font-size:1.6rem; font-weight:800; color:var(--text); }
    .metric .title{ font-size:0.9rem; color:var(--muted); }

    /* shimmer skeleton */
    .skeleton{ background: linear-gradient(90deg, rgba(11,11,12,0.03) 0%, rgba(11,11,12,0.06) 50%, rgba(11,11,12,0.03) 100%); background-size:200% 100%; animation: shimmer 1.6s linear infinite; border-radius:6px; }
    @keyframes shimmer{ 0%{ background-position:200% 0 } 100%{ background-position:-200% 0 } }

    /* responsive */
    @media (max-width: 980px){
      .sidebar{ display:none; } /* hide sidebar on tablet/mobile */
      .main-body{ padding:14px; }
    }

    /* navbar icons small */
    .nav-icon{ width:20px; height:20px; display:inline-block; }

    /* micro interaction for sidebar badges */
    .badge{
      background: linear-gradient(90deg, var(--prime), #C7A6FF);
      color:#111; font-weight:700; padding:6px 8px; border-radius:999px; font-size:0.8rem;
      box-shadow: 0 6px 18px rgba(230,199,255,0.12);
    }

    /* link reset */
    a { color: inherit; text-decoration:none; }

  </style>
</head>
<body>
  <div class="admin-root">
    {{-- TOP BAR --}}
    <div class="topbar">
      <div class="brand">
        <div class="logo">
          <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRKV4y9snY_3mPxdOqtcAngk1wiaw_qc0HkCQ&s" alt="logo" style="width:100%;height:100%;object-fit:cover;">
        </div>
        <h1>Analytics • X Space</h1>
      </div>

      <div style="display:flex;align-items:center;gap:12px">
        <div class="muted" style="color:var(--muted);font-size:13px">ثيم أبيض • prime <span style="color:var(--prime); font-weight:700">#E6C7FF</span></div>
        <div style="display:flex;gap:8px;align-items:center">
          <a href="#" title="Notifications" style="padding:8px;border-radius:8px"><svg class="nav-icon" viewBox="0 0 24 24" fill="none"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118.5 14.5V11a6.5 6.5 0 10-13 0v3.5c0 .538-.214 1.055-.595 1.445L3 17h5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
          <a href="#" title="Profile" style="padding:8px;border-radius:8px"><svg class="nav-icon" viewBox="0 0 24 24" fill="none"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="1.4"/></svg></a>
        </div>
      </div>
    </div>

    {{-- MAIN --}}
    <div class="main-body">
      {{-- SIDEBAR (desktop) --}}
      <aside class="sidebar" aria-label="Analytics navigation">
        @php
          $nav = [
            ['name'=>'نظرة عامة','route'=>'admin.analytics.index','icon'=>'M3 12h18'],
            ['name'=>'مالي','route'=>'admin.analytics.financial','icon'=>'M12 8v8'],
            ['name'=>'القاعات','route'=>'admin.analytics.halls','icon'=>'M4 6h16v12H4z'],
            ['name'=>'الحجوزات','route'=>'admin.analytics.bookings','icon'=>'M3 7h18'],
            ['name'=>'الاشتراكات','route'=>'admin.analytics.subscriptions','icon'=>'M12 2l3 7h7l-5.5 4 2 7L12 16l-6.5 4 2-7L2 9h7L12 2z'],
            ['name'=>'الجلسات','route'=>'admin.analytics.sessions','icon'=>'M5 12h14'],
            ['name'=>'المخزن','route'=>'admin.analytics.inventory','icon'=>'M3 3h18v4H3z'],
            ['name'=>'العملاء','route'=>'admin.analytics.customers','icon'=>'M12 12a5 5 0 100-10 5 5 0 000 10z'],
          ];
          $current = Route::currentRouteName();
        @endphp

        <nav style="display:flex;flex-direction:column;">
          @foreach($nav as $item)
            @php $isActive = $current === $item['route']; @endphp
            <a href="{{ route($item['route']) }}" class="nav-item {{ $isActive ? 'active' : '' }}" aria-current="{{ $isActive ? 'page' : '' }}">
              {{-- simple svg placeholder --}}
              <span style="width:34px; height:34px; display:grid; place-items:center; border-radius:8px; background: {{ $isActive ? 'linear-gradient(90deg,var(--prime),#C7A6FF)' : 'transparent' }};">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="{{ $item['icon'] }}" stroke="{{ $isActive ? '#111' : 'rgba(11,11,12,0.45)' }}" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </span>
              <div style="flex:1; display:flex; justify-content:space-between; align-items:center;">
                <span>{{ $item['name'] }}</span>
                @if($item['route']=='admin.analytics.inventory' && isset($summary['low_stock_count']) && $summary['low_stock_count']>0)
                  <span class="badge">{{ $summary['low_stock_count'] }}</span>
                @endif
              </div>
            </a>
          @endforeach
        </nav>

        {{-- Filters collapsible (mobile toggled) --}}
        <div style="margin-top:12px" class="card">
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <div style="font-weight:700">الفلاتر</div>
            <div style="font-size:12px;color:var(--muted)">Quick</div>
          </div>
          <div style="margin-top:10px; display:flex; flex-direction:column; gap:8px">
            <input type="date" id="from" class="skeleton" style="padding:8px;border-radius:8px;border:none;"/>
            <input type="date" id="to" class="skeleton" style="padding:8px;border-radius:8px;border:none;"/>
            <select id="hallSelect" class="skeleton" style="padding:8px;border-radius:8px;border:none;">
              <option value="">كل القاعات</option>
            </select>
            <button id="applyFilters" class="card" style="cursor:pointer; text-align:center; background:linear-gradient(90deg,var(--prime),#C7A6FF); color:#111; font-weight:700;">تطبيق</button>
          </div>
        </div>
      </aside>

      {{-- CONTENT --}}
      <section class="content">
        <div style="display:flex; gap:12px; align-items:center; margin-bottom:16px;">
          <div style="flex:1">
            <h2 style="margin:0; font-size:20px; font-weight:800">@yield('title','لوحة التحليلات')</h2>
            <div style="color:var(--muted); font-size:13px; margin-top:6px">نظرة سريعة — بيانات حقيقية & تجربة سلسة</div>
          </div>

          <div style="display:flex; gap:8px;">
            <button id="refreshBtn" class="card" style="padding:8px 12px; cursor:pointer;">⟳ تحديث</button>
            <a href="#" class="card" style="padding:8px 12px; display:flex; align-items:center; gap:8px;">📥 تصدير</a>
          </div>
        </div>

        {{-- slot content --}}
        <div>
          @yield('content')
        </div>

      </section>
    </div> {{-- end main-body --}}

    <footer style="padding:18px; text-align:center; color:var(--muted); border-top:1px solid rgba(11,11,12,0.03)">
      © {{ date('Y') }} X Space — Analytics. Performance tips: cache (30-300s), pre-agg job, queue heavy jobs.
    </footer>

  </div>

  {{-- Simple JS: fetch summary and populate small dynamic bits and charts placeholders --}}
  <script>
    (function(){
      const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

      async function fetchSummary(){
        try {
          const res = await fetch("{{ route('admin.analytics.api.summary') }}", {
            headers: { 'X-CSRF-TOKEN': token, 'Accept':'application/json' },
            credentials: 'same-origin'
          });
          if(!res.ok) throw new Error('fetch failed');
          const json = await res.json();
          return json;
        } catch(err){
          console.warn('summary fetch error', err);
          return null;
        }
      }

      async function render(){
        const data = await fetchSummary();
        // populate summary badges in sidebar
        if(data && data.summary){
          // small graceful DOM updates: update badge element if present
          const badge = document.querySelector('.badge');
          if(badge && data.summary.low_stock_count !== undefined){
            badge.textContent = data.summary.low_stock_count;
            badge.style.display = data.summary.low_stock_count ? 'inline-block' : 'none';
          }
        }
        // placeholder: if you want to init charts on the page, map data.trends -> Chart.js
        try {
          const ctx = document.getElementById('chart-bookings');
          if(ctx && data && data.trends){
            const labels = Object.keys(data.trends);
            const values = Object.values(data.trends);
            new Chart(ctx, {
              type: 'line',
              data: { labels, datasets:[{ label:'حجوزات', data: values, fill:true, tension:0.32, borderWidth:2, pointRadius:0 }]},
              options: { responsive:true, plugins:{ legend:{display:false} }, scales:{ x:{display:false}, y:{display:false} } }
            });
          }
        } catch(e){ console.warn(e) }
      }

      document.getElementById('refreshBtn').addEventListener('click', ()=>{ render(); });
      // init
      render();

      // small progressive enhancement: re-fetch when becoming visible
      document.addEventListener('visibilitychange', ()=>{ if(document.visibilityState==='visible') render(); });
    })();
  </script>
</body>
</html>
