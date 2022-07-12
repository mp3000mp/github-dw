package model

import (
  "time"

  "gorm.io/gorm/schema"
)

type Repository struct {
	ID uint                                             `gorm:"primaryKey"`
	MainLanguage, Name, Username string                 `gorm:"size:100;not null"`
	FullName, URL string                                `gorm:"size:255;not null"`
	LicenseName string                                  `gorm:"size:100"`
	ForksCount, OpenIssuesCount, StargazersCount uint32 `gorm:"not null"`
	GithubId, Size uint                                 `gorm:"not null"`
	CreatedAt, PushedAt, Routine1At time.Time           `gorm:"type:DATETIME(0);not null"`
	Routine2At, Routine3At time.Time                    `gorm:"type:DATETIME(0)"`
	Languages []RepositoryLanguage
	Topics []RepositoryTopic
}

type RepositoryLanguage struct {
	ID uint           `gorm:"primaryKey"`
	RepositoryID uint `gorm:"not null"`
	Language string   `gorm:"size:100;not null"`
	weight int        `gorm:"not null"`
}

type RepositoryTopic struct {
	ID uint           `gorm:"primaryKey"`
	RepositoryID uint `gorm:"not null"`
	Topic string      `gorm:"size:100;not null"`
}

func GetNamingStrategy() schema.NamingStrategy {
	return schema.NamingStrategy{
		SingularTable: true,
	}
}
