package system

import (
	"runtime"
)

func GetUsedMem() uint64 {
	var m runtime.MemStats
	runtime.ReadMemStats(&m)

	return bToMb(m.Alloc)
}

func bToMb(b uint64) uint64 {
    return b / 1024 / 1024
}
