<script lang="ts" setup>
import { computed, ref } from 'vue'
import { useSearchStore } from '@/stores/search'
import SelectedDependency from '@/views/home/SelectedDependency.vue'
import type { Dependency } from '@/stores/search/types'

const props = defineProps<{
  dependencies: Dependency[]
  repoName: string
  description: string
}>()
const emit = defineEmits([
  'update:repoName',
  'update:description',
  'remove',
  'update-versions',
  'search'
])

const searchStore = useSearchStore()

const showAdvancedSearch = ref(false)

const searchRequest = computed(() => searchStore.actionRequests.search)
const repoNameModel = computed({
  get: () => props.repoName,
  set: (val) => emit('update:repoName', val)
})
const descriptionModel = computed({
  get: () => props.description,
  set: (val) => emit('update:description', val)
})
</script>

<template>
  <div
    class="p-3 app-block col-md-9 mx-auto"
    v-if="dependencies.length > 0 || searchRequest.callCount > 0"
  >
    <div class="row">
      <h2>Selected dependencies</h2>
      <div v-if="dependencies.length === 0">None</div>
      <div v-else class="mb-2">
        <selected-dependency
          v-for="dependency in dependencies"
          :key="dependency.idx"
          :dependency="dependency"
          @remove="(id) => $emit('remove', id)"
          @update-versions="(payload) => $emit('update-versions', payload)"
        />
      </div>
    </div>
    <div v-if="showAdvancedSearch" class="row mt-2">
      <h2>Repository</h2>
      <div class="form-group col-md-6 mb-2">
        <input
          class="form-control"
          id="repoName"
          type="text"
          placeholder="Repository title contains"
          v-model="repoNameModel"
        />
      </div>
      <div class="form-group col-md-6 mb-2">
        <input
          class="form-control"
          id="description"
          type="text"
          placeholder="Repository description contains"
          v-model="descriptionModel"
        />
      </div>
    </div>
    <div class="row">
      <div class="form-group">
        <input
          :disabled="dependencies.length === 0"
          class="btn fa-pull-right"
          type="submit"
          value="Search"
          @click.prevent="$emit('search')"
        />
        <div v-if="searchRequest.isError" class="danger mb-0 p-2">
          {{ searchRequest.message }}
        </div>
      </div>
    </div>
  </div>
</template>
