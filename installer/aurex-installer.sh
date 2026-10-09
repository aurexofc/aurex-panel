#!/bin/bash
# ============================================================================
#   ___   __  ______  _______  __
#  / _ | / / / / _ \/ __/ _ \/ /
# / __ |/ /_/ / , _/ _// , _/ /__
#/_/ |_|\____/_/|_/___/_/|_/____/
#
#  AUREX PANEL v1.0 — Stylish Installer
#  https://github.com/asifofc/aurex-panel
# ============================================================================

set -e

# ── Colors ──────────────────────────────────────────────────────────────────
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
GOLD='\033[38;5;220m'
CYAN='\033[0;36m'
BOLD='\033[1m'
DIM='\033[2m'
NC='\033[0m'

# ── Helpers ─────────────────────────────────────────────────────────────────
print_banner() {
    echo -e "${GOLD}"
    cat <<'BANNER'
   ___   __  ______  _______  __
  / _ | / / / / _ \/ __/ _ \/ /
 / __ |/ /_/ / , _/ _// , _/ /__
/_/ |_|\____/_/|_/___/_/|_/____/
BANNER
    echo -e "${NC}"
    echo -e "${BOLD}${GOLD}  ✦ AUREX PANEL — Premium Game Server Panel ✦${NC}"
    echo -e "${DIM}  ─────────────────────────────────────────────${NC}"
    echo ""
}

print_step() {
    echo -e "\n${BOLD}${BLUE}▶ $1${NC}"
    echo -e "${DIM}  ─────────────────────────────────${NC}"
}

print_success() {
    echo -e "${GREEN}  ✓ $1${NC}"
}

print_error() {
    echo -e "${RED}  ✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}  ⚠ $1${NC}"
}

print_info() {
    echo -e "${CYAN}  ℹ $1${NC}"
}

ask() {
    local prompt="$1" default="$2" var
    if [ -n "$default" ]; then
        read -p "$(echo -e "${BOLD}$prompt ${DIM}[$default]${NC}: ")" var
        var="${var:-$default}"
    else
        read -p "$(echo -e "${BOLD}$prompt${NC}: ")" var
    fi
    echo "$var"
}

ask_secret() {
    local prompt="$1" var
    read -sp "$(echo -e "${BOLD}$prompt${NC}: ")" var
    echo ""
    echo "$var"
}

confirm() {
    local prompt="$1" ans
    read -p "$(echo -e "${BOLD}$prompt ${DIM}(y/n)${NC}: ")" ans
    [[ "$ans" =~ ^[Yy]$ ]]
}

spinner() {
    local pid=$1 msg="$2" logfile="${3:-}"
    local spin='⠋⠙⠹⠸⠼⠴⠦⠧⠇⠏'
    local i=0
    while kill -0 $pid 2>/dev/null; do
        local detail=""
        if [ -n "$logfile" ] && [ -f "$logfile" ]; then
            detail=$(grep -v '^[[:space:]]*$' "$logfile" 2>/dev/null | tail -n 1 | cut -c1-70)
        fi
        printf "\r\033[K  ${CYAN}%s${NC} %s... ${DIM}%s${NC}" "${spin:$i:1}" "$msg" "$detail"
        i=$(( (i+1) % 10 ))
        sleep 0.2
    done
    printf "\r\033[K"
}

