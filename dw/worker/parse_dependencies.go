package worker

import (
	"fmt"
	"strings"
	"time"

	"main/model"
	"main/parser"
	"main/query"
	"main/system"
)

// repos with fewer than minDepsCount dependencies are ignored (80% of GitHub repos have less than 6)
const minDepsCount = 6

// consume first ParseDependencies queue item
func ParseDependencies(queryContext *query.Context) {
	defer func() {
		if r := recover(); r != nil {
			system.Error.Printf("ParseDependencies panic: %v", r)
			endParseDependencies(queryContext)
		}
	}()

	queryContext.Mu.Lock()
	repoPackageFile := queryContext.Routine3Queue[0]
	pkgType := queryContext.Routine1PackageType
	queryContext.Mu.Unlock()

	var repo model.Repository
	var dbPackage model.Package
	queryContext.DB.Where("id = ?", repoPackageFile.RepositoryID).First(&repo)
	system.Info.Printf("Start ParseDependencies: repo '%s' file '%s'\n", repo.URL, repoPackageFile.Path)

	blob, err := query.QueryBlob(queryContext, repo.Username, repo.Name, repoPackageFile.SHA)
	if err != nil {
		msg := fmt.Sprintf("ParseDependencies => Error while querying blob %s file %s: %s", repo.URL, repoPackageFile.Path, err.Error())
		system.Error.Println(msg)
		endParseDependencies(queryContext)
		if strings.Contains(err.Error(), "404 Not Found") {
			queryContext.DB.Delete(&repoPackageFile)
			return
		}
		queryContext.DB.Model(&repoPackageFile).Updates(model.RepositoryPackageTypeFile{ID: repoPackageFile.ID, Routine3At: time.Now(), RoutineError: msg})
		return
	}

	packages := make([]parser.Package, 0)
	if pkgType.File == "composer.json" {
		packages, err = parser.ParseComposerJson(blob.Content)
	} else if pkgType.File == "package.json" {
		packages, err = parser.ParsePackageJson(blob.Content)
	} else if pkgType.File == "go.mod" {
		packages, err = parser.ParseGoMod(blob.Content)
	} else if pkgType.File == "requirements.txt" {
		packages, err = parser.ParseRequirementsTxt(blob.Content)
	} else {
		err = fmt.Errorf("ParseDependencies => Package file %s is not supported", pkgType.File)
	}
	if err != nil {
		msg := fmt.Sprintf("ParseDependencies => Error while parsing package file: %s", err.Error())
		system.Error.Println(msg)
		if strings.Contains(msg, "invalid character") || strings.Contains(msg, "unexpected end of JSON") || strings.Contains(msg, "cannot unmarshal") {
			queryContext.DB.Delete(&repoPackageFile)
			endParseDependencies(queryContext)
			return
		}
		endParseDependencies(queryContext)
		queryContext.DB.Model(&repoPackageFile).Updates(model.RepositoryPackageTypeFile{ID: repoPackageFile.ID, Routine3At: time.Now(), RoutineError: msg})
		return
	}

	if len(packages) < minDepsCount {
		system.Info.Printf("ParseDependencies => Ignored because less than %d dependencies: %d", minDepsCount, len(packages))
		queryContext.DB.Delete(&repoPackageFile)
		endParseDependencies(queryContext)
		return
	}

	repoPackage := model.RepositoryPackage{}
	queryContext.DB.Where("repository_package_type_file_id = ?", repoPackageFile.ID).Delete(&repoPackage)
	for _, pkgItem := range packages {
		versionRange := parser.GetVersionRange(pkgItem.Version)
		if !versionRange.Valid {
			system.Warn.Printf("ParseDependencies => Warning invalid version '%s'\n", pkgItem.Version)
			versionRange = parser.GetVersionRange("*")
			versionRange.Valid = false
		}

		dbPackage = model.Package{}
		r := queryContext.DB.Where("package_type_file_id = ? AND name = ?", repoPackageFile.PackageTypeFileID, pkgItem.Name).Limit(1).Find(&dbPackage)
		if r.RowsAffected == 0 {
			dbPackage = model.Package{
				PackageTypeFileID: repoPackageFile.PackageTypeFileID,
				Name:              pkgItem.Name,
			}
			queryContext.DB.Create(&dbPackage)
		}

		repoPackage = model.RepositoryPackage{
			RepositoryPackageTypeFileID: repoPackageFile.ID,
			PackageID:                   dbPackage.ID,
			RepositoryID:                repo.ID,
			VersionStr:                  pkgItem.Version,
			VersionMinMajor:             versionRange.MinMajor,
			VersionMinMinor:             versionRange.MinMinor,
			VersionMinPatch:             versionRange.MinPatch,
			VersionMaxMajor:             versionRange.MaxMajor,
			VersionMaxMinor:             versionRange.MaxMinor,
			VersionMaxPatch:             versionRange.MaxPatch,
			Valid:                       versionRange.Valid,
		}
		queryContext.DB.Create(&repoPackage)
	}

	queryContext.DB.Model(&repoPackageFile).Updates(model.RepositoryPackageTypeFile{
		Routine3At: time.Now(),
	})

	endParseDependencies(queryContext)
}

func endParseDependencies(queryContext *query.Context) {
	queryContext.Mu.Lock()
	queryContext.Routine3Queue = queryContext.Routine3Queue[1:]
	queryContext.Routine3Running = false
	queryContext.Mu.Unlock()
	system.Info.Println("End ParseDependencies")
}
