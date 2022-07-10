package query

import (
	"context"

	"github.com/google/go-github/v45/github"
)

type Repository struct {
	CreatedAt, FullName, URL, LicenseName, MainLanguage, Name, PushedAt, Username string
	ForksCount, OpenIssuesCount, StargazersCount, Size int
	ID int64
	Topics []string
	Languages map[string]int
}

// list all repositories matching search
func QueryRepo(client *github.Client, ctx context.Context, userName string, repoName string) (Repository, error) {
	githubRepo, _, err := client.Repositories.Get(ctx, userName, repoName)
	if !CheckResponse(err) {
		return Repository{}, err
	}

	githubLanguages, _, err := client.Repositories.ListLanguages(ctx, userName, repoName)
	if !CheckResponse(err) {
		return Repository{}, err
	}

	data := Repository{
    	CreatedAt: githubRepo.CreatedAt.Time.Format("2006-01-02T15:04:05.000Z"),
    	ForksCount: *githubRepo.ForksCount,
    	FullName: *githubRepo.FullName,
    	ID: *githubRepo.ID,
    	Languages: githubLanguages,
    	MainLanguage: *githubRepo.Language,
    	Name: *githubRepo.Name,
    	OpenIssuesCount: *githubRepo.OpenIssuesCount,
		PushedAt: githubRepo.PushedAt.Time.Format("2006-01-02T15:04:05.000Z"),
    	Size: *githubRepo.Size,
    	StargazersCount: *githubRepo.StargazersCount,
		Topics: githubRepo.Topics,
		URL: *githubRepo.HTMLURL,
		Username: *githubRepo.Owner.Login,
	}
	if githubRepo.License != nil {
		data.LicenseName = *githubRepo.License.Name
	}

	return data, nil
}
