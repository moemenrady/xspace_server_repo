        <button class="drawer-toggle" onclick="toggleDrawer()">☰</button>

        <nav class="navbar">
            <!-- زرار الـ Drawer -->


            <a href="{{ route('main.create') }}" class={{ Request::is('/') || Request::is('main') ? 'active' : '' }}>
                الرئيسية
            </a>
            {{-- <a href="{{ route('bookings.index') }}" class={{ Request::is('bookings') ? 'active' : '' }}>
                الحجوزات
            </a> --}}
            <a href="{{ route('subscriptions.index') }}" class={{ Request::is('subscriptions') ? 'active' : '' }}>
                الاشتراكات
            </a>
            <a href="{{ route('clients.index') }}" class={{ Request::is('clients') ? 'active' : '' }}>
                العملاء
            </a>
            @if (Auth::user()->role === 'user')
                <a href="{{ route('shift.index') }}" class={{ Request::is('shift') ? 'active' : '' }}>
                    الشفتات
                </a>
                @endif




            @if (Auth::user()->role === 'admin')
                <a href="{{ route('products.index') }}" class={{ Request::is('products') ? 'active' : '' }}>
                    المخزن
                </a>
                <a href="{{ route('managment.create') }}" class={{ Request::is('managment') ? 'active' : '' }}>
                    الأدارة
                </a>
            @endif




        </nav>
