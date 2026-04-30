import { nextTick, ref, type Ref } from 'vue'

const { ChartMock } = vi.hoisted(() => ({ ChartMock: vi.fn() }))
vi.mock('chart.js/auto', () => ({
  default: ChartMock
}))

import { useTimelineChart } from '@/composables/admin/useTimelineChart'
import type { Timeline } from '@/stores/admin/types'

function makeTimeline(): Timeline {
  return {
    labels: ['2024-01-01', '2024-01-02'],
    routine1: [{ label: '2024-01-01', done: 1 }],
    routine2: [
      { label: '2024-01-01', done: 10, errors: 1 },
      { label: '2024-01-02', done: 20, errors: 2 }
    ],
    routine3: [{ label: '2024-01-02', done: 5, errors: 0 }]
  }
}

describe('composables/admin/useTimelineChart', () => {
  beforeEach(() => {
    ChartMock.mockReset()
    ChartMock.mockImplementation(function (this: { destroy: () => void }) {
      this.destroy = vi.fn()
    })
  })

  it('builds a chart with 5 datasets and zero-fills missing routine entries', async () => {
    const canvasRef: Ref<HTMLCanvasElement | null> = ref(document.createElement('canvas'))
    const timeline = ref<Timeline | null>(null)
    useTimelineChart(canvasRef, timeline)

    timeline.value = makeTimeline()
    await nextTick()

    expect(ChartMock).toHaveBeenCalledTimes(1)
    const config = ChartMock.mock.calls[0][1]
    expect(config.type).toBe('bar')
    expect(config.data.labels).toEqual(['2024-01-01', '2024-01-02'])
    expect(config.data.datasets).toHaveLength(5)
    expect(config.data.datasets[0].data).toEqual([1, 0])
    expect(config.data.datasets[1].data).toEqual([10, 20])
    expect(config.data.datasets[2].data).toEqual([0, 5])
    expect(config.data.datasets[3].data).toEqual([1, 2])
    expect(config.data.datasets[4].data).toEqual([0, 0])
  })

  it('destroys the previous chart before creating a new one on timeline change', async () => {
    const canvasRef: Ref<HTMLCanvasElement | null> = ref(document.createElement('canvas'))
    const timeline = ref<Timeline | null>(null)
    useTimelineChart(canvasRef, timeline)

    timeline.value = makeTimeline()
    await nextTick()
    const firstInstance = ChartMock.mock.results[0].value

    timeline.value = { ...makeTimeline(), labels: ['2024-02-01'] }
    await nextTick()

    expect(firstInstance.destroy).toHaveBeenCalledTimes(1)
    expect(ChartMock).toHaveBeenCalledTimes(2)
  })

  it('computes the tooltip footer with done/error totals and error rates', async () => {
    const canvasRef: Ref<HTMLCanvasElement | null> = ref(document.createElement('canvas'))
    const timeline = ref<Timeline | null>(null)
    useTimelineChart(canvasRef, timeline)

    timeline.value = makeTimeline()
    await nextTick()

    const config = ChartMock.mock.calls[0][1]
    const footer = config.options.plugins.tooltip.callbacks.footer
    const items = [
      { datasetIndex: 0, parsed: { y: 5 } },
      { datasetIndex: 1, parsed: { y: 100 } },
      { datasetIndex: 2, parsed: { y: 50 } },
      { datasetIndex: 3, parsed: { y: 10 } },
      { datasetIndex: 4, parsed: { y: 5 } }
    ]

    const result = footer(items)

    expect(result).toContain('Total done: 155')
    expect(result).toContain('Total errors: 15')
    expect(result).toContain('% error2: 10%')
    expect(result).toContain('% error3: 10%')
  })
})
