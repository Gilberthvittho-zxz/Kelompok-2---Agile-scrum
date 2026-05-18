<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@motoku.test'],
            [
                'name' => 'Pemilik Usaha',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $categories = [
            ['name' => 'Oli & Pelumas', 'description' => 'Pelumas mesin dan transmisi motor'],
            ['name' => 'Ban & Velg', 'description' => 'Ban luar, ban dalam, dan velg motor'],
            ['name' => 'Rem & Kampas', 'description' => 'Sistem pengereman motor'],
            ['name' => 'Kelistrikan', 'description' => 'Aki, lampu, dan komponen listrik'],
            ['name' => 'Aksesoris', 'description' => 'Aksesoris tambahan motor'],
        ];
        foreach ($categories as $c) {
            Category::updateOrCreate(['name' => $c['name']], $c);
        }

        $suppliers = [
            ['name' => 'PT Astra Motor Parts', 'contact_person' => 'Budi Santoso', 'email' => 'budi@astraparts.co.id', 'phone' => '021-5550111', 'address' => 'Jl. Sudirman No. 10, Jakarta'],
            ['name' => 'CV Yamaha Sparepart', 'contact_person' => 'Siti Aminah', 'email' => 'siti@yamahasp.com', 'phone' => '022-7770222', 'address' => 'Jl. Merdeka No. 45, Bandung'],
            ['name' => 'UD Suzuki Jaya', 'contact_person' => 'Hendra Wijaya', 'email' => 'hendra@suzukijaya.id', 'phone' => '031-8880333', 'address' => 'Jl. Pahlawan No. 22, Surabaya'],
        ];
        foreach ($suppliers as $s) {
            Supplier::updateOrCreate(['name' => $s['name']], $s);
        }
    }
}
