package main

import (
	"testing"
	"github.com/stretchr/testify/assert"
)

func TestMain(t *testing.T) {
	assert := assert.New(t)

	assert.Equal("1", "1")
}
