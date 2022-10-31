package parser

import (
	"strings"
)

func IsPackage(name string) bool {
	if len(name) < 2 {
		return false
	}
	forbidden := []string{
		"#",
		"//",
		"/*",
		"..",
	}
	for _, f := range forbidden {
		if strings.Contains(name, f) {
			return false
		}
	}

	return true
}
