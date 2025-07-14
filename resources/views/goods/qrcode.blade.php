@extends('layouts.app')

@section('content')
    <h1>QR Code for Good: {{ $good->eg_code }}</h1>
    <div>
        <img src="{{ $qrUrl }}" alt="QR Code for {{ $good->eg_code }}" style="max-width: 220px;">
    </div>
    <div class="mt-3">
        <a href="{{ $qrUrl }}" download class="btn btn-success">
            <i class="fas fa-download me-1"></i> Download QR Code
        </a>
    </div>
    <a href="{{ route('goods.show', $good->eg_id) }}" class="btn btn-primary mt-3">Back to Good</a>
@endsection 