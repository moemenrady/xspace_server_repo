<!-- تجديد الاشتراك (محدث) -->
<div id="renewSubscriptionModal" class="custom-modal" aria-hidden="true" style="display:none;">
  <div class="custom-modal-backdrop"></div>

  <div class="custom-modal-dialog" role="dialog" aria-modal="true" tabindex="-1">
    <div class="custom-modal-content">
      <div class="custom-modal-header">
        <h4 class="custom-modal-title">🔄 تجديد الاشتراك</h4>
        <button type="button" id="renewModalClose" class="btn-small" aria-label="close">✕</button>
      </div>

      <div class="custom-modal-body">
        <p class="muted">ممكن تختار تغيير الخطة عند التجديد أو تترك الخطة الحالية.</p>

        <form id="renewSubscriptionForm">
          @csrf
          <div class="form-group">
            <label class="form-label">اختر الخطة (اختياري)</label>

            <!-- استخدمت غلاف لإضافة السهم المخصص -->
            <div class="select-wrapper">
              <select name="plan_id" id="renew_plan_id" class="styled-select">
                <option value="">-- اترك نفس الخطة --</option>
                @foreach(\App\Models\SubscriptionPlan::orderBy('price')->get() as $p)
                  <option value="{{ $p->id }}" {{ $p->id == $plan->id ? 'selected' : '' }}>
                    {{ $p->name }} — {{ number_format($p->price,2) }} جنيه — {{ $p->visits_count }} زيارات — {{ $p->duration_days }} يوم
                  </option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="actions-row">
            <button type="button" class="btn-small" id="renewCancel">إلغاء</button>
            <button type="submit" class="btn" id="renewConfirm">✅ تجديد الآن</button>
          </div>
        </form>

        <div id="renewModalMessage" class="modal-message"></div>
      </div>
    </div>
  </div>
</div>

<!-- ستايلات محسنة -->
<style>
  :root{
    /* عدّل اللون هنا ليطابق الـ theme عندك */
    --theme: #D9B1AB;
    --muted: #6b6b6b;
    --card-bg: #ffffff;
    --surface-shadow: 0 14px 40px rgba(0,0,0,0.12);
  }

  /* عام للمودال */
  .custom-modal {
    position: fixed;
    inset: 0;
    z-index: 99990;
    display:flex;
    align-items:center;
    justify-content:center;
    padding: 20px;
    direction: rtl; /* مهم للغة العربية */
  }
  .custom-modal-backdrop {
    position:absolute;
    inset:0;
    background:rgba(0,0,0,0.45);
    backdrop-filter: blur(1px);
  }
  .custom-modal-dialog {
    position:relative;
    max-width:560px;
    width:100%;
    z-index:99991;
    animation: fadeInUp .28s ease;
  }
  .custom-modal-content {
    background:var(--card-bg);
    border-radius:18px;
    padding:20px;
    box-shadow:var(--surface-shadow);
    overflow:hidden;
    font-family: Inter, "Noto Sans Arabic", sans-serif;
  }

  .custom-modal-header { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:14px; }
  .custom-modal-title { margin:0; font-size:18px; color:#222; }
  .custom-modal-body { font-size:15px; color:#333; }

  .muted { color: var(--muted); margin:0 0 10px 0; }

  /* فورم و spacing */
  .form-group { margin-bottom:16px; display:flex; flex-direction:column; gap:8px; }
  .form-label { font-weight:600; font-size:14px; color:#2e2e2e; }

  /* زرار بسيط */
  .btn-small {
    background:#f2f2f2;
    border:0;
    padding:8px 12px;
    border-radius:10px;
    cursor:pointer;
    font-weight:600;
  }

  /* زرار التأكيد - يستخدم لون الـ theme */
  .btn {
    background:var(--theme);
    color:#fff;
    padding:10px 16px;
    border-radius:12px;
    border:0;
    cursor:pointer;
    font-weight:700;
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
  }

  /* صف الأزرار مضبوط ومحاذاة لليمين (RTL) */
  .actions-row {
    margin-top:8px;
    display:flex;
    gap:10px;
    justify-content:flex-end;
    align-items:center;
  }

  /* رسالة أسفل الفورم */
  .modal-message { margin-top:12px; font-weight:700; color: #2b2b2b; min-height:20px; }

  /* تحسين الselect - مظهر عصري */
  .select-wrapper {
    position: relative;
  }
  .styled-select {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    width:100%;
    padding:12px 44px 12px 14px; /* مساحة للسهم من اليسار (RTL) */
    border-radius:12px;
    border:1px solid rgba(0,0,0,0.08);
    background: linear-gradient(180deg, #fff, #fbfbfb);
    font-size:15px;
    outline: none;
    box-shadow: 0 3px 10px rgba(0,0,0,0.03) inset;
    cursor: pointer;
    text-align:right; /* مهم للـ Arabic */
  }
  .styled-select:focus {
    border-color: rgba(0,0,0,0.12);
    box-shadow: 0 6px 20px rgba(0,0,0,0.06);
  }

  /* أيقونة السهم (متموضع على اليسار لأننا RTL) */
  .select-wrapper::after{
    content: "";
    position: absolute;
    left: 14px; /* ضبط موقع السهم داخل الحقل */
    top: 50%;
    transform: translateY(-50%);
    width: 18px;
    height: 18px;
    pointer-events: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24'%3E%3Cpath fill='%23707070' d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
    background-repeat:no-repeat;
    background-size: contain;
    opacity:0.9;
  }

  /* responsive */
  @media (max-width:420px){
    .custom-modal-content { padding:14px; border-radius:12px; }
    .styled-select { padding:10px 40px 10px 12px; font-size:14px; }
  }

  @keyframes fadeInUp { from { opacity:0; transform: translateY(10px); } to { opacity:1; transform: translateY(0); } }
</style>