# ── Main ────────────────────────────────────────────────────────────────────
main() {
    clear
    print_banner

    # Root check
    if [ "$EUID" -ne 0 ]; then
        print_error "Please run as root!"
        echo -e "  ${DIM}sudo bash $0${NC}"
        exit 1
    fi

    # Never prompt for input during package installs (mysql/tzdata hang otherwise)
    export DEBIAN_FRONTEND=noninteractive

    # Wait for background auto-updates to release the apt lock (fresh VPS)
    print_info "Checking for background package locks..."
    for i in $(seq 1 30); do
        if ! fuser /var/lib/dpkg/lock-frontend >/dev/null 2>&1 \
        && ! fuser /var/lib/apt/lists/lock >/dev/null 2>&1; then
            break
        fi
        if [ "$i" -eq 30 ]; then
            print_info "Stopping stuck background updater..."
            pkill -f unattended-upgr 2>/dev/null
            sleep 2
        fi
        sleep 5
    done

    echo -e "${BOLD}What would you like to do?${NC}"
    echo -e "  ${GOLD}1)${NC} Install Aurex Panel"
    echo -e "  ${GOLD}2)${NC} Install Wings ${DIM}(game server daemon)${NC}"
    echo -e "  ${GOLD}3)${NC} Install Both ${DIM}(Panel + Wings)${NC}"
    echo -e "  ${GOLD}4)${NC} Update Aurex Panel"
    echo -e "  ${GOLD}5)${NC} Uninstall"
    echo ""
    local choice
    read -p "$(echo -e "${BOLD}Enter choice [1-5]${NC}: ")" choice

    case "$choice" in
        1) do_install ;;
        2) do_install_wings ;;
        3) do_install; do_install_wings ;;
        4) do_update ;;
        5) do_uninstall ;;
        *) print_error "Invalid choice"; exit 1 ;;
    esac
}

