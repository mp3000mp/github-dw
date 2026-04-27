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
	if err != nil {
		return packages, err
	}

	// find packages
	for _, deps := range []map[string]string{data.Require, data.DevRequire} {
		for pkg, version := range deps {
			for _, v := range strings.Split(strings.ReplaceAll(version, " ", ""), "|") {
				if v != "" && IsPackage(pkg) {
					packages = append(packages, Package{Name: pkg, Version: v})
				}
			}
		}
	}

	return packages, nil
}
