@extends('layouts.app')    
@section('content')     
       <!-- Main content -->     
    <main class="container py-5">
                                 
        <div class="row g-4 justify-content-center">
                                 
            <div class="col-12 col-sm-6 col-lg-4">
                <form action="{{ route('session.index-manager') }}" method="GET">
                    <button type="submit" class="custom-card w-100">
                        <img src="https://img.icons8.com/ios/50/user-group-man-man.png" alt="icon">
                        <span>ادارة الجلسات</span>
                    </button>
                </form>
            </div>

            <div class="col-12 col-sm-6 col-lg-4">
                <form action="{{ route('session.create') }}" method="GET">
                    <button type="submit" class="custom-card w-100">
                        <img src="https://img.icons8.com/ios/50/stopwatch.png" alt="icon">
                        <span>بدء جلسة لعميل</span>
                    </button>
                </form>
            </div>

            <div class="col-12 col-sm-6 col-lg-4">
                <form action="{{ route('sale_proccess.create') }}" method="GET">
                    <button type="submit" class="custom-card w-100">
                        <img src="https://img.icons8.com/ios/50/sell.png" alt="icon">
                        <span>بدء عملية بيع</span>
                    </button>
                </form>
            </div>

            @if (Auth::user()->role === 'admin')
                <div class="col-12 col-sm-6 col-lg-4">
                    <form action="{{ route('expenses.create') }}" method="GET">
                        <button type="submit" class="custom-card w-100">
                            <img src="https://img.icons8.com/ios/50/money-transfer.png" alt="icon">
                            <span>اضافة مصروفات</span>
                        </button>
                    </form>
                </div>
            @else
                <div class="col-12 col-sm-6 col-lg-4">
                    <form action="{{ route('expense-drafts.index') }}" method="GET">
                        <button type="submit" class="custom-card w-100">
                            <img src="https://img.icons8.com/ios/50/money-transfer.png" alt="icon">
                            <span>مصروفات</span>
                        </button>
                    </form>
                </div>
            @endif

            <div class="col-12 col-sm-6 col-lg-4">
                <form action="{{ route('bookings.create') }}" method="GET">
                    <button type="submit" class="custom-card w-100">
                        <img src="https://img.icons8.com/ios/50/calendar.png" alt="icon">
                        <span>اضافة حجز</span>
                    </button>
                </form>
            </div>
            <div class="col-12 col-sm-6 col-lg-4">
                <form action="{{ route('bookings.index-manager') }}" method="GET">
                    <button type="submit" class="custom-card w-100">
                        <img src="https://img.icons8.com/ios/50/calendar.png" alt="icon">
                        <span>ادارة الحجوزات</span>
                    </button>
                </form>
            </div>

            <div class="col-12 col-sm-6 col-lg-4">
                <form action="{{ route('subscriptions.index-manager') }}" method="GET">
                    <button type="submit" class="custom-card w-100">
                        <img src="https://img.icons8.com/ios/50/conference.png" alt="icon">
                        <span>المشتركين</span>
                    </button>
                </form>
            </div>




            @if (Auth::user()->role === 'admin')
                <div class="col-12 col-sm-6 col-lg-4">
                    <form action="{{ route('admin.calendar') }}" method="GET">
                        <button type="submit" class="custom-card w-100">
                            <div class="card-content shift-stack">
                                <span>حساب موظفين</span>
                            </div>
                        </button>
                    </form>
                </div>
            @else
                @php
                    $openShift = \App\Models\Shift::where('user_id', Auth::id())->whereNull('end_time')->first();
                @endphp

                <div class="col-12 col-sm-6 col-lg-4">
                    <form action="{{ route('shift.create') }}" method="GET">
                        <button type="submit" class="custom-card w-100">
                            <div class="card-content shift-stack">
                                <span class="shift-indicator-top {{ $openShift ? 'open' : 'closed' }}"></span>
                                <span>الشفت</span>
                            </div>
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </main>
    
@endsection
