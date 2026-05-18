@extends('layouts.app')
@section('title', 'Tambah Supplier')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <h3 class="mb-3"><i class="bi bi-plus-circle"></i> Tambah Supplier</h3>
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form method="POST" action="{{ route('suppliers.store') }}">
                    @include('suppliers._form')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
