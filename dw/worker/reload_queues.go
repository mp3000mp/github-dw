package worker

import (
	"time"

	"main/model"
	"main/query"
	"main/system"
)

const queueLimit = 200

func ReloadQueues(queryContext *query.Context) {
	queryContext.Mu.Lock()
	queryContext.PreroutineRunning = true
	queryContext.Mu.Unlock()

	newPackageType := model.PackageTypeFile{}
	system.Info.Println("Start ReloadQueues")

	queryContext.DB.Order("priority desc, updated_at asc").First(&newPackageType)

	queryContext.Mu.Lock()
	samePackageType := queryContext.Routine1PackageType != nil && newPackageType.ID == queryContext.Routine1PackageType.ID
	queryContext.Mu.Unlock()

	if samePackageType {
		system.Info.Println("ReloadQueues => no change")
		endReloadQueues(queryContext)
		return
	}

	// wait for all workers to finish before swapping queues
	for {
		queryContext.Mu.Lock()
		allDone := !queryContext.Routine1Running && !queryContext.Routine2Running && !queryContext.Routine3Running
		queryContext.Mu.Unlock()
		if allDone {
			break
		}
		time.Sleep(Tick)
	}

	queryContext.Mu.Lock()
	queryContext.Routine1PackageType = &newPackageType
	queryContext.Mu.Unlock()

	system.Info.Printf("Working on package type file '%s' file '%s'", newPackageType.Name, newPackageType.File)

	system.Info.Println("Loading queue for SearchRepos...")
	queryContext.DB.Order("routine1_at asc").Where("routine_error IS NULL AND routine2_at IS NULL").Limit(queueLimit).Find(&queryContext.Routine2Queue)
	system.Info.Printf("%d items in queue2", len(queryContext.Routine2Queue))

	system.Info.Println("Loading queue for ParseDependencies...")
	queryContext.DB.Order("routine1_at asc").Where("package_type_file_id = ? AND routine_error IS NULL AND routine3_at IS NULL", newPackageType.ID).Limit(queueLimit).Find(&queryContext.Routine3Queue)
	system.Info.Printf("%d items in queue3", len(queryContext.Routine3Queue))

	endReloadQueues(queryContext)
}

func endReloadQueues(queryContext *query.Context) {
	queryContext.Mu.Lock()
	queryContext.PreroutineRunning = false
	queryContext.Mu.Unlock()
	queryContext.PreroutineLastReload = time.Now()
	system.Info.Println("End ReloadQueues")
}
