@extends('layouts.app_page_admin')

@section('content')
    <div class="hall-wrapper">

        <!-- فورم إضافة منتج مهم -->
        <div class="hall-form">
            <h2>⭐ إضافة منتج مهم</h2>

            <form id="importantProductForm" action="{{ route('important_products.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label>اسم التعريف (مثال: مياه صغيره)</label>
                    <input type="text" name="name" placeholder="مثال: مياه صغيره" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="small text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group position-relative">
                    <label>اختر منتج موجود (أو ابحث عنه)</label>
                    <input type="text" id="productSearch" placeholder="🔍 اكتب اسم المنتج للبحث..." autocomplete="off"
                        class="form-control">
                    <input type="hidden" name="product_id" id="productIdInput" value="{{ old('product_id') }}">

                    <!-- نتائج البحث -->
                    <div id="productResults" class="list-group position-absolute d-none"></div>

                    <!-- المنتج المختار -->
                    <div id="selectedProduct" class="selected mt-2" style="display:none;">
                        محدد الآن: <strong id="selectedProductText"></strong>
                        <button type="button" id="clearSelected" class="btn-small">إلغاء</button>
                    </div>
                </div>

                <button type="submit" class="btn-submit mt-3">💾 حفظ المنتج المهم</button>
            </form>
        </div>

        <!-- ليستة المنتجات المهمة -->
        <div class="hall-list">
            <h3>📋 قائمة المنتجات المهمة</h3>
            <div class="cards">
                @foreach ($importantProducts as $ip)
                    <div class="card-content">
                        <div class="content-left">
                            <h4>⭐ {{ $ip->name }}</h4>
                            <p class="meta small">منتج مرتبط: {{ $ip->product->name ?? '—' }}</p>
                        </div>
                        <div class="content-right">
                            <button class="btn btn-sm btn-warning edit-btn" data-id="{{ $ip->id }}"
                                data-name="{{ $ip->name }}" data-product="{{ $ip->product->name ?? '' }}"
                                data-product-id="{{ $ip->product_id ?? '' }}">
                                ✏️ تعديل
                            </button>
                        </div>
                    </div>
                @endforeach

            </div>

            @if (method_exists($importantProducts, 'links'))
                <div class="mt-3">{{ $importantProducts->links() }}</div>
            @endif
        </div>

    </div>
    <!-- Modal التعديل -->
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
                            <input type="text" id="editProductSearch" placeholder="🔍 اكتب اسم المنتج..."
                                class="form-control">
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // سكريبت المودال للتعديل
        $(document).on('click', '.edit-btn', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const productName = $(this).data('product');
            const productId = $(this).data('product-id');

            // املأ البيانات في المودال
            $('#editName').val(name);
            $('#editSelectedProductText').text(productName);
            $('#editSelectedProduct').show();
            $('#editProductId').val(productId);

            // حدّث الأكشن بتاع الفورم
            $('#editForm').attr('action', `/important-products/${id}`);

            // افتح المودال
            $('#editModal').modal('show');
        });

        // بحث داخل المودال
        $('#editProductSearch').on('keyup', function() {
            let query = $(this).val().trim();
            if (query.length < 1) {
                $('#editProductResults').addClass('d-none');
                return;
            }

            $.ajax({
                url: "{{ route('products.search') }}",
                type: "GET",
                data: {
                    q: query
                },
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

        // اختيار منتج من نتائج البحث داخل المودال
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

        $(document).ready(function() {
            let selectedProduct = null;

            $('#productSearch').on('keyup', function() {
                let query = $(this).val().trim();
                $('#productIdInput').val('');
                $('#selectedProduct').hide();
                selectedProduct = null;

                if (query.length < 1) {
                    $('#productResults').addClass('d-none');
                    return;
                }

                $.ajax({
                    url: "{{ route('products.search') }}",
                    type: "GET",
                    data: {
                        q: query
                    },
                    success: function(data) {
                        let html = '';
                        if (data.length > 0) {
                            data.forEach(item => {
                                html += `<a href="#" class="list-group-item list-group-item-action result-item" 
                                data-id="${item.id}" data-name="${item.name}">
                                #${item.id} - ${item.name}
                            </a>`;
                            });
                        } else {
                            html =
                                '<div class="list-group-item text-muted">لا توجد نتائج</div>';
                        }
                        $('#productResults').html(html).removeClass('d-none');
                    }
                });
            });

            $(document).on('click', '.result-item', function(e) {
                e.preventDefault();
                selectedProduct = {
                    id: $(this).data('id'),
                    name: $(this).data('name')
                };
                $('#productSearch').val(selectedProduct.name);
                $('#productIdInput').val(selectedProduct.id);
                $('#selectedProductText').text(selectedProduct.name);
                $('#selectedProduct').show();
                $('#productResults').addClass('d-none');
            });

            $('#clearSelected').on('click', function() {
                $('#productIdInput').val('');
                $('#selectedProductText').text('');
                $('#selectedProduct').hide();
                $('#productSearch').val('');
                selectedProduct = null;
            });

            $(document).click(function(e) {
                if (!$(e.target).closest('#productResults, #productSearch').length) {
                    $('#productResults').addClass('d-none');
                }
            });
        });
    </script>
@endsection

@section('style')
    <style>
        /* اعتمدنا نفس ستايل صفحة الاشتراكات مع تعديل لنتائج البحث */
        :root {
            --accent: #E6C7FF;
            --bg: #F6F7FB;
            --card: #FFFFFF;
            --muted: #777;
            --max-width: 1100px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: "Cairo", sans-serif;
            margin: 0;
            color: #333;
            background: var(--bg);
        }

        .hall-wrapper {
            max-width: var(--max-width);
            margin: 0 auto;
            padding: 28px;
            display: flex;
            flex-direction: column;
            gap: 22px;
            padding-bottom: 40px;
        }

        .hall-form,
        .hall-list {
            background: var(--card);
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 10px 26px rgba(0, 0, 0, 0.06);
            border: 1px solid #f1ecef;
            transition: transform .28s, box-shadow .28s;
        }

        .hall-form:hover,
        .hall-list:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.08);
        }

        h2,
        h3 {
            margin: 0 0 14px;
            font-size: 20px;
        }

        .form-group {
            margin-bottom: 14px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
        }

        input[type="text"],
        input[type="number"],
        select.styled-select {
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid #e9e6e6;
            font-size: 15px;
            background: #fff;
        }

        input:focus,
        select.styled-select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 10px 30px rgba(230, 199, 255, 0.12);
        }

        .btn-submit {
            display: block;
            width: 100%;
            background: var(--accent);
            color: #fff;
            border: 0;
            padding: 12px 14px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            min-height: 44px;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
        }

        .cards {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 6px;
        }

        .card-content {
            background: #FBFBFF;
            padding: 14px;
            border-radius: 12px;
            border-left: 6px solid var(--accent);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
        }

        .card-content h4 {
            margin: 0;
            font-size: 16px;
            font-weight: 800;
        }

        .card-content .meta,
        .card-content .date {
            font-size: 13px;
            color: var(--muted);
            margin: 0;
        }

        #productResults {
            top: 100%;
            left: 0;
            right: 0;
            max-height: 200px;
            overflow-y: auto;
            z-index: 10;
        }

        .result-item {
            cursor: pointer;
        }

        .empty {
            color: #999;
            text-align: center;
            padding: 18px;
            font-style: italic;
        }

        @media(min-width:900px) {
            .hall-wrapper {
                flex-direction: row;
                gap: 28px;
                align-items: flex-start;
            }

            .hall-form {
                flex: 0 0 380px;
                padding: 22px;
            }

            .hall-list {
                flex: 1;
                padding: 22px;
            }
        }

        @media(min-width:700px) and (max-width:899px) {
            .hall-wrapper {
                padding: 28px;
            }

            .hall-form {
                flex: 0 0 360px;
            }
        }

        @media(max-width:420px) {
            .hall-wrapper {
                padding: 16px;
            }

            .hall-form,
            .hall-list {
                padding: 14px;
                border-radius: 12px;
            }

            h2,
            h3 {
                font-size: 18px;
            }

            input[type="text"],
            input[type="number"],
            select.styled-select {
                padding: 10px 12px;
                font-size: 14px;
            }

            .btn-submit {
                padding: 10px;
                font-size: 15px;
                min-height: 40px;
            }

            .card-content {
                padding: 12px;
            }
        }
    </style>
@endsection
