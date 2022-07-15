package query

import (
	"context"
	"testing"
	"time"

	"github.com/google/go-github/v45/github"

	"github.com/stretchr/testify/assert"
	"github.com/migueleliasweb/go-github-mock/src/mock"
)

func TestQueryRepo(t *testing.T) {
	assert := assert.New(t)

	mockedUser := github.User{Login: github.String("user")}
	mockedLicense := github.License{Name: github.String("license")}
	tp := time.Date(2022, time.July, 16, 1, 0, 0, 0, time.UTC)
	//tp, _ := time.Parse("2006-01-02 15:04:05", "2022-07-16 01:00:00")
	ts := github.Timestamp{tp}

	mockedClient := mock.NewMockedHTTPClient(
		mock.WithRequestMatch(
			mock.GetRepositories,
			github.Repository{
				CreatedAt: &ts,
				ForksCount: github.Int(1),
				FullName: github.String("fullName"),
				ID: github.Int64(1000),
				Language: github.String("PHP"),
				License: &mockedLicense,
				Name: github.String("repo"),
				OpenIssuesCount: github.Int(2),
				PushedAt: &ts,
				Size: github.Int(99),
				StargazersCount: github.Int(3),
				HTMLURL: github.String("url"),
				Owner: &mockedUser,
			},
		),
	)
	ctx := context.Background()
	queryContext := Context{Client: github.NewClient(mockedClient), Context: &ctx}

	r, err := QueryRepo(&queryContext, "user", "repo")
	assert.Equal(nil, err)
	languages := make(map[string]int, 0)
	languages["PHP"] = 10
	languages["go"] = 20
	expected := Repository{
		CreatedAt: "2022-07-16 01:00:00",
		ForksCount: 1,
		FullName: "fullName",
		ID: 1000,
		Languages: languages,
		LicenseName: "license",
		MainLanguage: "PHP",
		Name: "repo",
		OpenIssuesCount: 2,
		PushedAt: "2022-07-16 01:00:00",
		Size: 99,
		StargazersCount: 3,
		Topics: []string{"topicA", "topicB"},
		URL: "url",
		Username: "user",
	}
	assert.Equal(expected, r)
	assert.Equal(true, queryContext.RateLimiter.SearchLast429.IsZero())
	assert.Equal(true, queryContext.RateLimiter.SearchLastQuery.IsZero())
	assert.Equal(true, queryContext.RateLimiter.CoreLast429.IsZero())
	assert.Equal(false, queryContext.RateLimiter.CoreLastQuery.IsZero())
}
