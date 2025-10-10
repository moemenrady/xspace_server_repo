@extends('layouts.app')

@section('content')

<div class ="center">

        <h1>{{ $client->name }}</h1>

          </div>
          
    <div class ="center">
      
        {{-- باركود 1D (زي المحلات) --}}

        <div>

            {!! DNS1D::getBarcodeHTML((string) $client->id, 'C128', 2, 60) !!}
            
            <p>ID: {{ $client->id }}</p>

        </div>

        {{-- أو QR Code لو عايز --}}
      
    </div>

      <div class ="center">

            {!! DNS2D::getBarcodeHTML((string) $client->id, 'QRCODE', 5, 5) !!}
        
          </div>

    <style>

        .center {

            margin: 0;
            padding: 0;
            min-height: 20vh;
            display: flex;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(8px);

        }

    </style>
    
@endsection
