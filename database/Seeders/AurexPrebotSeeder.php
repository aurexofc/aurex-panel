<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Pterodactyl\Models\AurexPrebot;

/**
 * Seeds the PreBots store: AUREX (featured) + popular open-source WhatsApp bots.
 */
class AurexPrebotSeeder extends Seeder
{
    public function run(): void
    {
        $bots = [
            [
                'name' => 'AUREX Bot',
                'slug' => 'aurex-bot',
                'description' => 'The official AUREX WhatsApp bot — royal gold menu, VIP welcome cards with profile pictures, rank system, sticker maker & group management. Built for kings. 👑',
                'github_url' => 'https://github.com/aurexofc/aurex-panel',
                'icon' => '👑',
                'price_coins' => 500,
                'memory' => 512,
                'disk' => 2048,
                'cpu' => 100,
                'featured' => true,
                'sort_order' => 0,
            ],
            [
                'name' => 'La Suki Bot',
                'slug' => 'la-suki-bot',
                'description' => 'One of the most popular multi-device WhatsApp bots — fun commands, games, stickers and group tools.',
                'github_url' => 'https://github.com/russellxz/LASUKIBOT.git',
                'icon' => '🌸',
                'price_coins' => 400,
                'memory' => 512,
                'disk' => 2048,
                'cpu' => 100,
                'sort_order' => 1,
            ],
            [
                'name' => 'GataBot-MD',
                'slug' => 'gatabot-md',
                'description' => 'Full-featured WhatsApp MD bot with economy, games, anime commands and powerful group administration.',
                'github_url' => 'https://github.com/GataNina-Li/GataBot-MD',
                'icon' => '🐱',
                'price_coins' => 400,
                'memory' => 512,
                'disk' => 2048,
                'cpu' => 100,
                'sort_order' => 2,
            ],
            [
                'name' => 'GataBotLite-MD',
                'slug' => 'gatabotlite-md',
                'description' => 'Lightweight version of GataBot — fast, simple and perfect for smaller servers.',
                'github_url' => 'https://github.com/GataNina-Li/GataBotLite-MD',
                'icon' => '🐈',
                'price_coins' => 300,
                'memory' => 256,
                'disk' => 1024,
                'cpu' => 75,
                'sort_order' => 3,
            ],
            [
                'name' => 'CORTANA 2.0',
                'slug' => 'cortana-2',
                'description' => 'Smart WhatsApp assistant bot with AI-style responses, utilities and entertainment commands.',
                'github_url' => 'https://github.com/edu-qariz/cortana_md',
                'icon' => '💙',
                'price_coins' => 350,
                'memory' => 512,
                'disk' => 2048,
                'cpu' => 100,
                'sort_order' => 4,
            ],
            [
                'name' => 'AZURA ULTRA 2.0',
                'slug' => 'azura-ultra-2',
                'description' => 'Premium multi-device bot with ultra-fast responses, media downloaders and group security.',
                'github_url' => 'https://github.com/russellxz/AZURA-ULTRA-2.0-BOT',
                'icon' => '⚡',
                'price_coins' => 450,
                'memory' => 512,
                'disk' => 2048,
                'cpu' => 100,
                'sort_order' => 5,
            ],
            [
                'name' => 'WhatsApp-Bot TS',
                'slug' => 'base-zeta-ts',
                'description' => 'Clean TypeScript WhatsApp bot base (Baileys) — perfect for developers who want to build their own commands.',
                'github_url' => 'https://github.com/AiDarkEzio/WhatsApp-Bot',
                'icon' => '🛠️',
                'price_coins' => 250,
                'memory' => 256,
                'disk' => 1024,
                'cpu' => 75,
                'sort_order' => 6,
            ],
        ];

        foreach ($bots as $bot) {
            AurexPrebot::updateOrCreate(['slug' => $bot['slug']], $bot + ['active' => true]);
        }
    }
}
