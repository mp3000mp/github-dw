package query

import (
	"context"
	"fmt"
	"os"
	"strconv"

	"github.com/google/go-github/v45/github"
)

// list all codes matching search
// info: cannot use Repo search because filename is a code search only parameter...
func QuerySearchCodes(client *github.Client, ctx context.Context, searchFileName string, page int, nbPerPage int) bool {
	opts := &github.SearchOptions{Sort: "indexed", Order: "desc", ListOptions: github.ListOptions{Page: page, PerPage: nbPerPage}}
	// info: https://docs.github.com/en/search-github/searching-on-github/searching-code
	searchRes, _, err := client.Search.Code(ctx, fmt.Sprintf("filename:%s", searchFileName), opts)
	// searchRes, _, err := client.Search.Repositories(ctx, fmt.Sprintf("pushed:>2021-01-01 size:>1000 filename:%s", searchFileName), opts)
	if !CheckResponse(err) {
		return false
	}

	fmt.Printf("Starting search batch %s/%s\n", strconv.Itoa(page*nbPerPage), strconv.Itoa(*searchRes.Total))

	fileName := fmt.Sprintf("./output/%s.txt", strconv.Itoa(page*nbPerPage))
	f, err := os.Create(fileName)

	for _, cResult := range searchRes.CodeResults {
		if *cResult.Repository.Fork || *cResult.Repository.Private {
			continue
		}

		if err != nil {
			fmt.Printf("Error while creating file %s\n", fileName)
			return false
		}
		_, err = f.WriteString(fmt.Sprintf("%s %s %s\n", *cResult.Repository.Owner.Login, *cResult.Repository.Name, *cResult.Repository.HTMLURL))
		if err != nil {
			fmt.Printf("Error while writing in file %s\n", fileName)
			return false
		}
	}

	fmt.Println("Search batch success")

	return true
}
