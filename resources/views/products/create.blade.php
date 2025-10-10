@extends('layouts.app')

@section('page_title', 'إضافة منتج جديد')

@section('content')
    <div class="container py-5">
        <div class="card shadow-lg border-0 rounded-4 animate__animated animate__fadeInDown">

            <div class="card-header bg-warning text-dark text-center fw-bold fs-4 rounded-top-4">
                ➕ إضافة منتج جديد
            </div>
            <div class="card-body p-4">
                <form action="{{ route('products.store') }}" method="POST" class="row g-3">
                    @csrf
                    <div class="col-md-6">
                        <label class="form-label">اسم المنتج</label>
                        <input type="text" name="name" class="form-control form-control-lg"
                            placeholder="ادخل اسم المنتج" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">السعر</label>
                        <input type="number" step="0.01" name="price" class="form-control form-control-lg"
                            placeholder="ادخل السعر" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">التكلفة</label>
                        <input type="number" step="0.01" name="cost" class="form-control form-control-lg"
                            placeholder="ادخل التكلفة" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">الكمية</label>
                        <input type="number" name="quantity" class="form-control form-control-lg" placeholder="ادخل الكمية"
                            required>
                    </div>

                    <div class="col-12 text-center mt-3">
                        <button type="submit" class="btn btn-warning btn-lg px-5 shadow-sm fw-bold">
                            ✅ حفظ المنتج
                        </button>
                        <a href="{{ route('products.index') }}" class="btn btn-outline-dark btn-lg px-5 ms-3">
                            ⬅️ رجوع
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
