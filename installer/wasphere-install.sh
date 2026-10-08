#!/bin/bash
# ============================================================================
#  WASPHERE — One-Command Installer for Aurex Panel
# ============================================================================
#  Installs Docker + WaSphere (self-hosted WhatsApp API) on your VPS.
#
#  Usage: sudo bash wasphere-install.sh
#
#  After install:
#   1. Open http://YOUR-VPS-IP:3004 in browser
#   2. Register admin account
#   3. Settings → WA Server → URL: http://wa-server:3001 + WA_TOKEN (shown below)
#   4. Sessions → Create session → Scan QR with your SPARE WhatsApp number
#   5. Create API key → paste into Aurex Admin → Aurex → WhatsApp settings
# ============================================================================

set -e

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; GOLD='\033[38;5;220m'; BOLD='\033[1m'; NC='\033[0m'

echo -e "${GOLD}${BOLD}"
echo " __      __   _____       _________      .__                          "
echo "/  \    /  \ /  _  \     /   _____/_____ |  |__   ___________   ____   "
echo "\   \/\/   //  /_\  \    \_____  \\____ \|  |  \_/ __ \_  __ \_/ __ \  "
echo " \        //    |    \   /        \  |_> >   Y  \  ___/|  | \/\  ___/  "
echo "  \__/\  / \____|__  /  /_______  /   __/|___|  /\___  >__|    \___  > "
echo "       \/          \/           \/|__|        \/     \/            \/  "
echo -e "${NC}"
echo -e "${BOLD}  Self-hosted WhatsApp API for Aurex Panel${NC}\n"

if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}Please run as root: sudo bash $0${NC}"
    exit 1
fi

# ── Step 1: Docker ──
echo -e "${BOLD}▶ [1/4] Installing Docker...${NC}"
if ! command -v docker &> /dev/null; then
    curl -fsSL https://get.docker.com/ | sh
    systemctl enable --now docker
    echo -e "${GREEN}  ✓ Docker installed${NC}"
else
    echo -e "${GREEN}  ✓ Docker already installed${NC}"
fi

# ── Step 2: Clone WaSphere ──
echo -e "${BOLD}▶ [2/4] Downloading WaSphere...${NC}"
INSTALL_DIR="/opt/wasphere"
if [ -d "$INSTALL_DIR" ]; then
    echo -e "${YELLOW}  ⚠ $INSTALL_DIR exists, updating...${NC}"
    cd "$INSTALL_DIR" && git pull -q 2>/dev/null || true
else
    git clone -q https://github.com/wasphere/wasphere.git "$INSTALL_DIR"
fi
cd "$INSTALL_DIR"
echo -e "${GREEN}  ✓ WaSphere downloaded${NC}"

# ── Step 3: Generate secrets ──
echo -e "${BOLD}▶ [3/4] Generating secrets...${NC}"
cp -n .env.example .env 2>/dev/null || true

gen() { openssl rand -hex 32; }
POSTGRES_PASSWORD=$(gen)
JWT_SECRET=$(gen)
ENCRYPTION_KEY=$(gen)
WA_TOKEN=$(gen)
WEBHOOK_SIGNING_SECRET=$(gen)
INTERNAL_WEBHOOK_SECRET=$(gen)

# Write secrets into .env (replace if exists, append if not)
set_kv() {
    local key="$1" val="$2" file=".env"
    if grep -q "^${key}=" "$file" 2>/dev/null; then
        sed -i "s|^${key}=.*|${key}=${val}|" "$file"
    else
        echo "${key}=${val}" >> "$file"
    fi
}
set_kv "POSTGRES_PASSWORD" "$POSTGRES_PASSWORD"
set_kv "JWT_SECRET" "$JWT_SECRET"
set_kv "ENCRYPTION_KEY" "$ENCRYPTION_KEY"
set_kv "WA_TOKEN" "$WA_TOKEN"
set_kv "WEBHOOK_SIGNING_SECRET" "$WEBHOOK_SIGNING_SECRET"
set_kv "INTERNAL_WEBHOOK_SECRET" "$INTERNAL_WEBHOOK_SECRET"
echo -e "${GREEN}  ✓ Secrets generated${NC}"

# ── Step 4: Start ──
echo -e "${BOLD}▶ [4/4] Starting WaSphere...${NC}"
docker compose up -d 2>&1 | tail -3
echo -e "${GREEN}  ✓ WaSphere running${NC}"

VPS_IP=$(curl -s -m 5 ifconfig.me 2>/dev/null || echo "YOUR-VPS-IP")

echo ""
echo -e "${GOLD}╔══════════════════════════════════════════════════╗${NC}"
echo -e "${GOLD}║${NC}  ${BOLD}${GREEN}✓ WASPHERE INSTALLED!${NC}                        ${GOLD}║${NC}"
echo -e "${GOLD}╚══════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "  ${BOLD}Next steps:${NC}"
echo -e "  1. Open: ${BOLD}http://${VPS_IP}:3004${NC}"
echo -e "  2. Register an admin account"
echo -e "  3. Settings → WA Server:"
echo -e "     URL: ${YELLOW}http://wa-server:3001${NC}"
echo -e "     Token: ${YELLOW}${WA_TOKEN}${NC}"
echo -e "  4. Sessions → New session → scan QR with ${BOLD}SPARE${NC} WhatsApp number"
echo -e "     ${RED}⚠ Do NOT use your main number (ban risk)${NC}"
echo -e "  5. Create API key → paste into Aurex Admin → Aurex → WhatsApp"
echo ""
echo -e "  ${YELLOW}Save this WA_TOKEN — you'll need it in step 3!${NC}"
echo ""
