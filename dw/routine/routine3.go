package routine

import (
	"context"
	"log"
	"time"

	// "main/model"
	"main/query"

	"github.com/google/go-github/v45/github"
	"gorm.io/gorm"
)

// consume first queue item
// todo autre type que query.SearchCodeItem ?
func RunRoutine3(db *gorm.DB, client *github.Client, ctx context.Context, isRunning *bool, queue *[]query.SearchCodeItem) {
	*isRunning = true
	codeItem := (*queue)[0]
	log.Printf("Start routine 3: %s/%s\n", codeItem.User, codeItem.Name)

	time.Sleep(time.Second * 3)

// todo get raw file
// 	repo, err := query.QueryRepo(client, ctx, codeItem.User, codeItem.Name)
// 	if err != nil {
// 		log.Printf("Routine 3 => Error while querying repo %s/%s: %s", codeItem.User, codeItem.Name, err.Error())
// 		return
// 	}

	// todo parse file regarding package type (php, js, python, go...)

	// todo insert into db

	log.Println("End routine 3")
	// todo sync ?
	*queue = (*queue)[1:]
	*isRunning = false
}
