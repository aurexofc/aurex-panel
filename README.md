<div align="center">

<img src="public/assets/aurex-logo.png" alt="Aurex Logo" width="160"/>

# 👑 AUREX

### *The Premium Game Server Panel*

**Deploy Minecraft, Rust, ARK & 100+ game servers with coins — no credit card needed.**

[![License: MIT](https://img.shields.io/badge/License-MIT-gold?style=for-the-badge)](LICENSE)
[![PHP 8.2](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Laravel 11](https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![React 18](https://img.shields.io/badge/React-18-61DAFB?style=for-the-badge&logo=react&logoColor=black)](https://reactjs.org)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://docker.com)

<br>

[🚀 Install in 5 Minutes](#-installation) •
[✨ Features](#-features) •
[🎮 Live Demo](https://aurex.waseem.website/) •
[📖 Docs](#-installation) •
[💬 Support](#-support)

<br>

**⭐ Star this repo if you love free game hosting! ⭐**

</div>

---

<div align="center">

| 🪙 **Coin Economy** | 📺 **Rewarded Ads** | 💱 **10 Currencies** | 🎨 **5 Royal Themes** |
|:---:|:---:|:---:|:---:|
| Earn, buy & spend | Watch ads, get coins | Auto geo-pricing | VIP gold design |

| 📱 **WhatsApp Alerts** | 🌍 **EN + ES** | 🔗 **Referrals** | 🛡️ **Anti-Abuse** |
|:---:|:---:|:---:|:---:|
| Free self-hosted | Full i18n | Dual-sided bonuses | VPN + IP blocking |

</div>

---

## 🌟 What is Aurex?

**Aurex** is a next-generation game server hosting panel — a fully rebranded, feature-packed evolution of Pterodactyl with a **VIP royal experience**.

Users earn coins by **watching ads**, **inviting friends**, or **topping up** with local payment methods — then deploy game servers instantly. No credit card. No complexity.

> 🎮 **For gamers, by gamers.** Coins in → servers out.

### 🆚 Why Aurex over plain Pterodactyl?

| Feature | Pterodactyl | 👑 Aurex |
|:--------|:-----------:|:--------:|
| Coin economy | ❌ | ✅ |
| Server store (buy with coins) | ❌ | ✅ |
| Rewarded ads | ❌ | ✅ |
| Referral bonuses | ❌ | ✅ |
| Manual top-ups (Easypaisa/JazzCash/USDT) | ❌ | ✅ |
| Multi-currency + auto geo-pricing | ❌ | ✅ |
| WhatsApp notifications | ❌ | ✅ |
| 5 switchable themes | ❌ | ✅ |
| Multi-language (EN/ES) | ❌ | ✅ |
| VIP animated dashboard | ❌ | ✅ |

---

## ✨ Features

<details open>
<summary><b>🪙 Coin Economy</b></summary>
<br>

- Virtual coin system with **full ledger** & transaction history
- **Server store** — buy Minecraft, Rust, FiveM & more with coins
- **Welcome bonus** for new signups (configurable amount)
- Admin coin grants/deducts with **audit trail**

</details>

<details open>
<summary><b>📺 Watch Ads, Earn Coins</b></summary>
<br>

- Rewarded ad system with **server-side anti-abuse**
- Daily limits, cooldowns & IP throttling
- Plug in **any ad network** (AdSense, custom HTML/JS)

</details>

<details open>
<summary><b>💸 Manual Top-Ups</b></summary>
<br>

- **Easypaisa, JazzCash, USDT, Binance** & custom methods
- 2-step flow: package → payment instructions → TID submit
- Admin **approve/reject** with notes + user notifications
- Transaction ID verification

</details>

<details open>
<summary><b>💱 Multi-Currency + Geo-Pricing</b></summary>
<br>

- **10 currencies**: PKR, USD, EUR, GBP, INR, IDR, AED, SAR, TRY, BDT
- **Automatic IP-based detection** — Spain sees EUR, Pakistan sees PKR
- Live exchange rates (cached 24h, fail-safe)
- Admin-editable prices per package

</details>

<details open>
<summary><b>📱 WhatsApp Notifications</b></summary>
<br>

- **Self-hosted WaSphere gateway** — message ANY number, **free forever**
- New top-up alerts → admin instantly
- Approve/reject alerts → users
- Welcome messages + password-reset alerts

</details>

<details open>
<summary><b>🎨 VIP Design System</b></summary>
<br>

- **5 royal themes**: Gold, Crimson, Ocean, Emerald, Purple
- Instant switching, **no page reload**
- Animated dashboard: particles, count-ups, staggered entrances
- AI-generated luxury background with floating gold dust
- Animated *"VIP HOST OWNER"* footer marquee

</details>

<details>
<summary><b>🌍 Multi-Language (EN + ES)</b></summary>
<br>

- Full React UI + backend + email translations
- One-click language switcher on Account page
- Neutral Latin American Spanish

</details>

<details>
<summary><b>🔗 Referral System</b></summary>
<br>

- Unique invite codes per user
- **Dual-sided bonuses** — inviter AND invitee both earn
- One account per IP + VPN/proxy blocking

</details>

<details>
<summary><b>🛡️ Anti-Abuse</b></summary>
<br>

- reCAPTCHA on registration
- Rate limiting on all endpoints
- One account per IP enforcement
- VPN/proxy detection via IP reputation API

</details>

---

## 🚀 Installation

### ⚡ One-Command Install *(Recommended)*

```bash
bash <(curl -sSL "https://raw.githubusercontent.com/aurexofc/aurex-panel/1.0-develop/installer/aurex-installer.sh")
```

The stylish installer handles **everything automatically**:

| Step | What it does |
|:-----|:-------------|
| ✅ Dependencies | PHP 8.2, MySQL, Redis, Nginx, Node.js 20 |
| ✅ SSL | Free Let's Encrypt certificate |
| ✅ Database | Auto-created + admin user setup |
| ✅ Background jobs | Cronjob + queue worker (systemd) |
| ✅ Firewall | Optional UFW (22, 80, 443) |
| ✅ Wings | Optional game daemon install |

**Requirements:** Fresh Ubuntu 22.04/24.04 VPS with 2GB+ RAM

### 🔧 Manual Install

```bash
# 1. Clone the repo
git clone https://github.com/aurexofc/aurex-panel.git /var/www/aurex-panel
cd /var/www/aurex-panel

# 2. Install dependencies
composer install --no-dev --optimize-autoloader
npm install && npm run build

# 3. Configure environment
cp .env.example .env
php artisan key:generate

# 4. Setup database
php artisan migrate --force
php artisan db:seed --force

# 5. Fix permissions
chown -R www-data:www-data /var/www/aurex-panel

# 6. Queue worker + scheduler
echo "* * * * * php /var/www/aurex-panel/artisan schedule:run >> /dev/null 2>&1" | crontab -
systemctl enable --now aurex-queue
```

### 📱 WhatsApp Setup (WaSphere)

```bash
sudo bash <(curl -sSL "https://raw.githubusercontent.com/aurexofc/aurex-panel/1.0-develop/installer/wasphere-install.sh")
```

Then:
1. Open `http://YOUR-VPS-IP:3004` → create account
2. Go to **Settings → WA Server**
3. **Sessions** → scan QR with a **spare** WhatsApp number
4. Create API key → paste into **Aurex Admin → Aurex → Top-ups → WhatsApp**

> ⚠️ Use a spare number — unofficial WhatsApp automation carries ban risk.

---

## 🛠️ Tech Stack

<div align="center">

| Layer | Technology |
|:------|:-----------|
| **Backend** | PHP 8.2, Laravel 11 |
| **Frontend** | React 18, TypeScript, Tailwind CSS |
| **Database** | MySQL / MariaDB |
| **Cache & Queue** | Redis |
| **Game Daemon** | Wings (Pterodactyl) + Docker |
| **WhatsApp** | WaSphere (self-hosted, Baileys) |
| **Email** | Gmail SMTP (free) |

</div>

---

## 🗺️ Roadmap

- [ ] 🏆 Leaderboard (top referrers & coin holders)
- [ ] 🎁 Daily login streak bonuses
- [ ] 📸 Payment screenshot upload for top-ups
- [ ] 📊 Admin earnings dashboard
- [ ] 🌍 More languages (Indonesian, Turkish, Arabic)
- [ ] 🤖 Discord bot integration

*Have an idea? [Open an issue](https://github.com/aurexofc/aurex-panel/issues) — we love community suggestions!*

---

## ❓ FAQ

**Q: Is Aurex free?**
A: 100% free and open-source (MIT). No paid tiers, no locked features.

**Q: Do I need a credit card to use it?**
A: No! Users earn coins via ads/referrals or top up with local methods like Easypaisa.

**Q: Can I use my own domain?**
A: Yes — the installer sets up Nginx + free SSL for any domain you point at your VPS.

**Q: Will WhatsApp get my number banned?**
A: WaSphere uses unofficial automation. Always use a **spare number**, never your main.

**Q: How is this different from Pterodactyl?**
A: Aurex is built ON Pterodactyl (same Wings daemon, same reliability) but adds an entire monetization & engagement layer on top — coins, store, ads, referrals, top-ups, themes.

---

## 🤝 Contributing

We love contributions! Here's how:

1. 🍴 Fork the repo
2. 🌿 Create your branch (`git checkout -b feature/AmazingFeature`)
3. 💾 Commit (`git commit -m 'Add AmazingFeature'`)
4. 📤 Push (`git push origin feature/AmazingFeature`)
5. 🎉 Open a Pull Request

---

## 📄 License

MIT License — see [LICENSE](LICENSE.md) for details.

---

## 💬 Support

- 🐛 **Bug reports**: [GitHub Issues](https://github.com/aurexofc/aurex-panel/issues)
- 🌐 **Live demo**: [aurex.waseem.website](https://aurex.waseem.website/)

---

<div align="center">

**👑 Built with passion for the gaming community**

*If Aurex helped you host your first server, drop a ⭐ — it keeps the project alive!*

<br>

`game-server` `minecraft-server` `pterodactyl` `game-hosting` `free-hosting` `rust-server` `ark-server` `fivem-server` `server-panel` `coin-economy` `rewarded-ads` `whatsapp-api` `laravel` `react` `docker` `self-hosted` `gaming` `esports`

</div>
