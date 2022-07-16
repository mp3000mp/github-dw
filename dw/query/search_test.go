package query

import (
	"context"
	"testing"

	"github.com/google/go-github/v45/github"

	"github.com/stretchr/testify/assert"
	"github.com/migueleliasweb/go-github-mock/src/mock"
)

func TestQuerySearchCodes(t *testing.T) {
	assert := assert.New(t)

	f := false
	mockedUserA := github.User{Login: github.String("userA")}
	mockedUserB := github.User{Login: github.String("userB")}
	mockedRepoA := github.Repository{
		Fork: &f,
		HTMLURL: github.String("URLA"),
		Name: github.String("repoA"),
		Owner: &mockedUserA,
		Private: &f,
	}
	mockedRepoB := github.Repository{
		Fork: &f,
		HTMLURL: github.String("URLB"),
		Name: github.String("repoB"),
		Owner: &mockedUserB,
		Private: &f,
	}
	mockedCodeResultA := github.CodeResult{
		Path: github.String("pathA"),
		Repository: &mockedRepoA,
		SHA: github.String("SHAA"),
	}
	mockedCodeResultB := github.CodeResult{
		Path: github.String("pathB"),
		Repository: &mockedRepoB,
		SHA: github.String("SHAB"),
	}
	mockedCodeResults := []*github.CodeResult{
		&mockedCodeResultA,
		&mockedCodeResultB,
	}

	mockedClient := mock.NewMockedHTTPClient(
		mock.WithRequestMatch(
			mock.GetSearchCode,
			github.CodeSearchResult{
				Total: github.Int(1001),
				CodeResults: mockedCodeResults,
			},
		),
	)
	ctx := context.Background()
	queryContext := Context{Client: github.NewClient(mockedClient), Context: &ctx}

	r, maxPage, err := QuerySearchCodes(&queryContext, "test.txt", 100, 0, 100)
	assert.Equal(nil, err)
	assert.Equal(11, maxPage)
	expected := []SearchCodeItem{
		{
			User: "userA",
			Name: "repoA",
			URL: "URLA",
			Path: "pathA",
			SHA: "SHAA",
		},
		{
			User: "userB",
			Name: "repoB",
			URL: "URLB",
			Path: "pathB",
			SHA: "SHAB",
		},
	}
	assert.Equal(expected, r)
	assert.Equal(true, queryContext.RateLimiter.SearchLast429.IsZero())
	assert.Equal(false, queryContext.RateLimiter.SearchLastQuery.IsZero())
	assert.Equal(true, queryContext.RateLimiter.CoreLast429.IsZero())
	assert.Equal(true, queryContext.RateLimiter.CoreLastQuery.IsZero())
}
