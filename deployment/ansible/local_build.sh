#!/bin/bash
set -x

# dw
(cd ../../dw && CGO_ENABLED=0 go build -o bin/)

# backend
# (cd ../../backend && composer install)

# frontend
(cd ../../frontend && npm run build)
