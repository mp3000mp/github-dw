<script lang="ts" setup>
import dayjs from 'dayjs'
import {computed, ref} from 'vue'
import {useSearchStore} from '@/stores/search'
import AutocompleteSelect from '@/components/AutocompleteSelect.vue'
import PaginationComponent from '@/components/PaginationComponent.vue'
import {LanguageColorEnum} from '@/stores/search/types'
const searchStore = useSearchStore()

let packageIdx = 1
interface Package {
  idx: number;
  language: string;
  name: string|null;
  id: number;
  minVersion: string|null;
  maxVersion: string|null;
}

const languages = ref(['Go', 'Javascript', 'PHP', 'Python'])

const repoName = ref('')
const description = ref('')
const dependencies = ref([] as Package[])

const language = ref('')
const dependencyName = ref('')
const selectedDependency = ref(0)
const minVersion = ref('')
const maxVersion = ref('')

const currentPage = ref(1)
const perPage = ref(8)

const shownPackages = computed(() => dependencies.value)
const autocompleteResults = computed(() => searchStore.packageOptions)
const searchResults = computed(() => searchStore.repositories)
const totalResults = computed(() => searchStore.totalRepositories)
const autocompleteOptions = computed(() => autocompleteResults.value.map(ar => ({value: ar.id, label: ar.name})))
const isAlreadyInSearch = computed(() => dependencies.value.findIndex(d => d.id === selectedDependency.value) >= 0)
const maxPage = computed(() => Math.ceil(totalResults.value/perPage.value))

function addDependency() {
  dependencies.value.push({
    idx: packageIdx,
    language: language.value,
    name: dependencyName.value,
    id: selectedDependency.value,
    minVersion: minVersion.value === '' ? null : minVersion.value,
    maxVersion: maxVersion.value === '' ? null : maxVersion.value,
  })
  packageIdx++
  language.value = ''
  dependencyName.value = ''
  selectedDependency.value = 0
  searchStore.resetPackageOptions()
  minVersion.value = ''
  maxVersion.value = ''
}
function removeDependency(id: number) {
  dependencies.value = dependencies.value.filter(p => p.id !== id)
}
function search() {
  const query = {
    page: currentPage.value,
    perPage: perPage.value,
    search: {
      name: repoName.value === '' ? null : repoName.value,
      description: description.value === '' ? null : description.value,
      packages: dependencies.value.map(p => {
        return {
          id: p.id,
          minVersion: p.minVersion,
          maxVersion: p.maxVersion,
        }
      })
    }
  }
  searchStore.search(query)
}
function selectDependency(id: number) {
  selectedDependency.value = id
}
function resetDependencies() {
  searchStore.resetPackageOptions()
}
function searchDependencies(search: string) {
  searchStore.packageAutocomplete({
    language: language.value,
    text: search,
  })
}
function selectPage(page: number) {
  currentPage.value = page
  search()
}

function getDependencyLabel(pkg: Package): string {
  let label = [pkg.name]
  if (pkg.minVersion) {
    label.push('>='+pkg.minVersion)
  }
  if (pkg.minVersion) {
    label.push('<'+pkg.maxVersion)
  }
  return label.join(' ')
}
</script>

