@extends('layouts.app_page')

@section('title', "تفاصيل المنتج — {$product->name}")

@section('content')
<div class="product-container">
    <div class="card fade-in">
        <div class="card-header">
            <h2>🛒 تفاصيل المنتج</h2>
            <span class="badge">#{{ $product->id }}</span>

            <div class="header-left">
                <a href="#" id="openEditModal" class="btn edit-btn" title="تعديل المنتج" data-bs-toggle="modal" data-bs-target="#editProductModal">
    ✏️ تعديل
</a>

            </div>
        </div>

        {{-- بيانات أساسية --}}
        <div class="section product-main">
            <div class="box">
                <div class="row">
                    <div class="col">
                        <label class="lbl">📦 الاسم</label>
                        <p class="value">{{ $product->name }}</p>
                    </div>
                    <div class="col">
                        <label class="lbl">💰 سعر البيع</label>
                        <p class="value">{{ number_format($product->price, 2) }} جنيه</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <label class="lbl">🧾 التكلفة</label>
                        <p class="value">{{ number_format($product->cost, 2) }} جنيه</p>
                    </div>
                    <div class="col">
                        <label class="lbl">📦 الكمية بالمخزون</label>
                        <p class="value">{{ $product->quantity }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- حالة المنتج المهم --}}
        <div class="section statuses">
            <h3>⭐ حالة المنتج</h3>
            <div class="box flex-grid">
                <div class="status-card">
                    <label class="d-check">
                        <input type="checkbox" {{ $importantProduct ? 'checked' : '' }} disabled>
                        <span>منتج مهم</span>
                    </label>

                    @if ($importantProduct)
                        <button 
                                class="btn small btn-warning edit-important-btn"
                                data-id="{{ $importantProduct->id }}"
                                data-name="{{ $importantProduct->name }}"
                                data-product="{{ $importantProduct->product->name ?? $product->name }}"
                                data-product-id="{{ $importantProduct->product_id ?? $product->id }}">
                                ✏️ تعديل المنتج المهم
                            </button>
                        <p class="small">مسجل كمنتج مهم باسم: <strong>{{ $importantProduct->name }}</strong></p>
                      
                    @else
                        <p class="small muted">غير مسجل كمنتج مهم</p>
                        <div class="actions">
                            <form method="POST" action="{{ route('important_products.store') }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="name" value="{{ $product->name }}">
                                <button type="submit" class="btn small success">➕ إضافة للمنتجات المهمة</button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- نظرة عامة --}}
        <div class="section">
            <h3>📊 نظرة عامة</h3>
            <div class="box">
                <ul class="mini-list">
                    <li><strong>تاريخ الإضافة:</strong> {{ $product->created_at->format('Y-m-d H:i') }}</li>
                    <li><strong>آخر تعديل:</strong> {{ $product->updated_at->format('Y-m-d H:i') }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- 🟣 مودال تعديل المنتج المهم --}}
<div id="editModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 16px;">
            <div class="modal-header">
                <h5 class="modal-title">✏️ تعديل المنتج المهم</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editForm" method="POST" action="">
                    @csrf
                    @method('PUT')

                    <div class="form-group mb-3">
                        <label>اسم التعريف</label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>

                    <div class="form-group position-relative mb-3">
                        <label>اختر منتج جديد (اختياري)</label>
                        <input type="text" id="editProductSearch" placeholder="🔍 اكتب اسم المنتج..." class="form-control">
                        <input type="hidden" name="product_id" id="editProductId">

                        <div id="editProductResults" class="list-group position-absolute d-none"></div>

                        <div id="editSelectedProduct" class="selected mt-2" style="display:none;">
                            محدد الآن: <strong id="editSelectedProductText"></strong>
                            <button type="button" id="editClearSelected" class="btn-small">إلغاء</button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">💾 حفظ التعديلات</button>
                </form>
            </div>
        </div>
    </div>
</div>
{{-- 🟢 مودال تعديل المنتج --}}
<div id="editProductModal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content" style="border-radius: 16px;">
          <div class="modal-header">
              <h5 class="modal-title">✏️ تعديل بيانات المنتج</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <form id="updateProductForm">
                  @csrf
                  @method('PUT')

                  <div class="form-group mb-3">
                      <label>📦 اسم المنتج</label>
                      <input type="text" name="name" id="edit_name" class="form-control" required>
                  </div>

                  <div class="form-group mb-3">
                      <label>💰 سعر البيع</label>
                      <input type="number" name="price" id="edit_price" step="0.01" class="form-control" required>
                  </div>

                  <div class="form-group mb-3">
                      <label>🧾 التكلفة</label>
                      <input type="number" name="cost" id="edit_cost" step="0.01" class="form-control" required>
                  </div>

                  <div class="form-group mb-3">
                      <label>📦 الكمية بالمخزون</label>
                      <input type="number" name="quantity" id="edit_quantity" class="form-control" required>
                  </div>

                  <div id="editProductAlert" class="alert d-none mt-3"></div>

                  <button type="submit" class="btn btn-primary w-100 mt-2">💾 حفظ التعديلات</button>
              </form>
          </div>
      </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // فتح المودال وملء البيانات الحالية
    $('#openEditModal').on('click', function(e) {
        e.preventDefault();
        $('#edit_name').val(`{{ $product->name }}`);
        $('#edit_price').val(`{{ $product->price }}`);
        $('#edit_cost').val(`{{ $product->cost }}`);
        $('#edit_quantity').val(`{{ $product->quantity }}`);
        $('#editProductModal').modal('show');
    });

    // عند الحفظ
    $('#updateProductForm').on('submit', function(e) {
        e.preventDefault();

        let formData = $(this).serialize();
        let url = "{{ route('products.update', $product->id) }}";
        let alertBox = $('#editProductAlert');

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function(response) {
                alertBox
                    .removeClass('d-none alert-danger')
                    .addClass('alert-success')
                    .text('✅ تم تحديث بيانات المنتج بنجاح!');
                
                setTimeout(() => {
                    $('#editProductModal').modal('hide');
                    location.reload(); // لتحديث الصفحة بعد الحفظ
                }, 1200);
            },
            error: function(xhr) {
                let msg = 'حدث خطأ أثناء الحفظ.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alertBox
                    .removeClass('d-none alert-success')
                    .addClass('alert-danger')
                    .text('❌ ' + msg);
            }
        });
    });
});
</script>

