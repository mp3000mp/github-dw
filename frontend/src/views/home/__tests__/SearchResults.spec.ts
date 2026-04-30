import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'

import SearchResults from '@/views/home/SearchResults.vue'
import { useSearchStore } from '@/stores/search'
import type { Repository } from '@/stores/search/types'

const childStubs = {
  PaginationComponent: {
    name: 'PaginationComponent',
    props: ['maxPage', 'currentPage'],
    emits: ['select-page'],
    template: '<div class="pagination-stub" @click="$emit(\'select-page\', 2)" />'
  },
  RepositoryItem: {
    name: 'RepositoryItem',
    props: ['repository'],
    template: '<div class="repository-item-stub" />'
  }
}

function makeRepo(id: number): Repository {
  return {
    id,
    name: `repo${id}`,
    username: 'user',
    mainLanguage: 'Go',
    url: `https://github.com/user/repo${id}`,
    fullName: `user/repo${id}`,
    description: '',
    licenceName: 'MIT',
    forksCount: 0,
    openIssuesCount: 0,
    stargazersCount: 0,
    createdAt: '2024-01-01T00:00:00Z',
    pushedAt: '2024-01-01T00:00:00Z',
    topics: []
  }
}

function buildWrapper() {
  return mount(SearchResults, {
    props: { currentPage: 1, perPage: 8 },
    global: {
      plugins: [createTestingPinia({ createSpy: vi.fn })],
      stubs: childStubs
    }
  })
}

describe('views/home/SearchResults.vue', () => {
  it('shows the empty state once a search has run with zero results', async () => {
    const wrapper = buildWrapper()
    const store = useSearchStore()
    store.actionRequests.search.callCount = 1
    store.totalRepositories = 0
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('No result found')
    expect(wrapper.find('.repository-item-stub').exists()).toBe(false)
  })

  it('renders one RepositoryItem per result and forwards pagination clicks', async () => {
    const wrapper = buildWrapper()
    const store = useSearchStore()
    store.actionRequests.search.callCount = 1
    store.totalRepositories = 2
    store.repositories = [makeRepo(1), makeRepo(2)]
    await wrapper.vm.$nextTick()

    expect(wrapper.findAll('.repository-item-stub')).toHaveLength(2)

    await wrapper.find('.pagination-stub').trigger('click')

    expect(wrapper.emitted('select-page')).toBeTruthy()
    expect(wrapper.emitted('select-page')![0]).toEqual([2])
  })
})
