import { watch, type Ref } from 'vue'
import Chart from 'chart.js/auto'
import type { ChartDataset } from 'chart.js/auto'
import type { Routine1Timeline, Timeline } from '@/stores/admin/types'
import { percent } from '@/utils/percent'

function getData<T extends Routine1Timeline>(
  labels: string[],
  data: T[],
  key: keyof T & ('done' | 'errors')
): number[] {
  return labels.map((label) => {
    const count = data.find((r) => r.label === label)
    if (count) {
      return Number(count[key])
    }
    return 0
  })
}

export function useTimelineChart(
  canvasRef: Ref<HTMLCanvasElement | null>,
  timeline: Ref<Timeline | null>
) {
  let chart: Chart<'bar' | 'line'> | null = null

  watch(timeline, () => {
    if (timeline.value === null || canvasRef.value === null) {
      return
    }
    const labels = timeline.value.labels
    const datasets: ChartDataset<'bar' | 'line', number[]>[] = [
      {
        label: 'Routine1 done',
        data: getData(labels, timeline.value.routine1, 'done'),
        backgroundColor: '#007000',
        yAxisID: 'y',
        type: 'bar',
        order: 2
      },
      {
        label: 'Routine2 done',
        data: getData(labels, timeline.value.routine2, 'done'),
        backgroundColor: '#008080',
        yAxisID: 'y',
        type: 'bar',
        order: 2
      },
      {
        label: 'Routine3 done',
        data: getData(labels, timeline.value.routine3, 'done'),
        backgroundColor: '#700070',
        yAxisID: 'y',
        type: 'bar',
        order: 2
      },
      {
        label: 'Routine2 errors',
        data: getData(labels, timeline.value.routine2, 'errors'),
        borderColor: '#0e4444',
        backgroundColor: '#0e4444',
        yAxisID: 'yLine',
        type: 'line',
        order: 1
      },
      {
        label: 'Routine3 errors',
        data: getData(labels, timeline.value.routine3, 'errors'),
        borderColor: '#570d57',
        backgroundColor: '#570d57',
        yAxisID: 'yLine',
        type: 'line',
        order: 1
      }
    ]
    const data = { labels, datasets }
    if (chart) {
      chart.destroy()
    }
    chart = new Chart<'bar' | 'line', number[]>(canvasRef.value, {
      type: 'bar',
      data,
      options: {
        interaction: {
          intersect: false,
          mode: 'index'
        },
        plugins: {
          tooltip: {
            callbacks: {
              footer: function (items) {
                let sumError = 0
                let sumDone = 0
                const data = []
                for (const item of items) {
                  data[item.datasetIndex] = item.parsed.y
                  if (item.datasetIndex < 3) {
                    sumDone += item.parsed.y
                  } else {
                    sumError += item.parsed.y
                  }
                }
                return (
                  'Total done: ' +
                  sumDone +
                  '\n' +
                  'Total errors: ' +
                  sumError +
                  '\n' +
                  '% error2: ' +
                  percent(data[3], data[1]) +
                  '\n' +
                  '% error3: ' +
                  percent(data[4], data[2])
                )
              }
            }
          }
        },
        responsive: true,
        scales: {
          x: { stacked: true },
          y: { stacked: true, position: 'left' },
          yLine: { position: 'right' }
        }
      }
    })
  })
}
