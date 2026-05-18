@extends('layouts.app')
@section('title', 'Edit Supplier')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <h3 class="mb-3"><i class="bi bi-pencil-square"></i> Edit Supplier</h3>
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form method="POST" action="{{ route('suppliers.update', $supplier) }}">
                    @method('PUT')
                    @include('suppliers._form')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
