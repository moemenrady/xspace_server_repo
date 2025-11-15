
<!-- Modal حساب منفصل -->
<div class="modal fade animate__animated animate__zoomIn" id="splitSessionModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-4 shadow-lg">
      
      <!-- الهيدر -->
      <div class="modal-header bg-info text-white rounded-top-4">
        <h5 class="modal-title">🔀 حساب منفصل</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <!-- البودي -->
      <form action="{{ route('sessions.split') }}" method="POST" class="p-4">
        @csrf

        <!-- id الجلسة -->
        <input type="hidden" name="session_id" value="{{ $session->id }}">
        <input type="hidden" name="hours" value="{{ $hours }}">

        <!-- عدد الأفراد -->
        <div class="mb-3">
          <label class="form-label">👥 عدد الأفراد في الجلسة</label>
          <input type="number" class="form-control form-control-lg" value="{{ $session->persons }}" readonly>
        </div>

        <!-- الأفراد اللي هيتحاسبوا -->
        <div class="mb-3">
          <label class="form-label">👤 عدد الأفراد اللي هيدفعوا حساب منفصل</label>
          <input type="number" name="split_persons" class="form-control form-control-lg" min="1" max="{{ $session->persons-1 }}" required>
        </div>
<!-- المشتريات -->
<div class="mb-3">
  <label class="form-label">🛒 اختر المشتريات الخاصة بيهم</label>
  <div class="list-group">
    
    @foreach($session->purchases as $purchase)
      <div class="d-flex align-items-center mb-2 p-2 border rounded">
        <div class="flex-grow-1">
          <strong>{{ $purchase->product->name }}</strong>
          <span class="text-muted">({{ $purchase->quantity }} × {{ $purchase->price }} جنيه)</span>
        </div>
        <div style="width:120px">
          <input 
            type="number" 
            name="items[{{ $purchase->product->id }}]" 
            class="form-control" 
            min="0" 
            max="{{ $purchase->quantity }}" 
            value="0"
            placeholder="العدد">
        </div>
      </div>
    @endforeach
  </div>
  <small class="text-muted d-block mt-2">
    💡 لا يمكنك إدخال عدد أكبر من الكمية المتاحة في الجلسة.
  </small>
</div>


        <!-- زرار التأكيد -->
        <div class="text-center mt-4">
          <button type="submit" class="btn btn-info btn-lg px-5 fw-bold">
            ✅ تأكيد الحساب المنفصل
          </button>
        </div>
        <!-- عرض السعر النهائي عصري -->
<div id="splitPriceBox" class="split-price-box mt-3 p-3 rounded-3 shadow-sm">
  <div class="d-flex align-items-center justify-content-between">
    <div>
      <div class="small text-muted">الحساب المنفصل — ملخص</div>
      <div class="h5 mb-0" id="splitPriceValue">0.00 جنيه</div>
    </div>
    <div class="text-end">
      <div class="small text-muted">المشتريات</div>
      <div id="splitItemsValue">0.00 جنيه</div>
      <div class="small text-muted mt-1">حصة الساعات</div>
      <div id="splitHoursValue">0.00 جنيه</div>
    </div>
  </div>
  <div class="mt-2">
    <small class="text-muted">يتم الحساب فورًا عند تعديل عدد الأفراد أو عدد المشتريات.</small>
  </div>
</div>

      </form>
    </div>
  </div>
</div>
<style>
:root {
  --theme: #37a92d; /* غيّرها لتطابق ثيمك */
  --text-dark: #0b1320;
}

/* بطاقة السعر */
.split-price-box {
  background: linear-gradient(90deg, rgba(255,255,255,0.95), rgba(255,255,255,0.9));
  border-left: 6px solid var(--theme);
  color: var(--text-dark);
  transition: transform .14s ease, box-shadow .14s ease;
}
.split-price-box:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 30px rgba(13, 41, 57, 0.06);
}

/* القيم */
#splitPriceValue {
  color: var(--theme);
  font-weight: 800;
  letter-spacing: 0.2px;
}
#splitItemsValue, #splitHoursValue {
  font-weight: 700;
}
</style>
