package routine

import (
	"context"
	"log"
	"time"

	"main/model"
	// "main/query"

	"github.com/google/go-github/v45/github"
	"gorm.io/gorm"
)

// consume first queue item
// todo autre type que query.SearchCodeItem ?
func RunRoutine3(db *gorm.DB, client *github.Client, ctx context.Context, isRunning *bool, pack model.Package, queue *[]model.Repository) {
	*isRunning = true
	repo := (*queue)[0]
	log.Printf("Start routine 3: %s\n", repo.URL)

	// todo remove
	time.Sleep(time.Second * 2)

// todo get raw file
// 	repo, err := query.QueryRepo(client, ctx, codeItem.User, codeItem.Name)
// 	if err != nil {
// 		msg := fmt.Sprintf("Routine 3 => Error while querying repo %s: %s", repo.URL, err.Error())
// 		log.Println(msg)
//		EndRoutine3(isRunning, queue)
//      db.Model(&repo).Updates(model.Repository{Routine3At: time.Now(), RoutineError: msg})
// 		return
// 	}

	// todo parse file regarding package type (php, js, python, go...)

	// update repo routine3At=now
	db.Model(&repo).Updates(model.Repository{
		Routine3At: time.Now(),
	})

	EndRoutine3(isRunning, queue)
}

func EndRoutine3(isRunning *bool, queue *[]model.Repository) {
	// todo sync ?
	*queue = (*queue)[1:]
	*isRunning = false
	log.Println("End routine 3")
}