<template>
  <div class="container">
    <form class="row">
      <div class="p-3 app-block col-md-9 mx-auto mb-2">
        <h2>Search dependency</h2>
        <div class="row mb-2">
          <div class="form-group col-md-3 mb-md-0 mb-2">
            <select v-model="language" class="form-select">
              <option value="" disabled="disabled">Language</option>
              <option v-for="option in languages" :key="option" :value="option">{{ option }}</option>
            </select>
          </div>
          <div class="form-group col-md-9">
            <autocomplete-select
                :disabled="language === ''"
                :input-id="'dependency-name'"
                :options="autocompleteOptions"
                :placeholder="'Dependency name'"
                v-model="dependencyName"
                @reset-options="resetDependencies"
                @search="searchDependencies"
                @select-option="selectDependency"
            />
          </div>
        </div>
        <div class="row mb-2">
          <div class="form-group col-md-6 mb-md-0 mb-2">
            <div class="input-group">
              <input :disabled="selectedDependency === 0" autocomplete="off" class="form-control" id="min-version" type="text" placeholder="Dependency min version" v-model="minVersion" />
              <span class="input-group-text" title="Greater or equal"><font-awesome icon="question" /></span>
            </div>
          </div>
          <div class="form-group col-md-6">
            <div class="input-group">
              <input :disabled="selectedDependency === 0" autocomplete="off" class="form-control" id="max-version" type="text" placeholder="Dependency max version" v-model="maxVersion" />
              <span class="input-group-text" title="Strictly lower"><font-awesome icon="question" /></span>
            </div>
          </div>
        </div>
        <div class="form-group">
          <input :disabled="selectedDependency === 0 || dependencies.length > 4 || isAlreadyInSearch" class="btn btn-primary fa-pull-right" type="submit" value="Add" @click.prevent="addDependency" />
          <div v-if="isAlreadyInSearch" class="alert alert-danger mb-0 p-2">This dependency is already selected.</div>
        </div>
      </div>

      <div class="p-3 app-block col-md-9 mx-auto">
        <div class="row">
          <h2>Dependencies</h2>
          <div v-if="shownPackages.length === 0">
            None
          </div>
          <div v-else class="mb-2">
            <span v-for="dependency in shownPackages" :key="dependency.idx" :title="dependency.language" class="badge mx-1" :style="{'background-color': LanguageColorEnum[dependency.language]}">{{ getDependencyLabel(dependency) }} <font-awesome icon="trash-can" class="cp danger" title="Remove" @click="removeDependency(dependency.id)" /></span>
          </div>
        </div>
        <div class="row">
          <h2>Repository</h2>
          <div class="form-group col-md-6 mb-2">
            <input class="form-control" id="repoName" type="text" placeholder="Repository title contains" v-model="repoName" />
          </div>
          <div class="form-group col-md-6 mb-2">
            <input class="form-control" id="description" type="text" placeholder="Repository description contains" v-model="description" />
          </div>
          <div class="form-group">
            <input :disabled="dependencies.length === 0" class="btn btn-primary fa-pull-right" type="submit" value="Search" @click.prevent="search" />
          </div>
        </div>
      </div>
    </form>

    <div class="app-block p-3 mt-2" v-if="searchResults.length > 0">
      <div class="row justify-content-between">
        <h2 class="col-auto">Results ({{ totalResults }})</h2>
        <div class="col-auto">
          <pagination-component :max-page="maxPage" :current-page="currentPage" @select-page="selectPage" />
        </div>
      </div>
      <div v-for="repo in searchResults" :key="repo.id" class="card mb-2">
        <div class="card-header p-3">
          <h3><a :href="repo.url">{{ repo.fullName }}</a></h3>
        </div>
        <div class="card-body p-3">
          <div class="row align-items-center">
            <div class="col-md-10" v-if="repo.description !== ''">
              <p><strong>Description</strong>: {{ repo.description }}</p>
              <div v-if="repo.topics.length > 0">
                Topics: <span class="badge" v-for="topic in repo.topics" :key="topic.topic">{{ topic.topic }}</span>
              </div>
            </div>
            <div class="col-md-2">
              <font-awesome icon="scale-balanced" title="License" /> {{ repo.licenceName ?? 'Unknown' }}<br />
              <font-awesome icon="code-fork" /> {{ repo.forksCount ?? 0 }} forks<br />
              <font-awesome icon="circle-dot" title="Open issues" /> {{ repo.openIssuesCount ?? 0 }} issues<br />
              <font-awesome icon="star" /> {{ repo.stargazersCount ?? 0 }} stars<br />
              <font-awesome icon="clock-rotate-left" title="Last pushed at" /> {{ dayjs(repo.pushedAt).format('YYYY-MM-DD') }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
