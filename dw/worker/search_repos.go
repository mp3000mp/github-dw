package worker

import (
	"time"

	"main/model"
	"main/query"
	"main/system"

	"gorm.io/gorm/clause"
)

const nbPerPage = 100

func SearchRepos(queryContext *query.Context) {
	defer func() {
		if r := recover(); r != nil {
			system.Error.Printf("SearchRepos panic: %v", r)
			endSearchRepos(queryContext)
		}
	}()

	queryContext.Mu.Lock()
	pkgType := queryContext.Routine1PackageType
	queryContext.Mu.Unlock()

	system.Info.Printf("Start SearchRepos: size %d, page %d\n", int(pkgType.GithubCurrentSize), int(pkgType.GithubCurrentPage))

	codes, maxPage, err := query.QuerySearchCodes(queryContext, pkgType.File, int(pkgType.GithubCurrentSize), int(pkgType.GithubCurrentPage), nbPerPage)
	if err != nil {
		system.Error.Printf("SearchRepos => Error while querying codes: %s", err.Error())
		endSearchRepos(queryContext)
		return
	}

	for _, code := range codes {
		repo := model.Repository{Name: code.Name, Username: code.User, URL: code.URL, Routine1At: time.Now()}
		result := queryContext.DB.Select("Name", "Username", "URL", "Routine1At").Clauses(clause.OnConflict{DoNothing: true}).Create(&repo)
		if result.RowsAffected > 0 {
			queryContext.Mu.Lock()
			queryContext.Routine2Queue = append(queryContext.Routine2Queue, repo)
			queryContext.Mu.Unlock()
		} else {
			queryContext.DB.Where("URL = ?", repo.URL).First(&repo)
		}
		repoPackageFile := model.RepositoryPackageTypeFile{RepositoryID: repo.ID, PackageTypeFileID: pkgType.ID, Path: code.Path, SHA: code.SHA, Routine1At: time.Now()}
		queryContext.DB.Where("repository_id = ? AND package_type_file_id = ? AND path = ?", repoPackageFile.RepositoryID, repoPackageFile.PackageTypeFileID, repoPackageFile.Path).Delete(&repoPackageFile)
		queryContext.DB.Select("RepositoryID", "PackageTypeFileID", "Path", "SHA", "Routine1At").Create(&repoPackageFile)
		queryContext.Mu.Lock()
		queryContext.Routine3Queue = append(queryContext.Routine3Queue, repoPackageFile)
		queryContext.Mu.Unlock()
	}

	// next page
	pkgType.GithubCurrentPage, pkgType.GithubCurrentSize = nextPage(pkgType.GithubCurrentPage, pkgType.GithubCurrentSize, maxPage)
	pkgType.UpdatedAt = time.Now()
	queryContext.DB.Save(pkgType)

	endSearchRepos(queryContext)
}

// nextPage advances pagination. GitHub free accounts cap at 1000 results (10 pages),
// so reaching page 10 or the last page triggers a size increment and page reset.
func nextPage(currentPage, currentSize uint32, maxPage int) (uint32, uint32) {
	if currentPage == 10 || currentPage >= uint32(maxPage) {
		return 1, currentSize + 1
	}
	return currentPage + 1, currentSize
}

func endSearchRepos(queryContext *query.Context) {
	queryContext.Mu.Lock()
	queryContext.Routine1Running = false
	queryContext.Mu.Unlock()
	system.Info.Println("End SearchRepos")
}
