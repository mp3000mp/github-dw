package parser

import (
	"encoding/json"
)

type packageJson struct {
	Require map[string]string `json:"dependencies"`
	DevRequire map[string]string `json:"devDependencies"`
	PeerRequire map[string]string `json:"peerDependencies"`
	Engines map[string]string `json:"engines"`
}

func ParsePackageJson(rawContent string) ([]Package, error) {
	packages := make([]Package, 0)

	// json decode
	data := packageJson{}
	err := json.Unmarshal([]byte(rawContent), &data)
	if (err != nil) {
		return packages, err
	}

	// find packages
	for pkg, version := range data.Require {
		if IsPackage(pkg) {
			packages = append(packages, Package{Name: pkg, Version: version})
		}
	}
	for pkg, version := range data.DevRequire {
		if IsPackage(pkg) {
			packages = append(packages, Package{Name: pkg, Version: version})
		}
	}
	for pkg, version := range data.PeerRequire {
		if IsPackage(pkg) {
			packages = append(packages, Package{Name: pkg, Version: version})
		}
	}
	for pkg, version := range data.Engines {
		if IsPackage(pkg) {
			packages = append(packages, Package{Name: pkg, Version: version})
		}
	}

	return packages, nil
}
