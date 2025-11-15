{{-- resources/views/auth/login-fancy.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>تسجيل الدخول</title>

  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    /* ====== base layout tweaks ====== */
    :root{
      --beige-1: #F8E0C1;
      --beige-2: #D9B1AB;
      --glass: rgba(255,255,255,0.06);
      --card-bg: rgba(255,255,255,0.03);
      --accent: linear-gradient(90deg,var(--beige-1),var(--beige-2));
    }
    html,body{height:100%}
    body{
      background: radial-gradient(1200px 600px at 10% 10%, rgba(217,177,171,0.06), transparent 10%),
                  radial-gradient(1000px 500px at 90% 90%, rgba(248,224,193,0.04), transparent 8%),
                  #0b0b0c; /* deep black */
      font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      -webkit-font-smoothing:antialiased;
      -moz-osx-font-smoothing:grayscale;
      color: #e9edf0;
      overflow-x:hidden;
    }

    /* container */
    .login-wrap{
      min-height:100vh;
      display:flex;
      align-items:center;
      justify-content:center;
      padding:3rem 1rem;
      position:relative;
      gap:2rem;
    }

    /* two-column card */
    .card {
      width:100%;
      max-width:1100px;
      border-radius:18px;
      overflow:hidden;
      display:grid;
      grid-template-columns: 520px 1fr;
      box-shadow: 0 10px 40px rgba(0,0,0,0.6), inset 0 1px 0 rgba(255,255,255,0.02);
      backdrop-filter: blur(6px) saturate(110%);
      border: 1px solid rgba(255,255,255,0.04);
    }

    /* mobile adjustments */
    @media (max-width: 880px){
      .card{grid-template-columns:1fr; border-radius:14px;}
      .left-hero{order:2}
      .form-area{order:1;padding:2rem}
    }

    /* left hero (logo + headline + visuals) */
    .left-hero{
      padding:48px;
      background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
      position:relative;
      overflow:hidden;
      display:flex;
      flex-direction:column;
      justify-content:center;
      gap:18px;
    }
    .logo-wrap{
      display:flex;
      align-items:center;
      gap:14px;
    }
    .logo-wrap img{
      width:72px;
      height:72px;
      object-fit:cover;
      border-radius:12px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.6), 0 1px 0 rgba(255,255,255,0.02) inset;
      transform:translateZ(0);
      animation: logoFloat 6s ease-in-out infinite;
    }
    @keyframes logoFloat{
      0%{transform:translateY(0) rotate(-2deg)}
      50%{transform:translateY(-6px) rotate(2deg)}
      100%{transform:translateY(0) rotate(-2deg)}
    }

    .hero-title{
      font-weight:700;
      font-size:34px;
      line-height:1;
      background: var(--accent);
      -webkit-background-clip:text;
      -webkit-text-fill-color:transparent;
      letter-spacing: -0.5px;
    }
    .hero-sub{
      color: rgba(233,237,240,0.78);
      font-size:14px;
      max-width:380px;
    }

    /* animated glossy stripe */
    .shine{
      position:absolute;
      left:-30%;
      top:-20%;
      width:160%;
      height:200%;
      background: linear-gradient(120deg, rgba(255,255,255,0.04) 0%, rgba(255,255,255,0.06) 20%, rgba(255,255,255,0.0) 40%);
      transform: rotate(-12deg);
      animation: slowShift 10s linear infinite;
      pointer-events:none;
    }
    @keyframes slowShift{
      0%{transform:translateX(-30%) rotate(-12deg)}
      50%{transform:translateX(0%) rotate(-12deg)}
      100%{transform:translateX(30%) rotate(-12deg)}
    }

    /* decorative dots */
    .dots { position:absolute; inset:0; pointer-events:none; }
    .dot {
      position:absolute;
      width:8px;height:8px;border-radius:999px;
      background:rgba(217,177,171,0.14);
      box-shadow: 0 0 18px rgba(217,177,171,0.06);
      animation: floatDot 8s ease-in-out infinite;
    }
    @keyframes floatDot{
      0%{transform:translateY(0) translateX(0) scale(1)}
      50%{transform:translateY(-18px) translateX(6px) scale(1.15)}
      100%{transform:translateY(0) translateX(0) scale(1)}
    }

    /* form area */
    .form-area{
      padding:42px;
      background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
      display:flex;
      flex-direction:column;
      gap:18px;
      justify-content:center;
    }
    .glass-card{
      background: linear-gradient(180deg, rgba(255,255,255,0.018), rgba(255,255,255,0.01));
      border-radius:12px;
      padding:20px;
      border:1px solid rgba(255,255,255,0.035);
      box-shadow: 0 8px 30px rgba(2,6,23,0.6);
    }

    label{display:block; font-size:13px; color:rgba(233,237,240,0.78); margin-bottom:6px}
    input[type="email"], input[type="password"]{
      width:100%;
      padding:12px 14px;
      border-radius:10px;
      background: rgba(255,255,255,0.03);
      border: 1px solid rgba(255,255,255,0.04);
      color:#e9edf0;
      outline:none;
      transition: box-shadow .18s ease, transform .12s ease, border-color .12s ease;
    }
    input:focus{
      box-shadow: 0 6px 30px rgba(217,177,171,0.08), 0 0 0 3px rgba(217,177,171,0.06);
      transform: translateY(-1px);
      border-color: rgba(217,177,171,0.26);
    }
    .muted { font-size:13px; color:rgba(233,237,240,0.6) }

    /* fancy button */
    .btn-primary{
      display:inline-flex;
      align-items:center;
      gap:10px;
      padding:12px 18px;
      border-radius:12px;
      font-weight:700;
      cursor:pointer;
      border:none;
      background:var(--accent);
      color:#111;
      box-shadow: 0 8px 30px rgba(217,177,171,0.12);
      transition: transform .12s ease, box-shadow .12s ease;
      position:relative;
      overflow:hidden;
    }
    .btn-primary:hover{ transform:translateY(-3px); box-shadow: 0 18px 40px rgba(217,177,171,0.18) }
    .btn-primary:active{ transform:translateY(-1px) }

    /* small gloss line on button */
    .btn-primary::after{
      content:"";
      position:absolute;
      top:-40%;
      left:-10%;
      width:120%;
      height:40%;
      background:linear-gradient(90deg, rgba(255,255,255,0.28), rgba(255,255,255,0.06), rgba(255,255,255,0));
      transform:rotate(-20deg);
      animation: btnShine 3s linear infinite;
      pointer-events:none;
      opacity:.7;
    }
    @keyframes btnShine{ 0%{transform:translateX(-100%) rotate(-20deg)} 100%{transform:translateX(200%) rotate(-20deg)} }

    /* footer link row */
    .links-row{ display:flex; gap:12px; align-items:center; flex-wrap:wrap; }
    .link-muted{ color:rgba(233,237,240,0.6); font-size:13px; text-decoration:none }

    /* small responsive tweaks */
    @media (max-width:480px){
      .hero-title{ font-size:28px }
      .left-hero{ padding:22px }
      .form-area{ padding:20px }
      .logo-wrap img{ width:58px;height:58px }
      .card{ box-shadow: 0 8px 30px rgba(0,0,0,0.7) }
    }

    /* respect reduced motion */
    @media (prefers-reduced-motion: reduce){
      .shine, .dot, .logo-wrap img, .btn-primary::after{ animation:none; }
    }
  </style>
</head>
<body>
  <div class="login-wrap">
    <div class="card">

      {{-- LEFT HERO --}}
      <div class="left-hero">
        <div class="logo-wrap" style="align-items:center">
          <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRKV4y9snY_3mPxdOqtcAngk1wiaw_qc0HkCQ&s" alt="logo">
          <div>
            <div class="hero-title">X Space</div>
            <div class="hero-sub">تصميم أنيق، حركة سلسة وتجربة مستخدم محترفة على جميع الشاشات</div>
          </div>
        </div>

        <div style="height:18px"></div>

        <div class="glass-card muted">
          <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
            <div style="flex:1">
              <div style="font-weight:700; font-size:13px; color:rgba(233,237,240,0.9)">مزج ألوان أسود — أبيض — بيج</div>
              <div style="font-size:12px; color:rgba(233,237,240,0.6)">تأثيرات زجاجية ولمعة خفيفة</div>
            </div>
            <div style="display:flex; gap:8px">
              <div style="width:14px; height:14px; border-radius:6px; background:var(--beige-1)"></div>
              <div style="width:14px; height:14px; border-radius:6px; background:var(--beige-2)"></div>
              <div style="width:14px; height:14px; border-radius:6px; background:#fff"></div>
            </div>
          </div>
        </div>

        <div class="shine" aria-hidden="true"></div>

        {{-- decorative dots (random positions by inline style) --}}
        <div class="dots" aria-hidden="true">
          <div class="dot" style="left:10%; top:12%; width:6px;height:6px; animation-delay:-1s"></div>
          <div class="dot" style="left:24%; top:66%; width:10px;height:10px; background:rgba(248,224,193,0.06); animation-delay:-2s"></div>
          <div class="dot" style="left:80%; top:24%; width:8px;height:8px; animation-delay:-3s"></div>
          <div class="dot" style="left:68%; top:78%; width:6px;height:6px; animation-delay:-4s"></div>
        </div>
      </div>

      {{-- FORM AREA --}}
      <div class="form-area">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap">
          <div style="display:flex; gap:12px; align-items:center">
            <h2 style="margin:0; font-size:20px; font-weight:700">تسجيل دخول</h2>
            <div class="muted" style="font-size:13px">أدخل بياناتك</div>
          </div>
        </div>

        <div class="glass-card">
          {{-- Session Status --}}
          <x-auth-session-status class="mb-4" :status="session('status')" />

          <form method="POST" action="{{ route('login') }}" novalidate>
            @csrf

            <div style="margin-bottom:14px">
              <label for="email">البريد الإلكتروني</label>
              <input id="email" class="@error('email') border-red-400 @enderror" type="email" name="email" :value="old('email')" required autofocus autocomplete="username">
              <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div style="margin-bottom:8px">
              <label for="password">كلمة المرور</label>
              <input id="password" type="password" name="password" required autocomplete="current-password">
              <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; margin-top:8px; margin-bottom:18px">
              <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" name="remember" style="width:18px;height:18px;border-radius:6px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.04)">
                <span style="margin-inline-start:8px; color:rgba(233,237,240,0.7); font-size:13px">تذكرني</span>
              </label>

              @if (Route::has('password.request'))
                <a class="link-muted" href="{{ route('password.request') }}">نسيت كلمة السر؟</a>
              @endif
            </div>

            <div style="display:flex; gap:12px; align-items:center; justify-content:flex-end">
              <button type="submit" class="btn-primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <path d="M5 12h14M12 5l7 7-7 7" stroke="#111" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                تسجيل الدخول
              </button>
            </div>
          </form>
        </div>

        <div style="display:flex; justify-content:center; margin-top:12px">
          <div class="muted" style="font-size:13px">نسخة آمنة — تجربة سريعة على الموبايل والديسكتوب</div>
        </div>
      </div>

    </div>
  </div>

  {{-- Optional: small JS to sprinkle subtle parallax on mouse move for desktop --}}
  <script>
    (function(){
      const left = document.querySelector('.left-hero');
      if(!left) return;
      let active = false;
      function move(evt){
        if(window.matchMedia('(pointer: coarse)').matches) return; // no parallax on touch
        const rect = left.getBoundingClientRect();
        const cx = rect.left + rect.width/2;
        const cy = rect.top + rect.height/2;
        const dx = (evt.clientX - cx)/rect.width;
        const dy = (evt.clientY - cy)/rect.height;
        left.style.transform = `translate3d(${dx*6}px, ${dy*6}px, 0)`;
      }
      window.addEventListener('mousemove', move);
      // cleanup on unload (not strictly necessary here)
    })();
  </script>
</body>
</html>
