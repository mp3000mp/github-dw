package parser

import (
	"errors"
	"fmt"
	"testing"
	"time"

	"github.com/stretchr/testify/assert"
)

type TestIsPackageCase struct {
	Expected string
	Case string
}

func TestIsPackage(t *testing.T) {
	assert := assert.New(t)

	cases := []TestIsPackageCase{
		{Expected: true, Case: "name1"},
		{Expected: true, Case: "name1/name2"},
		{Expected: false, Case: ""},
		{Expected: false, Case: "n"},
		{Expected: false, Case: "//name1"},
		{Expected: false, Case: "/*name1"},
		{Expected: false, Case: "#name1"},
		{Expected: false, Case: "/../name1"},
	}

	for _, c := range cases {
		r := IsPackage(c.Case)
		assert.Equal(c.Expected, r, c.Case)
	}
}
