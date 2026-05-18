@extends('layouts.app')
@section('title', 'Edit Produk')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <h3 class="mb-3"><i class="bi bi-pencil-square"></i> Edit Produk</h3>
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
                    @method('PUT')
                    @include('products._form')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
