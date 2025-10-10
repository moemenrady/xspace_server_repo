@extends('layouts.app_page')

@section('page_title', 'خطأ في البيانات الاساسيه للنظام')

@section('content')
    <style>
        body {
            background: linear-gradient(135deg, #ff9a9e, #fad0c4);
            font-family: 'Cairo', sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .error-box {
            background: #fff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 600px;
            width: 100%;
            animation: pop 0.8s ease-out;
        }

        @keyframes pop {
            0% { transform: scale(0.8); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        .error-icon {
            font-size: 80px;
            color: #e63946;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.7; }
            100% { transform: scale(1); opacity: 1; }
        }

        .error-message {
            margin-top: 20px;
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }

        .system-note {
            margin-top: 15px;
            font-size: 16px;
            color: #555;
        }

        .dev-note {
            margin-top: 25px;
            padding: 15px;
            background: #ffe5e5;
            color: #c00;
            border-radius: 10px;
            font-size: 14px;
            direction: ltr;
            text-align: left;
        }
    </style>

    <div class="error-box">
        <div class="error-icon">⚠️</div>
        <div class="error-message">خطأ في البيانات الاساسيه</div>
        <div class="system-note">برجاء الرجوع الى فريق الصيانة: <b>01094619040</b></div>

        @if(!empty($error))
            <div class="dev-note">
                {{ $error }}
            </div>
        @endif
    </div>
@endsection
