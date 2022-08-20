<script lang="ts" setup>
import {computed, onMounted, ref, Ref, watch} from 'vue'
import {useAdminStore} from '@/stores/admin'
import tooltipPopper from 'vue3-popper'
import dayjs from 'dayjs'
import Chart, {ChartItem} from 'chart.js/auto'
import {LanguageColorEnum} from '@/stores/search/types'
import {Routine1Timeline, RoutineTimeline} from '@/stores/admin/types'

const adminStore = useAdminStore()

const camembertRef = ref(null) as Ref<ChartItem|null>
const timelineRef = ref(null) as Ref<ChartItem|null>

const adminRequests = computed(() => adminStore.actionRequests)
const packageTypeFiles = computed(() => adminStore.packageTypeFiles)
const stats = computed(() => adminStore.stats)
const errors = computed(() => adminStore.errors)
const timeline = computed(() => adminStore.timeline)
const tableData = computed(() => {
  return packageTypeFiles.value.map(ptf => {
    ptf.count = 0
    if (stats.value !== null) {
      const stat = stats.value.packageTypeFiles.find(s => s.id === ptf.id)
      ptf.count = stat ? stat.count ?? 0 : 0
    }
    ptf.updatedAt = dayjs(ptf.updatedAt).format('YYYY-MM-DD HH:mm')
    return ptf
  })
})

function setPriority(id: number) {
  adminStore.setPriority(id)
}

onMounted(() => {
  adminStore.getStats()
  adminStore.getTimeline()
  adminStore.getAll()
  adminStore.getErrors()
})

watch(tableData, () => {
  if (tableData.value.length === 0 || camembertRef.value === null) {
    return
  }
  const data = {
    labels: tableData.value.map(p => p.file+' ('+p.language+')'),
    datasets: [{
      label: 'total',
      data: tableData.value.map(p => p.count),
      backgroundColor: tableData.value.map(p => {
        return LanguageColorEnum[p.language] ?? '#000000'
      })
    }]
  }
  new Chart(camembertRef.value, {
    type: 'pie',
    data,
    options: {
      responsive: true,
    }
  })
})
function getData(labels: string[], data: RoutineTimeline[]|Routine1Timeline[], key: 'done'|'errors'): number[] {
  return labels.map(label => {
    const count = data.find(r => r.label === label)
    if (count) {
      return Number(count[key])
    }
    return 0
  })
}
watch(timeline, () => {
  if (timeline.value === null || timelineRef.value === null) {
    return
  }
  const labels = timeline.value.labels
  const data = {
    labels,
    datasets: [{
      label: 'Routine1 done',
      data: getData(labels, timeline.value.routine1, 'done'),
      backgroundColor: '#007000',
      yAxisID: 'y',
      type: 'bar',
      order: 2
    }, {
      label: 'Routine2 done',
      data: getData(labels, timeline.value.routine2, 'done'),
      backgroundColor: '#008080',
      yAxisID: 'y',
      type: 'bar',
      order: 2
    }, {
      label: 'Routine3 done',
      data: getData(labels, timeline.value.routine3, 'done'),
      backgroundColor: '#700070',
      yAxisID: 'y',
      type: 'bar',
      order: 2
    }, {
      label: 'Routine2 errors',
      data: getData(labels, timeline.value.routine2, 'errors'),
      borderColor: '#0e4444',
      backgroundColor: '#0e4444',
      yAxisID: 'yLine',
      type: 'line',
      order: 1
    }, {
      label: 'Routine3 errors',
      data: getData(labels, timeline.value.routine3, 'errors'),
      borderColor: '#570d57',
      backgroundColor: '#570d57',
      yAxisID: 'yLine',
      type: 'line',
      order: 1
    }]
  }
  new Chart(timelineRef.value, {
    type: 'bar',
    data,
    options: {
      responsive: true,
      scales: {
        x: {stacked: true},
        y: {stacked: true, position: 'left'},
        yLine: {position: 'right'}
      }
    }
  })
})
</script>

