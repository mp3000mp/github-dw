package parser

import (
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestParseGoMod(t *testing.T) {
	assert := assert.New(t)

	expected := []Package{
		{Name: "go", Version: "1.18"},
		{Name: "pkgA", Version: "v1.0.0"},
		{Name: "pkgB", Version: "v1.1.0"},
		{Name: "pkgC", Version: "v1.2.0"},
	}
	r, _ := ParseGoMod(`module main

	go 1.18

	require (
		pkgA v1.0.0
		pkgB v1.1.0
		pkgZ v1.1.0 // indirect
	)

	require pkgC v1.2.0`)
	assert.ElementsMatch(expected, r)
}
