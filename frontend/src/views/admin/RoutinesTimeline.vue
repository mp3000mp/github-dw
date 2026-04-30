<script lang="ts" setup>
import { computed, ref } from 'vue'
import { useAdminStore } from '@/stores/admin'
import { useTimelineChart } from '@/composables/admin/useTimelineChart'
import { percent } from '@/utils/percent'

const adminStore = useAdminStore()
const stats = computed(() => adminStore.stats)
const timeline = computed(() => adminStore.timeline)

const timelineRef = ref<HTMLCanvasElement | null>(null)
useTimelineChart(timelineRef, timeline)
</script>

<template>
  <div v-if="stats" class="row app-block p-3 mb-2">
    <h2 class="mb-2">Waiting lists</h2>
    <div class="col-md-2">
      <h3>Routine1</h3>
      <span class="mx-1">Done: {{ stats.routines.routine1Count }}</span>
    </div>
    <div class="col-md-5">
      <h3>Routine2</h3>
      <span class="mx-1"
        >Done: {{ stats.routines.routine2DoneCount }} ({{
          percent(stats.routines.routine2DoneCount, stats.routines.routine2Count)
        }})</span
      >
      <span class="mx-1"
        >Todo: {{ stats.routines.routine2Count - stats.routines.routine2DoneCount }}</span
      >
      <span class="mx-1"
        >Errors: {{ stats.routines.routine2ErrorCount }} ({{
          percent(stats.routines.routine2ErrorCount, stats.routines.routine2DoneCount)
        }})</span
      >
    </div>
    <div class="col-md-5">
      <h3>Routine3</h3>
      <span class="mx-1"
        >Done: {{ stats.routines.routine3DoneCount }} ({{
          percent(stats.routines.routine3DoneCount, stats.routines.routine3Count)
        }})</span
      >
      <span class="mx-1"
        >Todo: {{ stats.routines.routine3Count - stats.routines.routine3DoneCount }}</span
      >
      <span class="mx-1"
        >Errors: {{ stats.routines.routine3ErrorCount }} ({{
          percent(stats.routines.routine3ErrorCount, stats.routines.routine3DoneCount)
        }})</span
      >
    </div>
    <div class="col-12 mt-3">
      <canvas class="mx-auto" ref="timelineRef" id="timeline"></canvas>
    </div>
  </div>
</template>

<style lang="scss">
#timeline {
  width: 90%;
}
</style>
