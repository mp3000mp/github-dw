package parser

import (
	"fmt"
	semver "github.com/blang/semver/v4"
)

func Ttt(v string) string {
	r, _ := semver.Make(v)
	fmt.Printf("%v\n", r)
	return fmt.Sprintf("%d", r.Major)
}
