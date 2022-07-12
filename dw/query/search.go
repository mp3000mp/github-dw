package query

import (
	"context"
	"fmt"

	"github.com/google/go-github/v45/github"
)

type SearchCodeItem struct {
	User string
	Name string
	URL  string
}

// list all codes matching search
// info: cannot use Repo search because filename is a code search only parameter...
func QuerySearchCodes(client *github.Client, ctx context.Context, searchFileName string, page int, nbPerPage int) ([]SearchCodeItem, error) {
	codes := make([]SearchCodeItem, 0)
	opts := &github.SearchOptions{Sort: "indexed", Order: "desc", ListOptions: github.ListOptions{Page: page, PerPage: nbPerPage}}

	codes = append(codes, SearchCodeItem{User: "1", Name: "4", URL: "a"})
	codes = append(codes, SearchCodeItem{User: "2", Name: "5", URL: "b"})
	codes = append(codes, SearchCodeItem{User: "3", Name: "6", URL: "c"})
	if len(codes) > 0 {
		return codes, nil
	}

	// info: https://docs.github.com/en/search-github/searching-on-github/searching-code
	searchRes, _, err := client.Search.Code(ctx, fmt.Sprintf("filename:%s", searchFileName), opts)
	// searchRes, _, err := client.Search.Repositories(ctx, fmt.Sprintf("pushed:>2021-01-01 size:>1000 filename:%s", searchFileName), opts)
	if !CheckResponse(err) {
		return codes, err
	}

	for _, cResult := range searchRes.CodeResults {
		if *cResult.Repository.Fork || *cResult.Repository.Private {
			continue
		}

		item := SearchCodeItem{User: *cResult.Repository.Owner.Login, Name: *cResult.Repository.Name, URL: *cResult.Repository.HTMLURL}
		// todo sync ?
		codes = append(codes, item)
	}

	return codes, nil
}
