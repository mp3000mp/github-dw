<script lang="ts" setup>
import { computed, ref } from 'vue'
import dayjs from 'dayjs'
import tooltipPopper from 'vue3-popper'
import { useAdminStore } from '@/stores/admin'
import { useCamembertChart } from '@/composables/admin/useCamembertChart'

defineEmits<{ refresh: [] }>()

const adminStore = useAdminStore()
const adminRequests = computed(() => adminStore.actionRequests)
const stats = computed(() => adminStore.stats)
const packageTypeFiles = computed(() => adminStore.packageTypeFiles)

const tableData = computed(() => {
  return packageTypeFiles.value.map((ptf) => {
    ptf.count = 0
    if (stats.value !== null) {
      const stat = stats.value.packageTypeFiles.find((s) => s.id === ptf.id)
      ptf.count = stat ? (stat.count ?? 0) : 0
    }
    ptf.updatedAt = dayjs(ptf.updatedAt).format('YYYY-MM-DD HH:mm')
    return ptf
  })
})

const camembertRef = ref<HTMLCanvasElement | null>(null)
useCamembertChart(camembertRef, tableData)

function setPriority(id: number) {
  adminStore.setPriority(id)
}
</script>

<template>
  <div class="row app-block mb-2 p-3">
    <h2 class="mb-2">
      Package file types
      <span><font-awesome class="cp" icon="refresh" @click="$emit('refresh')" /></span>
    </h2>
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
</template>

<style lang="scss">
#camembert {
  max-width: 400px;
}
</style>
