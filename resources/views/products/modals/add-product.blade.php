<!-- Modal إضافة منتج جديد -->
<div class="modal fade animate__animated animate__fadeInDown" id="addProductModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-4 shadow">
      <div class="modal-header bg-warning text-dark rounded-top-4">
        <h5 class="modal-title">➕ منتج جديد</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('products.store') }}" method="POST" class="p-4">
        @csrf
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">اسم المنتج</label>
            <input type="text" name="name" class="form-control form-control-lg" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">السعر</label>
            <input type="number" step="0.01" name="price" class="form-control form-control-lg" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">التكلفة</label>
            <input type="number" step="0.01" name="cost" class="form-control form-control-lg" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">الكمية</label>
            <input type="number" name="quantity" class="form-control form-control-lg" required>
          </div>
        </div>
        <div class="text-center mt-4">
          <button type="submit" class="btn btn-warning btn-lg px-5 fw-bold">✅ حفظ</button>
        </div>
      </form>
    </div>
  </div>
</div>
