# github-dw

Uses github API to create a datawarehouse

 - **Routine 1**: queries and lists all repositories containing file named x (composer.json, package.json...) in db
 - **Routine 2**: queries and stores repo infos (+languages) in db
 - **Routine 3**: queries and stores packages and versions in db


## Test

    golangci-lint run
    go test ./...


# Dev

    cd deployment/docker
    docker-compose run --rm dw 

## Launch
    
    go run main.go


## Build

    go build -o bin/


# github api rate-limit

- routine 1 => search 30 query / hour => 700 / day
- routine 2 => github API => 5000 query / hour => 120k / day
- limit dans header x-ratelimit-used / x-ratelimit-limit / x-ratelimit-remaining
- https://api.github.com/rate_limit
- if header Retry-After=x => faire une pause de x secondes
