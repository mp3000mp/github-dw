package routine

import (
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
	// todo keep relation instead of query
	queryContext.DB.Where("id = ?", repoPackageFile.RepositoryID).First(&repo)
	log.Printf("Start routine 3: repo '%s' file '%s'\n", repo.URL, repoPackageFile.Path)

	// get raw file
	blob, err := query.QueryBlob(queryContext, repo.Username, repo.Name, repoPackageFile.SHA)
	if err != nil {
		msg := fmt.Sprintf("Routine 3 => Error while querying blob %s file %s: %s", repo.URL, repoPackageFile.Path, err.Error())
		log.Println(msg)
		EndRoutine3(&queryContext.Routine3Running, queryContext.Routine3Queue)
	 	queryContext.DB.Model(&repoPackageFile).Updates(model.RepositoryPackageTypeFile{ID: repoPackageFile.ID, Routine3At: time.Now(), RoutineError: msg})
		return
	}

	// parse package file
	packages := make([]parser.Package, 0)
	if queryContext.Routine1PackageType.File == "composer.json" {
		packages, err = parser.ParseComposerJson(blob.Content)
	} else if queryContext.Routine1PackageType.File == "package.json" {
		packages, err = parser.ParsePackageJson(blob.Content)
	} else if queryContext.Routine1PackageType.File == "go.mod" {
		packages, err = parser.ParseGoMod(blob.Content)
	} else if queryContext.Routine1PackageType.File == "requirements.txt" {
		packages, err = parser.ParseRequirementsTxt(blob.Content)
// todo
//	} else if queryContext.Routine1PackageType.File == "setup.py" {
//		packages, err = parser.ParseSetupPy(blob.Content)
	} else {
		err = fmt.Errorf("Package file %s is not supported", queryContext.Routine1PackageType.File)
	}
	if err != nil {
		msg := fmt.Sprintf("Routine 3 => Error while parsing package file: %s", err.Error())
		log.Println(msg)
		EndRoutine3(&queryContext.Routine3Running, queryContext.Routine3Queue)
	 	queryContext.DB.Model(&repoPackageFile).Updates(model.RepositoryPackageTypeFile{ID: repoPackageFile.ID, Routine3At: time.Now(), RoutineError: msg})
		return
	}

	// store packages in db
	repoPackage := model.RepositoryPackage{}
	queryContext.DB.Where("repository_package_type_file_id = ?", repoPackageFile.ID).Delete(&repoPackage)
	for _, pkg := range packages {
		versionRange := parser.GetVersionRange(pkg.Version)
		// if version not valid, we considere it must be here
		if !versionRange.Valid {
			log.Printf("Routine 3 => Warning invalid version '%s'\n", pkg.Version)
			versionRange = parser.GetVersionRange("*")
			versionRange.Valid = false
		}
		repoPackage = model.RepositoryPackage{
			RepositoryPackageTypeFileID: repoPackageFile.ID,
			Name: pkg.Name,
			VersionStr: pkg.Version,
			VersionMinMajor: versionRange.MinMajor,
			VersionMinMinor: versionRange.MinMinor,
			VersionMinPatch: versionRange.MinPatch,
			VersionMaxMajor: versionRange.MaxMajor,
			VersionMaxMinor: versionRange.MaxMinor,
			VersionMaxPatch: versionRange.MaxPatch,
			Valid: versionRange.Valid,
		}
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
