<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            ['code' => 'cash',     'name' => 'Tunai',    'icon' => 'bi-cash-stack', 'sort_order' => 1],
            ['code' => 'transfer', 'name' => 'Transfer', 'icon' => 'bi-bank',       'sort_order' => 2],
            ['code' => 'qris',     'name' => 'QRIS',     'icon' => 'bi-qr-code',    'sort_order' => 3],
        ];
        foreach ($methods as $m) {
            PaymentMethod::updateOrCreate(['code' => $m['code']], $m + ['is_active' => true]);
        }
    }
}
