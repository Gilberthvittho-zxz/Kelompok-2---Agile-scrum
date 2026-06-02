<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'adjustment_date', 'reason', 'note', 'created_by'])]
class StockAdjustment extends Model
{
    use HasFactory;

    public const REASONS = [
        'rusak' => 'Barang Rusak',
        'expired' => 'Barang Expired',
        'hilang' => 'Barang Hilang',
        'koreksi' => 'Koreksi Pencatatan',
        'opname' => 'Hasil Stock Opname',
        'lain' => 'Lain-lain',
    ];

    protected function casts(): array
    {
        return ['adjustment_date' => 'datetime'];
    }

    public function details(): HasMany
    {
        return $this->hasMany(StockAdjustmentDetail::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function reasonLabel(): string
    {
        return self::REASONS[$this->reason] ?? $this->reason;
    }

    public static function generateCode(\DateTimeInterface $when = null): string
    {
        $when = $when ?? now();
        $date = $when->format('Ymd');
        $prefix = 'ADJ-'.$date.'-';
        $last = static::where('code', 'like', $prefix.'%')->orderByDesc('code')->value('code');
        $next = $last ? ((int) substr($last, -4) + 1) : 1;
        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
