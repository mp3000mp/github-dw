package routine

import (
	"fmt"
	"log"
	"strings"
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
	var dbPackage model.Package
	// todo keep relation instead of query
	queryContext.DB.Where("id = ?", repoPackageFile.RepositoryID).First(&repo)
	log.Printf("Start routine 3: repo '%s' file '%s'\n", repo.URL, repoPackageFile.Path)

	// get raw file
	blob, err := query.QueryBlob(queryContext, repo.Username, repo.Name, repoPackageFile.SHA)
	if err != nil {
		msg := fmt.Sprintf("Routine 3 => Error while querying blob %s file %s: %s", repo.URL, repoPackageFile.Path, err.Error())
		log.Println(msg)
		endRoutine3(&queryContext.Routine3Running, queryContext.Routine3Queue)
		if strings.Contains(err.Error(), "404 Not Found") {
			queryContext.DB.Delete(&repoPackageFile)
			return
		}
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
// todo ?
//	} else if queryContext.Routine1PackageType.File == "setup.py" {
//		packages, err = parser.ParseSetupPy(blob.Content)
	} else {
		err = fmt.Errorf("Routine 3 => Package file %s is not supported", queryContext.Routine1PackageType.File)
	}
	if err != nil {
		msg := fmt.Sprintf("Routine 3 => Error while parsing package file: %s", err.Error())
		log.Println(msg)
		// invalid file
		if strings.Contains(msg, "invalid character") || strings.Contains(msg, "unexpected end of JSON") || strings.Contains(msg, "cannot unmarshal") {
			queryContext.DB.Delete(&repoPackageFile)
			endRoutine3(&queryContext.Routine3Running, queryContext.Routine3Queue)
			return
		}
		endRoutine3(&queryContext.Routine3Running, queryContext.Routine3Queue)
	 	queryContext.DB.Model(&repoPackageFile).Updates(model.RepositoryPackageTypeFile{ID: repoPackageFile.ID, Routine3At: time.Now(), RoutineError: msg})
		return
	}

	// 80% of github repo has less than 5 deps
	if len(packages) < 6 {
		msg := fmt.Sprintf("Routine 3 => Ignored because less than 6 dependencies: %d", len(packages))
		log.Println(msg)
		queryContext.DB.Delete(&repoPackageFile)
		endRoutine3(&queryContext.Routine3Running, queryContext.Routine3Queue)
		return
	}

	// clean packages then store packages in db
	repoPackage := model.RepositoryPackage{}
	queryContext.DB.Where("repository_package_type_file_id = ?", repoPackageFile.ID).Delete(&repoPackage)
	for _, pkgItem := range packages {
		versionRange := parser.GetVersionRange(pkgItem.Version)
		// if version not valid, we considere it must be here
		if !versionRange.Valid {
			log.Printf("Routine 3 => Warning invalid version '%s'\n", pkgItem.Version)
			versionRange = parser.GetVersionRange("*")
			versionRange.Valid = false
		}

		// check if package exists or create it
		dbPackage = model.Package{}
		r := queryContext.DB.Where("package_type_file_id = ? AND name = ?", repoPackageFile.PackageTypeFileID, pkgItem.Name).Limit(1).Find(&dbPackage)
		if (r.RowsAffected == 0) {
			dbPackage = model.Package{
				PackageTypeFileID: repoPackageFile.PackageTypeFileID,
				Name: pkgItem.Name,
			}
			queryContext.DB.Create(&dbPackage)
		}

		// create repoPackage
		repoPackage = model.RepositoryPackage{
			RepositoryPackageTypeFileID: repoPackageFile.ID,
			PackageID: dbPackage.ID,
			RepositoryID: repo.ID,
			VersionStr: pkgItem.Version,
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

	endRoutine3(&queryContext.Routine3Running, queryContext.Routine3Queue)
}

func endRoutine3(isRunning *bool, queue *[]model.RepositoryPackageTypeFile) {
	*queue = (*queue)[1:]
	*isRunning = false
	log.Println("End routine 3")
}
