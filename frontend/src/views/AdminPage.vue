<script lang="ts" setup>
import {computed, onMounted, ref, Ref, watch} from 'vue'
import {useAdminStore} from '@/stores/admin'
import {useSecurityStore} from '@/stores/security'
import dayjs from 'dayjs'
import Chart, {ChartItem} from 'chart.js/auto'
import {LanguageColorEnum} from '@/stores/search/types'

const adminStore = useAdminStore()
const securityStore = useSecurityStore()

const camembert = ref(null) as Ref<ChartItem|null>

const adminRequests = computed(() => adminStore.actionRequests)
const packageTypeFiles = computed(() => adminStore.packageTypeFiles)
const stats = computed(() => adminStore.stats)
const errors = computed(() => adminStore.errors)
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
  if (securityStore.getIsAuth) {
    adminStore.getAll()
    adminStore.getStats()
    adminStore.getErrors()
  }
})

watch(stats, () => {
  if (tableData.value.length === 0 || camembert.value === null) {
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
  new Chart(camembert.value, {
    type: 'pie',
    data
  })
})
</script>

<template>
  <div class="container">
    <div v-if="!stats || adminRequests.getAll.isLoading">...</div>
    <div v-else>
      <div class="row app-block mb-2 p-3">
        <h2>Package file types</h2>
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
                <font-awesome v-else title="set priority" class="cp" @click="setPriority(packageTypeFile.id)" icon="ellipsis" />
              </td>
            </tr>
            </tbody>
          </table>
          <span class="danger">{{ adminRequests.setPriority.message }}</span>
        </div>
        <div class="col-md-4">
          <canvas class="mx-auto" ref="camembert"></canvas>
        </div>
      </div>

      <div class="row app-block p-3">
        <h2>Waiting lists</h2>
        <h3>Routine1</h3>
        <div class="col-md-4">
          Done: {{ stats.routines.routine1Count }}
        </div>
        <h3>Routine2</h3>
        <div class="col-md-4">
          Done: {{ stats.routines.routine2DoneCount }}
        </div>
        <div class="col-md-4">
          Todo: {{ stats.routines.routine2Count-stats.routines.routine2DoneCount }}
        </div>
        <div class="col-md-4">
          Errors: {{ stats.routines.routine2ErrorCount }}
        </div>
        <h3>Routine3</h3>
        <div class="col-md-4">
          Done: {{ stats.routines.routine3DoneCount }}
        </div>
        <div class="col-md-4">
          Todo: {{ stats.routines.routine3Count-stats.routines.routine3DoneCount }}
        </div>
        <div class="col-md-4">
          Errors: {{ stats.routines.routine3ErrorCount }}
        </div>
      </div>

      <div class="row app-block p-3">
        <h2>Waiting lists</h2>
        <h3>Routine2 ({{ errors.routine2.length }})</h3>
          <ul>
            <li v-for="error in errors.routine2" :key="error.date">
              {{ dayjs(error.date).format('YYYY-MM-DD HH:mm:ss') }} - {{ error.error }} ({{ error.url }})
            </li>
          </ul>
        <h3>Routine3 ({{ errors.routine3.length }})</h3>
        <ul>
          <li v-for="error in errors.routine3" :key="error.date">
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
</style>
