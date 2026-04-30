import { nextTick, ref, type Ref } from 'vue'

const { ChartMock } = vi.hoisted(() => ({ ChartMock: vi.fn() }))
vi.mock('chart.js/auto', () => ({
  default: ChartMock
}))

import { useCamembertChart } from '@/composables/admin/useCamembertChart'
import type { PackageTypeFiles } from '@/stores/admin/types'

function makePtf(overrides: Partial<PackageTypeFiles> = {}): PackageTypeFiles {
  return {
    id: 1,
    file: 'package.json',
    language: 'JavaScript',
    name: 'npm',
    githubCurrentSize: 0,
    githubCurrentPage: 0,
    updatedAt: '2024-01-01',
    priority: false,
    count: 0,
    ...overrides
  }
}

describe('composables/admin/useCamembertChart', () => {
  beforeEach(() => {
    ChartMock.mockReset()
    ChartMock.mockImplementation(function (this: { destroy: () => void }) {
      this.destroy = vi.fn()
    })
  })

  it('builds a pie chart with labels, counts and language colors', async () => {
    const canvasRef: Ref<HTMLCanvasElement | null> = ref(document.createElement('canvas'))
    const data = ref<PackageTypeFiles[]>([])
    useCamembertChart(canvasRef, data)

    data.value = [
      makePtf({ id: 1, file: 'package.json', language: 'JavaScript', count: 5 }),
      makePtf({ id: 2, file: 'go.mod', language: 'Go', count: 8 })
    ]
    await nextTick()

    expect(ChartMock).toHaveBeenCalledTimes(1)
    const [ctx, config] = ChartMock.mock.calls[0]
    expect(ctx).toBe(canvasRef.value)
    expect(config.type).toBe('pie')
    expect(config.data.labels).toEqual(['package.json (JavaScript)', 'go.mod (Go)'])
    expect(config.data.datasets[0].data).toEqual([5, 8])
    expect(config.data.datasets[0].backgroundColor).toHaveLength(2)
  })

  it('destroys the previous chart before creating a new one on data change', async () => {
    const canvasRef: Ref<HTMLCanvasElement | null> = ref(document.createElement('canvas'))
    const data = ref<PackageTypeFiles[]>([])
    useCamembertChart(canvasRef, data)

    data.value = [makePtf({ id: 1, count: 1 })]
    await nextTick()
    const firstInstance = ChartMock.mock.results[0].value

    data.value = [makePtf({ id: 1, count: 2 }), makePtf({ id: 2, count: 3 })]
    await nextTick()

    expect(firstInstance.destroy).toHaveBeenCalledTimes(1)
    expect(ChartMock).toHaveBeenCalledTimes(2)
  })
})
