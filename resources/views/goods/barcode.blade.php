@extends('layouts.app')

@section('content')
    <h1>Barcode for Good: {{ $good->eg_code }}</h1>
    <div>
        <img src="{{ $barcodeUrl }}" alt="Barcode for {{ $good->eg_code }}" style="max-width: 220px;">
    </div>
    <div class="mt-3">
        <a href="{{ $barcodeUrl }}" download class="btn btn-success">
            <i class="fas fa-download me-1"></i> Download Barcode
        </a>
    </div>
    <a href="{{ route('goods.show', $good->eg_id) }}" class="btn btn-primary mt-3">Back to Good</a>
@endsection 