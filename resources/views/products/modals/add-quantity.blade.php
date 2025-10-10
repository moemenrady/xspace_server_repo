<!-- Modal إضافة كمية -->
<div class="modal fade animate__animated animate__fadeInUp" id="addQuantityModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-4 shadow">
      <div class="modal-header bg-success text-white rounded-top-4">
        <h5 class="modal-title">📦 إضافة كمية</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="p-4">
        <input type="text" id="searchProduct" class="form-control form-control-lg" placeholder="🔍 ابحث عن المنتج...">
        <ul id="searchResults" class="list-group mt-2"></ul>

        <form id="addQuantityForm" method="POST" style="display: none;" class="mt-4">
          @csrf
          <input type="hidden" name="product_id" id="product_id">

          <div class="mb-3">
            <label class="form-label">الكمية المراد إضافتها</label>
            <input type="number" name="quantity" class="form-control form-control-lg" required>
          </div>

          <div class="text-center">
            <button type="submit" class="btn btn-success btn-lg px-5 fw-bold">✅ إضافة</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
