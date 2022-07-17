package parser

import (
	"math"
	"strings"
	"strconv"
	"regexp"
)

// only support major, minor, patch
type VersionRange struct {
	MinMajor uint16
	MinMinor uint16
	MinPatch uint16
	MaxMajor uint16
	MaxMinor uint16
	MaxPatch uint16
	Valid bool
}

type Semver struct {
	Major uint16
	Minor uint16
	Patch uint16
	Valid bool
}

// caution: does not handle || operator. Split the version before using this func
// caution: min version is ">=" (greater or equal). max version is "<" (strict lower than)
func GetVersionRange(v string) VersionRange {
	vr := VersionRange{Valid: false}
	v = strings.ReplaceAll(v, " ", "")

	if v == "*" {
		vr.MinMajor = 0
		vr.MinMinor = 0
		vr.MinPatch = 0
		vr.MaxMajor = math.MaxUint16
		vr.MaxMinor = math.MaxUint16
		vr.MaxPatch = math.MaxUint16
		vr.Valid = true
		return vr
	}

	// handle hyphenated range
	hyphenatedRegex := regexp.MustCompile(`(^|[^\d])(\d{1,5}(?:\.\d{1,5})?(?:\.\d{1,5})?)\s*-\s*v?(\d{1,5}(?:\.\d{1,5})?(?:\.\d{1,5})?)`)
	rs := hyphenatedRegex.FindStringSubmatch(v)

	if len(rs) > 0 {
		tmpVr := VersionRange{}
		semver := Coerce(rs[2], true)
		RangeGTE(semver, &tmpVr)
		vr.MinMajor = tmpVr.MinMajor
		vr.MinMinor = tmpVr.MinMinor
		vr.MinPatch = tmpVr.MinPatch
		if len(strings.Split(rs[3], ".")) == 3 {
			semver = Coerce(rs[3], true)
		} else {
			semver = Coerce(rs[3], false)
		}
		RangeLT(semver, &tmpVr)
		vr.MaxMajor = tmpVr.MaxMajor
		vr.MaxMinor = tmpVr.MaxMinor
		vr.MaxPatch = tmpVr.MaxPatch
		vr.Valid = IsValidRange(vr)
		return vr
	}

	// handle combining ranges (only AND)
	combiningRangesRegex := regexp.MustCompile(`([=<>]+v?\d{1,5}(?:\.\d{1,5})?(?:\.\d{1,5})?)([=<>]+v?\d{1,5}(?:\.\d{1,5})?(?:\.\d{1,5})?)`)
	rs = combiningRangesRegex.FindStringSubmatch(v)

	if len(rs) > 0 {
		var min VersionRange
		var max VersionRange
		if strings.Contains(rs[1], ">") && strings.Contains(rs[2], "<") {
			min = GetVersionRange(rs[1])
			max = GetVersionRange(rs[2])
		} else if strings.Contains(rs[1], "<") && strings.Contains(rs[2], ">") {
			min = GetVersionRange(rs[2])
			max = GetVersionRange(rs[1])
		} else {
			vr.Valid = false
			return vr
		}
		vr.MinMajor = min.MinMajor
		vr.MinMinor = min.MinMinor
		vr.MinPatch = min.MinPatch
		vr.MaxMajor = max.MaxMajor
		vr.MaxMinor = max.MaxMinor
		vr.MaxPatch = max.MaxPatch
		vr.Valid = IsValidRange(vr)
		return vr
	}

	// handle other
	semverRegex := regexp.MustCompile(`([=<>\^~]*)(.+)`)
	rs = semverRegex.FindStringSubmatch(v)


	if rs[1] == "=" || rs[1] == "" {
		min := Coerce(rs[2], true)
		max := Coerce(rs[2], false)
		vr.MinMajor = min.Major
		vr.MinMinor = min.Minor
		vr.MinPatch = min.Patch
		vr.MaxMajor = max.Major
		vr.MaxMinor = max.Minor
		vr.MaxPatch = max.Patch
		vr.Valid = IsValidRange(vr)
		return vr
	}

	semver := Coerce(rs[2], true)
	if !semver.Valid {
		return vr
	}

	if rs[1] == ">" {
		RangeGT(semver, &vr)
	} else if rs[1] == ">=" {
		RangeGTE(semver, &vr)
	} else if rs[1] == "<" {
		RangeLT(semver, &vr)
	} else if rs[1] == "<=" {
		RangeLTE(semver, &vr)
	} else if rs[1] == "~" {
		RangeTilde(semver, &vr)
	} else if rs[1] == "^" {
		RangeCaret(semver, &vr)
	} else {
		return vr
	}

	return vr
}

