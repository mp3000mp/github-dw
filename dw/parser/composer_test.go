package parser

import (
	"testing"
	"github.com/stretchr/testify/assert"
)

func TestParseComposerJson(t *testing.T) {
	assert := assert.New(t)

	// invalid json
	_, err := ParseComposerJson(`{"bad": "json",}`)
	assert.Equal("invalid character '}' looking for beginning of object key string", err.Error())

	// empty json
	expected := make([]Package, 0)
	r, err := ParseComposerJson(`{"empty": "json"}`)
	assert.Equal(nil, err)
	assert.Equal(expected, r)

	// json with packages
	expected = []Package{
		{Name: "pkgA", Version: "^1.0.0"},
		{Name: "pkgB", Version: "1.1.*"},
		{Name: "pkgC", Version: "^1.7"},
		{Name: "pkgC", Version: "^2.0.0-0"},
	}
	r, err = ParseComposerJson(`{"require": {"pkgA": "^1.0.0", "pkgB": "1.1.*"}, "require-dev": {"pkgC": "^1.7 || ^2.0.0-0"}}`)
	assert.Equal(nil, err)
	assert.Equal(expected, r)
}
