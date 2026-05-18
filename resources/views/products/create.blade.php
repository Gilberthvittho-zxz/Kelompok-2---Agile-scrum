@extends('layouts.app')
@section('title', 'Tambah Produk')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <h3 class="mb-3"><i class="bi bi-plus-circle"></i> Tambah Produk</h3>
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
                    @include('products._form')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
