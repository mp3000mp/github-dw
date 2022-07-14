package main

import (
	"context"
	"log"
	"os"
	"time"

	"main/model"
	"main/query"
	"main/routine"

	"github.com/joho/godotenv"
)

func main() {
	queryContext := query.Context{}
	tick := time.Millisecond * 100

	log.Println("Loading config...")
	err := godotenv.Load()
	if err != nil {
 		log.Printf("Error while loading .env file")
 		os.Exit(1)
	}

	log.Println("Connecting to database...")
   	queryContext.DB, err = model.GetConnection()
	if err != nil {
 		log.Printf("Error while connecting to database")
 		os.Exit(1)
	}
	err = model.InitDatabase(queryContext.DB)
	if err != nil {
 		log.Printf("Error while initializing database: %s", err.Error())
 		os.Exit(1)
	}

	log.Println("Creating client...")
	ctx := context.Background()
	queryContext.Client = query.CreateClient(ctx, os.Getenv("api_key"))

	// todo add go and python
	log.Println("Loading packageType for routine 1...")
	queryContext.DB.Order("updated_at asc").Where("language IN ('PHP', 'Javascript')").First(&queryContext.Routine1PackageType)
	log.Printf("Working on packageType '%s' file '%s'", queryContext.Routine1PackageType.Name, queryContext.Routine1PackageType.File)

	log.Println("Loading queue for routine 2...")
	queryContext.DB.Order("routine1_at asc").Where("routine_error IS NULL AND routine2_at IS NULL").Find(&queryContext.Routine2Queue)
	log.Printf("%d items in queue2", len(*queryContext.Routine2Queue))

	log.Println("Loading queue for routine 3...")
	queryContext.DB.Order("routine1_at asc").Where("routine_error IS NULL AND routine3_at IS NULL").Find(&queryContext.Routine3Queue)
	log.Printf("%d items in queue3", len(*queryContext.Routine3Queue))

	for {
		if !queryContext.Routine1Running {
			go routine.RunRoutine1(&queryContext)
		}

		// todo sync ?
		if !queryContext.Routine2Running && len(*queryContext.Routine2Queue) > 0 {
			go routine.RunRoutine2(&queryContext)
		}

		// todo sync ?
		if !queryContext.Routine3Running && len(*queryContext.Routine3Queue) > 0 {
			go routine.RunRoutine3(&queryContext)
		}

		// todo gérer rate limiter
		// a, _, _ := client.RateLimits(ctx)
		// log.Printf("%v\n", a)

		time.Sleep(tick)
	}
}
