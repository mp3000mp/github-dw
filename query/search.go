package query

import (
	"context"
	"fmt"
	"os"
	"strconv"

	"github.com/google/go-github/v45/github"
)

// list all repositories matching search
func QuerySearch(client *github.Client, ctx context.Context, fileName string, page int, nbPerPage int) bool {
	opts := &github.SearchOptions{Sort: "created", Order: "asc", ListOptions: github.ListOptions{Page: page, PerPage: nbPerPage}}
	// info: github query generator: https://github.com/search/advanced
	searchRes, _, err := client.Search.Code(ctx, fmt.Sprintf("created:>2019-01-01 filename:%s", fileName), opts)
	if !CheckResponse(err) {
		return false
	}

	fmt.Printf("Starting search batch %s/%s\n", strconv.Itoa(page*nbPerPage), strconv.Itoa(*searchRes.Total))

	for _, cResult := range searchRes.CodeResults {
		if *cResult.Repository.Fork || *cResult.Repository.Private {
			continue
		}

		fileName := fmt.Sprintf("./%s.txt", strconv.Itoa(page*nbPerPage))
		f, err := os.Create(fileName)
		if err != nil {
			fmt.Printf("Error while creating file %s\n", fileName)
			return false
		}
		_, err = f.WriteString(fmt.Sprintf("%s %s", *cResult.Repository.Owner.Name, *cResult.Repository.Name))
		if err != nil {
			fmt.Printf("Error while writing in file %s\n", fileName)
			return false
		}
		fmt.Println(cResult.Name)
		fmt.Println(cResult.Path)
	}

	fmt.Println("Search batch success")

	return true
}
