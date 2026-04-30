import { mount, RouterLinkStub } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'

import HomePage from '@/views/HomePage.vue'
import { useSearchStore } from '@/stores/search'

const childStubs = {
  DependencyForm: {
    name: 'DependencyForm',
    props: ['dependencies'],
    emits: ['add'],
    template: '<div class="dependency-form-stub" />'
  },
  SearchPanel: {
    name: 'SearchPanel',
    props: ['dependencies', 'repoName', 'description'],
    emits: ['remove', 'update-versions', 'search', 'update:repoName', 'update:description'],
    template: '<div class="search-panel-stub" />'
  },
  SearchResults: {
    name: 'SearchResults',
    props: ['currentPage', 'perPage'],
    emits: ['select-page'],
    template: '<div class="search-results-stub" />'
  },
  RouterLink: RouterLinkStub
}

function buildWrapper() {
  return mount(HomePage, {
    global: {
      plugins: [createTestingPinia({ createSpy: vi.fn })],
      stubs: childStubs
    }
  })
}

describe('views/HomePage.vue', () => {
  it('appends a dependency with an incrementing idx when DependencyForm emits add', async () => {
    const wrapper = buildWrapper()
    const form = wrapper.findComponent({ name: 'DependencyForm' })

    form.vm.$emit('add', {
      language: 'Go',
      name: 'cobra',
      id: 42,
      minVersion: '1.0.0',
      maxVersion: null
    })
    form.vm.$emit('add', {
      language: 'Go',
      name: 'gin',
      id: 43,
      minVersion: null,
      maxVersion: null
    })
    await wrapper.vm.$nextTick()

    const panel = wrapper.findComponent({ name: 'SearchPanel' })
    expect(panel.props('dependencies')).toEqual([
      { idx: 1, language: 'Go', name: 'cobra', id: 42, minVersion: '1.0.0', maxVersion: null },
      { idx: 2, language: 'Go', name: 'gin', id: 43, minVersion: null, maxVersion: null }
    ])
  })

  it('builds the query and calls searchStore.search when SearchPanel emits search', async () => {
    const wrapper = buildWrapper()
    const store = useSearchStore()
    const form = wrapper.findComponent({ name: 'DependencyForm' })

    form.vm.$emit('add', {
      language: 'Go',
      name: 'cobra',
      id: 42,
      minVersion: '1.0.0',
      maxVersion: '2.0.0'
    })
    await wrapper.vm.$nextTick()

    const panel = wrapper.findComponent({ name: 'SearchPanel' })
    panel.vm.$emit('search')
    await wrapper.vm.$nextTick()

    expect(store.search).toHaveBeenCalledTimes(1)
    expect(store.search).toHaveBeenCalledWith({
      page: 1,
      perPage: 8,
      search: {
        name: null,
        description: null,
        packages: [{ id: 42, minVersion: '1.0.0', maxVersion: '2.0.0' }]
      }
    })
  })
})
