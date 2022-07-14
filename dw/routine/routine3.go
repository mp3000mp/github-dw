package routine

import (
	"errors"
	"fmt"
	"log"
	"time"

	"main/model"
	"main/parser"
	"main/query"
)

// consume first queue item
// todo autre type que query.SearchCodeItem ?
func RunRoutine3(queryContext *query.Context) {
	queryContext.Routine3Running = true
	repoPackageFile := (*queryContext.Routine3Queue)[0]
	var repo model.Repository
	log.Printf("Start routine 3: %s\n", repoPackageFile.Path)

	// todo remove
	time.Sleep(time.Second * 2)

	// todo keep relation instead of query
	queryContext.DB.Where("id = ?", repoPackageFile.RepositoryID).First(&repo)

	// get raw file
	blob, err := query.QueryBlob(queryContext, repo.Username, repo.Name, repoPackageFile.SHA)
	if err != nil {
		msg := fmt.Sprintf("Routine 3 => Error while querying repo %s file %s: %s", repo.URL, repoPackageFile.Path, err.Error())
		log.Println(msg)
		EndRoutine3(&queryContext.Routine3Running, queryContext.Routine3Queue)
	 	queryContext.DB.Model(&repo).Updates(model.RepositoryPackageTypeFile{Routine3At: time.Now(), RoutineError: msg})
		return
	}

	// parse package file
	// todo support go and python
	packages := make(map[string]string)
	if queryContext.Routine1PackageType.File == "composer.json" {
		packages, err = parser.ParseComposerJson(blob.Content)
	} else if queryContext.Routine1PackageType.File == "package.json" {
		packages, err = parser.ParsePackageJson(blob.Content)
	} else {
		err = errors.New("Routine 3 => Package file %s is not supported")
	}
	if err != nil {
		msg := fmt.Sprintf(err.Error(), queryContext.Routine1PackageType.File)
		log.Println(msg)
		EndRoutine3(&queryContext.Routine3Running, queryContext.Routine3Queue)
	 	queryContext.DB.Model(&repo).Updates(model.RepositoryPackageTypeFile{Routine3At: time.Now(), RoutineError: msg})
		return
	}

	// store packages in db
	repoPackage := model.RepositoryPackage{}
	queryContext.DB.Where("repository_package_type_file_id = ?", repoPackageFile.ID).Delete(&repoPackage)
	for pkg, version := range packages {
		repoPackage = model.RepositoryPackage{RepositoryPackageTypeFileID: repoPackageFile.ID, Name: pkg, Version: version}
		queryContext.DB.Create(&repoPackage)
	}

	// update package file routine3At=now
	queryContext.DB.Model(&repoPackageFile).Updates(model.RepositoryPackageTypeFile{
		Routine3At: time.Now(),
	})

	EndRoutine3(&queryContext.Routine3Running, queryContext.Routine3Queue)
}

func EndRoutine3(isRunning *bool, queue *[]model.RepositoryPackageTypeFile) {
	// todo sync ?
	*queue = (*queue)[1:]
	*isRunning = false
	log.Println("End routine 3")
}
