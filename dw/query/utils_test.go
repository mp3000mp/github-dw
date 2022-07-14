package query

import (
	"errors"
	"testing"
	"time"

	"github.com/stretchr/testify/assert"
)

func TestCheckResponse(t *testing.T) {
	assert := assert.New(t)
	rl := RateLimiter{}

	// misc error
	r := CheckResponse(errors.New("test"), &rl, "search")
	assert.Equal(false, r)
	assert.Equal(true, rl.SearchLast429.IsZero())

	// no error
	r = CheckResponse(nil, &rl, "search")
	assert.Equal(true, r)
	assert.Equal(true, rl.SearchLast429.IsZero())

    // rate limiter error
	r = CheckResponse(errors.New("You have exceeded a secondary rate limit. Please wait a few minutes before you try again."), &rl, "search")
	assert.Equal(false, r)
	assert.Equal(false, rl.SearchLast429.IsZero())
}

func TestWaitBeforeQuery(t *testing.T) {
	assert := assert.New(t)
	now := time.Now()
	rl := RateLimiter{}

	// no data
	r := WaitBeforeQuery(rl, "search")
	assert.Equal(0, r)
	r = WaitBeforeQuery(rl, "core")
	assert.Equal(0, r)

	// no 429 nok
	rl.SearchLastQuery = now.Add(-119 * time.Second)
	rl.CoreLastQuery = now.Add(-500 * time.Millisecond)
	r = WaitBeforeQuery(rl, "search")
	assert.NotEqual(0, r)
	r = WaitBeforeQuery(rl, "core")
	assert.NotEqual(0, r)

	// no 429 ok
	rl.SearchLastQuery = now.Add(-121 * time.Second)
	rl.CoreLastQuery = now.Add(-800 * time.Millisecond)
	r = WaitBeforeQuery(rl, "search")
	assert.Equal(0, r)
	r = WaitBeforeQuery(rl, "core")
	assert.Equal(0, r)

	// 429 nok
	rl.SearchLast429 = now.Add(-3599 * time.Second)
	rl.CoreLast429 = now.Add(-3599 * time.Second)
	r = WaitBeforeQuery(rl, "search")
	assert.NotEqual(0, r)
	r = WaitBeforeQuery(rl, "core")
	assert.NotEqual(0, r)

	// 429 ok
	rl.SearchLast429 = now.Add(-3601 * time.Second)
	rl.CoreLast429 = now.Add(-3601 * time.Second)
	r = WaitBeforeQuery(rl, "search")
	assert.Equal(0, r)
	r = WaitBeforeQuery(rl, "core")
	assert.Equal(0, r)
}
