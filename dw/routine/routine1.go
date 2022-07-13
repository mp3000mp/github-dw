package routine

import (
	"context"
	"log"
	"time"

	"main/model"
	"main/query"

	"github.com/google/go-github/v45/github"
	"gorm.io/gorm"
	"gorm.io/gorm/clause"
)

const nbPerPage = 100

func RunRoutine1(db *gorm.DB, client *github.Client, ctx context.Context, isRunning *bool, pack *model.Package, queue *[]model.Repository) {
	*isRunning = true
	log.Printf("Start routine 1: %d\n", int(pack.GithubCurrentPage))

	// todo remove
	time.Sleep(time.Second * 5)

	// get repos with file x
	codes, err := query.QuerySearchCodes(client, ctx, pack.File, int(pack.GithubCurrentPage), nbPerPage)
	if err != nil {
		log.Printf("Routine 1 => Error while querying codes: %s", err.Error())
		return
	}

	// insert repo routine1At=now
	for _, code := range codes {
		repo := model.Repository{Name: code.Name, Username: code.User, URL: code.URL, Routine1At: time.Now()}
		result := db.Select("Name", "Username", "URL", "Routine1At").Clauses(clause.OnConflict{DoNothing: true}).Create(&repo)
		if result.RowsAffected > 0 {
			*queue = append(*queue, repo)
		}
	}

	// next page
	pack.GithubCurrentPage++
	pack.UpdatedAt = time.Now()
	db.Save(pack)

	*isRunning = false
	log.Println("End routine 1")
}
