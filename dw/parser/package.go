package parser

import (
	"encoding/json"
)

type PackageJson struct {
	Require map[string]string `json:"dependencies"`
	DevRequire map[string]string `json:"devDependencies"`
	PeerRequire map[string]string `json:"peerDependencies"`
	Engines map[string]string `json:"engines"`
}

func ParsePackageJson(rawContent string) ([]Package, error) {
	packages := make([]Package, 0)

	// json decode
	data := PackageJson{}
	err := json.Unmarshal([]byte(rawContent), &data)
	if (err != nil) {
		return packages, err
	}

	// find packages
	for pkg, version := range data.Require {
		packages = append(packages, Package{Name: pkg, Version: version})
	}
	for pkg, version := range data.DevRequire {
		packages = append(packages, Package{Name: pkg, Version: version})
	}
	for pkg, version := range data.PeerRequire {
		packages = append(packages, Package{Name: pkg, Version: version})
	}
	for pkg, version := range data.Engines {
		packages = append(packages, Package{Name: pkg, Version: version})
	}

	return packages, nil
}
