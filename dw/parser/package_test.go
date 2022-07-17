package parser

import (
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestParsePackageJson(t *testing.T) {
	assert := assert.New(t)

	// invalid json
	_, err := ParsePackageJson(`{"bad": "json",}`)
	assert.Equal("invalid character '}' looking for beginning of object key string", err.Error())

	// empty json
	expected := make([]Package, 0)
	r, err := ParsePackageJson(`{"empty": "json"}`)
	assert.Equal(nil, err)
	assert.Equal(expected, r)

	// json with packages
	expected = []Package{
		{Name: "pkgA", Version: "^1.0.0"},
		{Name: "pkgB", Version: "1.1.*"},
		{Name: "pkgC", Version: "^2.0.0-0"},
		{Name: "pkgD", Version: "oui"},
	}
	r, err = ParsePackageJson(`{"dependencies": {"pkgA": "^1.0.0", "pkgB": "1.1.*"}, "devDependencies": {"pkgC": "^2.0.0-0"}, "peerDependencies": {"pkgD": "oui"}}`)
	assert.Equal(nil, err)
	assert.ElementsMatch(expected, r)
}
