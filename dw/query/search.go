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
func QuerySearchCodes(context *Context, searchFileName string, page int, nbPerPage int) ([]SearchCodeItem, error) {
	codes := make([]SearchCodeItem, 0)
	opts := &github.SearchOptions{Sort: "indexed", Order: "desc", ListOptions: github.ListOptions{Page: page, PerPage: nbPerPage}}

	// info: https://docs.github.com/en/search-github/searching-on-github/searching-code
	wait := WaitBeforeQuery(context.RateLimiter, "search")
	if wait > 0 {
		time.Sleep(time.Duration(wait * 1000 * 1000) * time.Millisecond)
	}
	context.RateLimiter.SearchLastQuery = time.Now()
	searchRes, _, err := context.Client.Search.Code(*context.Context, fmt.Sprintf("filename:%s", searchFileName), opts)

	if !CheckResponse(err, &context.RateLimiter, "search") {
		return codes, err
	}

	for _, cResult := range searchRes.CodeResults {
		if *cResult.Repository.Fork || *cResult.Repository.Private || strings.Contains(*cResult.Path, "node_modules") || strings.Contains(*cResult.Path, "vendor") {
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

	return codes, nil
}
