package main

import (
	"context"
	"fmt"
	"os"

	"main/query"

	"github.com/joho/godotenv"

	"github.com/google/go-github/v45/github"
	"golang.org/x/oauth2"
)

func main() {
	fmt.Println("Loading config...")
	err := godotenv.Load()
	if err != nil {
 		fmt.Printf("Error loading .env file")
 		os.Exit(1)
	}

	fmt.Println("Creating client...")
	ctx := context.Background()
	ts := oauth2.StaticTokenSource(
		&oauth2.Token{AccessToken: os.Getenv("api_key")},
	)
	tc := oauth2.NewClient(ctx, ts)
	client := github.NewClient(tc)


// 	fmt.Println("Starting search loop")
// 	searchPage := 0
// 	nbPerPage := 100
// 	query.QuerySearch(client, ctx, searchPage, nbPerPage)

	fmt.Println("Starting repo loop")
	query.QueryRepo(client, ctx, "mp3000mp", "template-symfony-vuejs")
}
