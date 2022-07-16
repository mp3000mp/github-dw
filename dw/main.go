package main

import (
	"context"
	"fmt"
	"io"
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
 		panic("Error while loading .env file")
	}

	log.Println("Connecting to database...")
   	queryContext.DB, err = model.GetConnection()
	if err != nil {
 		panic("Error while connecting to database")
	}
	err = model.InitDatabase(queryContext.DB)
	if err != nil {
 		panic(fmt.Sprintf("Error while initializing database: %s", err.Error()))
	}

	log.Println("Creating client...")
	ctx := context.Background()
	queryContext.Client = query.CreateClient(ctx, os.Getenv("api_key"))
	queryContext.Context = &ctx

	log.Println("Creating log file...")
	logFile, err := os.OpenFile(fmt.Sprintf("log/%s.txt", time.Now().Format("20060102_150405")), os.O_RDWR | os.O_CREATE | os.O_APPEND, 0666)
	if err != nil {
		panic(fmt.Sprintf("Error while creating log file: %s", err.Error()))
	}
	multiW := io.MultiWriter(logFile, os.Stdout)
    log.SetOutput(multiW)

	// todo python
	log.Println("Loading packageType for routine 1...")
	queryContext.DB.Order("updated_at asc").Where("language IN ('PHP'/*, 'Javascript', 'Go'*/)").First(&queryContext.Routine1PackageType)
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
