package routine

import (
	"context"
	"log"
	"strconv"
	"time"

	"main/query"

	"github.com/google/go-github/v45/github"
)

const nbPerPage = 100

func RunRoutine1(client *github.Client, ctx context.Context, isRunning *bool, fileName string, searchPage *int, queue *[]query.SearchCodeItem) {
	*isRunning = true
	log.Printf("Start routine 1: %s\n", strconv.Itoa(*searchPage))

	time.Sleep(time.Second * 1)

	// get repos with file
	codes, err := query.QuerySearchCodes(client, ctx, fileName, *searchPage, nbPerPage)
	if err != nil {
		log.Printf("Routine 1 => Error while querying codes: %s", err.Error())
		return
	}

	tmpQueue := make([]query.SearchCodeItem, 0)
	for _, item := range codes {
		tmpQueue = append(tmpQueue, item)
	}
	// todo insert codes in db
	// todo select queue in db and update queue
	// todo remove
	*queue = append(*queue, tmpQueue...)

	log.Println("End routine 1")
	*searchPage++
	*isRunning = false
}
