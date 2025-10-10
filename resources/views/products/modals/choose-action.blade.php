<!-- Modal اختيار العملية -->
<div class="modal fade animate__animated animate__zoomIn" id="chooseActionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-lg">
            <div class="modal-header text-dark rounded-top-4 d-flex align-items-center"
                style="background-color: #d9b3ff;">
                <button type="button" class="btn-close me-auto" data-bs-dismiss="modal"></button>
                <h5 class="modal-title mx-auto">⚡ اختر العملية</h5>
            </div>
            <div class="modal-body text-center py-4">

                <button class="btn btn-lg w-100 mb-3 fw-bold btn-purple" data-bs-target="#addProductModal"
                    data-bs-toggle="modal" data-bs-dismiss="modal">
                    ➕ إضافة منتج جديد
                </button>

                <button class="btn btn-lg w-100 fw-bold btn-green" data-bs-target="#addQuantityModal"
                    data-bs-toggle="modal" data-bs-dismiss="modal">
                    📦 إضافة كمية لمنتج موجود
                </button>

            </div>
        </div>
    </div>
</div>

<style>
    .btn-purple {
        background-color: #9b59b6;
        color: white;
    }

    .btn-purple:hover {
        background-color: #8e44ad;
        color: #fff;
    }

    .btn-green {
        background-color: #27ae60;
        color: white;
    }

    .btn-green:hover {
        background-color: #219150;
        color: #fff;
    }
</style>
