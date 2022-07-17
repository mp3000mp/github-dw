package parser

import (
	"regexp"
	"strings"
)

func ParseGoMod(rawContent string) ([]Package, error) {
	packages := make([]Package, 0)

	// parse
	data := strings.Split(rawContent, "\n")
	isInRequire := false

	// find packages
	goRegex := regexp.MustCompile(`^go\s\d\.\d+`)
	for _, row := range data {
		trimed := strings.Trim(strings.TrimSpace(row), "\t")

		goVersion := goRegex.FindString(trimed)
		if goVersion != "" {
			pkg := strings.Split(goVersion, " ")
			packages = append(packages, Package{Name: pkg[0], Version: pkg[1]})
		}

		if strings.HasPrefix(trimed, "require") {
			if trimed == "require (" {
				isInRequire = true
				continue
			}
			pkg := strings.Split(trimed, " ")
			if len(pkg) >= 3 && pkg[0] == "require" && strings.HasPrefix(pkg[2], "v") {
				packages = append(packages, Package{Name: pkg[1], Version: pkg[2]})
				continue
			}
		}
		if strings.HasPrefix(trimed, ")") {
			isInRequire = false
			continue
		}

		if isInRequire {
			pkg := strings.Split(trimed, " ")
			if len(pkg) >= 2 && strings.HasPrefix(pkg[1], "v") {
				packages = append(packages, Package{Name: pkg[0], Version: pkg[1]})
			}
		}
	}

	return packages, nil
}
