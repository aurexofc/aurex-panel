#!/bin/bash
#
# AUREX PANEL - Single-File Installer
# Based on pterodactyl-installer logic by Vilhelm Prytz and contributors (GPL-3.0)
# https://github.com/pterodactyl-installer/pterodactyl-installer
#
# Aurex: https://github.com/aurexofc/aurex-panel
#
# Usage: bash <(curl -sSL "https://raw.githubusercontent.com/aurexofc/aurex-panel/1.0-develop/installer/aurex-installer-v2.sh")
#

set -e
set -o pipefail

# ── Colors ──
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
CYAN='\033[0;36m'; GOLD='\033[1;33m'; NC='\033[0m'; BOLD='\033[1m'; DIM='\033[2m'

output()  { echo -e "* $1"; }
success() { echo -e "${GREEN}✓ $1${NC}"; }
error()   { echo -e "${RED}✖ $1${NC}"; }
warning() { echo -e "${YELLOW}⚠ $1${NC}"; }
info()    { echo -e "${CYAN}ℹ $1${NC}"; }

print_brake() {
  local n=${1:-70}
  printf '%*s\n' "$n" '' | tr ' ' '-'
}

# ── Config ──
AUREX_REPO="https://github.com/aurexofc/aurex-panel.git"
AUREX_BRANCH="1.0-develop"
AUREX_DIR="/var/www/aurex-panel"
PHP_V="8.3"

export DEBIAN_FRONTEND=noninteractive

# ── Helpers ──
gen_passwd() {
  local length=$1 password=""
  while [ ${#password} -lt "$length" ]; do
    password+=$(LC_ALL=C tr -dc 'A-Za-z0-9' </dev/urandom | head -c 64)
  done
  echo "$password"
}

valid_email() {
  [[ "$1" =~ ^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$ ]]
}

required_input() {
  local __resultvar=$1 prompt="$2" err="$3"
  local val=""
  while [ -z "$val" ]; do
    echo -n "* $prompt: "
    read -r val
    [ -z "$val" ] && error "$err"
  done
  eval "$__resultvar='$val'"
}

email_input() {
  local __resultvar=$1 prompt="$2" err="$3"
  local val=""
  while ! valid_email "$val"; do
    echo -n "* $prompt: "
    read -r val
    ! valid_email "$val" && error "$err"
  done
  eval "$__resultvar='$val'"
}

password_input() {
  local __resultvar=$1 prompt="$2" err="$3"
  local val="" val2=""
  while [ -z "$val" ] || [ "$val" != "$val2" ]; do
    echo -n "* $prompt: "
    read -rs val; echo
    echo -n "* Confirm: "
    read -rs val2; echo
    { [ -z "$val" ] || [ "$val" != "$val2" ]; } && error "$err"
  done
  eval "$__resultvar='$val'"
}

# ── OS detection ──
detect_os() {
  if [ -f /etc/os-release ]; then
    . /etc/os-release
    OS=$ID
    OS_VER=$VERSION_ID
  else
    error "Cannot detect OS. Ubuntu 22.04/24.04 or Debian 11/12 required."
    exit 1
  fi
  case "$OS" in
    ubuntu|debian) ;;
    *) error "Unsupported OS: $OS. Ubuntu or Debian required."; exit 1 ;;
  esac
  output "Detected $OS $OS_VER"
}

# ── Step 1: Dependencies ──
install_dependencies() {
  output "Installing system dependencies..."

  # Wait for background auto-updates to release apt lock (fresh VPS)
  info "Checking for background package locks..."
  for i in $(seq 1 30); do
    if ! fuser /var/lib/dpkg/lock-frontend >/dev/null 2>&1 \
    && ! fuser /var/lib/apt/lists/lock >/dev/null 2>&1; then
      break
    fi
    if [ "$i" -eq 30 ]; then
      info "Stopping stuck background updater..."
      pkill -f unattended-upgr 2>/dev/null || true
      sleep 2
    fi
    sleep 5
  done

  apt update

  # PHP 8.3 via sury.org (battle-tested, same as pterodactyl-installer)
  info "Adding PHP repository (sury.org)..."
  apt install -y software-properties-common apt-transport-https ca-certificates gnupg curl
  curl -fsSL https://packages.sury.org/php/apt.gpg -o /etc/apt/trusted.gpg.d/php.gpg
  echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list

  # Node.js 20
  info "Adding Node.js repository..."
  curl -fsSL https://deb.nodesource.com/setup_20.x | bash -

  apt update

  info "Installing packages (this takes a while)..."
  apt install -y \
    php${PHP_V} php${PHP_V}-{cli,common,gd,mysql,mbstring,bcmath,xml,fpm,curl,zip} \
    mariadb-common mariadb-server mariadb-client \
    nginx redis-server \
    zip unzip tar git cron nodejs

  if [ "$CONFIGURE_LETSENCRYPT" = true ]; then
    apt install -y certbot python3-certbot-nginx
  fi

  # Composer
  info "Installing composer..."
  curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

  systemctl enable redis-server mariadb nginx
  systemctl start redis-server mariadb

  success "Dependencies installed!"
}

