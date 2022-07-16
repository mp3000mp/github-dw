package routine

import (
	"log"
	"time"

	"main/model"
	"main/query"

	"gorm.io/gorm/clause"
)

const nbPerPage = 100

func RunRoutine1(queryContext *query.Context) {
	queryContext.Routine1Running = true
	log.Printf("Start routine 1: size %d, page %d\n", int(queryContext.Routine1PackageType.GithubCurrentSize), int(queryContext.Routine1PackageType.GithubCurrentPage))

	// get repos with file x
	codes, maxPage, err := query.QuerySearchCodes(queryContext, queryContext.Routine1PackageType.File, int(queryContext.Routine1PackageType.GithubCurrentSize), int(queryContext.Routine1PackageType.GithubCurrentPage), nbPerPage)
	if err != nil {
		log.Printf("Routine 1 => Error while querying codes: %s", err.Error())
		return
	}

	for _, code := range codes {
		// insert repo routine1At=now
		repo := model.Repository{Name: code.Name, Username: code.User, URL: code.URL, Routine1At: time.Now()}
		result := queryContext.DB.Select("Name", "Username", "URL", "Routine1At").Clauses(clause.OnConflict{DoNothing: true}).Create(&repo)
		if result.RowsAffected > 0 {
			*queryContext.Routine2Queue = append(*queryContext.Routine2Queue, repo)
		} else {
			queryContext.DB.Where("URL = ?", repo.URL).First(&repo)
		}
		// insert package file routine1At=now
		repoPackageFile := model.RepositoryPackageTypeFile{RepositoryID: repo.ID, PackageTypeID: queryContext.Routine1PackageType.ID, Path: code.Path, SHA: code.SHA, Routine1At: time.Now()}
		queryContext.DB.Where("repository_id = ? AND package_type_id = ? AND path = ?", repoPackageFile.RepositoryID, repoPackageFile.PackageTypeID, repoPackageFile.Path).Delete(&repoPackageFile)
		queryContext.DB.Clauses(clause.OnConflict{DoNothing: true}).Create(&repoPackageFile)
		*queryContext.Routine3Queue = append(*queryContext.Routine3Queue, repoPackageFile)
	}

	// next page
	// info: free account are limited to 1000 first results
	if queryContext.Routine1PackageType.GithubCurrentPage == 10 || queryContext.Routine1PackageType.GithubCurrentPage >= uint32(maxPage) {
		queryContext.Routine1PackageType.GithubCurrentSize++
		queryContext.Routine1PackageType.GithubCurrentPage = 1
	} else {
		queryContext.Routine1PackageType.GithubCurrentPage++
	}
	queryContext.Routine1PackageType.UpdatedAt = time.Now()
	queryContext.DB.Save(queryContext.Routine1PackageType)

	queryContext.Routine1Running = false
	log.Println("End routine 1")
}
