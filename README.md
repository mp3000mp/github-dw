# github-dw

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
    
    go run src/main.go

## Build

    go build -o bin/
