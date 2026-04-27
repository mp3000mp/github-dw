package query

import (
	"context"
	"testing"

	"github.com/google/go-github/v45/github"

	"github.com/stretchr/testify/assert"
	"github.com/migueleliasweb/go-github-mock/src/mock"
)

func TestQueryBlob(t *testing.T) {
	assert := assert.New(t)

	mockedClient := mock.NewMockedHTTPClient(
		mock.WithRequestMatch(
			mock.GetReposGitBlobsByOwnerByRepoByFileSha,
			github.Blob{
				Content: github.String("eyJvdWkiOiAibm9uIn0="),
			},
		),
	)
	ctx := context.Background()
	queryContext := Context{Client: github.NewClient(mockedClient), Ctx: &ctx}

	r, err := QueryBlob(&queryContext, "user", "repo", "sha")
	assert.Equal(nil, err)
	expected := Blob{Content: `{"oui": "non"}`}
	assert.Equal(expected, r)
	assert.Equal(true, queryContext.RateLimiter.SearchLast429.IsZero())
	assert.Equal(true, queryContext.RateLimiter.SearchLastQuery.IsZero())
	assert.Equal(true, queryContext.RateLimiter.CoreLast429.IsZero())
	assert.Equal(false, queryContext.RateLimiter.CoreLastQuery.IsZero())
}
