package routine

import (
	"context"
	"log"
	"time"

	"main/query"

	"github.com/google/go-github/v45/github"
)

// consume first queue item
func RunRoutine2(client *github.Client, ctx context.Context, isRunning *bool, queue *[]query.SearchCodeItem) {
	*isRunning = true
	codeItem := (*queue)[0]
	log.Printf("Start routine 2: %s/%s\n", codeItem.User, codeItem.Name)

	time.Sleep(time.Second * 2)

	repo, err := query.QueryRepo(client, ctx, codeItem.User, codeItem.Name)
	if err != nil {
		log.Printf("Routine 2 => Error while querying repo %s/%s: %s", codeItem.User, codeItem.Name, err.Error())
		return
	}

	// todo insert into db
	log.Printf("%s", repo.URL)

	log.Println("End routine 2")
	// todo sync ?
	*queue = (*queue)[1:]
	*isRunning = false
}
