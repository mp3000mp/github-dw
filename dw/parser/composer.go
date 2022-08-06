package parser

import (
	"encoding/json"
	"strings"
)

type composerJson struct {
	Require map[string]string `json:"require"`
	DevRequire map[string]string `json:"require-dev"`
}

func ParseComposerJson(rawContent string) ([]Package, error) {
	packages := make([]Package, 0)

	// json decode
	data := composerJson{}
	err := json.Unmarshal([]byte(rawContent), &data)
	if (err != nil) {
		return packages, err
	}

	// find packages
	for pkg, version := range data.Require {
		versions := strings.Split(strings.ReplaceAll(version, " ", ""), "|")
		for _, v := range versions {
			if v != "" {
				packages = append(packages, Package{Name: pkg, Version: v})
			}
		}
	}
	for pkg, version := range data.DevRequire {
		versions := strings.Split(strings.ReplaceAll(version, " ", ""), "|")
		for _, v := range versions {
			if v != "" {
				packages = append(packages, Package{Name: pkg, Version: v})
			}
		}
	}

	return packages, nil
}
