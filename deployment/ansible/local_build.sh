#!/bin/bash
set -x

# dw
(cd ../../dw && CGO_ENABLED=0 go build -o bin/)

# backend
# (cd ../../backend && composer install)

# frontend
(
# get version
reg='(.+): *(.+)'

# get version
line=$(sed -n '/^app_version: /p' vars.yml)
[[ "$line" =~ $reg ]]
version="${BASH_REMATCH[2]}"

# get backend host
line=$(sed -n '/^backend_server_name: /p' vars.yml)
[[ "$line" =~ $reg ]]
backend_host="${BASH_REMATCH[2]}"

# build front
cd ../../frontend &&
  echo "VITE_VERSION=$version" > .env.production.local &&
  echo "VITE_BACKEND_BASE_URL=https://$backend_host" >> .env.production.local &&
  npm run build-only
)
