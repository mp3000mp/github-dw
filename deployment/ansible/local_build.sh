#!/bin/bash

# Usage:
#   ./local_build.sh                     # builds backend, frontend and dw
#   ./local_build.sh backend frontend    # builds only the given targets
# Called by site.yml with the targets matching the run tags.

cd "$(dirname "$0")"

TARGETS="${*:-backend frontend dw}"
for t in $TARGETS; do
  if [ "$t" != "backend" ] && [ "$t" != "frontend" ] && [ "$t" != "dw" ]; then
    echo "Unknown build target: $t (expected backend, frontend or dw)" >&2
    exit 1
  fi
done

set -euxo pipefail

# dw
# Go version read from go.mod, like the CI (setup-go go-version-file): independent of the local Go.
if [[ " $TARGETS " == *" dw "* ]]; then
(
go_version=$(sed -nE 's/^go ([0-9.]+)$/\1/p' ../../dw/go.mod)
go_cache="${XDG_CACHE_HOME:-$HOME/.cache}/go-docker"
mkdir -p "$go_cache"
docker run --rm --user "$(id -u):$(id -g)" \
  -e CGO_ENABLED=0 -e GOPATH=/cache/gopath -e GOCACHE=/cache/build -e GOTOOLCHAIN=local \
  -v "$go_cache:/cache" -v "$PWD/../../dw:/src" -w /src \
  "golang:$go_version" go build -o bin/
)
fi

# backend (prod vendor/ only, shipped by deploy_back)
# Built in a staging copy so the local vendor/ keeps its dev dependencies.
# Dev image: same PHP and extensions as prod, plus composer. --pull: same base as the server.
# --no-scripts: cache:clear would boot dev bundles removed by --no-dev; the server clears its own cache (launch role).
if [[ " $TARGETS " == *" backend "* ]]; then
(
build_dir="$PWD/.build/backend"
composer_cache="${XDG_CACHE_HOME:-$HOME/.cache}/composer"
mkdir -p "$build_dir" "$composer_cache"
rsync -a --delete --exclude=/vendor --exclude=/var --exclude=/coverage --exclude=/.env.local ../../backend/ "$build_dir/"
docker build --pull -q --target dev -t ghf-backend-build ../docker/backend
docker run --rm --user "$(id -u):$(id -g)" -e COMPOSER_HOME=/tmp/composer \
  -v "$composer_cache:/tmp/composer/cache" -v "$build_dir:/var/www/html" ghf-backend-build \
  composer install --no-dev --no-scripts --no-interaction --optimize-autoloader --classmap-authoritative
)
fi

# frontend
if [[ " $TARGETS " == *" frontend "* ]]; then
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
  npm ci &&
  npm run build-only
)
fi
