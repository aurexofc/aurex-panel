<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Pterodactyl\Models\TopupPackage;

class AurexTopupPackagesSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            ['name' => 'Starter Pack', 'coins' => 600, 'price' => 50, 'sort_order' => 1],
            ['name' => 'Popular Pack', 'coins' => 1300, 'price' => 100, 'sort_order' => 2],
            ['name' => 'Pro Pack', 'coins' => 3000, 'price' => 200, 'sort_order' => 3],
            ['name' => 'Whale Pack', 'coins' => 8000, 'price' => 500, 'sort_order' => 4],
        ];

        foreach ($packages as $p) {
            TopupPackage::updateOrCreate(
                ['name' => $p['name']],
                array_merge($p, ['active' => true])
            );
        }
    }
}
