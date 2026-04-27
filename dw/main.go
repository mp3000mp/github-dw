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
	"main/system"
	"main/worker"

	"github.com/joho/godotenv"
)

func main() {
	queryContext := query.Context{}
	tickReload := time.Second * 60

	err := godotenv.Load()
	if err != nil {
		log.Fatalf("Error while loading .env file: %s", err.Error())
	}

	if len(os.Args) > 1 && os.Args[1] == "version" {
		fmt.Println(os.Getenv("DW_VERSION"))
		return
	}

	queryContext.DB, err = model.GetConnection(os.Getenv("DATABASE_URL"))
	if err != nil {
		log.Fatalf("Error while connecting to database: %s", err.Error())
	}

	if len(os.Args) > 1 && os.Args[1] == "migrate" {
		err = model.InitDatabase(queryContext.DB)
		if err != nil {
			log.Fatalf("Error while initializing database: %s", err.Error())
		}
		fmt.Println("SUCCESS")
		return
	}

	ctx := context.Background()
	queryContext.Client = query.CreateClient(ctx, os.Getenv("api_key"))
	queryContext.Ctx = &ctx

	logFile, err := os.OpenFile(fmt.Sprintf("log/%s.txt", time.Now().Format("20060102_150405")), os.O_RDWR|os.O_CREATE|os.O_APPEND, 0666)
	if err != nil {
		log.Fatalf("Error while creating log file: %s", err.Error())
	}
	defer logFile.Close()
	multiW := io.MultiWriter(logFile, os.Stdout)
	log.SetOutput(multiW)
	system.InitLogger(multiW)

	worker.ReloadQueues(&queryContext)

	for {
		queryContext.Mu.Lock()
		preroutineRunning := queryContext.PreroutineRunning
		queryContext.Mu.Unlock()
		if preroutineRunning {
			time.Sleep(worker.Tick)
			continue
		}

		queryContext.RateLimiterMu.Lock()
		searchLastQuery := queryContext.RateLimiter.SearchLastQuery
		queryContext.RateLimiterMu.Unlock()

		queryContext.Mu.Lock()
		if !queryContext.Routine1Running &&
			(searchLastQuery.IsZero() || time.Until(searchLastQuery.Add(query.TickSearch)).Milliseconds() < 0) {
			queryContext.Routine1Running = true
			queryContext.Mu.Unlock()
			go worker.SearchRepos(&queryContext)
		} else {
			queryContext.Mu.Unlock()
		}

		queryContext.Mu.Lock()
		if !queryContext.Routine2Running && len(queryContext.Routine2Queue) > 0 {
			queryContext.Routine2Running = true
			queryContext.Mu.Unlock()
			go worker.FetchRepoDetails(&queryContext)
		} else {
			queryContext.Mu.Unlock()
		}

		queryContext.Mu.Lock()
		if !queryContext.Routine3Running && len(queryContext.Routine3Queue) > 0 {
			queryContext.Routine3Running = true
			queryContext.Mu.Unlock()
			go worker.ParseDependencies(&queryContext)
		} else {
			queryContext.Mu.Unlock()
		}

		if time.Until(queryContext.PreroutineLastReload.Add(tickReload)).Milliseconds() < 0 {
			worker.ReloadQueues(&queryContext)
			system.Info.Printf("Memory used: %dMo\n", system.GetUsedMem())
		}

		time.Sleep(worker.Tick)
	}
}
