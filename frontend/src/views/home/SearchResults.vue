<script lang="ts" setup>
import { computed } from 'vue'
import { useSearchStore } from '@/stores/search'
import PaginationComponent from '@/components/PaginationComponent.vue'
import RepositoryItem from '@/views/home/RepositoryItem.vue'

const props = defineProps<{
  currentPage: number
  perPage: number
}>()
defineEmits(['select-page'])

const searchStore = useSearchStore()

const searchRequest = computed(() => searchStore.actionRequests.search)
const searchResults = computed(() => searchStore.repositories)
const totalResults = computed(() => searchStore.totalRepositories)
const maxPage = computed(() => Math.ceil(totalResults.value / props.perPage))
</script>

<template>
  <div class="row">
    <div class="app-block p-3 mt-3 text-center" v-if="searchRequest.isLoading">...</div>
    <div
      class="app-block p-3 mt-3 text-center"
      v-else-if="searchRequest.callCount > 0 && totalResults === 0"
    >
      No result found, please try with less restrictive criteria.
    </div>
    <div class="app-block p-3 mt-3" v-else-if="searchRequest.callCount > 0">
      <div class="row justify-content-between pt-0 p-3">
        <h2 class="col-auto">Results ({{ totalResults }})</h2>
        <div class="col-auto">
          <pagination-component
            :max-page="maxPage"
            :current-page="currentPage"
            @select-page="(page) => $emit('select-page', page)"
          />
        </div>
      </div>
      <Repository-item
        v-for="repo in searchResults"
        :key="repo.id"
        class="mb-2"
        :repository="repo"
      />
      <div class="row justify-content-between pt-0 p-3" v-if="searchResults.length > 3">
        <h2 class="col-auto">Results ({{ totalResults }})</h2>
        <div class="col-auto">
          <pagination-component
            :max-page="maxPage"
            :current-page="currentPage"
            @select-page="(page) => $emit('select-page', page)"
          />
        </div>
      </div>
    </div>
  </div>
</template>
