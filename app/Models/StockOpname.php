<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'opname_date', 'note', 'created_by'])]
class StockOpname extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['opname_date' => 'datetime'];
    }

    public function details(): HasMany
    {
        return $this->hasMany(StockOpnameDetail::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public static function generateCode(\DateTimeInterface $when = null): string
    {
        $when = $when ?? now();
        $date = $when->format('Ymd');
        $prefix = 'OPN-'.$date.'-';
        $last = static::where('code', 'like', $prefix.'%')->orderByDesc('code')->value('code');
        $next = $last ? ((int) substr($last, -4) + 1) : 1;
        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Stock Opname terbaru yang dilakukan pada atau setelah tanggal tertentu.
     * Dipakai untuk mengunci transaksi/waste lama: kalau ada opname setelahnya,
     * transaksi tersebut tidak boleh di-void/dihapus.
     */
    public static function lockingSince(\DateTimeInterface|string $date): ?self
    {
        return static::where('opname_date', '>=', $date)
            ->orderByDesc('opname_date')
            ->first();
    }
}
