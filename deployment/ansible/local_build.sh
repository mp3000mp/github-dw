#!/bin/bash
set -x

# dw
(cd ../../dw && CGO_ENABLED=0 go build -o bin/)

# frontend
# (cd ../../frontend && npm run build)
