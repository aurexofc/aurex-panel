<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Pterodactyl\Models\AurexPremiumPackage;

class AurexPremiumPackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Weekly',
                'slug' => 'weekly',
                'duration_days' => 7,
                'price_coins' => 499,
                'max_servers' => 10,
                'sort_order' => 1,
            ],
            [
                'name' => 'Monthly',
                'slug' => 'monthly',
                'duration_days' => 30,
                'price_coins' => 1499,
                'max_servers' => 10,
                'sort_order' => 2,
            ],
            [
                'name' => 'Yearly',
                'slug' => 'yearly',
                'duration_days' => 365,
                'price_coins' => 11999,
                'max_servers' => 10,
                'sort_order' => 3,
            ],
            [
                'name' => 'Lifetime',
                'slug' => 'lifetime',
                'duration_days' => 36500,
                'price_coins' => 29999,
                'max_servers' => 10,
                'sort_order' => 4,
            ],
        ];

        foreach ($packages as $package) {
            AurexPremiumPackage::updateOrCreate(
                ['slug' => $package['slug']],
                $package + ['ads_free' => true, 'active' => true]
            );
        }
    }
}
