<script lang="ts" setup>
import {computed, onMounted, watch} from 'vue'
import {useAdminStore} from '@/stores/admin'
import {useSecurityStore} from '@/stores/security'
import dayjs from 'dayjs'
import Chart from 'chart.js/auto'

const adminStore = useAdminStore()
const securityStore = useSecurityStore()

const languageColors = [
  {language: 'Go', color: '#00a7d0'},
  {language: 'Javascript', color: '#efd81d'},
  {language: 'PHP', color: '#7377ad'},
  {language: 'Python', color: '#3571a3'},
]

const adminRequests = computed(() => adminStore.actionRequests)
const packageTypeFiles = computed(() => adminStore.packageTypeFiles)
const stats = computed(() => adminStore.stats)
const tableData = computed(() => {
  return packageTypeFiles.value.map(ptf => {
    const stat = stats.value.find(s => s.id === ptf.id)
    ptf.count = stat ? stat.count ?? 0 : 0
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
  }
})

watch(stats, () => {
  if (tableData.value.length === 0 ) {
    return
  }
  const ctx = document.getElementById('camembert')
  const data = {
    labels: tableData.value.map(p => p.file+' ('+p.language+')'),
    datasets: [{
      label: 'total',
      data: tableData.value.map(p => p.count),
      backgroundColor: tableData.value.map(p => languageColors.find(lc => lc.language === p.language).color)
    }]
  }
  console.log(data)
  const chart = new Chart(ctx, {
    type: 'pie',
    data
  })
})
</script>

<template>
  <div>
    <div v-if="adminRequests.getAll.isLoading">...</div>
    <div v-else>
      <table>
        <thead>
        <tr>
          <th>Name</th>
          <th>Language</th>
          <th>File</th>
          <th>Github size</th>
          <th>Github page</th>
          <th>Count</th>
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
    <canvas class="mx-auto" id="camembert"></canvas>
  </div>
</template>

<style lang="scss">
#camembert {
  max-width: 400px;
}
</style>
