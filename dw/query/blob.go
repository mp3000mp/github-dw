package query

import (
	b64 "encoding/base64"
	"time"
)

type Blob struct {
	Content string
}

// list all repositories matching search
func QueryBlob(context *Context, userName string, repoName string, fileSHA string) (Blob, error) {
	WaitBeforeQuery(context.RateLimiter, "core", true)
	context.RateLimiter.CoreLastQuery = time.Now()
	blob, _, err := context.Client.Git.GetBlob(*context.Context, userName, repoName, fileSHA)
	if !CheckResponse(err, &context.RateLimiter, "core") {
		return Blob{}, err
	}

	sDec, err := b64.StdEncoding.DecodeString(*blob.Content)
	if err != nil {
		return Blob{}, err
	}

	return Blob{Content: string(sDec)}, nil
}
