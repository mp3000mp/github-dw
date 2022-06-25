package query

import (
	"context"
	"encoding/json"
	"fmt"
	"io/ioutil"
	"strconv"

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
func QueryRepo(client *github.Client, ctx context.Context, userName string, repoName string) bool {
	githubRepo, _, err := client.Repositories.Get(ctx, userName, repoName)
	if !CheckResponse(err) {
		return false
	}

	githubLanguages, _, err := client.Repositories.ListLanguages(ctx, userName, repoName)
	if !CheckResponse(err) {
		return false
	}

	data := Repository{
    	CreatedAt: githubRepo.CreatedAt.Time.Format("2006-01-02T15:04:05.000Z"),
    	ForksCount: *githubRepo.ForksCount,
    	FullName: *githubRepo.FullName,
    	ID: *githubRepo.ID,
    	Languages: githubLanguages,
		LicenseName: *githubRepo.License.Name,
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

	f, err := json.MarshalIndent(data, "", " ")
	if err != nil {
		fmt.Println("Error while parsing into json format")
	}
	fileName := fmt.Sprintf("output/%s.json", strconv.FormatInt(data.ID, 10))
	err = ioutil.WriteFile(fileName, f, 0644)
	if err != nil {
		fmt.Printf("Error while writing into file %s\n", fileName)
	}
	fmt.Printf("Repo %s success\n", repoName)

	return true
}
