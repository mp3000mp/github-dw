package parser

import (
	"encoding/json"
)

type ComposerJson struct {
	Require map[string]string `json:"require"`
	DevRequire map[string]string `json:"require-dev"`
}

func ParseComposerJson(rawContent string) (map[string]string, error) {
	requires := make(map[string]string)

	// json decode
	data := ComposerJson{}
	err := json.Unmarshal([]byte(rawContent), &data)
	if (err != nil) {
		return requires, err
	}

	// find packages
	for pkg, version := range data.Require {
		requires[pkg] = version
	}
	for pkg, version := range data.DevRequire {
		requires[pkg] = version
	}

	return requires, nil
}
