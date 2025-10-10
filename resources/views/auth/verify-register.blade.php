@extends('layouts.app_page')

@section('content')
<div style="max-width:480px;margin:40px auto;padding:20px;border-radius:8px;background:#fff;">
    <h3>التحقق من البريد</h3>
    <p>أدخل كود التحقق المرسل إلى: <strong>{{ $email ?? old('email') }}</strong></p>

    <form method="POST" action="{{ route('register.verify.post') }}">
        @csrf
        <input type="hidden" name="email" value="{{ $email ?? old('email') }}">
        <div class="mb-3">
            <label>كود التحقق</label>
            <input type="text" name="code" class="form-control" required autofocus>
            @error('code') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div>
            <button class="btn btn-success">تحقق وتسجيل</button>
        </div>
    </form>
</div>
@endsection
