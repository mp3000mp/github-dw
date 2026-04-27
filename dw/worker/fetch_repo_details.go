package worker

import (
	"fmt"
	"strings"
	"time"

	"main/model"
	"main/query"
	"main/system"
)

const maxDescriptionLen = 4000

// consume first FetchRepoDetails queue item
func FetchRepoDetails(queryContext *query.Context) {
	defer func() {
		if r := recover(); r != nil {
			system.Error.Printf("FetchRepoDetails panic: %v", r)
			endFetchRepoDetails(queryContext)
		}
	}()

	queryContext.Mu.Lock()
	repo := queryContext.Routine2Queue[0]
	queryContext.Mu.Unlock()

	system.Info.Printf("Start FetchRepoDetails: repo '%s'\n", repo.URL)

	searchResult, err := query.QueryRepo(queryContext, repo.Username, repo.Name)
	if err != nil {
		msg := fmt.Sprintf("FetchRepoDetails => Error while querying repo %s: %s", repo.URL, err.Error())
		system.Error.Println(msg)
		endFetchRepoDetails(queryContext)
		if strings.Contains(err.Error(), "404 Not Found") {
			queryContext.DB.Delete(&repo)
			return
		}
		queryContext.DB.Model(&repo).Updates(model.Repository{Routine2At: time.Now(), RoutineError: msg})
		return
	}

	createdAt, _ := time.Parse("2006-01-02T15:04:05.000Z", searchResult.CreatedAt)
	pushedAt, _ := time.Parse("2006-01-02T15:04:05.000Z", searchResult.PushedAt)
	topics := make([]model.RepositoryTopic, 0)
	for _, topic := range searchResult.Topics {
		topics = append(topics, model.RepositoryTopic{RepositoryID: repo.ID, Topic: topic})
	}

	if len(searchResult.Description) > maxDescriptionLen {
		searchResult.Description = fmt.Sprintf("%s...", searchResult.Description[:maxDescriptionLen-4])
	}
	queryContext.DB.Model(&repo).Updates(model.Repository{
		Description:     searchResult.Description,
		MainLanguage:    searchResult.MainLanguage,
		FullName:        searchResult.FullName,
		LicenseName:     searchResult.LicenseName,
		ForksCount:      uint32(searchResult.ForksCount),
		OpenIssuesCount: uint32(searchResult.OpenIssuesCount),
		StargazersCount: uint32(searchResult.StargazersCount),
		Size:            uint(searchResult.Size),
		GithubId:        uint(searchResult.ID),
		CreatedAt:       createdAt,
		PushedAt:        pushedAt,
		Routine2At:      time.Now(),
	})
	_ = queryContext.DB.Model(&repo).Association("Topics").Clear()
	_ = queryContext.DB.Model(&repo).Association("Topics").Append(topics)

	endFetchRepoDetails(queryContext)
}

func endFetchRepoDetails(queryContext *query.Context) {
	queryContext.Mu.Lock()
	queryContext.Routine2Queue = queryContext.Routine2Queue[1:]
	queryContext.Routine2Running = false
	queryContext.Mu.Unlock()
	system.Info.Println("End FetchRepoDetails")
}
