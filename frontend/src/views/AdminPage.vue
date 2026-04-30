<script lang="ts" setup>
import { computed, onMounted } from 'vue'
import { useAdminStore } from '@/stores/admin'
import PackageTypeFiles from '@/views/admin/PackageTypeFiles.vue'
import RoutinesTimeline from '@/views/admin/RoutinesTimeline.vue'
import LastErrors from '@/views/admin/LastErrors.vue'

const adminStore = useAdminStore()
const adminRequests = computed(() => adminStore.actionRequests)
const stats = computed(() => adminStore.stats)

function refresh() {
  adminStore.getStats()
  adminStore.getTimeline()
  adminStore.getAll()
  adminStore.getErrors()
}
onMounted(() => {
  refresh()
})
</script>

<template>
  <div class="container">
    <div v-if="!stats || adminRequests.getStats.isLoading" class="text-center">...</div>
    <div v-else>
      <package-type-files @refresh="refresh" />
      <routines-timeline />
      <last-errors />
    </div>
  </div>
</template>
