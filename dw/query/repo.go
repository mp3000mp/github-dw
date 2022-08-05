package query

import (
	"time"
)

type Repository struct {
	CreatedAt, Description, FullName, URL, LicenseName, MainLanguage, Name, PushedAt, Username string
	ForksCount, OpenIssuesCount, StargazersCount, Size int
	ID int64
	Topics []string
	Languages map[string]int
}

// list all repositories matching search
func QueryRepo(context *Context, userName string, repoName string) (Repository, error) {
	WaitBeforeQuery(context.RateLimiter, "core", true)
	context.RateLimiter.CoreLastQuery = time.Now()
	githubRepo, _, err := context.Client.Repositories.Get(*context.Context, userName, repoName)
	if !CheckResponse(err, &context.RateLimiter, "core") {
		return Repository{}, err
	}

	// not used anymore because it costs one query
// 	WaitBeforeQuery(context.RateLimiter, "core", true)
// 	context.RateLimiter.CoreLastQuery = time.Now()
// 	githubLanguages, _, err := context.Client.Repositories.ListLanguages(*context.Context, userName, repoName)
// 	if !CheckResponse(err, &context.RateLimiter, "core") {
// 		return Repository{}, err
// 	}

	mainLanguage := ""
	if githubRepo.Language != nil {
		mainLanguage = *githubRepo.Language
	}
	description := ""
	if githubRepo.Description != nil {
		description = *githubRepo.Description
	}
	repo := Repository{
    	CreatedAt: githubRepo.CreatedAt.Time.Format("2006-01-02T15:04:05.000Z"),
    	Description: description,
    	ForksCount: *githubRepo.ForksCount,
    	FullName: *githubRepo.FullName,
    	ID: *githubRepo.ID,
//    	Languages: githubLanguages,
    	MainLanguage: mainLanguage,
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
		repo.LicenseName = *githubRepo.License.Name
	}

	return repo, nil
}
