@extends('layouts.app')
@section('title', 'Edit Kategori')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <h3 class="mb-3"><i class="bi bi-pencil-square"></i> Edit Kategori</h3>
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form method="POST" action="{{ route('categories.update', $category) }}">
                    @method('PUT')
                    @include('categories._form')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
