<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /** Warna gradient per kategori (HEX dari → ke) */
    private array $palette = [
        'Oli & Pelumas' => ['#f59e0b', '#b45309'],
        'Ban & Velg'    => ['#374151', '#111827'],
        'Rem & Kampas'  => ['#dc2626', '#991b1b'],
        'Kelistrikan'   => ['#3b82f6', '#1e40af'],
        'Aksesoris'     => ['#8b5cf6', '#6d28d9'],
    ];

    public function run(): void
    {
        $catId = Category::pluck('id', 'name');
        $supId = Supplier::pluck('id', 'name');

        Storage::disk('public')->makeDirectory('products');

        $products = [
            [
                'code' => 'OLI-001', 'name' => 'Oli Mesin Yamalube 1L', 'abbr' => 'YM',
                'category' => 'Oli & Pelumas', 'supplier' => 'CV Yamaha Sparepart',
                'purchase_price' => 55000, 'selling_price' => 65000, 'stock' => 50, 'min_stock' => 10,
                'description' => 'Oli mesin Yamalube full synthetic untuk motor matic & bebek 4-tak.',
            ],
            [
                'code' => 'OLI-002', 'name' => 'Oli Mesin AHM SPX2 1L', 'abbr' => 'AH',
                'category' => 'Oli & Pelumas', 'supplier' => 'PT Astra Motor Parts',
                'purchase_price' => 50000, 'selling_price' => 60000, 'stock' => 40, 'min_stock' => 10,
                'description' => 'Oli resmi Honda AHM SPX2, cocok untuk motor Honda matic/bebek.',
            ],
            [
                'code' => 'BAN-001', 'name' => 'Ban Luar IRC NR53 80/90-14', 'abbr' => 'IR',
                'category' => 'Ban & Velg', 'supplier' => 'PT Astra Motor Parts',
                'purchase_price' => 180000, 'selling_price' => 220000, 'stock' => 15, 'min_stock' => 5,
                'description' => 'Ban luar tubeless IRC ukuran 80/90 ring 14 untuk motor matic.',
            ],
            [
                'code' => 'BAN-002', 'name' => 'Ban Dalam Swallow 70/90-14', 'abbr' => 'SW',
                'category' => 'Ban & Velg', 'supplier' => 'UD Suzuki Jaya',
                'purchase_price' => 30000, 'selling_price' => 45000, 'stock' => 25, 'min_stock' => 8,
                'description' => 'Ban dalam Swallow ukuran 70/90 ring 14, karet tebal anti bocor.',
            ],
            [
                'code' => 'REM-001', 'name' => 'Kampas Rem Depan Aspira', 'abbr' => 'KR',
                'category' => 'Rem & Kampas', 'supplier' => 'PT Astra Motor Parts',
                'purchase_price' => 35000, 'selling_price' => 50000, 'stock' => 30, 'min_stock' => 8,
                'description' => 'Kampas rem depan Aspira premium untuk Honda Beat/Vario.',
            ],
            [
                'code' => 'REM-002', 'name' => 'Kampas Kopling Daytona', 'abbr' => 'KK',
                'category' => 'Rem & Kampas', 'supplier' => 'CV Yamaha Sparepart',
                'purchase_price' => 75000, 'selling_price' => 95000, 'stock' => 4, 'min_stock' => 5,
                'description' => 'Kampas kopling Daytona untuk motor bebek Yamaha (set 4 pcs).',
            ],
            [
                'code' => 'LIS-001', 'name' => 'Aki GS Astra GTZ5S', 'abbr' => 'AK',
                'category' => 'Kelistrikan', 'supplier' => 'PT Astra Motor Parts',
                'purchase_price' => 250000, 'selling_price' => 295000, 'stock' => 8, 'min_stock' => 3,
                'description' => 'Aki kering GS Astra GTZ5S 12V 3.5Ah, garansi 12 bulan.',
            ],
            [
                'code' => 'LIS-002', 'name' => 'Lampu LED Vario Eagle Eye', 'abbr' => 'LE',
                'category' => 'Kelistrikan', 'supplier' => 'CV Yamaha Sparepart',
                'purchase_price' => 45000, 'selling_price' => 65000, 'stock' => 0, 'min_stock' => 5,
                'description' => 'Lampu LED depan/belakang model Eagle Eye, hemat daya & terang.',
            ],
            [
                'code' => 'AKS-001', 'name' => 'Helm Half Face NHK', 'abbr' => 'HN',
                'category' => 'Aksesoris', 'supplier' => 'UD Suzuki Jaya',
                'purchase_price' => 180000, 'selling_price' => 230000, 'stock' => 12, 'min_stock' => 4,
                'description' => 'Helm half face NHK SNI, busa empuk + visor anti gores.',
            ],
            [
                'code' => 'AKS-002', 'name' => 'Spion Bulat Universal', 'abbr' => 'SP',
                'category' => 'Aksesoris', 'supplier' => 'CV Yamaha Sparepart',
                'purchase_price' => 35000, 'selling_price' => 55000, 'stock' => 20, 'min_stock' => 5,
                'description' => 'Spion bulat universal dengan baut 10mm, sepasang.',
            ],
        ];

        foreach ($products as $p) {
            $imagePath = 'products/'.Str::slug($p['code']).'.svg';
            Storage::disk('public')->put($imagePath, $this->buildSvg($p));

            Product::updateOrCreate(
                ['code' => $p['code']],
                [
                    'name' => $p['name'],
                    'description' => $p['description'],
                    'category_id' => $catId[$p['category']] ?? null,
                    'supplier_id' => $supId[$p['supplier']] ?? null,
                    'purchase_price' => $p['purchase_price'],
                    'selling_price' => $p['selling_price'],
                    'stock' => $p['stock'],
                    'min_stock' => $p['min_stock'],
                    'image' => $imagePath,
                    'is_active' => true,
                ]
            );
        }
    }

    private function buildSvg(array $p): string
    {
        [$from, $to] = $this->palette[$p['category']] ?? ['#475569', '#1e293b'];
        $abbr = htmlspecialchars($p['abbr'], ENT_XML1);
        $name = htmlspecialchars($p['name'], ENT_XML1);
        $cat  = htmlspecialchars(strtoupper($p['category']), ENT_XML1);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400" width="400" height="400">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0" stop-color="{$from}"/>
      <stop offset="1" stop-color="{$to}"/>
    </linearGradient>
  </defs>
  <rect width="400" height="400" fill="url(#bg)"/>
  <text x="200" y="65" text-anchor="middle" fill="white" font-family="Segoe UI, Arial, sans-serif" font-size="16" opacity=".85" letter-spacing="2">{$cat}</text>
  <circle cx="200" cy="210" r="120" fill="rgba(255,255,255,.08)"/>
  <text x="200" y="220" text-anchor="middle" fill="white" font-family="Segoe UI, Arial, sans-serif" font-size="140" font-weight="900" dominant-baseline="middle">{$abbr}</text>
  <rect x="0" y="320" width="400" height="80" fill="rgba(0,0,0,.35)"/>
  <text x="200" y="368" text-anchor="middle" fill="white" font-family="Segoe UI, Arial, sans-serif" font-size="22" font-weight="bold">{$name}</text>
</svg>
SVG;
    }
}
