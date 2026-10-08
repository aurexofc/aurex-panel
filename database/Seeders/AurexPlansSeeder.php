<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Pterodactyl\Models\ServerPlan;

class AurexPlansSeeder extends Seeder
{
    /**
     * Seed the default Aurex store plans. Egg is left null so purchases
     * use the panel's default egg (first available).
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Spark',
                'description' => 'Chhota server — testing aur light games ke liye.',
                'memory' => 1024,
                'cpu' => 50,
                'disk' => 3072,
                'price_coins' => 2000,
                'duration_days' => 30,
                'active' => true,
            ],
            [
                'name' => 'Blaze',
                'description' => 'Medium server — Minecraft SMP aur doston ke liye best.',
                'memory' => 2048,
                'cpu' => 100,
                'disk' => 6144,
                'price_coins' => 6000,
                'duration_days' => 30,
                'active' => true,
            ],
            [
                'name' => 'Titan',
                'description' => 'Bara server — modpacks aur bari communities ke liye.',
                'memory' => 4096,
                'cpu' => 200,
                'disk' => 12288,
                'price_coins' => 12000,
                'duration_days' => 30,
                'active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            ServerPlan::query()->updateOrCreate(
                ['name' => $plan['name']],
                $plan
            );
        }
    }
}
