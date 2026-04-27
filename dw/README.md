# github-dw

Uses the GitHub API to build a datawarehouse of repositories and their dependencies.

## How it works

- **Pre-routine**: reloads the work queues from DB (runs every 60s)
- **Routine 1**: searches GitHub for repositories containing a target file (`composer.json`, `package.json`, `go.mod`…) and stores them in DB
- **Routine 2**: fetches full repository metadata (stars, forks, language, topics…) for each discovered repo
- **Routine 3**: downloads and parses the dependency file to extract package names and versions


## Local development (Docker)

All commands run from `deployment/docker/`.

**First-time setup** — create your `.env` file:

```bash
cp ../../dw/.env.example ../../dw/.env
# then fill in api_key with a real GitHub personal access token
```

**Run the app:**

```bash
docker-compose run --rm dw go run main.go
```

**Migrate the database** (first run, or after a schema change):

```bash
docker-compose run --rm dw go run main.go migrate
```
# detects concurrent accesses that are not synchronized during compilation
**Run tests:**

```bash
docker-compose run --rm dw_test
```

Tests have no external dependencies — GitHub API and DB are mocked.

**Start supporting services only** (DB + adminer):

```bash
docker-compose up mariadb adminer
```

Adminer is available at http://localhost:5001.


**Lint:**

```bash
docker-compose run --rm dw_lint
```


## Build

```bash
go build -o bin/
```


## GitHub API rate limits

The GitHub API has two separate quotas for authenticated requests:

| Type | Limit | Implemented interval | Throughput |
|------|-------|----------------------|------------|
| **Search** (code search) | 30 req/min | 120s between calls | 30 req/h — ~720/day |
| **Core** (repo, blob…) | 5 000 req/h | 720ms between calls | ~5 000 req/h — ~120 000/day |

**Why so conservative on Search?**  
GitHub free accounts are also capped at **1 000 results per search query**. Routine 1 works around this by iterating over file sizes (1 byte, 2 bytes, …) — each size is a separate query returning up to 1 000 results. Running 30 size queries per hour is intentional.

**Rate limit headers** returned by the API:

```
X-RateLimit-Limit      total quota
X-RateLimit-Remaining  remaining calls in the current window
X-RateLimit-Used       calls used in the current window
X-RateLimit-Reset      UNIX timestamp when the window resets
```

You can also check current usage at: https://api.github.com/rate_limit

**When a 429 is received**, the code records the timestamp and waits 1 hour before retrying (`waitAfter429 = 3600s` in `query/utils.go`). The `Retry-After` header sent by GitHub is not yet read — the 1-hour wait is hardcoded.
