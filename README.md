<div align="center">

# 👑 AUREX

### *The Premium Game Server Panel*

<img src="public/assets/aurex-logo.png" alt="Aurex Logo" width="120"/>

**Deploy Minecraft, Rust, ARK & 100+ game servers with coins — no credit card needed.**

[![License: MIT](https://img.shields.io/badge/License-MIT-gold.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![React](https://img.shields.io/badge/React-18-61DAFB?logo=react&logoColor=black)](https://reactjs.org)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?logo=docker&logoColor=white)](https://docker.com)

[🚀 One-Command Install](#-installation) • [✨ Features](#-features) • [💱 Currencies](#-multi-currency--geo-pricing) • [📸 Screenshots](#-screenshots) • [🤝 Contributing](#-contributing)

</div>

---

## 🌟 What is Aurex?

**Aurex** is a next-generation game server hosting panel — a fully rebranded, feature-packed fork of Pterodactyl with a **VIP royal experience**. Users earn coins by watching ads, inviting friends, or topping up with local payment methods — then deploy game servers instantly.

> 🎮 **For gamers, by gamers.** No credit card. No complexity. Just coins → servers.

---

## ✨ Features

### 🪙 Coin Economy
- **Virtual coin system** with full ledger & transaction history
- **Server store** — buy Minecraft, Rust, FiveM & more with coins
- **Welcome bonus** for new signups (configurable)
- **Admin coin grants** with audit trail

### 📺 Watch Ads, Earn Coins
- Rewarded ad system with **server-side anti-abuse**
- Daily limits, cooldowns & IP throttling
- Plug in any ad network (AdSense, custom HTML)

### 💸 Manual Top-Ups
- **Easypaisa, JazzCash, USDT, Binance** support
- Admin approve/reject flow with notes
- Transaction ID verification

### 💱 Multi-Currency + Geo-Pricing
- **10 currencies**: PKR, USD, EUR, GBP, INR, IDR, AED, SAR, TRY, BDT
- **Automatic IP-based currency detection** — a user in Spain sees EUR, in Pakistan sees PKR
- Live exchange rates, admin-editable prices

### 📱 WhatsApp Notifications (WaSphere)
- **Self-hosted WhatsApp gateway** — message ANY number, free forever
- New top-up alerts → admin
- Approve/reject alerts → users
- Welcome + password-reset messages

### 📧 Email Notifications
- Beautiful HTML emails via Gmail SMTP (free)
- Top-up alerts, approvals, rejections

### 🎨 5 Royal Themes + VIP Design
- **Gold, Crimson, Ocean, Emerald, Purple** — switch instantly, no reload
- Animated dashboard (particles, count-ups, staggered entrances)
- AI-generated luxury background, floating gold dust
- Animated **"VIP HOST OWNER"** footer marquee

### 🌍 Multi-Language
- **English + Spanish** (full React UI + backend + emails)
- One-click language switcher

### 🔗 Referral System
- Invite codes, **dual-sided bonuses** (inviter + invitee)
- One account per IP + VPN/proxy blocking

### 🛡️ Anti-Abuse
- reCAPTCHA, rate limiting, one-account-per-IP
- VPN/proxy detection via IP reputation

---

## 🚀 Installation

### One-Command Install (Recommended)

```bash
bash <(curl -sSL "https://raw.githubusercontent.com/aurexofc/aurex-panel/1.0-develop/installer/aurex-installer.sh")
```

The stylish installer handles **everything**:
- ✅ System dependencies (PHP 8.2, MySQL, Redis, Nginx, Node.js 20)
- ✅ Free SSL via Let's Encrypt
- ✅ Database + admin user setup
- ✅ Cronjob + queue worker
- ✅ Optional firewall (UFW)
- ✅ Optional **Wings** daemon install

### Manual Install

```bash
# 1. Clone
git clone https://github.com/YOUR-USERNAME/aurex-panel.git /var/www/aurex-panel
cd /var/www/aurex-panel

# 2. Dependencies
composer install --no-dev --optimize-autoloader
npm install && npm run build

# 3. Configure
cp .env.example .env
php artisan key:generate

# 4. Database
php artisan migrate --force
php artisan db:seed --force

# 5. Permissions
chown -R www-data:www-data /var/www/aurex-panel

# 6. Queue worker + cron
# Add to crontab: * * * * * php /var/www/aurex-panel/artisan schedule:run
systemctl enable --now aurex-queue
```

### 📱 WhatsApp Setup (WaSphere)

```bash
sudo bash <(curl -sSL "https://raw.githubusercontent.com/aurexofc/aurex-panel/1.0-develop/installer/wasphere-install.sh")
# Then: open http://YOUR-VPS-IP:3004 → register → Settings → WA Server
# Sessions → scan QR with a SPARE number → create API key
# Paste key into Aurex Admin → Aurex → Top-ups → WhatsApp
```

---

## 📸 Screenshots

> *Coming soon — star ⭐ this repo to get notified!*

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8.2, Laravel 11 |
| Frontend | React 18, TypeScript, Tailwind CSS |
| Database | MySQL / MariaDB |
| Cache/Queue | Redis |
| Game Daemon | Wings (Pterodactyl) + Docker |
| WhatsApp | WaSphere (self-hosted, Baileys) |

---

## 🏷️ Topics

`game-server` `minecraft-server` `pterodactyl` `game-hosting` `free-hosting` `rust-server` `ark-server` `fivem-server` `server-panel` `coin-economy` `rewarded-ads` `whatsapp-api` `laravel` `react` `docker` `self-hosted` `gaming` `esports` `pakistan` `indonesia`

---

## 🤝 Contributing

Pull requests are welcome! For major changes, please open an issue first.

1. Fork the repo
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit (`git commit -m 'Add AmazingFeature'`)
4. Push (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📄 License

MIT License — see [LICENSE](LICENSE) for details.

---

<div align="center">

**👑 Built with passion for the gaming community**

*If Aurex helped you, drop a ⭐ — it keeps the project alive!*

</div>
