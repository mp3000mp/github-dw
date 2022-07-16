package parser

import (
	"testing"
	"github.com/stretchr/testify/assert"
)

func TestTtt(t *testing.T) {
	assert := assert.New(t)

	r := Ttt("1.0.*")
	assert.Equal("1", r)
}
