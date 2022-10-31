<script lang="ts" setup>
import {computed, ref, watch} from 'vue'
import {formatVersion, validVersion, validVersions} from '@/utils/version'
import {useSearchStore} from '@/stores/search'
import AutocompleteSelect from '@/components/AutocompleteSelect.vue'
import PaginationComponent from '@/components/PaginationComponent.vue'
import SelectedDependency from '@/views/home/SelectedDependency.vue'
import RepositoryItem from '@/views/home/RepositoryItem.vue'
import {Dependency} from '@/stores/search/types'
import tooltipPopper from 'vue3-popper'
const searchStore = useSearchStore()

let packageIdx = 1

const languages = ref(['Go', 'Javascript', 'PHP', 'Python'])

const showAdvancedSearch = ref(false)
const repoName = ref('')
const description = ref('')
const dependencies = ref([] as Dependency[])

const language = ref('')
const dependencyName = ref('')
const selectedDependencyId = ref(0)
const minVersion = ref('')
const maxVersion = ref('')

const currentPage = ref(1)
const perPage = ref(8)

const shownPackages = computed(() => dependencies.value)
const autocompleteResults = computed(() => searchStore.packageOptions)
const searchRequest = computed(() => searchStore.actionRequests.search)
const autocompleteRequest = computed(() => searchStore.actionRequests.packageAutocomplete)
const searchResults = computed(() => searchStore.repositories)
const totalResults = computed(() => searchStore.totalRepositories)
const autocompleteOptions = computed(() => autocompleteResults.value.map(ar => ({value: ar.id, label: ar.name})))
const isAlreadyInSearch = computed(() => dependencies.value.findIndex(d => d.id === selectedDependencyId.value) >= 0)
const maxPage = computed(() => Math.ceil(totalResults.value/perPage.value))
const maxDependenciesReached = computed(() => dependencies.value.length >= 4)

const areValidVersions = ref(true)
let t = null as number|null
function refreshValidVersions() {
  if (t !== null) {
    clearTimeout(t)
  }
  t = setTimeout(() => {
    areValidVersions.value = validVersions(minVersion.value, maxVersion.value)
  }, 500)
}

