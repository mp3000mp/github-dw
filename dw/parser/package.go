package parser

import (
	"encoding/json"
)

type PackageJson struct {
	Require map[string]string `json:"dependencies"`
	DevRequire map[string]string `json:"devDependencies"`
	PeerRequire map[string]string `json:"peerDependencies"`
}

func ParsePackageJson(rawContent string) (map[string]string, error) {
	requires := make(map[string]string)

	// json decode
	data := PackageJson{}
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
	for pkg, version := range data.PeerRequire {
		requires[pkg] = version
	}

	return requires, nil
}