func IsValidRange(vr VersionRange) bool {
	if vr.MinMajor < vr.MaxMajor {
		return true
	}
	if vr.MinMajor == vr.MaxMajor {
		if vr.MinMinor < vr.MaxMinor {
			return true
		}
		if vr.MinMinor == vr.MaxMinor {
			if vr.MinPatch < vr.MaxPatch {
				return true
			}
		}
	}
	return false
}

// patchMin:
//   if true, 1.* means 1.0.0
//   if false, 1.* means 1.65535.65535
func Coerce(v string, patchMin bool) Semver {
	r := Semver{Valid: false}

	if v == "*" {
		if !patchMin {
			r.Major = math.MaxUint16
			r.Minor = math.MaxUint16
			r.Patch = math.MaxUint16
			r.Valid = true
			return r
		} else {
			v = "0"
		}
	}

	// from https://github.com/npm/node-semver/blob/main/internal/re.js#L127
	semverRegex := regexp.MustCompile(`(^|[^\d])(\d{1,5})(?:\.(\d{1,5}))?(?:\.(\d{1,5}))?(?:$|[^\d])`)
	rs := semverRegex.FindStringSubmatch(v)

	if len(rs) == 0 {
		return r
	}

	if rs[2] != "" {
		r.Valid = true
		major, _ := strconv.Atoi(rs[2])
		r.Major = uint16(major)
	}
	if rs[3] != "" {
		minor, _ := strconv.Atoi(rs[3])
		r.Minor = uint16(minor)
	} else if !patchMin {
		r.Major = r.Major+1
		r.Minor = 0
		return r
	}
	if rs[4] != "" {
		patch, _ := strconv.Atoi(rs[4])
		if !patchMin {
			patch++
		}
		r.Patch = uint16(patch)
	} else if !patchMin {
		r.Minor = r.Minor+1
		r.Patch = 0
	}
	return r
}

func RangeEq(semver Semver, vr *VersionRange) {
	vr.MinMajor = semver.Major
	vr.MinMinor = semver.Minor
	vr.MinPatch = semver.Patch
	vr.MaxMajor = semver.Major
	vr.MaxMinor = semver.Minor
	vr.MaxPatch = semver.Patch+1
	vr.Valid = true
}
func RangeGT(semver Semver, vr *VersionRange) {
	vr.MinMajor = semver.Major
	vr.MinMinor = semver.Minor
	vr.MinPatch = semver.Patch+1
	vr.MaxMajor = math.MaxUint16
	vr.MaxMinor = math.MaxUint16
	vr.MaxPatch = math.MaxUint16
	vr.Valid = true
}
func RangeGTE(semver Semver, vr *VersionRange) {
	vr.MinMajor = semver.Major
	vr.MinMinor = semver.Minor
	vr.MinPatch = semver.Patch
	vr.MaxMajor = math.MaxUint16
	vr.MaxMinor = math.MaxUint16
	vr.MaxPatch = math.MaxUint16
	vr.Valid = true
}
func RangeLT(semver Semver, vr *VersionRange) {
	if semver.Major == 0 && semver.Minor == 0 && semver.Patch == 0 {
		vr.Valid = false
 		return
 	}
	vr.MinMajor = 0
	vr.MinMinor = 0
	vr.MinPatch = 0
	vr.MaxMajor = semver.Major
	vr.MaxMinor = semver.Minor
	vr.MaxPatch = semver.Patch
	vr.Valid = true
}
func RangeLTE(semver Semver, vr *VersionRange) {
	vr.MinMajor = 0
	vr.MinMinor = 0
	vr.MinPatch = 0
	vr.MaxMajor = semver.Major
	vr.MaxMinor = semver.Minor
	vr.MaxPatch = semver.Patch+1
	vr.Valid = true
}
func RangeTilde(semver Semver, vr *VersionRange) {
	vr.MinMajor = semver.Major
	vr.MinMinor = semver.Minor
	vr.MinPatch = semver.Patch
	vr.MaxMajor = semver.Major
	vr.MaxMinor = semver.Minor+1
	vr.MaxPatch = 0
	vr.Valid = true
}
func RangeCaret(semver Semver, vr *VersionRange) {
	vr.MinMajor = semver.Major
	vr.MinMinor = semver.Minor
	vr.MinPatch = semver.Patch
	if semver.Major == 0 && semver.Minor == 0 {
		vr.MaxMajor = semver.Major
		vr.MaxMinor = semver.Minor
		vr.MaxPatch = semver.Patch+1
	} else if semver.Major == 0 {
		vr.MaxMajor = semver.Major
		vr.MaxMinor = semver.Minor+1
		vr.MaxPatch = 0
	} else {
		vr.MaxMajor = semver.Major+1
		vr.MaxMinor = 0
		vr.MaxPatch = 0
	}
	vr.Valid = true
}
