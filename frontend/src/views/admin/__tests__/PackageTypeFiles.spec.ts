import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import dayjs from 'dayjs'

vi.mock('vue3-popper', () => ({
  default: { template: '<span><slot /></span>' }
}))

const useCamembertChartMock = vi.fn()
vi.mock('@/composables/admin/useCamembertChart', () => ({
  useCamembertChart: (...args: unknown[]) => useCamembertChartMock(...args)
}))

import PackageTypeFiles from '@/views/admin/PackageTypeFiles.vue'
import { useAdminStore } from '@/stores/admin'
import type { PackageTypeFiles as PackageTypeFile, Stats } from '@/stores/admin/types'

const globalStubs = { 'font-awesome': true }

function makePtf(overrides: Partial<PackageTypeFile> = {}): PackageTypeFile {
  return {
    id: 1,
    file: 'package.json',
    language: 'JavaScript',
    name: 'npm',
    githubCurrentSize: 100,
    githubCurrentPage: 2,
    updatedAt: '2024-06-15T12:00:00Z',
    priority: false,
    count: 0,
    ...overrides
  }
}

function makeStats(overrides: Partial<Stats> = {}): Stats {
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
    },
    ...overrides
  }
}

function buildWrapper() {
  return mount(PackageTypeFiles, {
    global: {
      plugins: [createTestingPinia({ createSpy: vi.fn })],
      stubs: globalStubs
    }
  })
}

describe('views/admin/PackageTypeFiles.vue', () => {
  beforeEach(() => {
    useCamembertChartMock.mockClear()
  })

  it('renders one row per package type with the count from stats', () => {
    const wrapper = buildWrapper()
    const store = useAdminStore()
    store.packageTypeFiles = [
      makePtf({ id: 1, name: 'npm', language: 'JavaScript', file: 'package.json' }),
      makePtf({ id: 2, name: 'cargo', language: 'Rust', file: 'Cargo.toml' })
    ]
    store.stats = makeStats({
      packageTypeFiles: [
        { id: 1, count: 42 },
        { id: 2, count: 7 }
      ]
    })

    return wrapper.vm.$nextTick().then(() => {
      const rows = wrapper.findAll('tbody tr')
      expect(rows).toHaveLength(2)
      expect(rows[0].text()).toContain('npm')
      expect(rows[0].text()).toContain('JavaScript')
      expect(rows[0].text()).toContain('42')
      expect(rows[1].text()).toContain('cargo')
      expect(rows[1].text()).toContain('7')
    })
  })

  it('formats updatedAt with dayjs', async () => {
    const wrapper = buildWrapper()
    const store = useAdminStore()
    store.packageTypeFiles = [makePtf({ updatedAt: '2024-06-15T12:34:56Z' })]
    store.stats = makeStats()
    await wrapper.vm.$nextTick()

    const expected = dayjs('2024-06-15T12:34:56Z').format('YYYY-MM-DD HH:mm')
    expect(wrapper.find('tbody tr').text()).toContain(expected)
  })

  it('emits refresh when the refresh icon is clicked', async () => {
    const wrapper = buildWrapper()
    await wrapper.find('h2 .cp').trigger('click')
    expect(wrapper.emitted('refresh')).toHaveLength(1)
  })

  it('calls store.setPriority when the ellipsis is clicked', async () => {
    const wrapper = buildWrapper()
    const store = useAdminStore()
    store.packageTypeFiles = [makePtf({ id: 42, priority: false })]
    store.stats = makeStats()
    await wrapper.vm.$nextTick()

    await wrapper.find('tbody tr td:last-child .cp').trigger('click')
    expect(store.setPriority).toHaveBeenCalledWith(42)
  })

  it('hands the canvas ref and tableData to useCamembertChart', () => {
    buildWrapper()
    expect(useCamembertChartMock).toHaveBeenCalledTimes(1)
    const [canvasRef, dataRef] = useCamembertChartMock.mock.calls[0]
    expect(canvasRef).toHaveProperty('value')
    expect(dataRef).toHaveProperty('value')
    expect(Array.isArray(dataRef.value)).toBe(true)
  })
})
