@extends('layouts.app_page_admin') @section('content')
    <!-- Main content -->
    <main class="container py-5">
        <div class="row g-4 justify-content-center">
            <div class="col-12 col-sm-6 col-lg-4">
                <form action="{{ route('halls.create') }}" method="GET"> <button type="submit" class="custom-card w-100">
                        القاعات
                    </button> </form>
            </div>
            <div class="col-12 col-sm-6 col-lg-4">
                <form action="{{ route('records.create') }}" method="GET"> <button type="submit" class="custom-card w-100">
                        سعر الساعة الاساسية
                    </button> </form>
            </div>
            <div class="col-12 col-sm-6 col-lg-4">
                <form action="{{ route('important-products.create') }}" method="GET"> <button type="submit" class="custom-card w-100">
                        المنتجات المهمه
                    </button> </form>
            </div>



<div class="col-12 col-sm-6 col-lg-4">
                <form action="{{ route('expense-type.create') }}" method="GET"> <button type="submit" class="custom-card w-100">
                        انواع المصروف
                    </button> </form>
            </div>
            <div class="col-12 col-sm-6 col-lg-4">
                <form action="{{ route('full-day-hours.create') }}" method="GET"> <button type="submit" class="custom-card w-100">
                        عدد ساعات اليوم الكامل
                    </button> </form>
            </div>
            <div class="col-12 col-sm-6 col-lg-4">
                <form action="{{ route('subscription-plan.create') }}" method="GET"> <button type="submit" class="custom-card w-100">
                        خطط الاشتراك
                    </button> </form>
            </div>
              <div class="col-12 col-sm-6 col-lg-4">
                <form action="{{ route('users.create') }}" method="GET"> <button type="submit" class="custom-card w-100">
                        حسابات النظام
                    </button> </form>
            </div>

        </div>
    </main>
    <style>
        .custom-card {
            background: #e6c7ff;
            border-radius: 16px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding:
                40px 20px;
            text-align: center;
            transition: all 0.4s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(6px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .custom-card img {
            width: 50px;
            height: 50px;
            margin-bottom: 15px;
        }

        .custom-card span {
            font-size: 19px;
            font-weight: 400;
        }

        /* جلو */
        .custom-card::after {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: 16px;
            padding: 2px;
            background: linear-gradient(135deg, #b75fff, #e6c7ff, #e8cdff);
            -webkit-mask:
                linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            opacity: 0;
            transition: opacity 0.6s ease;
        }

        .custom-card:hover::after {
            opacity: 1;
        }

        .custom-card * {
            position:
                relative;
            z-index: 1;
        }

        .custom-card:hover {
            transform: translateY(-7px) scale(1.001) rotateX(5deg);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25);
        }

        /* خلفية الصفحة مع التدرج */
        .custom-container {
            text-align: center;
        }

        h1 {
            font-size: 36px;
            color: #333;
        }

        p {
            font-size: 18px;
            color: #555;
        }

        .custom-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 24px;
            background: #E6C7FF;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            font-weight: bold;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .custom-btn:hover {
            background: #E6C7FF;
        }
    </style>
@endsection
