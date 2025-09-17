#!/usr/bin/env bash

# Automatically resolve the path to parameters.yml
#PARAM_FILE="$(dirname "$0")/config/parameters.yml"

if [ -z "$bashpath" ]
  then
    bashpath=$1
fi

if [ -z "$bashpath" ]; then
    #bashpath="/usr/local/bin"
    bashpath="/srv/order-lab"
fi

echo secret_update.sh: bashpath=$bashpath
PARAM_FILE="$bashpath/order-lab-$1/orderflex/config/parameters.yml"

# Ensure the file exists
if [[ ! -f "$PARAM_FILE" ]]; then
    echo "Error: parameters.yml not found at $PARAM_FILE"
    exit 1
fi

# Extract current secret value
current_secret=$(grep -E '^\s*secret:\s*[a-f0-9]+' "$PARAM_FILE" | awk '{print $2}')

# Validate the extracted secret
if [[ -z "$current_secret" ]]; then
    echo "Error: Could not find a valid 'secret' value in parameters.yml"
    exit 1
fi

# Generate a new secret of the same length
secret_length=${#current_secret}
new_secret=$(openssl rand -hex $((secret_length / 2)))

# Replace the old secret with the new one
sed -i "s/^\(\s*secret:\s*\)$current_secret/\1$new_secret/" "$PARAM_FILE"

echo "✅ Secret updated successfully in $PARAM_FILE"