function resetFields() {
  dependencyName.value = ''
  selectedDependencyId.value = 0
  searchStore.resetPackageOptions()
  minVersion.value = ''
  maxVersion.value = ''
}
function addDependency() {
  const selectedOption = autocompleteOptions.value.find(o => o.value === selectedDependencyId.value)
  if (!selectedOption) {
    return
  }
  dependencies.value.push({
    idx: packageIdx,
    language: language.value,
    name: selectedOption.label,
    id: selectedDependencyId.value,
    minVersion: formatVersion(minVersion.value),
    maxVersion: formatVersion(maxVersion.value),
  })
  packageIdx++
  resetFields()
}
function removeDependency(id: number) {
  dependencies.value = dependencies.value.filter(p => p.id !== id)
}
function updateDependencyVersions({id, minVersion, maxVersion}) {
  console.log({id, minVersion, maxVersion})
  const currentDep = dependencies.value.find(p => p.id === id)
  if (currentDep) {
    currentDep.minVersion = formatVersion(minVersion)
    currentDep.maxVersion = formatVersion(maxVersion)
  }
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
  selectedDependencyId.value = id
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
function onLanguageChange() {
  resetFields()
}

watch(minVersion, (newValue, oldValue) => {
  if (!validVersion(minVersion.value)) {
    minVersion.value = oldValue
    return
  }
  refreshValidVersions()
})
watch(maxVersion, (newValue, oldValue) => {
  if (!validVersion(maxVersion.value)) {
    maxVersion.value = oldValue
    return
  }
  refreshValidVersions()
})
</script>

<template>
  <div class="container">
    <form class="row">
      <div class="p-3 app-block col-md-9 mx-auto mb-2">
        <h2>Welcome</h2>
        <p>This tool helps you finding Github projects basing your search on the dependencies included in the project.</p>
        <ul>
          <li><strong>Step 1</strong>: choose one dependency and optionally a min/max version. Then click on "add" <i>(this step can be repeated up to 4 times)</i>.</li>
          <li><strong>Step 2</strong>: when you're ready click on "search" and navigate in the results using pagination.</li>
        </ul>
        <div class="text-end">
          <router-link :to="{name: 'about'}">Learn more...</router-link>
        </div>
      </div>
      <div class="p-3 app-block col-md-9 mx-auto mb-2">
        <h2>Search dependency</h2>
        <div class="row mb-2">
          <div class="form-group col-md-3 mb-md-0 mb-2">
            <select
              v-model="language"
              @change="onLanguageChange"
              class="form-select"
              :disabled="maxDependenciesReached"
            >
              <option value="" disabled="disabled">Language *</option>
              <option v-for="option in languages" :key="option" :value="option">{{ option }}</option>
            </select>
          </div>
          <div class="form-group col-md-9">
            <autocomplete-select
                :disabled="language === '' || maxDependenciesReached"
                :input-id="'dependency-name'"
                :is-loading="autocompleteRequest.loading"
                :options="autocompleteOptions"
                :placeholder="'Dependency name *'"
                v-model="dependencyName"
                @reset-options="searchStore.resetPackageOptions()"
                @search="searchDependencies"
                @select-option="selectDependency"
            />
          </div>
        </div>
        <div class="row mb-2">
          <div class="form-group col-md-6 mb-md-0 mb-2">
            <div class="input-group">
              <input
                :disabled="selectedDependencyId === 0"
                autocomplete="off"
                class="form-control"
                id="min-version"
                type="text"
                placeholder="Dependency min version (x.y.z)"
                v-model="minVersion"
              />
              <span class="input-group-text">
                <tooltip-popper content="Greater or equal" :hover="true" :arrow="true">
                  <font-awesome icon="question" />
                </tooltip-popper>
              </span>
            </div>
          </div>
          <div class="form-group col-md-6">
            <div class="input-group">
              <input
                :disabled="selectedDependencyId === 0"
                autocomplete="off"
                class="form-control"
                id="max-version"
                type="text"
                placeholder="Dependency max version (x.y.z)"
                v-model="maxVersion"
              />
              <span class="input-group-text">
                <tooltip-popper content="Strictly lower" :hover="true" :arrow="true">
                  <font-awesome icon="question" />
                </tooltip-popper>
              </span>
            </div>
          </div>
        </div>
        <div class="form-group">
          <input :disabled="selectedDependencyId === 0 || maxDependenciesReached || isAlreadyInSearch || !areValidVersions" class="btn fa-pull-right" type="submit" value="Add" @click.prevent="addDependency" />
          <div v-if="isAlreadyInSearch" class="danger mb-0 p-2">This dependency is already selected.</div>
          <div v-if="!areValidVersions" class="danger mb-0 p-2">Max version must be greater than min version.</div>
          <div v-if="maxDependenciesReached" class="warning mb-0 p-2">You cannot search for more than 4 dependencies.</div>
        </div>
      </div>

      <div class="p-3 app-block col-md-9 mx-auto" v-if="shownPackages.length > 0 || searchRequest.callCount > 0">
        <div class="row">
          <h2>Selected dependencies</h2>
          <div v-if="shownPackages.length === 0">
            None
          </div>
          <div v-else class="mb-2">
            <selected-dependency
                v-for="dependency in shownPackages"
                :key="dependency.idx"
                :dependency="dependency"
                @remove="removeDependency"
                @updateVersions="updateDependencyVersions"
            />
          </div>
        </div>
        <div v-if="showAdvancedSearch" class="row mt-2">
          <h2>Repository</h2>
          <div class="form-group col-md-6 mb-2">
            <input class="form-control" id="repoName" type="text" placeholder="Repository title contains" v-model="repoName" />
          </div>
          <div class="form-group col-md-6 mb-2">
            <input class="form-control" id="description" type="text" placeholder="Repository description contains" v-model="description" />
          </div>
        </div>
        <div class="row">
          <div class="form-group">
            <input :disabled="dependencies.length === 0" class="btn fa-pull-right" type="submit" value="Search" @click.prevent="search" />
            <div v-if="searchRequest.isError" class="danger mb-0 p-2">{{ searchRequest.message }}</div>
          </div>
        </div>
      </div>
    </form>

    <div class="row">
      <div class="app-block p-3 mt-3 text-center" v-if="searchRequest.loading">...</div>
      <div class="app-block p-3 mt-3 text-center" v-else-if="searchRequest.callCount > 0 && totalResults === 0">No result found, please try with less restrictive criteria.</div>
      <div class="app-block p-3 mt-3" v-else-if="searchRequest.callCount > 0">
        <div class="row justify-content-between pt-0 p-3">
          <h2 class="col-auto">Results ({{ totalResults }})</h2>
          <div class="col-auto">
            <pagination-component :max-page="maxPage" :current-page="currentPage" @select-page="selectPage" />
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
            <pagination-component :max-page="maxPage" :current-page="currentPage" @select-page="selectPage" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.card:last-child {
  margin-bottom: 0!important;
}
</style>
