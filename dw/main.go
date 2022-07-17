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
	tickReload := time.Second * 60

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

    routine.RunPreroutine(&queryContext)

	for {
		if queryContext.PreroutineRunning {
			continue
		}

		if !queryContext.Routine1Running {
			// avoid sleeping 120s that would possibly block other routines
			if queryContext.RateLimiter.SearchLastQuery.IsZero() ||
			   time.Until(queryContext.RateLimiter.SearchLastQuery.Add(query.TickSearch)).Milliseconds() < 0 {
				go routine.RunRoutine1(&queryContext)
			}
		}

		// todo sync ?
		if !queryContext.Routine2Running && len(*queryContext.Routine2Queue) > 0 {
			go routine.RunRoutine2(&queryContext)
		}

		// todo sync ?
		if !queryContext.Routine3Running && len(*queryContext.Routine3Queue) > 0 {
			go routine.RunRoutine3(&queryContext)
		}

		// we donot want to run preroutine to often
		if time.Until(queryContext.PreroutineLastReload.Add(tickReload)).Milliseconds() < 0 {
			routine.RunPreroutine(&queryContext)
		}

		// todo gérer rate limiter
		// a, _, _ := client.RateLimits(ctx)
		// log.Printf("%v\n", a)

		time.Sleep(routine.Tick)
	}
}
