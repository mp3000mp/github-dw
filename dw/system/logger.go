package system

import (
	"io"
	"log"
)

var (
	Info  = log.New(io.Discard, "INFO  | ", log.LstdFlags)
	Warn  = log.New(io.Discard, "WARN  | ", log.LstdFlags)
	Error = log.New(io.Discard, "ERROR | ", log.LstdFlags)
)

func InitLogger(w io.Writer) {
	Info = log.New(w, "INFO  | ", log.LstdFlags)
	Warn = log.New(w, "WARN  | ", log.LstdFlags)
	Error = log.New(w, "ERROR | ", log.LstdFlags)
}
