package model

import (
  "time"

  "gorm.io/driver/mysql"
  "gorm.io/gorm"
  "gorm.io/gorm/clause"
  "gorm.io/gorm/schema"
)

type Repository struct {
	ID uint                                             `gorm:"primaryKey"`
	Name string                                         `gorm:"size:100;not null;index"`
	Username string                                     `gorm:"size:100;not null"`
	MainLanguage string 			                    `gorm:"size:50"`
	URL string                                          `gorm:"uniqueIndex;size:255;not null"`
	FullName, RoutineError string                       `gorm:"size:255"`
	Description string                                  `gorm:"size:4000;class:FULLTEXT"`
	LicenseName string                                  `gorm:"size:100"`
	ForksCount, OpenIssuesCount, StargazersCount uint32
	GithubId, Size uint
	Routine1At time.Time                                `gorm:"type:DATETIME(0);not null"`
	CreatedAt, PushedAt, Routine2At time.Time           `gorm:"type:DATETIME(0);default:null"`
	Languages []RepositoryLanguage                      `gorm:"constraint:OnDelete:CASCADE"`
	Topics []RepositoryTopic                            `gorm:"constraint:OnDelete:CASCADE"`
	PackageTypeFiles []RepositoryPackageTypeFile        `gorm:"constraint:OnDelete:CASCADE"`
	RepositoryPackages []RepositoryPackage              `gorm:"constraint:OnDelete:CASCADE"`
}

// not used anymore because it costs one query
type RepositoryLanguage struct {
	ID uint           `gorm:"primaryKey"`
	RepositoryID uint `gorm:"not null"`
	Language string   `gorm:"size:100;not null"`
	Weight uint       `gorm:"not null"`
}

type RepositoryTopic struct {
	ID uint           `gorm:"primaryKey"`
	RepositoryID uint `gorm:"not null"`
	Topic string      `gorm:"size:100;not null"`
}

type PackageTypeFile struct {
	ID uint                                                `gorm:"primaryKey"`
	File string                                            `gorm:"uniqueIndex;size:50;not null"`
	Language, Name string                                  `gorm:"size:50;not null"`
	GithubCurrentSize uint32                               `gorm:"default:100;not null"`
	GithubCurrentPage uint32                               `gorm:"default:1;not null"`
	UpdatedAt time.Time                                    `gorm:"type:DATETIME(0);not null"`
	Priority bool                                          `gorm:"default:false;not null"`
	RepositoryPackageTypeFiles []RepositoryPackageTypeFile `gorm:"constraint:OnDelete:CASCADE"`
}

type RepositoryPackageTypeFile struct {
	ID uint                                `gorm:"primaryKey"`
	RepositoryID uint                      `gorm:"uniqueIndex:idx_uniq;not null"`
	PackageTypeFileID uint 	               `gorm:"uniqueIndex:idx_uniq;not null"`
	Path string                            `gorm:"uniqueIndex:idx_uniq;size:255;not null"`
	RoutineError string                    `gorm:"size:255"`
	SHA string                             `gorm:"size:100;not null"`
	Routine1At time.Time                   `gorm:"type:DATETIME(0);not null"`
	Routine3At time.Time                   `gorm:"type:DATETIME(0);default:null"`
	RepositoryPackages []RepositoryPackage `gorm:"constraint:OnDelete:CASCADE"`
}

type Package struct {
	ID uint                                `gorm:"primaryKey"`
	PackageTypeFileID uint                 `gorm:"uniqueIndex:idx_uniq;not null"`
	Name string                            `gorm:"uniqueIndex:idx_uniq;size:100;not null;index"`
	RepositoryPackages []RepositoryPackage `gorm:"constraint:OnDelete:CASCADE"`
}

type RepositoryPackage struct {
	ID uint                          `gorm:"primaryKey"`
	RepositoryPackageTypeFileID uint `gorm:"not null"` // no unique index because of OR version pattern
	PackageID uint                   `gorm:"not null"`
	RepositoryID uint                `gorm:"not null"` // not necessary but boost search perf
	VersionStr string                `gorm:"size:55;not null"`
	VersionMinMajor uint16           `gorm:"not null;index"`
	VersionMinMinor uint16           `gorm:"not null;index"`
	VersionMinPatch uint16           `gorm:"not null;index"`
	VersionMaxMajor uint16           `gorm:"not null;index"`
	VersionMaxMinor uint16           `gorm:"not null;index"`
	VersionMaxPatch uint16           `gorm:"not null;index"`
	Valid bool 						 `gorm:"default:false;not null"`
}

func getNamingStrategy() schema.NamingStrategy {
	return schema.NamingStrategy{
		TablePrefix: "dw_",
		SingularTable: true,
	}
}

var Connection *gorm.DB

func GetConnection(databaseUrl string) (*gorm.DB, error) {
	var err error
	if Connection == nil {
		Connection, err = gorm.Open(mysql.Open(databaseUrl), &gorm.Config{
			NamingStrategy: getNamingStrategy(),
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
		&PackageTypeFile{},
		&RepositoryPackageTypeFile{},
		&Package{},
		&RepositoryPackage{},
	)
	if err != nil {
		return err
	}

	packageTypes := []PackageTypeFile{
		PackageTypeFile{Language: "PHP", Name: "Composer", File: "composer.json", GithubCurrentSize: 500, Priority: true},
		PackageTypeFile{Language: "Javascript", Name: "npm", File: "package.json", GithubCurrentSize: 1000},
		PackageTypeFile{Language: "Go", Name: "Go", File: "go.mod", GithubCurrentSize: 500},
		PackageTypeFile{Language: "Python", Name: "pip", File: "requirements.txt", GithubCurrentSize: 400},
//		PackageTypeFile{Language: "Python", Name: "pip", File: "setup.py", GithubCurrentSize: 300}, // python interpreter needed
//		PackageTypeFile{Language: "Python", Name: "pip", File: "pyproject.toml", GithubCurrentSize: 300},
//		PackageTypeFile{Language: "Python", Name: "pip", File: "Pipfile", GithubCurrentSize: 300},
	}
	for _, pack := range packageTypes {
		db.Clauses(clause.OnConflict{DoNothing: true}).Create(&pack)
	}

	return nil
}
