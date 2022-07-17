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
	ts := github.Timestamp{Time: time.Date(2022, time.July, 16, 1, 0, 0, 0, time.UTC)}
	languages := make(map[string]int, 0)
	languages["PHP"] = 10
	languages["go"] = 20
	topics := []string{"topicA", "topicB"}

	mockedClient := mock.NewMockedHTTPClient(
		mock.WithRequestMatch(
			mock.GetReposByOwnerByRepo,
			github.Repository{
				CreatedAt: &ts,
				Description: github.String("description"),
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
				Topics: topics,
			},
		),
		mock.WithRequestMatch(
			mock.GetReposLanguagesByOwnerByRepo,
			languages,
		),
	)
	ctx := context.Background()
	queryContext := Context{Client: github.NewClient(mockedClient), Context: &ctx}

	r, err := QueryRepo(&queryContext, "user", "repo")
	sDate := "2022-07-16T01:00:00.000Z"
	assert.Equal(nil, err)
	expected := Repository{
		CreatedAt: sDate,
		Description: "description",
		ForksCount: 1,
		FullName: "fullName",
		ID: 1000,
		Languages: languages,
		LicenseName: "license",
		MainLanguage: "PHP",
		Name: "repo",
		OpenIssuesCount: 2,
		PushedAt: sDate,
		Size: 99,
		StargazersCount: 3,
		Topics: topics,
		URL: "url",
		Username: "user",
	}
	assert.Equal(expected, r)
	assert.Equal(true, queryContext.RateLimiter.SearchLast429.IsZero())
	assert.Equal(true, queryContext.RateLimiter.SearchLastQuery.IsZero())
	assert.Equal(true, queryContext.RateLimiter.CoreLast429.IsZero())
	assert.Equal(false, queryContext.RateLimiter.CoreLastQuery.IsZero())
}
