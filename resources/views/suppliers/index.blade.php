@extends('layouts.app')
@section('title', 'Supplier')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="mb-0"><i class="bi bi-truck"></i> Supplier</h3>
        <small class="text-muted">Daftar pemasok sparepart beserta kontaknya.</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('suppliers.template') }}" class="btn btn-outline-secondary"><i class="bi bi-download"></i> Template</a>
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importModal"><i class="bi bi-upload"></i> Import</button>
        <a href="{{ route('suppliers.create') }}" class="btn btn-dark">
            <i class="bi bi-plus-lg"></i> Tambah Supplier
        </a>
    </div>
</div>

@include('partials.import-modal', [
    'entity' => 'Supplier',
    'importRoute' => route('suppliers.import'),
    'templateRoute' => route('suppliers.template'),
    'columns' => 'name, contact_person, email, phone, address',
])

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama / PIC / telepon..." class="form-control">
                    @if($q)
                        <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="60">#</th>
                        <th>Nama Supplier</th>
                        <th>PIC / Kontak</th>
                        <th>Email</th>
                        <th>Telepon</th>
                        <th>Alamat</th>
                        <th class="text-center">Status</th>
                        <th width="80" class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($suppliers as $i => $s)
                        <tr class="{{ ! $s->is_active ? 'table-secondary' : '' }}">
                            <td>{{ $suppliers->firstItem() + $i }}</td>
                            <td><strong>{{ $s->name }}</strong></td>
                            <td>{{ $s->contact_person ?: '—' }}</td>
                            <td>{{ $s->email ?: '—' }}</td>
                            <td>{{ $s->phone ?: '—' }}</td>
                            <td><small class="text-muted">{{ \Illuminate\Support\Str::limit($s->address, 50) ?: '—' }}</small></td>
                            <td class="text-center">
                                @if($s->is_active)
                                    <span class="badge badge-soft-success"><i class="bi bi-check-circle"></i> Aktif</span>
                                @else
                                    <span class="badge badge-soft-danger"><i class="bi bi-pause-circle"></i> Non-aktif</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('suppliers.show', $s) }}" class="btn btn-sm btn-outline-secondary" title="Lihat detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Belum ada supplier.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $suppliers->links() }}</div>
    </div>
</div>
@endsection
