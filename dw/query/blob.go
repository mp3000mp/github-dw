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
	WaitBeforeQuery(context, "core", true)
	context.RateLimiterMu.Lock()
	context.RateLimiter.CoreLastQuery = time.Now()
	context.RateLimiterMu.Unlock()
	blob, _, err := context.Client.Git.GetBlob(*context.Ctx, userName, repoName, fileSHA)
	if !CheckResponse(err, context, "core") {
		return Blob{}, err
	}

	sDec, err := b64.StdEncoding.DecodeString(*blob.Content)
	if err != nil {
		return Blob{}, err
	}

	return Blob{Content: string(sDec)}, nil
}
