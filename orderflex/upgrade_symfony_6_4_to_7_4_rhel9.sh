#!/usr/bin/env bash

# ##############################################
# Upgrade Symfony 6.4 to 7.4 on RHEL9
#
# Final upgrade steps:
# 0) git pull
# 1a) composer self-update
# 1b) composer install
# 1c) php bin/console cache:clear
# 2) Convert array to json
#    php bin/console app:convert-array-to-json --all --apply --alter-schema
# 3) bash prepare_migration.sh
# 4) Run migration
#    php bin/console doctrine:migrations:status
#    php bin/console doctrine:migrations:migrate --all-or-nothing
# 5) Clear cache and deploy
#    php bin/console cache:clear
#    bash deploy.sh
# 6) Run all tests
#    ./vendor/bin/phpunit
#
# bash upgrade_symfony_6_4_to_7_4_rhel9.sh
# ##############################################

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

trap 'echo "[ERROR] Upgrade failed at line $LINENO." >&2' ERR

require_command() {
  if ! command -v "$1" >/dev/null 2>&1; then
    echo "[ERROR] Required command not found: $1" >&2
    exit 1
  fi
}

run_step() {
  local label="$1"
  shift
  echo
  echo "============================================================"
  echo "$label"
  echo "============================================================"
  "$@"
}

run_shell_step() {
  local label="$1"
  local script="$2"
  echo
  echo "============================================================"
  echo "$label"
  echo "============================================================"
  bash "$SCRIPT_DIR/$script"
}

run_shell_step_as_root() {
  local label="$1"
  local script="$2"
  echo
  echo "============================================================"
  echo "$label"
  echo "============================================================"
  if [[ "$EUID" -eq 0 ]]; then
    bash "$SCRIPT_DIR/$script"
  else
    sudo -n bash "$SCRIPT_DIR/$script"
  fi
}

require_command git
require_command composer
require_command php
require_command bash

if [[ ! -f "$SCRIPT_DIR/bin/console" ]]; then
  echo "[ERROR] This script must be run from the Symfony project root." >&2
  exit 1
fi

echo

echo "########## Upgrade Symfony 6.4 to 7.4 ##############"
echo "Project root: $SCRIPT_DIR"
echo

run_step "0) git pull" git pull

run_step "1a) composer self-update" composer self-update

run_step "1b) composer install" composer install

run_step "1c) php bin/console cache:clear" php bin/console cache:clear

run_step "2) Convert array to json" php bin/console app:convert-array-to-json --all --apply --alter-schema

run_shell_step "3) bash prepare_migration.sh" prepare_migration.sh

run_step "4a) php bin/console doctrine:migrations:status" php bin/console doctrine:migrations:status

run_step "4b) php bin/console doctrine:migrations:migrate --all-or-nothing" php bin/console doctrine:migrations:migrate --all-or-nothing

run_step "5a) php bin/console cache:clear" php bin/console cache:clear

run_shell_step_as_root "5b) bash deploy.sh" deploy.sh

#run_step "6) Run all tests" ./vendor/bin/phpunit

echo

echo "##############################################"
echo "Symfony upgrade workflow completed successfully."
echo "##############################################"
