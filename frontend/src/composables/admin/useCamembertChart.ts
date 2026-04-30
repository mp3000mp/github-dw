import { watch, type Ref } from 'vue'
import Chart from 'chart.js/auto'
import type { TooltipItem } from 'chart.js/auto'
import { LanguageColorEnum } from '@/stores/search/types'
import type { PackageTypeFiles } from '@/stores/admin/types'
import { percent } from '@/utils/percent'

export function useCamembertChart(
  canvasRef: Ref<HTMLCanvasElement | null>,
  data: Ref<PackageTypeFiles[]>
) {
  let chart: Chart<'pie'> | null = null

  watch(data, () => {
    if (data.value.length === 0 || canvasRef.value === null) {
      return
    }
    const chartData = {
      labels: data.value.map((p) => p.file + ' (' + p.language + ')'),
      datasets: [
        {
          label: 'total',
          data: data.value.map((p) => p.count),
          backgroundColor: data.value.map((p) => {
            return LanguageColorEnum[p.language as keyof typeof LanguageColorEnum] ?? '#000000'
          }),
          tooltip: {
            callbacks: {
              label: function (ctx: TooltipItem<'pie'>) {
                const total = ctx.dataset.data.reduce((p: number, c: number) => p + c, 0)
                return (
                  ctx.label + ': ' + ctx.formattedValue + ' (' + percent(ctx.parsed, total) + ')'
                )
              }
            }
          }
        }
      ]
    }
    if (chart) {
      chart.destroy()
    }
    chart = new Chart(canvasRef.value, {
      type: 'pie',
      data: chartData,
      options: {
        responsive: true
      }
    })
  })
}
