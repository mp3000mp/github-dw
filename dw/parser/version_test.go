package parser

import (
	"fmt"
	"strconv"
	"testing"

	"github.com/stretchr/testify/assert"
)

type TestGetValidRangeCase struct {
	Expected string
	Case string
}

// see https://devhints.io/semver
func TestGetValidRange(t *testing.T) {
	assert := assert.New(t)

	cases := []TestGetValidRangeCase{
		{Expected: "Not valid.", Case: ">v one"},
		{Expected: "Not valid.", Case: "<0"},
		{Expected: ">=1.2.0 <2.0.0", Case: "^1.2.0"},
		{Expected: ">=1.2.0 <2.0.0", Case: "^1.2.0-0"},
		{Expected: ">=1.2.0 <2.0.0", Case: "^1.2.0-2"},
		{Expected: ">=1.2.0 <1.3.0", Case: "~1.2.0"},
		{Expected: ">=1.2.1 <65535.65535.65535", Case: ">1.2.0"},
		{Expected: ">=1.2.0 <65535.65535.65535", Case: ">=1.2.0"},
		{Expected: ">=0.0.0 <1.2.0", Case: "<1.2.0"},
		{Expected: ">=0.0.0 <1.0.0", Case: "<1"},
		{Expected: ">=0.0.0 <1.2.1", Case: "<=1.2.0"},
		{Expected: ">=1.2.0 <1.2.1", Case: "1.2.0"},
		{Expected: ">=1.2.0 <1.2.1", Case: "=1.2.0"},
		{Expected: ">=1.2.0 <1.3.0", Case: "1.2.*"},
		{Expected: ">=1.0.0 <2.0.0", Case: "1.*"},
		{Expected: ">=1.2.1 <1.4.6", Case: ">=1.2.1 <=1.4.5"},
		{Expected: ">=1.2.1 <1.4.6", Case: "<=1.4.5 >=1.2.1"},
		{Expected: "Not valid.", Case: "<=1.2.1 >=1.4.5"},
		{Expected: ">=1.2.0 <2.1.4", Case: "1.2.0 - 2.1.4"},
		{Expected: ">=1.2.0 <3.0.0", Case: "1.2.0 - 2"},
		{Expected: "Not valid.", Case: "2.1.0 - 1.4.0"},
		{Expected: ">=0.0.0 <65535.65535.65535", Case: "*"},
		{Expected: ">=1.0.1 <65535.65535.65535", Case: ">1.*"},
		{Expected: ">=0.0.0 <1.0.0", Case: "<1.*"},
	}

	for _, c := range cases {
		r := GetVersionRange(c.Case)
		assert.Equal(c.Expected, RangeStr(r), c.Case)
	}
}

type TestCoerceCase struct {
	Expected string
	Case string
	PatchMin bool
}

func TestCoerce(t *testing.T) {
	assert := assert.New(t)

	cases := []TestCoerceCase{
		{Expected: "Not valid.", Case: "v one", PatchMin: true},
		{Expected: "1.1.2", Case: "1.1.2", PatchMin: true},
		{Expected: "1.1.3", Case: "1.1.2", PatchMin: false},
		{Expected: "1.1.2", Case: "v1.1.2", PatchMin: true},
		{Expected: "1.1.2", Case: "version1.1.2.4", PatchMin: true},
		{Expected: "1.1.2", Case: "v1.1.2-0", PatchMin: true},
		{Expected: "1.1.2", Case: "v1.1.2-alpha", PatchMin: true},
		{Expected: "1.1.0", Case: "1.1.*", PatchMin: true},
		{Expected: "1.1.0", Case: "1.1", PatchMin: true},
		{Expected: "1.1.0", Case: "1.1.x", PatchMin: true},
		{Expected: "1.2.0", Case: "1.1.*", PatchMin: false},
		{Expected: "1.2.0", Case: "1.1", PatchMin: false},
		{Expected: "1.2.0", Case: "1.1.x", PatchMin: false},
		{Expected: "1.0.0", Case: "1.*", PatchMin: true},
		{Expected: "1.0.0", Case: "1", PatchMin: true},
		{Expected: "1.0.0", Case: "1.x", PatchMin: true},
		{Expected: "1.0.0", Case: "1.x.x", PatchMin: true},
		{Expected: "2.0.0", Case: "1.*", PatchMin: false},
		{Expected: "2.0.0", Case: "1", PatchMin: false},
		{Expected: "2.0.0", Case: "1.x", PatchMin: false},
		{Expected: "2.0.0", Case: "1.x.x", PatchMin: false},
		{Expected: "0.0.0", Case: "*", PatchMin: true},
		{Expected: "65535.65535.65535", Case: "*", PatchMin: false},
	}

	for _, c := range cases {
		r := Coerce(c.Case, c.PatchMin)
		assert.Equal(c.Expected, SemverStr(r), fmt.Sprintf("%s %s", c.Case, strconv.FormatBool(c.PatchMin)))
	}
}

func SemverStr(semver Semver) string {
	if !semver.Valid {
		return "Not valid."
	}
	return fmt.Sprintf("%d.%d.%d", semver.Major, semver.Minor, semver.Patch)
}

func RangeStr(vr VersionRange) string {
	if !vr.Valid {
		return "Not valid."
	}
	return fmt.Sprintf(">=%d.%d.%d <%d.%d.%d", vr.MinMajor, vr.MinMinor, vr.MinPatch, vr.MaxMajor, vr.MaxMinor, vr.MaxPatch)
}