<script>
$(document).on('click', '.edit-important-btn', function() {
    const id = $(this).data('id');
    const name = $(this).data('name');
    const productName = $(this).data('product');
    const productId = $(this).data('product-id');

    $('#editName').val(name);
    $('#editSelectedProductText').text(productName);
    $('#editSelectedProduct').show();
    $('#editProductId').val(productId);

    $('#editForm').attr('action', `/important-products/${id}`);
    $('#editModal').modal('show');
});

$('#editProductSearch').on('keyup', function() {
    let query = $(this).val().trim();
    if (query.length < 1) {
        $('#editProductResults').addClass('d-none');
        return;
    }
    $.ajax({
        url: "{{ route('products.search') }}",
        type: "GET",
        data: { q: query },
        success: function(data) {
            let html = '';
            if (data.length > 0) {
                data.forEach(item => {
                    html += `<a href="#" class="list-group-item list-group-item-action edit-result-item"
                        data-id="${item.id}" data-name="${item.name}">
                        #${item.id} - ${item.name}
                    </a>`;
                });
            } else {
                html = '<div class="list-group-item text-muted">لا توجد نتائج</div>';
            }
            $('#editProductResults').html(html).removeClass('d-none');
        }
    });
});

$(document).on('click', '.edit-result-item', function(e) {
    e.preventDefault();
    const id = $(this).data('id');
    const name = $(this).data('name');
    $('#editProductId').val(id);
    $('#editSelectedProductText').text(name);
    $('#editSelectedProduct').show();
    $('#editProductResults').addClass('d-none');
});

$('#editClearSelected').on('click', function() {
    $('#editProductId').val('');
    $('#editSelectedProductText').text('');
    $('#editSelectedProduct').hide();
    $('#editProductSearch').val('');
});
</script>
@endsection

@section('style')
<style>
body {
    background: #fafafa;
    font-family: "Cairo", sans-serif;
}

.product-container {
    max-width: 960px;
    margin: 40px auto;
    padding: 20px;
    position: relative;
}

.card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    padding: 26px;
}

.card.fade-in {
    animation: fadeInUp 0.6s ease;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #f1f1f1;
    margin-bottom: 16px;
    padding-bottom: 10px;
}

.card-header h2 {
    font-size: 24px;
    color: #2b2b2b;
    margin: 0;
}

.badge {
    background: #D9B1AB;
    color: #fff;
    padding: 6px 12px;
    border-radius: 30px;
    font-weight: bold;
    box-shadow: 0 3px 6px rgba(0,0,0,0.1);
}

.section h3 {
    color: #a86f68;
    font-size: 20px;
    margin-bottom: 10px;
}

.box {
    background: #fafafa;
    padding: 14px 18px;
    border-radius: 12px;
    box-shadow: inset 0 2px 6px rgba(0,0,0,0.04);
    margin-bottom: 18px;
    font-size: 15px;
    line-height: 1.6;
}

.row {
    display: flex;
    gap: 20px;
    margin-bottom: 10px;
}

.col {
    flex: 1;
}

.lbl {
    display: block;
    font-weight: 600;
    color: #555;
}

.value {
    font-size: 17px;
    font-weight: 700;
    color: #222;
}

.flex-grid {
    display: grid;
    grid-template-columns: repeat(1, 1fr);
    gap: 14px;
}

.status-card {
    background: #fff;
    padding: 16px;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.d-check {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
}

.d-check input {
    transform: scale(1.2);
}

.actions {
    margin-top: 6px;
}

.btn.small {
    background: #D9B1AB;
    color: #fff;
    padding: 8px 12px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 700;
    box-shadow: 0 4px 10px rgba(0,0,0,0.06);
    transition: all .18s ease;
}

.btn.small:hover {
    transform: translateY(-3px);
    background: #a86f68;
}

.btn.small.success {
    background: #79c879;
}

.muted {
    color: #888;
}

.mini-list {
    list-style: none;
    padding-left: 0;
    margin: 0;
    color: #333;
}

.mini-list li {
    margin-bottom: 8px;
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
