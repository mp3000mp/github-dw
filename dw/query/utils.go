package query

import (
	"context"
	"log"
	"strings"
	"time"

	"github.com/google/go-github/v45/github"
    "golang.org/x/oauth2"
)

func CheckResponse(err error, rateLimiter *RateLimiter, rateType string) bool {
	if err != nil {
		if strings.Contains(err.Error(), "rate limit") {
			if rateType == "search" {
				rateLimiter.SearchLast429 = time.Now()
			} else {
				rateLimiter.CoreLast429 = time.Now()
			}
		}
 		log.Printf("Unknown response error: %s", err.Error())
 		return false
	}

	return true
}

func CreateClient(ctx context.Context, apiKey string) *github.Client {
	ts := oauth2.StaticTokenSource(
		&oauth2.Token{AccessToken: apiKey},
	)
	tc := oauth2.NewClient(ctx, ts)
	return github.NewClient(tc)
}

const TickSearch = time.Second * 120 // 30 queries / hour = 1/(30/3600) = 120s
const tickCore = time.Millisecond * 720 // 5000 queries / hour = 1000/(5000/3600) = 720ms
const waitAfter429 = time.Second * 3600

func WaitBeforeQuery(rateLimiter RateLimiter, rateType string, doWait bool) int {
	wait := 0
	var tick time.Duration
	var last429 time.Time
	var lastQuery time.Time

	if rateType == "search" {
		last429 = rateLimiter.SearchLast429
		lastQuery = rateLimiter.SearchLastQuery
		tick = TickSearch
	} else {
		last429 = rateLimiter.CoreLast429
		lastQuery = rateLimiter.CoreLastQuery
		tick = tickCore
	}

	if !last429.IsZero() {
		diff := time.Until(last429.Add(waitAfter429)).Milliseconds()
		if diff > 0 {
			log.Printf("Ratelimiter 429: %dms\n", int(diff))
			wait = int(diff)+1000
		}
	}
	if !lastQuery.IsZero() {
		diff := time.Until(lastQuery.Add(tick)).Milliseconds()
		if diff > 0 {
			//log.Printf("Ratelimiter last query: %dms\n", int(diff))
			wait = int(diff)+1000
		}
	}

	if wait > 0 && doWait {
		time.Sleep(time.Duration(wait) * time.Millisecond)
	}
	return wait
}
