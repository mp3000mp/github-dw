package main

import (
	"context"
	"log"
	"os"
	"time"

	"main/query"
	"main/routine"

	"github.com/joho/godotenv"

 	"github.com/google/go-github/v45/github"
 	"golang.org/x/oauth2"
)

func main() {
	tick := time.Millisecond * 100
	routine1Running := false
	searchPage := 0
	routine2Running := false
	routine2Queue := make([]query.SearchCodeItem, 0)
	routine3Running := false
	// todo quel type ?
	routine3Queue := make([]query.SearchCodeItem, 0)

	log.Println("Loading config...")
	err := godotenv.Load()
	if err != nil {
 		log.Printf("Error loading .env file")
 		os.Exit(1)
	}

	log.Println("Creating client...")
	ctx := context.Background()
	ts := oauth2.StaticTokenSource(
		&oauth2.Token{AccessToken: os.Getenv("api_key")},
	)
	tc := oauth2.NewClient(ctx, ts)
	client := github.NewClient(tc)

	// todo gérer rate limiter
	// a, _, _ := client.RateLimits(ctx)
	// log.Printf("%v\n", a)

	log.Println("Loading queue for routine 1...")
	// todo: load fileName et searchPage from db

	log.Println("Loading queue for routine 2...")
	// todo: load routine2Queue from db

	log.Println("Loading queue for routine 3...")
	// todo: load routine3Queue from db

	for {
		if !routine1Running {
			go routine.RunRoutine1(client, ctx, &routine1Running, "composer.json", &searchPage, &routine2Queue)
		}

		// todo sync ?
		if !routine2Running && len(routine2Queue) > 0 {
			// todo send routine3Queue to be feed
			go routine.RunRoutine2(client, ctx, &routine2Running, &routine2Queue)
		}

		// todo sync ?
		if !routine3Running && len(routine3Queue) > 0 {
			go routine.RunRoutine3(client, ctx, &routine3Running, &routine3Queue)
		}

		time.Sleep(tick)
	}
}
