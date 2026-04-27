package query

import (
	"errors"
	"fmt"
	"testing"
	"time"

	"github.com/stretchr/testify/assert"
)

func TestCheckResponse(t *testing.T) {
	assert := assert.New(t)
	ctx := &Context{}

	// no error
	r := CheckResponse(nil, ctx, "search")
	assert.Equal(true, r)
	assert.Equal(true, ctx.RateLimiter.SearchLast429.IsZero())

	fmt.Println("------ Test output START ------")
	// misc error
	r = CheckResponse(errors.New("test"), ctx, "search")
	assert.Equal(false, r)
	assert.Equal(true, ctx.RateLimiter.SearchLast429.IsZero())

	// rate limiter error
	r = CheckResponse(errors.New("You have exceeded a secondary rate limit. Please wait a few minutes before you try again."), ctx, "search")
	assert.Equal(false, r)
	assert.Equal(false, ctx.RateLimiter.SearchLast429.IsZero())
	fmt.Println("------ Test output END ------")
}

func TestWaitBeforeQuery(t *testing.T) {
	assert := assert.New(t)
	now := time.Now()
	ctx := &Context{}

	// no data
	r := WaitBeforeQuery(ctx, "search", false)
	assert.Equal(0, r)
	r = WaitBeforeQuery(ctx, "core", false)
	assert.Equal(0, r)

	// no 429 nok
	ctx.RateLimiter.SearchLastQuery = now.Add(-119 * time.Second)
	ctx.RateLimiter.CoreLastQuery = now.Add(-500 * time.Millisecond)
	r = WaitBeforeQuery(ctx, "search", false)
	assert.NotEqual(0, r)
	r = WaitBeforeQuery(ctx, "core", false)
	assert.NotEqual(0, r)

	// no 429 ok
	ctx.RateLimiter.SearchLastQuery = now.Add(-121 * time.Second)
	ctx.RateLimiter.CoreLastQuery = now.Add(-800 * time.Millisecond)
	r = WaitBeforeQuery(ctx, "search", false)
	assert.Equal(0, r)
	r = WaitBeforeQuery(ctx, "core", false)
	assert.Equal(0, r)

	fmt.Println("------ Test output START ------")
	// 429 nok
	ctx.RateLimiter.SearchLast429 = now.Add(-3599 * time.Second)
	ctx.RateLimiter.CoreLast429 = now.Add(-3599 * time.Second)
	r = WaitBeforeQuery(ctx, "search", false)
	assert.NotEqual(0, r)
	r = WaitBeforeQuery(ctx, "core", false)
	assert.NotEqual(0, r)
	fmt.Println("------ Test output END ------")

	// 429 ok
	ctx.RateLimiter.SearchLast429 = now.Add(-3601 * time.Second)
	ctx.RateLimiter.CoreLast429 = now.Add(-3601 * time.Second)
	r = WaitBeforeQuery(ctx, "search", false)
	assert.Equal(0, r)
	r = WaitBeforeQuery(ctx, "core", false)
	assert.Equal(0, r)
}
