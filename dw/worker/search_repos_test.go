package worker

import (
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestNextPage(t *testing.T) {
	tests := []struct {
		name        string
		currentPage uint32
		currentSize uint32
		maxPage     int
		wantPage    uint32
		wantSize    uint32
	}{
		{
			name: "mid-range: increment page",
			currentPage: 3, currentSize: 5, maxPage: 10,
			wantPage: 4, wantSize: 5,
		},
		{
			name: "page 10: reset and increment size",
			currentPage: 10, currentSize: 5, maxPage: 15,
			wantPage: 1, wantSize: 6,
		},
		{
			name: "reached last page (< 10): reset and increment size",
			currentPage: 7, currentSize: 5, maxPage: 7,
			wantPage: 1, wantSize: 6,
		},
		{
			name: "page 10 equals last page: reset and increment size",
			currentPage: 10, currentSize: 5, maxPage: 10,
			wantPage: 1, wantSize: 6,
		},
		{
			name: "page 1 single-page result: reset and increment size",
			currentPage: 1, currentSize: 0, maxPage: 1,
			wantPage: 1, wantSize: 1,
		},
		{
			name: "page just before cap: increment page",
			currentPage: 9, currentSize: 2, maxPage: 15,
			wantPage: 10, wantSize: 2,
		},
	}

	for _, tt := range tests {
		t.Run(tt.name, func(t *testing.T) {
			gotPage, gotSize := nextPage(tt.currentPage, tt.currentSize, tt.maxPage)
			assert.Equal(t, tt.wantPage, gotPage)
			assert.Equal(t, tt.wantSize, gotSize)
		})
	}
}
