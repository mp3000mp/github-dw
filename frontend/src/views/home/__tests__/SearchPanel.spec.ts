import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'

import SearchPanel from '@/views/home/SearchPanel.vue'
import { useSearchStore } from '@/stores/search'
import type { Dependency } from '@/stores/search/types'

const childStubs = {
  SelectedDependency: {
    name: 'SelectedDependency',
    props: ['dependency'],
    emits: ['remove', 'update-versions'],
    template: '<div class="selected-dependency-stub" />'
  }
}

function makeDep(overrides: Partial<Dependency> = {}): Dependency {
  return {
    idx: 1,
    language: 'Go',
    name: 'cobra',
    id: 42,
    minVersion: null,
    maxVersion: null,
    ...overrides
  }
}

function buildWrapper(
  props: Partial<{ dependencies: Dependency[]; repoName: string; description: string }> = {}
) {
  return mount(SearchPanel, {
    props: {
      dependencies: [],
      repoName: '',
      description: '',
      ...props
    },
    global: {
      plugins: [createTestingPinia({ createSpy: vi.fn })],
      stubs: childStubs
    }
  })
}

describe('views/home/SearchPanel.vue', () => {
  it('renders one SelectedDependency per dependency and emits search on click', async () => {
    const wrapper = buildWrapper({
      dependencies: [makeDep({ id: 1 }), makeDep({ idx: 2, id: 2, name: 'gin' })]
    })

    expect(wrapper.findAll('.selected-dependency-stub')).toHaveLength(2)

    await wrapper.get('input[type="submit"]').trigger('click')

    expect(wrapper.emitted('search')).toBeTruthy()
    expect(wrapper.emitted('search')!).toHaveLength(1)
  })

  it('stays hidden while no dependency is selected and no search has been issued', () => {
    const wrapper = buildWrapper()
    const store = useSearchStore()
    store.actionRequests.search.callCount = 0

    expect(wrapper.find('.app-block').exists()).toBe(false)
  })

  it('forwards remove and update-versions events from SelectedDependency', async () => {
    const wrapper = buildWrapper({ dependencies: [makeDep({ id: 7 })] })
    const child = wrapper.findComponent({ name: 'SelectedDependency' })

    child.vm.$emit('remove', 7)
    child.vm.$emit('update-versions', { id: 7, minVersion: '1.2.3', maxVersion: '2.0.0' })
    await wrapper.vm.$nextTick()

    expect(wrapper.emitted('remove')![0]).toEqual([7])
    expect(wrapper.emitted('update-versions')![0]).toEqual([
      { id: 7, minVersion: '1.2.3', maxVersion: '2.0.0' }
    ])
  })
})
