package query

import (
	"context"
	"time"

	"main/model"

	"github.com/google/go-github/v45/github"
	"gorm.io/gorm"
)

type Context struct {
	Client *github.Client
	Context *context.Context
	DB *gorm.DB
	PreroutineLastReload time.Time
	PreroutineRunning bool
	Routine1PackageType *model.PackageTypeFile
	Routine1Running bool
	Routine2Queue *[]model.Repository
	Routine2Running bool
	Routine3Queue *[]model.RepositoryPackageTypeFile
	Routine3Running bool
	RateLimiter RateLimiter
}

type RateLimiter struct {
	CoreLastQuery time.Time
	CoreLast429 time.Time
	SearchLastQuery time.Time
	SearchLast429 time.Time
}