# ── Step 2: Download Aurex ──
download_aurex() {
  output "Downloading Aurex panel..."
  mkdir -p "$AUREX_DIR"
  rm -rf /tmp/aurex-dl
  git clone --depth 1 --branch "$AUREX_BRANCH" "$AUREX_REPO" /tmp/aurex-dl
  cp -r /tmp/aurex-dl/. "$AUREX_DIR"/
  rm -rf /tmp/aurex-dl "$AUREX_DIR"/.git
  chmod -R 755 "$AUREX_DIR"/storage "$AUREX_DIR"/bootstrap/cache
  cp "$AUREX_DIR"/.env.example "$AUREX_DIR"/.env 2>/dev/null || true
  chown -R www-data:www-data "$AUREX_DIR"
  success "Aurex downloaded!"
}

# ── Step 3: Composer ──
install_composer_deps() {
  output "Installing PHP dependencies..."
  cd "$AUREX_DIR"
  COMPOSER_ALLOW_SUPERUSER=1 sudo -u www-data composer install --no-dev --optimize-autoloader
  success "PHP dependencies installed!"
}

# ── Step 4: Frontend ──
build_frontend() {
  output "Building frontend (this takes a while)..."
  cd "$AUREX_DIR"
  sudo -u www-data npm install
  sudo -u www-data npm run build
  success "Frontend built!"
}

# ── Step 5: Database ──
setup_database() {
  output "Setting up database..."

  # MariaDB root uses unix_socket by default on Ubuntu/Debian - no password needed
  MYSQL_CMD="mariadb -u root"

  $MYSQL_CMD -e "CREATE DATABASE IF NOT EXISTS ${MYSQL_DB} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
  # Drop + recreate so re-runs always sync password; cover both TCP and socket hosts
  for h in '127.0.0.1' 'localhost'; do
    $MYSQL_CMD -e "DROP USER IF EXISTS '${MYSQL_USER}'@${h};"
    $MYSQL_CMD -e "CREATE USER '${MYSQL_USER}'@${h} IDENTIFIED BY '${MYSQL_PASSWORD}';"
    $MYSQL_CMD -e "GRANT ALL PRIVILEGES ON ${MYSQL_DB}.* TO '${MYSQL_USER}'@${h} WITH GRANT OPTION;"
  done
  $MYSQL_CMD -e "FLUSH PRIVILEGES;"

  success "Database '${MYSQL_DB}' created!"
}

# ── Step 6: Configure ──
configure_aurex() {
  output "Configuring Aurex..."
  cd "$AUREX_DIR"

  local app_url="http://$FQDN"
  [ "$CONFIGURE_LETSENCRYPT" = true ] && app_url="https://$FQDN"

  sudo -u www-data php artisan key:generate --force

  sudo -u www-data php artisan p:environment:setup \
    --author="$EMAIL" \
    --url="$app_url" \
    --timezone="$TIMEZONE" \
    --cache="redis" \
    --session="redis" \
    --queue="redis" \
    --redis-host="localhost" \
    --redis-pass="null" \
    --redis-port="6379" \
    --telemetry="false" \
    --settings-ui=true

  sudo -u www-data php artisan p:environment:database \
    --host="127.0.0.1" \
    --port="3306" \
    --database="$MYSQL_DB" \
    --username="$MYSQL_USER" \
    --password="$MYSQL_PASSWORD"

  sudo -u www-data php artisan migrate --seed --force

  # Aurex-specific seeders
  for seeder in AurexPlansSeeder AurexTopupPackagesSeeder AurexPremiumPackageSeeder AurexPrebotSeeder; do
    sudo -u www-data php artisan db:seed --class="Database\\Seeders\\${seeder}" --force 2>/dev/null || warning "Seeder $seeder skipped"
  done

  sudo -u www-data php artisan p:user:make \
    --email="$USER_EMAIL" \
    --username="$USER_USERNAME" \
    --name-first="$USER_FIRSTNAME" \
    --name-last="$USER_LASTNAME" \
    --password="$USER_PASSWORD" \
    --admin=1

  chown -R www-data:www-data "$AUREX_DIR"
  success "Aurex configured!"
}

