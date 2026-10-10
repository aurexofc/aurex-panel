#!/bin/bash

set -e

######################################################################################
#                                                                                    #
# Aurex Panel Installer                                                              #
# Based on 'pterodactyl-installer' by Vilhelm Prytz and contributors (GPL-3.0)      #
# https://github.com/pterodactyl-installer/pterodactyl-installer                      #
#                                                                                    #
# Aurex: https://github.com/aurexofc/aurex-panel                                      #
#                                                                                    #
######################################################################################

export GITHUB_SOURCE="1.0-develop"
export SCRIPT_RELEASE="v1.0"
export GITHUB_BASE_URL="https://raw.githubusercontent.com/aurexofc/aurex-panel"
export GITHUB_PATH="installer/ptero"

LOG_PATH="/var/log/aurex-installer.log"

# check for curl
if ! [ -x "$(command -v curl)" ]; then
  echo "* curl is required in order for this script to work."
  echo "* install using apt (Debian and derivatives) or yum/dnf (CentOS)"
  exit 1
fi

# Always remove lib.sh, before downloading it
[ -f /tmp/lib.sh ] && rm -rf /tmp/lib.sh
curl -sSL -o /tmp/lib.sh "$GITHUB_BASE_URL/$GITHUB_SOURCE/$GITHUB_PATH/lib/lib.sh?cb=$(date +%s)"
# shellcheck source=lib/lib.sh
source /tmp/lib.sh

execute() {
  echo -e "\n\n* aurex-installer $(date) \n\n" >>$LOG_PATH

  [[ "$1" == *"canary"* ]] && export GITHUB_SOURCE="1.0-develop" && export SCRIPT_RELEASE="canary"
  update_lib_source
  run_ui "${1//_canary/}" |& tee -a $LOG_PATH

  if [[ -n $2 ]]; then
    echo -e -n "* Installation of $1 completed. Do you want to proceed to $2 installation? (y/N): "
    read -r CONFIRM
    if [[ "$CONFIRM" =~ [Yy] ]]; then
      execute "$2"
    else
      error "Installation of $2 aborted."
      exit 1
    fi
  fi
}

# lib.sh already points GITHUB_URL at our installer/ptero subpath,
# so run_installer / run_ui / update_lib_source work as-is.

print_aurex_banner() {
  echo -e "\033[1;33m"
  cat <<'BANNER'
    ___   __  ______  _______  __
   / _ | / / / / _ \/ __/ _ \ \/ /
  / __ |/ /_/ / , _/ _// , _/\  /
 /_/ |_|\____/_/|_/___/_/|_| /_/

  ✦ AUREX PANEL - Premium Game Server Panel ✦
BANNER
  echo -e "\033[0m"
}

print_aurex_banner

done=false
while [ "$done" == false ]; do
  options=(
    "Install Aurex Panel"
    "Install Wings (game server daemon)"
    "Install both [0] and [1] on the same machine"
    "Uninstall panel or wings"
  )

  actions=(
    "panel"
    "wings"
    "panel;wings"
    "uninstall"
  )

  output "What would you like to do?"

  for i in "${!options[@]}"; do
    output "[$i] ${options[$i]}"
  done

  echo -n "* Input 0-$((${#actions[@]} - 1)): "
  read -r action

  [ -z "$action" ] && error "Input is required" && continue

  valid_input=("$(for ((i = 0; i <= ${#actions[@]} - 1; i += 1)); do echo "${i}"; done)")
  [[ ! " ${valid_input[*]} " =~ ${action} ]] && error "Invalid option"
  [[ " ${valid_input[*]} " =~ ${action} ]] && done=true && IFS=";" read -r i1 i2 <<<"${actions[$action]}" && execute "$i1" "$i2"
done

# Remove lib.sh, so next time the script is run the, newest version is downloaded.
rm -rf /tmp/lib.sh

echo ""
echo -e "\033[1;33m  ✓ AUREX installation script finished!\033[0m"
echo ""
