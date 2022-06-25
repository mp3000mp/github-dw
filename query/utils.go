package query

import (
	"fmt"
)

func CheckResponse(err error) bool {
	if err != nil {
 		fmt.Printf("Unknown response error: %s", err.Error())
 		return false
	}

	return true
}
