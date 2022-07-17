package routine

import (
	"log"
	"time"

	"main/model"
	"main/query"
)

func RunPreroutine(queryContext *query.Context) {
	queryContext.PreroutineRunning = true
	newPackageType := model.PackageType{}
	log.Println("Start pre-routine")

	queryContext.DB.Order("priority desc, updated_at asc").First(&newPackageType)

	if queryContext.Routine1PackageType != nil && newPackageType.ID == queryContext.Routine1PackageType.ID {
		log.Println("Pre-routine => no change")
		EndPreroutine(&queryContext.PreroutineRunning, &queryContext.PreroutineLastReload)
		return
	}

	// wait for routines to be stopped
	for {
		if queryContext.Routine1Running || queryContext.Routine2Running || queryContext.Routine3Running {
			time.Sleep(Tick)
			continue
		}

		queryContext.Routine1PackageType = &newPackageType
		log.Printf("Working on packageType '%s' file '%s'", queryContext.Routine1PackageType.Name, queryContext.Routine1PackageType.File)

		log.Println("Loading queue for routine 2...")
		queryContext.DB.Order("routine1_at asc").Where("routine_error IS NULL AND routine2_at IS NULL").Find(&queryContext.Routine2Queue)
		log.Printf("%d items in queue2", len(*queryContext.Routine2Queue))

		log.Println("Loading queue for routine 3...")
		queryContext.DB.Order("routine1_at asc").Where("package_type_id = ? AND routine_error IS NULL AND routine3_at IS NULL", queryContext.Routine1PackageType.ID).Find(&queryContext.Routine3Queue)
		log.Printf("%d items in queue3", len(*queryContext.Routine3Queue))

		EndPreroutine(&queryContext.PreroutineRunning, &queryContext.PreroutineLastReload)
		return
	}
}

func EndPreroutine(isRunning *bool, lastReload *time.Time) {
	*isRunning = false
	*lastReload = time.Now()
	log.Println("End pre-routine")
}