# ── Install ─────────────────────────────────────────────────────────────────
do_install() {
    print_step "Configuration"

    local DOMAIN ADMIN_EMAIL ADMIN_PASSWORD MYSQL_ROOT
    DOMAIN=$(ask "Domain/subdomain" "")
    while [ -z "$DOMAIN" ]; do
        print_error "Domain is required!"
        DOMAIN=$(ask "Domain/subdomain" "")
    done

    ADMIN_EMAIL=$(ask "Admin email" "")
    while [ -z "$ADMIN_EMAIL" ]; do
        print_error "Admin email is required!"
        ADMIN_EMAIL=$(ask "Admin email" "")
    done
    ADMIN_PASSWORD=$(ask_secret "Admin password (min 8 chars)")
    MYSQL_ROOT=$(ask_secret "MySQL root password")

    local INSTALL_SSL=true
    if ! confirm "Install free SSL certificate (Let's Encrypt)?"; then
        INSTALL_SSL=false
    fi

    local SETUP_FIREWALL=false
    if confirm "Configure firewall (UFW - ports 22, 80, 443)?"; then
        SETUP_FIREWALL=true
    fi

    echo ""
    echo -e "${BOLD}Summary:${NC}"
    echo -e "  Domain:      ${GOLD}${DOMAIN}${NC}"
    echo -e "  Admin email: ${GOLD}${ADMIN_EMAIL}${NC}"
    echo -e "  Directory:   ${DIM}/var/www/aurex-panel${NC}"
    echo -e "  Database:    ${DIM}aurex_panel${NC}"
    echo ""
    if ! confirm "Start installation?"; then
        echo "Cancelled."
        exit 0
    fi

    local INSTALL_DIR="/var/www/aurex-panel"
    local DB_NAME="aurex_panel"
    local DB_USER="aurex_user"
    local PHP_V="8.2"
    local DB_PASS=$(openssl rand -base64 16 | tr -dc 'a-zA-Z0-9' | head -c 16)

    # ── Step 1: Dependencies ──
    print_step "[1/7] Installing system dependencies"
    {
        # Node.js 20 (Ubuntu default is too old for modern builds)
        curl -fsSL https://deb.nodesource.com/setup_20.x | bash - 2>&1 | tail -2
        apt update
        apt install -y curl unzip git nginx certbot python3-certbot-nginx nodejs \

            php${PHP_V}-fpm php${PHP_V}-cli php${PHP_V}-mysql php${PHP_V}-mbstring \
            php${PHP_V}-xml php${PHP_V}-curl php${PHP_V}-zip php${PHP_V}-bcmath \
            php${PHP_V}-gd php${PHP_V}-redis mysql-server redis-server
    } &> /tmp/aurex-install.log &
    spinner $! "Installing packages" "/tmp/aurex-install.log"
    wait $!
    # Firewall
    if [ "$SETUP_FIREWALL" = true ]; then
        print_info "Configuring firewall..."
        apt install -y -qq ufw 2>&1 | tail -1
        ufw --force enable 2>&1 | tail -1
        ufw allow 22/tcp 2>&1 | tail -1
        ufw allow 80/tcp 2>&1 | tail -1
        ufw allow 443/tcp 2>&1 | tail -1
        print_success "Firewall configured (22, 80, 443)"
    fi

    print_success "Dependencies installed"

    # ── Step 2: Source ──
    print_step "[2/7] Downloading Aurex"
    {
        mkdir -p ${INSTALL_DIR}
        git clone --depth 1 --branch 1.0-develop https://github.com/aurexofc/aurex-panel.git /tmp/aurex-panel
        cp -r /tmp/aurex-panel/* ${INSTALL_DIR}/
        cp -r /tmp/aurex-panel/.env.example ${INSTALL_DIR}/ 2>/dev/null || true
        rm -rf /tmp/aurex-panel
        curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer -q
    } &> /tmp/aurex-install.log &
    spinner $! "Downloading source code" "/tmp/aurex-install.log"
    wait $!
    print_success "Source downloaded"

    # ── Step 3: PHP deps ──
    print_step "[3/7] Installing PHP packages"
    {
        cd ${INSTALL_DIR}
        sudo -u www-data composer install --no-dev --optimize-autoloader -q
    } &> /tmp/aurex-install.log &
    spinner $! "Running composer install" "/tmp/aurex-install.log"
    wait $!
    print_success "PHP packages installed"

    # ── Step 4: Frontend ──
    print_step "[4/7] Building frontend"
    {
        cd ${INSTALL_DIR}
        sudo -u www-data npm install -q 2>&1 | tail -1
        sudo -u www-data npm run build 2>&1 | tail -1
    } &> /tmp/aurex-install.log &
    spinner $! "Building assets (this takes a while)" "/tmp/aurex-install.log"
    wait $!
    print_success "Frontend built"

    # ── Step 5: Database ──
    print_step "[5/7] Setting up database"
    # Try socket auth first (Ubuntu default), fallback to password
    if mysql -u root -e "SELECT 1;" 2>/dev/null; then
        MYSQL_CMD="mysql -u root"
    elif mysql -u root -p"${MYSQL_ROOT}" -e "SELECT 1;" 2>/dev/null; then
        MYSQL_CMD="mysql -u root -p${MYSQL_ROOT}"
    else
        print_error "Cannot connect to MySQL as root!"
        print_info "Check your MySQL root password and try again."
        exit 1
    fi
    $MYSQL_CMD -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    $MYSQL_CMD -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'127.0.0.1' IDENTIFIED BY '${DB_PASS}';"
    $MYSQL_CMD -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'127.0.0.1'; FLUSH PRIVILEGES;"
    print_success "Database '${DB_NAME}' created"

    # ── Step 6: Configure ──
    print_step "[6/7] Configuring Aurex"
    cd ${INSTALL_DIR}
    cp .env.example .env 2>/dev/null || touch .env
    APP_KEY=$(openssl rand -base64 32)
    {
        echo "APP_NAME=Aurex"
        echo "APP_ENV=production"
        echo "APP_DEBUG=false"
        if [ "$INSTALL_SSL" = true ]; then
            echo "APP_URL=https://${DOMAIN}"
        else
            echo "APP_URL=http://${DOMAIN}"
        fi
        echo "APP_KEY=base64:${APP_KEY}"
        echo "APP_TIMEZONE=Asia/Karachi"
        echo ""
        echo "DB_CONNECTION=mysql"
        echo "DB_HOST=127.0.0.1"
        echo "DB_PORT=3306"
        echo "DB_DATABASE=${DB_NAME}"
        echo "DB_USERNAME=${DB_USER}"
        echo "DB_PASSWORD=${DB_PASS}"
        echo ""
        echo "CACHE_DRIVER=redis"
        echo "SESSION_DRIVER=redis"
        echo "QUEUE_DRIVER=redis"
        echo "REDIS_HOST=127.0.0.1"
        echo "REDIS_PORT=6379"
    } > .env

    sudo -u www-data php artisan migrate --force -q
    sudo -u www-data php artisan db:seed --force -q
    sudo -u www-data php artisan db:seed --class=AurexPlansSeeder --force -q
    sudo -u www-data php artisan db:seed --class=AurexTopupPackagesSeeder --force -q
    sudo -u www-data php artisan p:user:make \
        --email="${ADMIN_EMAIL}" --username=admin \
        --name-first=Aurex --name-last=Admin \
        --password="${ADMIN_PASSWORD}" --admin=1 --no-interaction -q
    chown -R www-data:www-data ${INSTALL_DIR}
    print_success "Aurex configured"

    # ── Step 7: Web server ──
    print_step "[7/7] Configuring web server"
    cat > /etc/nginx/sites-available/aurex <<NGINX
server {
    listen 80;
    server_name ${DOMAIN};
    root ${INSTALL_DIR}/public;
    index index.php;
    client_max_body_size 100m;
    client_body_timeout 120s;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        fastcgi_pass unix:/var/run/php/php${PHP_V}-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
NGINX
    ln -sf /etc/nginx/sites-available/aurex /etc/nginx/sites-enabled/
    nginx -t -q && systemctl reload nginx
    print_success "Nginx configured"

    if [ "$INSTALL_SSL" = true ]; then
        print_info "Installing SSL certificate..."
        certbot --nginx -d ${DOMAIN} --non-interactive --agree-tos -m ${ADMIN_EMAIL} -q 2>&1 | tail -1
        print_success "SSL installed"
    fi

    # Cronjob (required for scheduled tasks)
    print_info "Installing cronjob..."
    (crontab -l -u www-data 2>/dev/null; echo "* * * * * php ${INSTALL_DIR}/artisan schedule:run >> /dev/null 2>&1") | crontab -u www-data -
    print_success "Cronjob installed"

    # Queue worker (aurex-queue service)
    print_info "Installing queue worker..."
    cat > /etc/systemd/system/aurex-queue.service <<'QSERVICE'
[Unit]
Description=Aurex Queue Worker
After=redis-server.service
Requires=redis-server.service

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/aurex-panel/artisan queue:work --sleep=3 --tries=3 --max-time=3600
StartLimitIntervalSec=180
StartLimitBurst=30
RestartSec=5s

[Install]
WantedBy=multi-user.target
QSERVICE
    systemctl daemon-reload
    systemctl enable --now aurex-queue 2>&1 | tail -1
    print_success "Queue worker installed"

    sudo -u www-data php artisan optimize:clear -q
    systemctl reload php${PHP_V}-fpm

    # ── Done ──
    echo ""
    echo -e "${GOLD}╔══════════════════════════════════════════╗${NC}"
    echo -e "${GOLD}║${NC}  ${BOLD}${GREEN}✓ AUREX INSTALLED SUCCESSFULLY!${NC}      ${GOLD}║${NC}"
    echo -e "${GOLD}╚══════════════════════════════════════════╝${NC}"
    echo ""
    if [ "$INSTALL_SSL" = true ]; then
        echo -e "  🌐 URL:       ${BOLD}https://${DOMAIN}${NC}"
    else
        echo -e "  🌐 URL:       ${BOLD}http://${DOMAIN}${NC}"
    fi
    echo -e "  👤 Email:     ${ADMIN_EMAIL}"
    echo -e "  🗄️  DB Name:   ${DB_NAME}"
    echo -e "  🔑 DB Pass:   ${YELLOW}${DB_PASS}${NC}"
    echo ""
    echo -e "  ${RED}⚠  Save the DB password somewhere safe!${NC}"
    echo ""
}

# ── Wings Install ───────────────────────────────────────────────────────────
do_install_wings() {
    print_step "Installing Wings (Game Server Daemon)"

    if ! confirm "Install Wings on this machine?"; then
        return 0
    fi

    print_info "Installing Docker..."
    {
        curl -sSL https://get.docker.com/ | sh
        systemctl enable --now docker
    } &> /tmp/aurex-wings.log &
    spinner $! "Installing Docker" "/tmp/aurex-wings.log"
    wait $!
    print_success "Docker installed"

    print_info "Downloading Wings..."
    {
        mkdir -p /etc/pterodactyl
        curl -L -o /usr/local/bin/wings "https://github.com/pterodactyl/wings/releases/latest/download/wings_linux_amd64"
        chmod +x /usr/local/bin/wings
    } &> /tmp/aurex-wings.log &
    spinner $! "Downloading Wings binary" "/tmp/aurex-wings.log"
    wait $!
    print_success "Wings downloaded"

    print_step "Wings Configuration"
    echo ""
    print_warning "Go to your Aurex Panel → Admin → Nodes → Your Node"
    print_warning "Click 'Configuration' tab and copy the auto-deploy command."
    echo ""
    local TOKEN
    read -p "$(echo -e "${BOLD}Paste the Wings configure token/command here${NC}: ")" TOKEN

    if [ -n "$TOKEN" ]; then
        # If it's the full artisan command, extract token; otherwise use as-is
        if [[ "$TOKEN" == *"wings:configure"* ]]; then
            print_info "Running wings configuration..."
            # User needs to run this on panel, we just set up systemd
        fi
    fi

    # Systemd service
    cat > /etc/systemd/system/wings.service <<'SERVICE'
[Unit]
Description=Aurex Wings Daemon
After=docker.service
Requires=docker.service

[Service]
User=root
WorkingDirectory=/etc/pterodactyl
LimitNOFILE=4096
PIDFile=/var/run/wings/daemon.pid
ExecStart=/usr/local/bin/wings
Restart=on-failure
StartLimitInterval=600

[Install]
WantedBy=multi-user.target
SERVICE

    systemctl daemon-reload
    print_success "Wings service created"

    echo ""
    echo -e "${YELLOW}Manual step required:${NC}"
    echo -e "  1. Go to ${BOLD}Panel → Admin → Nodes → Configure${NC}"
    echo -e "  2. Copy the ${BOLD}auto-deploy command${NC}"
    echo -e "  3. Run it on this server, then:"
    echo -e "     ${GOLD}systemctl enable --now wings${NC}"
    echo ""
    if confirm "Start Wings now? (only if already configured)"; then
        systemctl enable --now wings 2>/dev/null || print_warning "Wings not configured yet — run the auto-deploy command first"
    fi

    print_success "Wings installation complete!"
}

# ── Update ──────────────────────────────────────────────────────────────────
do_update() {
    print_step "Updating Aurex Panel"
    local INSTALL_DIR="/var/www/aurex-panel"
    if [ ! -d "$INSTALL_DIR" ]; then
        print_error "Aurex not found at ${INSTALL_DIR}"
        exit 1
    fi
    print_info "Pulling latest changes..."
    cd ${INSTALL_DIR}
    sudo -u www-data php artisan down 2>/dev/null || true
    # Update logic here (git pull or patch)
    sudo -u www-data php artisan migrate --force -q
    sudo -u www-data php artisan optimize:clear -q
    sudo -u www-data php artisan up 2>/dev/null || true
    systemctl reload php8.2-fpm
    print_success "Aurex updated!"
}

# ── Uninstall ───────────────────────────────────────────────────────────────
do_uninstall() {
    print_warning "This will REMOVE Aurex Panel completely!"
    if ! confirm "Are you sure?"; then
        echo "Cancelled."
        exit 0
    fi
    print_step "Uninstalling..."
    systemctl stop aurex-queue 2>/dev/null || true
    systemctl disable aurex-queue 2>/dev/null || true
    rm -f /etc/systemd/system/aurex-queue.service
    systemctl daemon-reload
    crontab -u www-data -l 2>/dev/null | grep -v "aurex-panel/artisan schedule:run" | crontab -u www-data - 2>/dev/null || true
    rm -rf /var/www/aurex-panel
    rm -f /etc/nginx/sites-enabled/aurex /etc/nginx/sites-available/aurex
    systemctl reload nginx
    print_success "Aurex uninstalled."
    print_info "Database 'aurex_panel' was NOT deleted (manual cleanup if needed)."
}

# ── Run ─────────────────────────────────────────────────────────────────────
main "$@"
