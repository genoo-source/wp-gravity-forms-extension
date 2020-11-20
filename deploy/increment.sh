#!/usr/bin/env bash

# Get needed versions from a
PLUGIN_CURRENT_VERSION=$(awk '/    Version/{print $NF}' wp-starter.php)
PLUGIN_NEXT_VERSION=$(echo $PLUGIN_CURRENT_VERSION | awk -F. -v OFS=. 'NF==1{print ++$NF}; NF>1{$NF=sprintf("%0*d", length($NF), ($NF+1)); print}')

# Replace versions with new version
# - In main file
sed -i "" "s/${PLUGIN_CURRENT_VERSION}/${PLUGIN_NEXT_VERSION}/g" wp-starter.php
# - In versin file
echo "{\"version\": \"${PLUGIN_NEXT_VERSION}\"}" > version.json

echo "New version: "$PLUGIN_NEXT_VERSION
