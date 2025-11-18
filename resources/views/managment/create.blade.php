@extends('layouts.app_admin')
@section('content')
    <!-- Main content -->
    <main class="container py-5">

        <div class="row g-4 justify-content-center">

            <div class="col-12">
                <form action="{{ route('analytics.money') }}" method="GET">
                    <button type="submit" class="custom-card">
                        التحليل
                    </button>
                </form>
            </div>

            <div class="col-12">
                <form action="{{ route('managment-system-edit.create') }}" method="GET">
                    <button type="submit" class="custom-card">
                      تعديلات في النظام
                    </button>
                </form>
            </div>
            <div class="col-12">
                <form action="{{ route('system-actions.index') }}" method="GET">
                    <button type="submit" class="custom-card">
                      برج المراقبه
                    </button>
                </form>
            </div>


        </div>

    </main>
@endsection
