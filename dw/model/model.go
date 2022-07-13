package model

import (
  "os"
  "time"

  "gorm.io/driver/mysql"
  "gorm.io/gorm"
  "gorm.io/gorm/clause"
  "gorm.io/gorm/schema"
)

type Repository struct {
	ID uint                                             `gorm:"primaryKey"`
	Name, Username string                               `gorm:"size:100;not null"`
	MainLanguage string 			                    `gorm:"size:50"`
	URL string                                          `gorm:"uniqueIndex;size:255;not null"`
	FullName, RoutineError string                       `gorm:"size:255"`
	LicenseName string                                  `gorm:"size:100"`
	ForksCount, OpenIssuesCount, StargazersCount uint32
	GithubId, Size uint
	Routine1At time.Time                                `gorm:"type:DATETIME(0);not null"`
	CreatedAt, PushedAt time.Time                       `gorm:"type:DATETIME(0);default:null"`
	Routine2At, Routine3At time.Time                    `gorm:"type:DATETIME(0)"`
	Languages []RepositoryLanguage                      `gorm:"constraint:OnUpdate:CASCADE;"`
	Topics []RepositoryTopic                            `gorm:"constraint:OnUpdate:CASCADE;"`
}

type RepositoryLanguage struct {
	ID uint           `gorm:"primaryKey"`
	RepositoryID uint `gorm:"not null"`
	Language string   `gorm:"size:100;not null"`
	Weight int        `gorm:"not null"`
}

type RepositoryTopic struct {
	ID uint           `gorm:"primaryKey"`
	RepositoryID uint `gorm:"not null"`
	Topic string      `gorm:"size:100;not null"`
}

type Package struct {
	ID uint                  `gorm:"primaryKey"`
	File string              `gorm:"uniqueIndex;size:50;not null"`
	Language, Name string    `gorm:"size:50;not null"`
	GithubCurrentPage uint32 `gorm:"default:0"`
	UpdatedAt time.Time      `gorm:"type:DATETIME(0);not null"`
}

func GetNamingStrategy() schema.NamingStrategy {
	return schema.NamingStrategy{
		SingularTable: true,
	}
}

var Connection *gorm.DB

func GetConnection() (*gorm.DB, error) {
	var err error
	if Connection == nil {
		Connection, err = gorm.Open(mysql.Open(os.Getenv("DATABASE_URL")), &gorm.Config{
			NamingStrategy: GetNamingStrategy(),
		})
	}
   	if err != nil {
		return nil, err
   	}
   	return Connection, nil
}

func InitDatabase(db *gorm.DB) error {
	err := db.AutoMigrate(&Repository{}, &Repository{}, &RepositoryLanguage{}, &RepositoryTopic{}, &Package{})
	if err != nil {
		return err
	}

	packages := []Package{
		Package{Language: "PHP", Name: "Composer", File: "composer.json"},
		Package{Language: "Javascript", Name: "npm", File: "package.json"},
		Package{Language: "Go", Name: "Go", File: "go.mod"},
		Package{Language: "Python", Name: "PyPi", File: "requirements.txt"},
		Package{Language: "Python", Name: "PyPi", File: "setup.py"},
	}
	for _, pack := range packages {
		db.Clauses(clause.OnConflict{DoNothing: true}).Create(&pack)
	}

	return nil
}
