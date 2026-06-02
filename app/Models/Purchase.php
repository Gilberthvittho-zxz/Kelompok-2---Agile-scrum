<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'code', 'purchase_date', 'arrival_date', 'arrived_at', 'arrived_by',
    'supplier_id', 'invoice_number',
    'subtotal', 'total', 'status', 'void_reason', 'voided_at', 'voided_by',
    'note', 'created_by',
])]
class Purchase extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'arrival_date' => 'date',
            'arrived_at' => 'datetime',
            'voided_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isConfirmed(): bool { return $this->status === 'confirmed'; }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function voider(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'voided_by');
    }

    public function arrivedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'arrived_by');
    }

    public function isVoided(): bool
    {
        return $this->status === 'voided';
    }

    public function totalItems(): int
    {
        return (int) $this->details->sum('qty');
    }

    public static function generateCode(\DateTimeInterface $when = null): string
    {
        $when = $when ?? now();
        $date = $when->format('Ymd');
        $prefix = 'PUR-'.$date.'-';
        $last = static::withTrashed()->where('code', 'like', $prefix.'%')->orderByDesc('code')->value('code');
        $next = $last ? ((int) substr($last, -4) + 1) : 1;
        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
