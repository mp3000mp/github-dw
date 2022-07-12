package routine

import (
	"context"
	"log"
	"strconv"
	"time"

	"main/model"
	"main/query"

	"github.com/google/go-github/v45/github"
	"gorm.io/gorm"
	"gorm.io/gorm/clause"
)

const nbPerPage = 100

func RunRoutine1(db *gorm.DB, client *github.Client, ctx context.Context, isRunning *bool, fileName string, searchPage *int, queue *[]query.SearchCodeItem) {
	*isRunning = true
	log.Printf("Start routine 1: %s\n", strconv.Itoa(*searchPage))

	time.Sleep(time.Second * 1)

	// get repos with file
	codes, err := query.QuerySearchCodes(client, ctx, fileName, *searchPage, nbPerPage)
	if err != nil {
		log.Printf("Routine 1 => Error while querying codes: %s", err.Error())
		return
	}

	for _, code := range codes {
		repo := model.Repository{Name: code.Name, Username: code.User, URL: code.URL, Routine1At: time.Now()}
		result := db.Select("Name", "Username", "URL", "Routine1At").Clauses(clause.OnConflict{DoNothing: true}).Create(&repo)
		if result.RowsAffected > 0 {
			*queue = append(*queue, code)
		}
	}

	log.Println("End routine 1")
	*searchPage++
	*isRunning = false
}
