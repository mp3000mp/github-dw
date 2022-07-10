# github-dw

Uses github API to create a datawarehouse

 - **Routine 1**: queries and lists all repositories containing file named x (composer.json, package.json...) in db
 - **Routine 2**: queries and stores repo infos (+languages) in db
 - **Routine 3**: queries and stores packages and versions in db


## Json format

```
version: string
crawledAt: datetime
repo:
  createdAt: datetime
  pushedAt: datetime
  url: string
  userName: string
  repoName: string
  description: string
  forksCount: int
  starsCount: int
  openIssuesCount: int
  size: float
  tags: string[]
  licence:
    key: string
    name: string
  languages: string[]
  packages[]:
    name: string
    version: string
```


## Test

    golangci-lint run
    go test ./...


## Launch
    
    go run main.go


## Build

    go build -o bin/


# github api rate-limit

- routine 1 => github API => 5000 query / hour => 120k / day
- routine 2 => search 30 query / hour => 700 / day
- limit dans header x-ratelimit-used / x-ratelimit-limit / x-ratelimit-remaining
- https://api.github.com/rate_limit
- if header Retry-After=x => faire une pause de x secondes
