package query

import (
	"fmt"
	"strings"
	"time"

	"github.com/google/go-github/v45/github"
)

type SearchCodeItem struct {
	User string
	Name string
	URL  string
	Path string
	SHA string
}

// list all codes matching search
// info: cannot use Repo search because filename is a code search only parameter...
//   so we cannot do for example: "pushed:>2021-01-01 size:>1000 filename:%s"
func QuerySearchCodes(context *Context, searchFileName string, fileSize int, page int, nbPerPage int) ([]SearchCodeItem, int, error) {
	codes := make([]SearchCodeItem, 0)
	opts := &github.SearchOptions{Sort: "indexed", Order: "desc", ListOptions: github.ListOptions{Page: page, PerPage: nbPerPage}}

	// info: https://docs.github.com/en/search-github/searching-on-github/searching-code
	// info: free account are limited to 1000 first results so we try to get more result by trying each size
	// info: we limit to / be cause of too much useless results in vendor, libs that should be in .gitignore...
	WaitBeforeQuery(context.RateLimiter, "search", true)
	context.RateLimiter.SearchLastQuery = time.Now()
	searchRes, _, err := context.Client.Search.Code(*context.Context, fmt.Sprintf("filename:%s size:%d..%d extension:%s path:/", searchFileName, fileSize, fileSize, getFileExt(searchFileName)), opts)

	if !CheckResponse(err, &context.RateLimiter, "search") {
		return codes, 0, err
	}

	for _, cResult := range searchRes.CodeResults {
		// todo: can we implement path filter in github search query ?
		if *cResult.Repository.Fork ||
		   *cResult.Repository.Private ||
		   strings.Contains(*cResult.Path, "node_modules") ||
		   strings.Contains(*cResult.Path, "vendor") {
			continue
		}

		item := SearchCodeItem{
			User: *cResult.Repository.Owner.Login,
			Name: *cResult.Repository.Name,
			URL: *cResult.Repository.HTMLURL,
			Path: *cResult.Path,
			SHA: *cResult.SHA,
		}
		// todo sync ?
		codes = append(codes, item)
	}

	maxPage := int(*searchRes.Total / nbPerPage)+1

	return codes, maxPage, nil
}

func getFileExt(fileName string) string {
	arr := strings.Split(fileName, ".")
	return arr[1]
}
