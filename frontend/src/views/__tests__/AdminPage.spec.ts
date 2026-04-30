import { mount, flushPromises } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'

import AdminPage from '@/views/AdminPage.vue'
import { useAdminStore } from '@/stores/admin'
import type { Stats } from '@/stores/admin/types'

const childStubs = {
  PackageTypeFiles: {
    name: 'PackageTypeFiles',
    emits: ['refresh'],
    template: '<div class="package-type-files-stub" @click="$emit(\'refresh\')"></div>'
  },
  RoutinesTimeline: { template: '<div class="routines-timeline-stub" />' },
  LastErrors: { template: '<div class="last-errors-stub" />' }
}

function makeStats(): Stats {
  return {
    packageTypeFiles: [],
    routines: {
      routine1Count: 0,
      routine2Count: 0,
      routine2DoneCount: 0,
      routine2ErrorCount: 0,
      routine3Count: 0,
      routine3DoneCount: 0,
      routine3ErrorCount: 0
    }
  }
}

function buildWrapper() {
  return mount(AdminPage, {
    global: {
      plugins: [createTestingPinia({ createSpy: vi.fn })],
      stubs: childStubs
    }
  })
}

describe('views/AdminPage.vue', () => {
  beforeEach(() => {
    const pinia = createTestingPinia({ createSpy: vi.fn })
    const store = useAdminStore(pinia)
    store.stats = null
    store.actionRequests.getStats.isLoading = false
  })

  it('shows the loading placeholder while stats are null', () => {
    const wrapper = buildWrapper()
    expect(wrapper.text()).toContain('...')
    expect(wrapper.find('.package-type-files-stub').exists()).toBe(false)
  })

  it('shows the loading placeholder while a getStats request is in flight', async () => {
    const wrapper = buildWrapper()
    const store = useAdminStore()
    store.stats = makeStats()
    store.actionRequests.getStats.isLoading = true
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('...')
    expect(wrapper.find('.package-type-files-stub').exists()).toBe(false)
  })

  it('renders the three sections once stats are loaded', async () => {
    const wrapper = buildWrapper()
    const store = useAdminStore()
    store.stats = makeStats()
    await wrapper.vm.$nextTick()

    expect(wrapper.find('.package-type-files-stub').exists()).toBe(true)
    expect(wrapper.find('.routines-timeline-stub').exists()).toBe(true)
    expect(wrapper.find('.last-errors-stub').exists()).toBe(true)
  })

  it('triggers all four store fetches on mount', async () => {
    buildWrapper()
    const store = useAdminStore()
    await flushPromises()

    expect(store.getStats).toHaveBeenCalledTimes(1)
    expect(store.getTimeline).toHaveBeenCalledTimes(1)
    expect(store.getAll).toHaveBeenCalledTimes(1)
    expect(store.getErrors).toHaveBeenCalledTimes(1)
  })

  it('refreshes all four endpoints when PackageTypeFiles emits refresh', async () => {
    const wrapper = buildWrapper()
    const store = useAdminStore()
    store.stats = makeStats()
    await wrapper.vm.$nextTick()
    ;(store.getStats as ReturnType<typeof vi.fn>).mockClear()
    ;(store.getTimeline as ReturnType<typeof vi.fn>).mockClear()
    ;(store.getAll as ReturnType<typeof vi.fn>).mockClear()
    ;(store.getErrors as ReturnType<typeof vi.fn>).mockClear()

    await wrapper.find('.package-type-files-stub').trigger('click')

    expect(store.getStats).toHaveBeenCalledTimes(1)
    expect(store.getTimeline).toHaveBeenCalledTimes(1)
    expect(store.getAll).toHaveBeenCalledTimes(1)
    expect(store.getErrors).toHaveBeenCalledTimes(1)
  })
})
