package parser

import (
	"regexp"
	"strings"
)

func ParseRequirementsTxt(rawContent string) ([]Package, error) {
	packages := make([]Package, 0)

	// parse
	data := strings.Split(rawContent, "\n")

	// find packages
	// todo donot capture # in regex
	pkgRegex := regexp.MustCompile(`^([^=<>~!]+)([=<>~!].+?(#|$))`)
	for _, row := range data {
		trimed := strings.Trim(strings.TrimSpace(row), "\t")

		if strings.HasPrefix(trimed, "#") || trimed == "" {
			continue
		}

		rs := pkgRegex.FindStringSubmatch(trimed)
		if len(rs) == 4 {
			pkg := strings.TrimSpace(rs[1])
			version := strings.ReplaceAll(strings.ReplaceAll(rs[2], " ", ""), "#", "")
			versions := strings.Split(version, ",")
			for _, v := range versions {
				if IsPackage(pkg) {
					packages = append(packages, Package{Name: pkg, Version: v})
				}
			}
			continue
		}

		packages = append(packages, Package{Name: trimed, Version: ""})
	}

	return packages, nil
}