# ── Step 7: Nginx ──
configure_nginx() {
  output "Configuring nginx..."

  cat > /etc/nginx/sites-available/aurex <<NGINX
server {
    listen 80;
    listen [::]:80;
    server_name ${FQDN};
    root ${AUREX_DIR}/public;
    index index.php;
    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    access_log off;
    error_log  /var/log/nginx/aurex.app-error.log error;

    client_max_body_size 100m;
    client_body_timeout 120s;
    sendfile off;

    location ~ \\.php\$ {
        fastcgi_split_path_info ^(.+\\.php)(/.+)\$;
        fastcgi_pass unix:/run/php/php${PHP_V}-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param PHP_VALUE "upload_max_filesize = 100M \\n post_max_size=100M";
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_param HTTP_PROXY "";
        fastcgi_intercept_errors off;
        fastcgi_buffer_size 16k;
        fastcgi_buffers 4 16k;
        fastcgi_connect_timeout 300;
        fastcgi_send_timeout 300;
        fastcgi_read_timeout 300;
    }

    location ~ /\\.ht {
        deny all;
    }
}
NGINX

  rm -f /etc/nginx/sites-enabled/default
  ln -sf /etc/nginx/sites-available/aurex /etc/nginx/sites-enabled/aurex
  nginx -t
  systemctl reload nginx 2>/dev/null || systemctl restart nginx

  success "Nginx configured!"

  if [ "$CONFIGURE_LETSENCRYPT" = true ]; then
    output "Installing SSL certificate..."
    if certbot --nginx --redirect --no-eff-email --email "$EMAIL" -d "$FQDN"; then
      success "SSL installed!"
    else
      warning "SSL failed - site will work over HTTP. Run certbot manually later."
    fi
  fi
}

# ── Step 8: Cron + Queue ──
setup_services() {
  output "Setting up background services..."

  # Cronjob
  (crontab -l -u www-data 2>/dev/null; echo "* * * * * php ${AUREX_DIR}/artisan schedule:run >> /dev/null 2>&1") | crontab -u www-data -
  success "Cronjob installed!"

  # Queue worker
  cat > /etc/systemd/system/aurex-queue.service <<QSERVICE
[Unit]
Description=Aurex Queue Worker
After=redis-server.service

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php ${AUREX_DIR}/artisan queue:work --queue=high,standard,low --sleep=3 --tries=3
StartLimitInterval=180
StartLimitBurst=30
RestartSec=5s

[Install]
WantedBy=multi-user.target
QSERVICE

  systemctl daemon-reload
  systemctl enable aurex-queue.service
  systemctl start aurex-queue
  success "Queue worker installed!"
}

# ── Firewall ──
setup_firewall() {
  if [ "$CONFIGURE_FIREWALL" = true ]; then
    output "Configuring firewall..."
    apt install -y ufw
    ufw --force enable
    ufw allow 22/tcp
    ufw allow 80/tcp
    ufw allow 443/tcp
    success "Firewall configured (22, 80, 443)!"
  fi
}

