<script lang="ts" setup>
import dayjs from 'dayjs'
import {computed, ref, watch} from 'vue'
import {useSearchStore} from '@/stores/search'
import AutocompleteSelect from '@/components/AutocompleteSelect.vue'
import PaginationComponent from '@/components/PaginationComponent.vue'
import {LanguageColorEnum} from '@/stores/search/types'
import tooltipPopper from 'vue3-popper'
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

const showAdvancedSearch = ref(false)
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
const searchRequest = computed(() => searchStore.actionRequests.search)
const searchResults = computed(() => searchStore.repositories)
const totalResults = computed(() => searchStore.totalRepositories)
const autocompleteOptions = computed(() => autocompleteResults.value.map(ar => ({value: ar.id, label: ar.name})))
const isAlreadyInSearch = computed(() => dependencies.value.findIndex(d => d.id === selectedDependency.value) >= 0)
const maxPage = computed(() => Math.ceil(totalResults.value/perPage.value))

function resetFields() {
  dependencyName.value = ''
  selectedDependency.value = 0
  searchStore.resetPackageOptions()
  minVersion.value = ''
  maxVersion.value = ''
}
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
  resetFields()
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

function correctVersion(version: string): string {
  if (!version.substring(version.length-1).match(/[\d.]/)) {
    version = version.substring(0, version.length-1)
  }
  return version
}
watch(minVersion, () => {
  if (minVersion.value === '') {
    return
  }
  minVersion.value = correctVersion(minVersion.value)
})
watch(maxVersion, () => {
  if (maxVersion.value === '') {
    return
  }
  maxVersion.value = correctVersion(maxVersion.value)
})
</script>

<template>
  <div class="container">
    <form class="row">
      <div class="p-3 app-block col-md-9 mx-auto mb-2">
        <h2>Search dependency</h2>
        <div class="row mb-2">
          <div class="form-group col-md-3 mb-md-0 mb-2">
            <select v-model="language" @change="onLanguageChange" class="form-select">
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
                @reset-options="searchStore.resetPackageOptions()"
                @search="searchDependencies"
                @select-option="selectDependency"
            />
          </div>
        </div>
        <div class="row mb-2">
          <div class="form-group col-md-6 mb-md-0 mb-2">
            <div class="input-group">
              <input :disabled="selectedDependency === 0" autocomplete="off" class="form-control" id="min-version" type="text" placeholder="Dependency min version (x.y.z)" v-model="minVersion" />
                <span class="input-group-text">
                  <tooltip-popper content="Greater or equal" :hover="true" :arrow="true">
                    <font-awesome icon="question" />
                  </tooltip-popper>
                </span>
            </div>
          </div>
          <div class="form-group col-md-6">
            <div class="input-group">
              <input :disabled="selectedDependency === 0" autocomplete="off" class="form-control" id="max-version" type="text" placeholder="Dependency max version (x.y.z)" v-model="maxVersion" />
              <span class="input-group-text">
                <tooltip-popper content="Strictly lower" :hover="true" :arrow="true">
                  <font-awesome icon="question" />
                </tooltip-popper>
              </span>
            </div>
          </div>
        </div>
        <div class="form-group">
          <input :disabled="selectedDependency === 0 || dependencies.length > 4 || isAlreadyInSearch" class="btn fa-pull-right" type="submit" value="Add" @click.prevent="addDependency" />
          <div v-if="isAlreadyInSearch" class="danger mb-0 p-2">This dependency is already selected.</div>
        </div>
      </div>

      <div class="p-3 app-block col-md-9 mx-auto">
        <div class="row">
          <h2>Selected dependencies</h2>
          <div v-if="shownPackages.length === 0">
            None
          </div>
          <div v-else class="mb-2">
            <span v-for="dependency in shownPackages" :key="dependency.idx" class="badge mx-1" :style="{'background-color': LanguageColorEnum[dependency.language], color: dependency.language === 'Javascript' ? '#000000' : 'inherit'}">
              <tooltip-popper  :content="dependency.language" :hover="true" :arrow="true">
                <span>{{ getDependencyLabel(dependency) }}</span>
              </tooltip-popper>
              <tooltip-popper content="Remove" :hover="true" :arrow="true">
                <font-awesome icon="trash-can" class="cp danger" @click="removeDependency(dependency.id)" />
              </tooltip-popper>
            </span>
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
      <div class="app-block p-3 mt-3" v-else>
        <div class="row justify-content-between pt-0 p-3">
          <h2 class="col-auto">Results ({{ totalResults }})</h2>
          <div class="col-auto">
            <pagination-component :max-page="maxPage" :current-page="currentPage" @select-page="selectPage" />
          </div>
        </div>
        <div v-for="repo in searchResults" :key="repo.id" class="card mb-2">
          <div class="card-header p-3">
            <div class="row">
              <h3 class="col-md-6"><a :href="repo.url" target="_blank">{{ repo.fullName }}</a></h3>
              <div class="col-md-6 text-end">
                <span class="mx-2">
                  <tooltip-popper content="License" :hover="true" :arrow="true">
                    <span>
                      <font-awesome icon="scale-balanced" /> {{ repo.licenceName ?? 'Unknown' }}
                    </span>
                  </tooltip-popper>
                </span>
                <span class="mx-2">
                  <tooltip-popper content="Last pushed at" :hover="true" :arrow="true">
                    <span>
                      <font-awesome icon="clock-rotate-left" /> {{ dayjs(repo.pushedAt).format('YYYY-MM-DD') }}
                    </span>
                  </tooltip-popper>
                </span>
              </div>
            </div>
          </div>
          <div class="card-body p-3">
            <p><strong>Description</strong>: {{ repo.description }}</p>
            <div v-if="repo.topics.length > 0">
              Topics: <span class="badge" v-for="topic in repo.topics" :key="topic.topic">{{ topic.topic }}</span>
            </div>
          </div>
          <div class="card-footer p-3">
          <span class="mx-2">
            <font-awesome icon="star" /> {{ repo.stargazersCount ?? 0 }} stars
          </span>
          <span class="mx-2">
            <font-awesome icon="code-fork" /> {{ repo.forksCount ?? 0 }} forks
          </span>
            <span class="mx-2">
              <tooltip-popper content="Open issues" :hover="true" :arrow="true">
                <span>
                  <font-awesome icon="circle-dot" /> {{ repo.openIssuesCount ?? 0 }} issues
                </span>
              </tooltip-popper>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.card:last-child {
  margin-bottom: 0!important;
}
</style>
