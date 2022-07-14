package query

import (
	b64 "encoding/base64"
	"fmt"
	"time"
)

type Blob struct {
	Content string
}

// list all repositories matching search
func QueryBlob(context *Context, userName string, repoName string, fileSHA string) (Blob, error) {
	wait := WaitBeforeQuery(context.RateLimiter, "core")
	if wait > 0 {
		time.Sleep(time.Duration(wait * 1000 * 1000) * time.Millisecond)
	}
	context.RateLimiter.CoreLastQuery = time.Now()
	blob, _, err := context.Client.Git.GetBlob(*context.Context, userName, repoName, fileSHA)
	if !CheckResponse(err, &context.RateLimiter, "core") {
		return Blob{}, err
	}

	sDec, err := b64.StdEncoding.DecodeString(*blob.Content)
	if err != nil {
		return Blob{}, err
	}
	fmt.Printf("%s\n", string(sDec))

	return Blob{Content: string(sDec)}, nil
}