# ── Main ──
main() {
  clear
  echo -e "${GOLD}"
  echo '    ___   __  ______  _______  __'
  echo '   / _ | / / / / _ \/ __/ _ \ \/ /'
  echo '  / __ |/ /_/ / , _/ _// , _/\  /'
  echo ' /_/ |_|\____/_/|_/___/_/|_| /_/'
  echo -e "${NC}"
  echo -e "  ${GOLD}✦ AUREX PANEL - Premium Game Server Panel ✦${NC}"
  print_brake 55
  echo ""

  if [ "$EUID" -ne 0 ]; then
    error "Please run as root!"
    exit 1
  fi

  detect_os

  echo ""
  echo -e "${BOLD}What would you like to do?${NC}"
  echo -e "  ${GOLD}[0]${NC} Install Aurex Panel"
  echo -e "  ${GOLD}[1]${NC} Install Wings (game server daemon)"
  echo -e "  ${GOLD}[2]${NC} Install both on the same machine"
  echo ""
  echo -n "* Input 0-2: "
  read -r ACTION

  case "$ACTION" in
    0|2) DO_PANEL=true ;;
    1)   DO_PANEL=false ;;
    *)   error "Invalid option"; exit 1 ;;
  esac
  case "$ACTION" in
    1|2) DO_WINGS=true ;;
    *)   DO_WINGS=false ;;
  esac

  if [ "$DO_PANEL" = true ]; then
    echo ""
    echo -e "${CYAN}▶ Panel Configuration${NC}"
    print_brake 55
    required_input FQDN "Domain/subdomain (e.g. panel.example.com)" "Domain cannot be empty"
    email_input EMAIL "Email for SSL and admin" "Invalid email"
    echo ""
    echo -e "${CYAN}▶ Admin Account${NC}"
    print_brake 55
    email_input USER_EMAIL "Admin email" "Invalid email"
    required_input USER_USERNAME "Admin username" "Username cannot be empty"
    required_input USER_FIRSTNAME "Admin first name" "Cannot be empty"
    required_input USER_LASTNAME "Admin last name" "Cannot be empty"
    password_input USER_PASSWORD "Admin password (min 8 chars)" "Passwords empty or do not match"
    echo ""
    echo -n "* Install free SSL certificate (Let's Encrypt)? (y/n): "
    read -r SSL_ASK
    [[ "$SSL_ASK" =~ [Yy] ]] && CONFIGURE_LETSENCRYPT=true || CONFIGURE_LETSENCRYPT=false
    echo -n "* Configure firewall (UFW - ports 22, 80, 443)? (y/n): "
    read -r FW_ASK
    [[ "$FW_ASK" =~ [Yy] ]] && CONFIGURE_FIREWALL=true || CONFIGURE_FIREWALL=false

    MYSQL_DB="aurex_panel"
    MYSQL_USER="aurex_user"
    MYSQL_PASSWORD=$(gen_passwd 32)
    TIMEZONE="Asia/Karachi"

    echo ""
    echo -e "${BOLD}Summary:${NC}"
    echo -e "  Domain:       ${GOLD}${FQDN}${NC}"
    echo -e "  Admin email:  ${GOLD}${USER_EMAIL}${NC}"
    echo -e "  Directory:    ${DIM}${AUREX_DIR}${NC}"
    echo -e "  Database:     ${DIM}${MYSQL_DB}${NC}"
    echo ""
    echo -n "* Start installation? (y/n): "
    read -r GO
    [[ ! "$GO" =~ [Yy] ]] && error "Aborted." && exit 1

    echo ""
    install_dependencies
    setup_firewall
    download_aurex
    install_composer_deps
    build_frontend
    setup_database
    configure_aurex
    configure_nginx
    setup_services

    echo ""
    print_brake 55
    echo -e "${GREEN}${BOLD}  ✓ AUREX PANEL INSTALLED SUCCESSFULLY!${NC}"
    print_brake 55
    echo -e "  🌐 URL:       ${GOLD}https://${FQDN}${NC}"
    echo -e "  👤 Email:     ${USER_EMAIL}"
    echo -e "  🗄️  DB Name:   ${MYSQL_DB}"
    echo -e "  🔑 DB Pass:   ${GOLD}${MYSQL_PASSWORD}${NC}"
    echo ""
    echo -e "  ${RED}⚠ Save the DB password somewhere safe!${NC}"
    echo ""
  fi

  if [ "$DO_WINGS" = true ]; then
    echo ""
    warning "Wings installation: use the official pterodactyl-installer wings script,"
    echo "  then configure it to point at your Aurex panel."
    echo "  See: https://github.com/pterodactyl-installer/pterodactyl-installer"
  fi
}

main
