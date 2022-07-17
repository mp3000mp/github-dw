package routine

import (
	"fmt"
	"log"
	"time"

	"main/model"
	"main/query"
)

// consume first queue2 item
func RunRoutine2(queryContext *query.Context) {
	queryContext.Routine2Running = true
	repo := (*queryContext.Routine2Queue)[0]
	log.Printf("Start routine 2: repo '%s'\n", repo.URL)

	searchResult, err := query.QueryRepo(queryContext, repo.Username, repo.Name)
	if err != nil {
		msg := fmt.Sprintf("Routine 2 => Error while querying repo %s: %s", repo.URL, err.Error())
		log.Println(msg)
		EndRoutine2(&queryContext.Routine2Running, queryContext.Routine2Queue)
		queryContext.DB.Model(&repo).Updates(model.Repository{Routine2At: time.Now(), RoutineError: msg})
		return
	}

	// update repo routine2At=now
	createdAt, _ := time.Parse("2006-01-02 15:04:05", searchResult.CreatedAt)
	pushedAt, _ := time.Parse("2006-01-02 15:04:05", searchResult.PushedAt)
	topics := make([]model.RepositoryTopic, 0)
	for _, topic := range searchResult.Topics {
		topics = append(topics, model.RepositoryTopic{RepositoryID: repo.ID, Topic: topic})
	}
	languages := make([]model.RepositoryLanguage, 0)
	for language, weight := range searchResult.Languages {
		languages = append(languages, model.RepositoryLanguage{RepositoryID: repo.ID, Language: language, Weight: weight})
	}
	queryContext.DB.Model(&repo).Updates(model.Repository{
		MainLanguage: searchResult.MainLanguage,
		FullName: searchResult.FullName,
		LicenseName: searchResult.LicenseName,
		ForksCount: HndlCount(&searchResult.ForksCount),
		OpenIssuesCount: HndlCount(&searchResult.OpenIssuesCount),
		StargazersCount: HndlCount(&searchResult.StargazersCount),
		GithubId: uint(searchResult.ID),
		CreatedAt: createdAt,
		PushedAt: pushedAt,
		Routine2At: time.Now(),
	})
	// relations
	_ = queryContext.DB.Model(&repo).Association("Topics").Clear()
	_ = queryContext.DB.Model(&repo).Association("Topics").Append(topics)
	_ = queryContext.DB.Model(&repo).Association("Languages").Clear()
	_ = queryContext.DB.Model(&repo).Association("Languages").Append(languages)

	EndRoutine2(&queryContext.Routine2Running, queryContext.Routine2Queue)
}

func EndRoutine2(isRunning *bool, queue *[]model.Repository) {
	// todo sync ?
	*queue = (*queue)[1:]
	*isRunning = false
	log.Println("End routine 2")
}

func HndlCount(count *int) uint32 {
	if count == nil {
		return 0
	}
	return uint32(*count)
}
