package query

import (
	"context"
	"log"
	"strings"
	"time"

	"github.com/google/go-github/v45/github"
	"golang.org/x/oauth2"
)

func CheckResponse(err error, ctx *Context, rateType string) bool {
	if err != nil {
		if strings.Contains(err.Error(), "rate limit") {
			ctx.RateLimiterMu.Lock()
			if rateType == "search" {
				ctx.RateLimiter.SearchLast429 = time.Now()
			} else {
				ctx.RateLimiter.CoreLast429 = time.Now()
			}
			ctx.RateLimiterMu.Unlock()
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

const TickSearch = time.Second * 120    // 30 queries / hour = 1/(30/3600) = 120s
const tickCore = time.Millisecond * 720 // 5000 queries / hour = 1000/(5000/3600) = 720ms
const waitAfter429 = time.Second * 3600

func WaitBeforeQuery(ctx *Context, rateType string, doWait bool) int {
	ctx.RateLimiterMu.Lock()
	var tick time.Duration
	var last429 time.Time
	var lastQuery time.Time
	if rateType == "search" {
		last429 = ctx.RateLimiter.SearchLast429
		lastQuery = ctx.RateLimiter.SearchLastQuery
		tick = TickSearch
	} else {
		last429 = ctx.RateLimiter.CoreLast429
		lastQuery = ctx.RateLimiter.CoreLastQuery
		tick = tickCore
	}
	ctx.RateLimiterMu.Unlock()

	wait := 0
	if !last429.IsZero() {
		diff := time.Until(last429.Add(waitAfter429)).Milliseconds()
		if diff > 0 {
			log.Printf("Ratelimiter 429: %dms\n", int(diff))
			wait = int(diff) + 1000
		}
	}
	if !lastQuery.IsZero() {
		diff := time.Until(lastQuery.Add(tick)).Milliseconds()
		if diff > 0 {
			wait = int(diff) + 1000
		}
	}

	if wait > 0 && doWait {
		time.Sleep(time.Duration(wait) * time.Millisecond)
	}
	return wait
}
