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

line=$(sed -n '/^app_version: /p' vars.yml)
[[ "$line" =~ $reg ]]
version="${BASH_REMATCH[2]}"

# get backend url
line=$(sed -n '/^backend_server_name: /p' vars.yml)
[[ "$line" =~ $reg ]]
url="https://${BASH_REMATCH[2]}"

echo "$version"
echo "$url"

cd ../../frontend &&
  mv config/variables.json config/variables.tmp.json &&
  echo "{\"APP_VERSION\":\"$version\",\"URL\":\"$url\"}" > config/variables.json
  npm run build &&
  cp config/variables.tmp.json config/variables.json
)
