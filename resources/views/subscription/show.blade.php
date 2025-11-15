@extends('layouts.app_page')

@section('title', 'تفاصيل الاشتراك')

@section('content')
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

    <div class="subscription-container">

        <div class="card">

            <!-- الهيدر -->
            <div class="card-header">
                <h2>📋 تفاصيل الاشتراك</h2>
                <span class="badge">#{{ $subscription->id }}</span>
            </div>

            <!-- بيانات العميل -->
            <div class="section">
                <h3>👤 بيانات العميل</h3>
                <div class="box">
                    <p><strong> المعرف: </strong>{{ $client->id }}</p>

                    <p><strong>الاسم:</strong> {{ $client->name }}</p>
                    <p><strong>الموبايل:</strong> {{ $client->phone }}</p>
                    <p><strong>عدد مرات التجديد:</strong> {{ $subscription->renewal_count }}</p>
                  <a href="{{ route('clients.edit', $client->id) }}" class="btn edit-btn" title="تعديل بيانات العميل">
                    <span class="edit-ico" aria-hidden="true">✏️</span>
                    <span class="edit-txt">تعديل</span>
                </a>
                </div>
            </div>
            @if (!$subscription->is_active)
                <form action="{{ route('subscriptions.renew', $subscription->id) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-success">🔄 تجديد الاشتراك</button>
                </form>
            @endif
            <div class="space"></div>
            <!-- بيانات الخطة -->
            <div class="section">
                <h3>📜 الخطة</h3>
                <div class="box">
                    <p><strong>اسم الخطة:</strong> {{ $plan->name }}</p>
                    <p><strong>السعر:</strong> <span class="price">{{ $plan->price }} جنيه</span></p>
                    <p><strong>إجمالي الزيارات:</strong> {{ $plan->visits_count }}</p>
                </div>
            </div>




            <!-- بيانات الاشتراك -->
            <div class="section">
                <h3>📅 تفاصيل الاشتراك</h3>
                <div class="box">
                    <p><strong>تاريخ البداية:</strong>
                        {{ \Carbon\Carbon::parse($subscription->start_date)->format('Y-m-d') }}</p>
                    <p><strong>تاريخ الانتهاء:</strong>
                        {{ \Carbon\Carbon::parse($subscription->end_date)->format('Y-m-d') }}</p>
                    <p><strong>الزيارات المتبقية:</strong>
                        <span class="remaining">{{ $subscription->remaining_visits }}</span>
                    </p>
                </div>
                <div style="display:flex; gap:10px; align-items:center;">
                    <a href="{{ route('subscriptions.visits.show', $subscription->id) }}" class="btn"
                        style="padding:8px 12px; font-size:14px;">
                        📑 عرض زيارات الاشتراك
                    </a>

                </div>
            </div>

            <!-- Progress Bar -->
            @php
                $used = $plan->visits_count - $subscription->remaining_visits;
                $percent = $plan->visits_count > 0 ? round(($used / $plan->visits_count) * 100) : 0;
            @endphp

            <p>
                <strong>الزيارات المستهلكه:</strong>
                <span class="used">{{ $used }}</span>
            </p>
            <div class="progress-section">

                <p><strong>نسبة الاستهلاك:</strong> {{ $percent }}%</p>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ $percent }}%"></div>
                </div>
            </div>

            <!-- زرار -->
            <div class="form-btn">
                <button type="button" id="decrease-btn" class="btn">➖ خصم زيارة</button>
            </div>

            <!-- هنا هنعرض رسالة نجاح أو خطأ -->
            <div id="message" style="margin-top:15px; font-weight:bold;"></div>
        </div>
    </div>

    <script>
        document.getElementById('decrease-btn').addEventListener('click', function() {
            const btn = this;
            btn.disabled = true;
            const originalText = btn.innerHTML;
            btn.innerHTML = "جاري المعالجة...";

            fetch("{{ route('subscriptions.decrease', $subscription->id) }}", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Content-Type": "application/json",
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = originalText;

                    if (data.redirect) {
                        alert(data.message);
                        window.location.href = data.redirect;
                        return;
                    }

                    if (data.success) {
                        // تحديث العدد المتبقي
                        document.querySelector('.remaining').textContent = data.remaining_visits;

                        // حساب وتحديث الزيارات المستهلكة (used) — نفس اللوچي بالباك إند
                        const total = {{ $plan->visits_count }};
                        const used = total - data.remaining_visits;
                        const usedEl = document.querySelector('.used');
                        if (usedEl) {
                            usedEl.textContent = used;

                            // إضافة تأثير بصري قصير
                            usedEl.classList.add('updated');
                            setTimeout(() => usedEl.classList.remove('updated'), 350);
                        }

                        // عدل في البار
                        let percent = total > 0 ? Math.round((used / total) * 100) : 0;
                        document.querySelector('.progress-fill').style.width = percent + "%";
                        document.querySelector('.progress-section p').innerHTML =
                            "<strong>نسبة الاستهلاك:</strong> " + percent + "%";

                        // رسالة نجاح
                        const msg = document.getElementById('message');
                        msg.style.color = "green";
                        msg.innerText = "✅ تم خصم زيارة بنجاح";

                    } else {
                        const msg = document.getElementById('message');
                        msg.style.color = "red";
                        msg.innerText = data.message || 'حدث خطأ';
                    }
                })
                .catch(err => {
                    console.error(err);
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    const msg = document.getElementById('message');
                    msg.style.color = "red";
                    msg.innerText = "حدث خطأ أثناء الاتصال بالسيرفر.";
                });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // زر التجديد الأصلي في صفحتك: بدل الفورم المباشر نخلي الزر يفتح المودال
            const renewButtons = document.querySelectorAll(
                'form[action="{{ route('subscriptions.renew', $subscription->id) }}"], button.renew-trigger');
            // إذا الزر لديك كما هو <form> داخل الصفحة (حسبك قلته سابقاً) بنحول السلوك.
            // سنبحث عن الزر الظاهر في الصفحة: الذي له نص "🔄 تجديد الاشتراك" أو form action المشار إليه.
            let openRenewModalBtn = null;

            // حاول إيجاد زر submit داخل الفورم الموجود
            const renewFormOnPage = document.querySelector(
                'form[action="{{ route('subscriptions.renew', $subscription->id) }}"]');
            if (renewFormOnPage) {
                // منع الإرسال الافتراضي، واستخرج زر العرض
                const submitBtn = renewFormOnPage.querySelector('button[type="submit"]');
                if (submitBtn) {
                    openRenewModalBtn = submitBtn;
                    renewFormOnPage.addEventListener('submit', function(e) {
                        e.preventDefault(); // نمنع ال submit الافتراضي
                        showRenewModal();
                    });
                }
            } else {
                // fallback: اي زر مباشر بعنصر id أو class
                openRenewModalBtn = document.querySelector('.renew-trigger');
                if (openRenewModalBtn) {
                    openRenewModalBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        showRenewModal();
                    });
                }
            }

            const modal = document.getElementById('renewSubscriptionModal');
            const modalClose = document.getElementById('renewModalClose');
            const renewCancel = document.getElementById('renewCancel');
            const renewForm = document.getElementById('renewSubscriptionForm');
            const msgBox = document.getElementById('renewModalMessage');

            function showRenewModal() {
                if (!modal) return;
                modal.style.display = 'flex';
                modal.setAttribute('aria-hidden', 'false');
                // مسح رسائل سابقة
                msgBox && (msgBox.innerText = '');
            }

            function closeRenewModal() {
                if (!modal) return;
                modal.style.display = 'none';
                modal.setAttribute('aria-hidden', 'true');
            }

            if (modalClose) modalClose.addEventListener('click', closeRenewModal);
            if (renewCancel) renewCancel.addEventListener('click', closeRenewModal);

            // اغلاق عند الضغط خارج المحتوى
            modal && modal.addEventListener('click', function(e) {
                if (e.target === modal) closeRenewModal();
            });

            // ارسال الفورم عن طريق AJAX
            renewForm && renewForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                msgBox && (msgBox.innerText = 'جاري المعالجة...');
                msgBox && (msgBox.style.color = '#444');

                const url = "{{ route('subscriptions.renew', $subscription->id) }}";
                const formData = new FormData(renewForm);

                try {
                    const resp = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')
                                ?.value || '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: formData,
                        credentials: 'same-origin'
                    });

                    const data = await resp.json().catch(() => ({}));

                    if (resp.ok) {
                        // نجحت العملية
                        closeRenewModal();

                        // عرض Snackbar نجاح (انت عندك دالة showSnackbar)
                        if (typeof showSnackbar === 'function') {
                            showSnackbar(data.message || '✅ تم تجديد الاشتراك بنجاح', 'success');
                        } else {
                            alert(data.message || '✅ تم تجديد الاشتراك بنجاح');
                        }

                        // لو استلمنا redirect URL نعيد التوجيه
                        if (data.redirect) {
                            window.location.href = data.redirect;
                            return;
                        }

                        // بخلاف ذلك نعمل reload للصفحة لتحديث التفاصيل
                        setTimeout(() => location.reload(), 700);

                    } else {
                        // خطأ في السيرفر أو تحقق
                        const message = data.message || (data.errors ? Object.values(data.errors).flat()
                            .join(' - ') : 'حدث خطأ');
                        msgBox && (msgBox.innerText = message);
                        msgBox && (msgBox.style.color = 'red');

                        if (typeof showSnackbar === 'function') {
                            showSnackbar(message, 'error');
                        }
                    }
                } catch (err) {
                    console.error(err);
                    const message = 'حدث خطأ أثناء الاتصال بالسيرفر.';
                    msgBox && (msgBox.innerText = message);
                    msgBox && (msgBox.style.color = 'red');
                    if (typeof showSnackbar === 'function') showSnackbar(message, 'error');
                }
            });
        });
    </script>

    @include('subscription.modal.renew_sub')
