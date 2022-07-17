package parser

import (
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestParseRequirementsTxt(t *testing.T) {
	assert := assert.New(t)

	expected := []Package{
		{Name: "pkgA", Version: ""},
		{Name: "pkgB", Version: "==1.0.0"},
		{Name: "pkgC", Version: ">=1.1.0"},
		{Name: "pkgD", Version: ">=1.1.0"},
		{Name: "pkgD", Version: ">=2.0.0"},
		{Name: "pkgE", Version: "==1.0.*"},
	}
	r, _ := ParseRequirementsTxt(`# comment
	pkgA

	pkgB == 1.0.0
	pkgC>=1.1.0 # comment
	pkgD >= 1.1.0, >=2.0.0
	pkgE == 1.0.*`)
	assert.ElementsMatch(expected, r)
}
