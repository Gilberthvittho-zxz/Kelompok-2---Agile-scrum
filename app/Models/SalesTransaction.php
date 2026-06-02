<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'code', 'transaction_date', 'payment_method_id', 'customer_name',
    'subtotal', 'discount', 'total', 'paid_amount', 'change_amount',
    'status', 'void_reason', 'voided_at', 'voided_by',
    'note', 'created_by',
])]
class SalesTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'transaction_date' => 'datetime',
            'voided_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'change_amount' => 'decimal:2',
        ];
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(SalesTransactionDetail::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function voider(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'voided_by');
    }

    public function isVoided(): bool
    {
        return $this->status === 'voided';
    }

    public function totalItems(): int
    {
        return (int) $this->details->sum('qty');
    }

    /**
     * Generate unique transaction code: SLS-YYYYMMDD-#### (4 digit running per hari)
     */
    public static function generateCode(\DateTimeInterface $when = null): string
    {
        $when = $when ?? now();
        $date = $when->format('Ymd');
        $prefix = 'SLS-'.$date.'-';
        $last = static::withTrashed()
            ->where('code', 'like', $prefix.'%')
            ->orderByDesc('code')
            ->value('code');

        $next = $last ? ((int) substr($last, -4) + 1) : 1;
        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
