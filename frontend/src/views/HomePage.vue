<script lang="ts" setup>
import { ref } from 'vue'
import { formatVersion } from '@/utils/version'
import { useSearchStore } from '@/stores/search'
import DependencyForm from '@/views/home/DependencyForm.vue'
import SearchPanel from '@/views/home/SearchPanel.vue'
import SearchResults from '@/views/home/SearchResults.vue'
import type { Dependency } from '@/stores/search/types'

const searchStore = useSearchStore()

let packageIdx = 1

const repoName = ref('')
const description = ref('')
const dependencies = ref([] as Dependency[])

const currentPage = ref(1)
const perPage = ref(8)

function addDependency(payload: Omit<Dependency, 'idx'>) {
  dependencies.value.push({ ...payload, idx: packageIdx })
  packageIdx++
}
function removeDependency(id: number) {
  dependencies.value = dependencies.value.filter((p) => p.id !== id)
}

interface updateDependencyVersionsPayload {
  id: number
  minVersion: string
  maxVersion: string
}
function updateDependencyVersions(payload: updateDependencyVersionsPayload) {
  const currentDep = dependencies.value.find((p) => p.id === payload.id)
  if (currentDep) {
    currentDep.minVersion = formatVersion(payload.minVersion)
    currentDep.maxVersion = formatVersion(payload.maxVersion)
  }
}
function search() {
  const query = {
    page: currentPage.value,
    perPage: perPage.value,
    search: {
      name: repoName.value === '' ? null : repoName.value,
      description: description.value === '' ? null : description.value,
      packages: dependencies.value.map((p) => {
        return {
          id: p.id,
          minVersion: p.minVersion,
          maxVersion: p.maxVersion
        }
      })
    }
  }
  searchStore.search(query)
}
function selectPage(page: number) {
  currentPage.value = page
  search()
}
</script>

<template>
  <div class="container">
    <form class="row">
      <div class="p-3 app-block col-md-9 mx-auto mb-2">
        <h2>Welcome</h2>
        <p>
          This tool helps you finding Github projects basing your search on the dependencies
          included in the project.
        </p>
        <ul>
          <li>
            <strong>Step 1</strong>: choose one dependency and optionally a min/max version. Then
            click on "add" <i>(this step can be repeated up to 4 times)</i>.
          </li>
          <li>
            <strong>Step 2</strong>: when you're ready click on "search" and navigate in the results
            using pagination.
          </li>
        </ul>
        <div class="text-end">
          <router-link :to="{ name: 'about' }">Learn more...</router-link>
        </div>
      </div>

      <dependency-form :dependencies="dependencies" @add="addDependency" />

      <search-panel
        :dependencies="dependencies"
        v-model:repo-name="repoName"
        v-model:description="description"
        @remove="removeDependency"
        @update-versions="updateDependencyVersions"
        @search="search"
      />
    </form>

    <search-results :current-page="currentPage" :per-page="perPage" @select-page="selectPage" />
  </div>
</template>

<style lang="scss" scoped>
.card:last-child {
  margin-bottom: 0 !important;
}
</style>