@endsection
<!-- استايل -->
@section('style')


    <style>
        body {
            background: #fafafa;
            font-family: "Tahoma", sans-serif;
        }

        .space {
            height: 30px;
        }

        .subscription-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }

        .card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            animation: fadeInUp 0.6s ease;
        }


        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #f1f1f1;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }

        .card-header h2 {
            font-size: 26px;
            color: #2b2b2b;
            margin: 0;
        }

        .badge {
            background: #D9B1AB;
            color: #fff;
            padding: 6px 15px;
            border-radius: 30px;
            font-weight: bold;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
        }

        .section h3 {
            color: #a86f68;
            font-size: 20px;
            margin-bottom: 10px;
        }

        .box {
            background: #fafafa;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
            font-size: 16px;
            line-height: 1.7;
        }

        .price {
            font-weight: bold;
            font-size: 18px;
            color: #2b2b2b;
        }

        .remaining {
            font-weight: bold;
            font-size: 22px;
            color: #008000;
        }

        /* Progress Bar */
        .progress-section {
            margin: 25px 0;
        }

        .progress-bar {
            background: #eaeaea;
            border-radius: 12px;
            height: 20px;
            overflow: hidden;
            box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .progress-fill {
            background: linear-gradient(90deg, #D9B1AB, #a86f68);
            height: 100%;
            width: 0;
            border-radius: 12px;
            transition: width 0.6s ease-in-out;
        }

        .form-btn {
            text-align: center;
            margin-top: 20px;
        }

        .btn {
            background: #D9B1AB;
            color: #fff;
            padding: 12px 30px;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            cursor: pointer;
            transition: 0.3s;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .btn:hover {
            background: #a86f68;
            transform: scale(1.05);
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

        .used {
            font-weight: bold;
            font-size: 22px;
            color: #c40000;
            /* أحمر قوي */
            transition: transform 0.25s ease, color 0.25s ease;
        }

        /* تأثير بسيط عند التحديث */
        .used.updated {
            transform: scale(1.12);
        }


        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endsection
