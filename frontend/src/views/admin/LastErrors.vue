<script lang="ts" setup>
import { computed } from 'vue'
import dayjs from 'dayjs'
import { useAdminStore } from '@/stores/admin'

const adminStore = useAdminStore()
const errors = computed(() => adminStore.errors)
</script>

<template>
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
        {{ dayjs(error.date).format('YYYY-MM-DD HH:mm:ss') }} - {{ error.error }} ({{ error.url }} -
        {{ error.path }})
      </li>
    </ul>
  </div>
</template>

<style lang="scss">
ul {
  list-style: none;
  margin: 0;
}
</style>
