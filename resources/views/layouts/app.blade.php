<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

    <title>@yield('page_title')</title>
    <style>
        /* drawer profile */
        .drawer {
            padding: 18px;
            width: 260px;
            box-sizing: border-box;
        }

        .drawer-profile {
            display: flex;
            gap: 12px;
            align-items: center;
            padding: 6px 4px;
        }

        .avatar-wrap {
            position: relative;
            width: 64px;
            height: 64px;
            flex: 0 0 64px;
        }

        .avatar {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: linear-gradient(180deg, #fff, #f6f6f6);
            border: 1px solid rgba(0, 0, 0, 0.06);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 20px;
            color: #333;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
        }

        /* الشارة الصغيرة (badge) فوق الدائرة */
        .role-badge {
            position: absolute;
            right: -6px;
            bottom: -6px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-grid;
            place-items: center;
            background: #fff;
            border: 1px solid rgba(0, 0, 0, 0.06);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.08);
            color: #fff;
        }

        /* ستايلات لكل دور */
        .role-admin {
            background: linear-gradient(180deg, #ffb86b, #ff8a6b);
            color: #fff;
        }

        .role-staff {
            background: linear-gradient(180deg, #7cc7ff, #4a9eff);
            color: #fff;
        }

        .profile-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 0;
        }

        .profile-name {
            font-weight: 800;
            font-size: 15px;
            color: #222;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .profile-role {
            font-size: 13px;
            color: #666;
        }

        /* فاصل وخيارات */
        .drawer-sep {
            margin: 12px 0;
            border: none;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(0, 0, 0, 0.06), transparent);
        }

        .drawer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .drawer-links li {
            margin: 6px 0;
        }

        .drawer-links a {
            display: inline-block;
            color: #333;
            text-decoration: none;
            font-weight: 600;
            padding: 8px 6px;
            border-radius: 8px;
            transition: background .18s ease, transform .12s ease;
        }

        .drawer-links a:hover {
            background: rgba(0, 0, 0, 0.04);
            transform: translateX(4px);
        }

        /* responsive: أصغر avatar للموبايل */
        @media (max-width: 576px) {
            .avatar-wrap {
                width: 52px;
                height: 52px;
            }

            .avatar {
                width: 52px;
                height: 52px;
                font-size: 16px;
            }

            .role-badge {
                width: 26px;
                height: 26px;
                right: -6px;
                bottom: -6px;
            }

            .drawer {
                width: 220px;
                padding: 12px;
            }
        }

        .back-btn {
            position: fixed;
            /* 👈 بدل absolute */
            top: 20px;
            left: 20px;
            background: #e5c6c3;
            border-radius: 50%;
            padding: 13px;
            cursor: pointer;
            transition: transform 0.2s;
            z-index: 2000;
        }

        .back-btn:hover {
            transform: scale(1.1) rotate(-10deg);
        }

        /* Snackbar style */
        .snackbar {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #333;
            color: #fff;
            padding: 12px 18px;
            border-radius: 10px;
            font-size: 14px;
            z-index: 9999;
            opacity: 0;
            transform: translateX(120%);
            transition: opacity 0.4s ease, transform 0.4s ease;
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
    </style>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

</head>

<body>

    @include('layouts.navigation')
    {{-- ✅ تنبيهات Toast باستخدام Snackbar --}}
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
    @yield('content')
    <!-- Bootstrap JS Bundle -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Drawer -->
    @auth
        <div class="drawer" id="drawer">
            <div class="drawer-profile">
                @php
                    $user = auth()->user();
                    // نحاول نقرأ الصلاحية بأكثر من طريقة (حقل role أو method hasRole)
                    $role = $user->role ?? null;
                    if (!$role && method_exists($user, 'hasRole')) {
                        $role = $user->hasRole('admin') ? 'ادمن' : 'موظف';
                    }
                    $role = $role
                        ? (strtolower($role) === 'admin' || strtolower($role) === 'أدمن'
                            ? 'ادمن'
                            : 'موظف')
                        : 'موظف';
                    $initials = trim(
                        collect(explode(' ', $user->name))
                            ->map(fn($w) => mb_substr($w, 0, 1))
                            ->take(2)
                            ->join(''),
                    );
                @endphp

                <div class="avatar-wrap" aria-hidden="true">
                    <div class="avatar" title="{{ $user->name }}">
                        {{-- الأحرف داخل الدائرة --}}
                        {{ $initials ?: mb_substr($user->name, 0, 1) }}
                    </div>

                    {{-- شارة الصلاحية --}}
                    <div class="role-badge role-{{ $role === 'ادمن' ? 'admin' : 'staff' }}"
                        title="الصلاحية: {{ $role }}">
                        @if ($role === 'ادمن')
                            {{-- تاج للأدمن --}}
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
                                stroke-linejoin="round" aria-hidden="true">
                                <path d="M2 15l4-9 4 9 4-9 4 9 4-9v9H2z"></path>
                            </svg>
                        @else
                            {{-- حقيبة للموظف --}}
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
                                stroke-linejoin="round" aria-hidden="true">
                                <path d="M2 7h20v13a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2z"></path>
                                <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"></path>
                            </svg>
                        @endif
                    </div>
                </div>

                <div class="profile-info">
                    <div class="profile-name">{{ $user->name }}</div>
                    <div class="profile-role">{{ $role === 'ادمن' ? 'أدمن' : 'موظف' }}</div>
                </div>
            </div>

            <hr class="drawer-sep">

            <ul class="drawer-links">
                <li>
                    <a href="#" id="logout-btn" role="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" style="vertical-align:middle; margin-right:6px;">
                            <!-- شكل الباب -->
                            <path d="M21 2H9a1 1 0 0 0-1 1v18a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1z"></path>
                            <!-- السهم للخروج ناحية الشمال -->
                            <path d="M10 12H3l3-3m-3 3l3 3"></path>
                        </svg>
                        تسجيل الخروج
                    </a>




                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>



                </li>
                {{-- تقدر تضيف روابط تانية هنا --}}
            </ul>
        </div>
    @endauth

    {{-- <!-- ✅ الفوتر -->
    <footer class="position-fixed bottom-0 start-50 translate-middle-x text-center py-2 shadow-lg"
        style="hight:10%; width: 100%;">
        <div class="footer-container">
            <p class="mb-1 text-light">© {{ date('Y') }} - نظام الاشتراكات</p> <small class=""> تم التطوير
                بواسطة <a href="https://example.com" target="_blank"
                    class="text-warning text-decoration-none">Moemen</a> </small>
        </div>
    </footer> --}}


    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const logoutForm = document.getElementById('logout-form');
            const logoutBtn = document.getElementById('logout-btn');

            if (!logoutForm || !logoutBtn) return;

            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault(); // نمنع الإرسال التلقائي

                // نطلب من السيرفر إذا في شيفت مفتوح
                fetch("{{ route('shift.check') }}", {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json'
                    }
                }).then(async response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    const data = await response.json();

                    if (data.open) {
                        // رسالة تأكيد بسيطة:
                        const proceed = confirm(
                            "لديك شيفت مفتوح.\nهل تريد تسجيل الخروج دون غلق الشيفت؟\n[OK] لتسجيل الخروج دون إغلاق الشيفت، [Cancel] لإلغاء تسجيل الخروج"
                        );
                        if (proceed) {
                            logoutForm.submit();
                        } else {
                            // إلغاء تسجيل الخروج — يمكن إضافة إشعار للمستخدم هنا
                        }
                    } else {
                        // لا شيفت مفتوح -> نكمل تسجيل الخروج مباشرة
                        logoutForm.submit();
                    }
                }).catch(err => {
                    console.error('Error checking open shift:', err);
                    // لو فشل الفحص نعرض خيار للمستخدم بدلاً من منع الخروج نهائياً
                    const proceedAnyway = confirm(
                        "تعذر التحقق من حالة الشيفت.\nهل تود المتابعة وتسجيل الخروج؟");
                    if (proceedAnyway) {
                        logoutForm.submit();
                    }
                    // وإلا نلغي تسجيل الخروج
                });
            });
        });
    </script>

    <script>
        function toggleDrawer() {
            document.getElementById("drawer").classList.toggle("open");
        }
    </script>
    <script>
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 2000
            })
        @endif

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: "{{ session('error') }}",
                showConfirmButton: false,
                timer: 2000
            })
        @endif
    </script>

    <!-- Pusher JS -->
    <script src="https://js.pusher.com/8.2/pusher.min.js"></script>
    <!-- Laravel Echo (IIFE) -->
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>

    <script>
        // عرض Snackbar بسيط
        function showSnackbar(message) {
            const box = document.createElement('div');
            box.style.position = 'fixed';
            box.style.bottom = '20px';
            box.style.left = '50%';
            box.style.transform = 'translateX(-50%)';
            box.style.padding = '12px 16px';
            box.style.background = '#333';
            box.style.color = '#fff';
            box.style.borderRadius = '8px';
            box.style.zIndex = '9999';
            box.textContent = message;
            document.body.appendChild(box);
            setTimeout(() => box.remove(), 3500);
        }

        // إعداد Echo على Pusher
        window.Echo = new Echo.Echo({
            broadcaster: 'pusher',
            key: '{{ env('PUSHER_APP_KEY') }}',
            cluster: '{{ env('PUSHER_APP_CLUSTER', 'mt1') }}',
            forceTLS: true
        });

        // الاستماع لقناة "bookings" والحدث "booking.status.updated"
        window.Echo.channel('bookings')
            .listen('.booking.status.updated', (e) => {
                // e جاي من broadcastWith
                if (e?.title) {
                    showSnackbar(`📣 الحجز "${e.title}" أصبح Due الآن`);
                } else {
                    showSnackbar('📣 تم تحديث حالة حجز إلى Due');
                }
            });
    </script>
    @if (session('show_start_shift_prompt'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (confirm("هل تريد بدء شيفت الآن؟")) {
                    // عمل POST لبدء الشيفت عبر fetch أو submited form
                    document.getElementById('start-shift-form').submit();
                }
            });
        </script>
    @endif

    <form id="start-shift-form" action="{{ route('shift.start') }}" method="POST" style="display:none;">
        @csrf
    </form>

</body>

</html>
