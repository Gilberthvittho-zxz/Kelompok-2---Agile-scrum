<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'stock_adjustment_id', 'product_id',
    'product_name_snapshot', 'product_code_snapshot',
    'qty_before', 'qty_after', 'qty_diff',
    'reason', 'note',
])]
class StockAdjustmentDetail extends Model
{
    use HasFactory;

    public const REASONS = [
        'rusak'   => 'Barang Rusak',
        'expired' => 'Barang Expired',
        'hilang'  => 'Barang Hilang',
        'koreksi' => 'Koreksi Pencatatan',
        'opname'  => 'Hasil Stock Opname',
        'lain'    => 'Lain-lain',
    ];

    public function adjustment(): BelongsTo
    {
        return $this->belongsTo(StockAdjustment::class, 'stock_adjustment_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function reasonLabel(): string
    {
        return self::REASONS[$this->reason] ?? $this->reason;
    }

    public function reasonBadgeClass(): string
    {
        return match ($this->reason) {
            'rusak', 'hilang' => 'badge-soft-danger',
            'expired'         => 'badge-soft-warning',
            'koreksi'         => 'badge-soft-success',
            'opname'          => 'bg-light text-dark',
            default           => 'bg-secondary text-white',
        };
    }
}