<template>
  <div class="container">
    <div v-if="!stats || adminRequests.getAll.isLoading" class="text-center">...</div>
    <div v-else>
      <div class="row app-block mb-2 p-3">
        <h2 class="mb-2">Package file types</h2>
        <div class="col-md-8">
          <table>
            <thead>
            <tr>
              <th>Name</th>
              <th>Language</th>
              <th>File</th>
              <th>Github size</th>
              <th>Github page</th>
              <th>Packages count</th>
              <th>Last update</th>
              <th>Priority</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="packageTypeFile in tableData" :key="packageTypeFile.id">
              <td>{{ packageTypeFile.name }}</td>
              <td>{{ packageTypeFile.language }}</td>
              <td>{{ packageTypeFile.file }}</td>
              <td>{{ packageTypeFile.githubCurrentSize }}</td>
              <td>{{ packageTypeFile.githubCurrentPage }}</td>
              <td>{{ packageTypeFile.count }}</td>
              <td>{{ packageTypeFile.updatedAt }}</td>
              <td>
                <font-awesome v-if="packageTypeFile.priority" icon="circle-exclamation" />
                <tooltip-popper v-else content="Set priority" :hover="true" :arrow="true">
                  <font-awesome class="cp" @click="setPriority(packageTypeFile.id)" icon="ellipsis" />
                </tooltip-popper>
              </td>
            </tr>
            </tbody>
          </table>
          <span class="danger">{{ adminRequests.setPriority.message }}</span>
        </div>
        <div class="col-md-4">
          <canvas class="mx-auto" ref="camembertRef" id="camembert"></canvas>
        </div>
      </div>

      <div class="row app-block p-3 mb-2">
        <h2 class="mb-2">Waiting lists</h2>
        <div class="col-md-4">
          <h3>Routine1</h3>
          <span class="mx-1">Done: {{ stats.routines.routine1Count }}</span>
        </div>
        <div class="col-md-4">
          <h3>Routine2</h3>
          <span class="mx-1">Done: {{ stats.routines.routine2DoneCount }} ({{ Math.round((stats.routines.routine2DoneCount/stats.routines.routine2Count)*100*100)/100 }}%)</span>
          <span class="mx-1">Todo: {{ stats.routines.routine2Count-stats.routines.routine2DoneCount }}</span>
          <span class="mx-1">Errors: {{ stats.routines.routine2ErrorCount }} ({{ Math.round((stats.routines.routine2ErrorCount/stats.routines.routine2DoneCount)*100*100)/100 }}%)</span>
        </div>
        <div class="col-md-4">
          <h3>Routine3</h3>
          <span class="mx-1">Done: {{ stats.routines.routine3DoneCount }} ({{ Math.round((stats.routines.routine3DoneCount/stats.routines.routine3Count)*100*100)/100 }}%)</span>
          <span class="mx-1">Todo: {{ stats.routines.routine3Count-stats.routines.routine3DoneCount }}</span>
          <span class="mx-1">Errors: {{ stats.routines.routine3ErrorCount }} ({{ Math.round((stats.routines.routine3ErrorCount/stats.routines.routine3DoneCount)*100*100)/100 }}%)</span>
        </div>
        <div class="col-12 mt-3">
          <canvas class="mx-auto" ref="timelineRef" id="timeline"></canvas>
        </div>
      </div>

      <div class="row app-block p-3">
        <h2 class="mb-2">Last errors</h2>
        <h3>Routine2 ({{ errors.routine2.length }})</h3>
        <ul class="col">
          <li class="mx-1" v-for="error in errors.routine2" :key="error.date">
            {{ dayjs(error.date).format('YYYY-MM-DD HH:mm:ss') }} - {{ error.error }} ({{ error.url }})
          </li>
        </ul>
        <h3 class="mt-2">Routine3 ({{ errors.routine3.length }})</h3>
        <ul class="col">
          <li class="mx-1" v-for="error in errors.routine3" :key="error.date">
            {{ dayjs(error.date).format('YYYY-MM-DD HH:mm:ss') }} - {{ error.error }} ({{ error.url }} - {{ error.path }})
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<style lang="scss">
#camembert {
  max-width: 400px;
}
#timeline {
  width: 90%;
}
ul {
  list-style: none;
  margin: 0;
}
</style>
