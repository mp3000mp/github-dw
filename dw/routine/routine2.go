package routine

import (
	"context"
	"fmt"
	"log"
	"time"

	"main/model"
	"main/query"

	"github.com/google/go-github/v45/github"
	"gorm.io/gorm"
)

// consume first queue2 item
func RunRoutine2(db *gorm.DB, client *github.Client, ctx context.Context, isRunning *bool, queue2 *[]model.Repository, queue3 *[]model.Repository) {
	*isRunning = true
	repo := (*queue2)[0]
	log.Printf("Start routine 2: %s\n", repo.URL)

	// todo remove
	time.Sleep(time.Second * 1)

	searchResult, err := query.QueryRepo(client, ctx, repo.Username, repo.Name)
	if err != nil {
		msg := fmt.Sprintf("Routine 2 => Error while querying repo %s: %s", repo.URL, err.Error())
		log.Println(msg)
		EndRoutine2(isRunning, queue2)
		db.Model(&repo).Updates(model.Repository{Routine2At: time.Now(), RoutineError: msg})
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
	db.Model(&repo).Updates(model.Repository{
		MainLanguage: searchResult.MainLanguage,
		FullName: searchResult.FullName,
		LicenseName: searchResult.LicenseName,
		ForksCount: uint32(searchResult.ForksCount),
		OpenIssuesCount: uint32(searchResult.OpenIssuesCount),
		StargazersCount: uint32(searchResult.StargazersCount),
		GithubId: uint(searchResult.ID),
		CreatedAt: createdAt,
		PushedAt: pushedAt,
		Routine2At: time.Now(),
	})
	_ = db.Model(&repo).Association("Topics").Clear()
	_ = db.Model(&repo).Association("Topics").Append(topics)
	_ = db.Model(&repo).Association("Languages").Clear()
	_ = db.Model(&repo).Association("Languages").Append(languages)

	EndRoutine2(isRunning, queue2)
	*queue3 = append(*queue3, repo)
}

func EndRoutine2(isRunning *bool, queue *[]model.Repository) {
	// todo sync ?
	*queue = (*queue)[1:]
	*isRunning = false
	log.Println("End routine 2")
}
