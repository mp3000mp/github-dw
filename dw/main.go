package main

import (
	"context"
	"log"
	"os"
	"time"

	"main/model"
	"main/routine"

	"github.com/joho/godotenv"
 	"github.com/google/go-github/v45/github"
 	"golang.org/x/oauth2"
)

func main() {
	tick := time.Millisecond * 100
	routine1Running := false
	pack := model.Package{}
	routine2Running := false
	routine2Queue := make([]model.Repository, 0)
	routine3Running := false
	routine3Queue := make([]model.Repository, 0)

	log.Println("Loading config...")
	err := godotenv.Load()
	if err != nil {
 		log.Printf("Error while loading .env file")
 		os.Exit(1)
	}

	log.Println("Connecting to database...")
   	db, err := model.GetConnection()
	if err != nil {
 		log.Printf("Error while connecting to database")
 		os.Exit(1)
	}
	err = model.InitDatabase(db)
	if err != nil {
 		log.Printf("Error while initializing database: %s", err.Error())
 		os.Exit(1)
	}

	log.Println("Creating client...")
	ctx := context.Background()
	ts := oauth2.StaticTokenSource(
		&oauth2.Token{AccessToken: os.Getenv("api_key")},
	)
	tc := oauth2.NewClient(ctx, ts)
	client := github.NewClient(tc)

	// todo only composer.json for now
	log.Println("Loading package for routine 1...")
	db.Order("updated_at asc").Where(&model.Package{Language: "PHP"}).First(&pack)
	log.Printf("Working on package '%s' file '%s'", pack.Name, pack.File)

	log.Println("Loading queue for routine 2...")
	db.Order("routine1_at asc").Where("routine_error IS NULL AND routine2_at IS NULL").Find(&routine2Queue)
	log.Printf("%d items in queue2", len(routine2Queue))

	log.Println("Loading queue for routine 3...")
	db.Order("routine2_at asc").Where("routine_error IS NULL AND routine2_at IS NOT NULL AND routine3_at IS NULL").Find(&routine3Queue)
	log.Printf("%d items in queue3", len(routine3Queue))

	for {
		if !routine1Running {
			go routine.RunRoutine1(db, client, ctx, &routine1Running, &pack, &routine2Queue)
		}

		// todo sync ?
		if !routine2Running && len(routine2Queue) > 0 {
			go routine.RunRoutine2(db, client, ctx, &routine2Running, &routine2Queue, &routine3Queue)
		}

		// todo sync ?
		if !routine3Running && len(routine3Queue) > 0 {
			go routine.RunRoutine3(db, client, ctx, &routine3Running, pack, &routine3Queue)
		}

		// todo gérer rate limiter
		// a, _, _ := client.RateLimits(ctx)
		// log.Printf("%v\n", a)

		time.Sleep(tick)
	}
}
