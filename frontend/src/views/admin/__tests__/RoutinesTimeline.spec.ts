import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'

const useTimelineChartMock = vi.fn()
vi.mock('@/composables/admin/useTimelineChart', () => ({
  useTimelineChart: (...args: unknown[]) => useTimelineChartMock(...args)
}))

import RoutinesTimeline from '@/views/admin/RoutinesTimeline.vue'
import { useAdminStore } from '@/stores/admin'
import type { Stats } from '@/stores/admin/types'

function makeStats(overrides: Partial<Stats['routines']> = {}): Stats {
  return {
    packageTypeFiles: [],
    routines: {
      routine1Count: 10,
      routine2Count: 200,
      routine2DoneCount: 150,
      routine2ErrorCount: 15,
      routine3Count: 100,
      routine3DoneCount: 80,
      routine3ErrorCount: 4,
      ...overrides
    }
  }
}

function buildWrapper() {
  return mount(RoutinesTimeline, {
    global: { plugins: [createTestingPinia({ createSpy: vi.fn })] }
  })
}

describe('views/admin/RoutinesTimeline.vue', () => {
  beforeEach(() => useTimelineChartMock.mockClear())

  it('renders nothing when stats are not loaded', () => {
    const wrapper = buildWrapper()
    expect(wrapper.find('.app-block').exists()).toBe(false)
  })

  it('renders counts and percentages from stats.routines', async () => {
    const wrapper = buildWrapper()
    const store = useAdminStore()
    store.stats = makeStats()
    await wrapper.vm.$nextTick()

    const text = wrapper.text()
    expect(text).toContain('Routine1')
    expect(text).toContain('Done: 10')

    expect(text).toContain('Done: 150 (75%)')
    expect(text).toContain('Todo: 50')
    expect(text).toContain('Errors: 15 (10%)')

    expect(text).toContain('Done: 80 (80%)')
    expect(text).toContain('Todo: 20')
    expect(text).toContain('Errors: 4 (5%)')
  })

  it('shows "NA" when totals are zero', async () => {
    const wrapper = buildWrapper()
    const store = useAdminStore()
    store.stats = makeStats({
      routine2Count: 0,
      routine2DoneCount: 0,
      routine2ErrorCount: 0,
      routine3Count: 0,
      routine3DoneCount: 0,
      routine3ErrorCount: 0
    })
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('Done: 0 (NA)')
    expect(wrapper.text()).toContain('Errors: 0 (NA)')
  })

  it('hands the canvas ref and timeline ref to useTimelineChart', () => {
    buildWrapper()
    expect(useTimelineChartMock).toHaveBeenCalledTimes(1)
    const [canvasRef, timelineRef] = useTimelineChartMock.mock.calls[0]
    expect(canvasRef).toHaveProperty('value')
    expect(timelineRef).toHaveProperty('value')
  })
})
