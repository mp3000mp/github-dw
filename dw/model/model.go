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
	CreatedAt, PushedAt, Routine2At time.Time           `gorm:"type:DATETIME(0);default:null"`
	Languages []RepositoryLanguage                      `gorm:"constraint:OnUpdate:CASCADE"`
	Topics []RepositoryTopic                            `gorm:"constraint:OnUpdate:CASCADE"`
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

type PackageType struct {
	ID uint                  `gorm:"primaryKey"`
	File string              `gorm:"uniqueIndex;size:50;not null"`
	Language, Name string    `gorm:"size:50;not null"`
	GithubCurrentSize uint32 `gorm:"default:100"`
	GithubCurrentPage uint32 `gorm:"default:1"`
	UpdatedAt time.Time      `gorm:"type:DATETIME(0);not null"`
}

type RepositoryPackageTypeFile struct {
	ID uint                                `gorm:"primaryKey"`
	RepositoryID uint                      `gorm:"not null"`
	PackageTypeID uint 	                   `gorm:"not null"`
	Path string                            `gorm:"size:255;not null"`
	RoutineError string                    `gorm:"size:255"`
	SHA string                             `gorm:"size:100;not null"`
	Routine1At time.Time                   `gorm:"type:DATETIME(0);not null"`
	Routine3At time.Time                   `gorm:"type:DATETIME(0);default:null"`
	RepositoryPackages []RepositoryPackage `gorm:"constraint:OnDelete:CASCADE"`
}

type RepositoryPackage struct {
	ID uint                          `gorm:"primaryKey"`
	RepositoryPackageTypeFileID uint `gorm:"not null"`
	Name string                      `gorm:"size:100;not null"`
	VersionStr string                `gorm:"size:20;not null"`
	VersionMin uint16                `gorm:"not null"`
	VersionMax uint16
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
	err := db.AutoMigrate(
		&Repository{},
		&Repository{},
		&RepositoryLanguage{},
		&RepositoryTopic{},
		&PackageType{},
		&RepositoryPackageTypeFile{},
		&RepositoryPackage{},
	)
	if err != nil {
		return err
	}

	packageTypes := []PackageType{
		PackageType{Language: "PHP", Name: "Composer", File: "composer.json"},
		PackageType{Language: "Javascript", Name: "npm", File: "package.json"},
		PackageType{Language: "Go", Name: "Go", File: "go.mod"},
		PackageType{Language: "Python", Name: "pip", File: "requirements.txt"},
		PackageType{Language: "Python", Name: "pip", File: "setup.py"},
	}
	for _, pack := range packageTypes {
		db.Clauses(clause.OnConflict{DoNothing: true}).Create(&pack)
	}

	return nil
}
