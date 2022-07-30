<script lang="ts" setup>
import {computed, ref, watch} from 'vue'
import {useSearchStore} from '@/stores/search'
import {Dependency} from '@/stores/search/types'

const searchStore = useSearchStore()

let packageIdx = 1
interface Package {
  idx: number;
  language: string;
  name: string|null;
  id: number|null;
  minVersion: string|null;
  maxVersion: string|null;
}

const languages = ref(['Go', 'Javascript', 'PHP', 'Python'])

const repoName = ref('')
const description = ref('')
const dependencies = ref([] as Package[])

const language = ref('')
const dependencyName = ref('')
const selectedDependency = ref({} as Dependency)
const minVersion = ref('')
const maxVersion = ref('')

const shownPackages = computed(() => dependencies.value.filter(p => p.id !== null))
const autocompleteResults = computed(() => searchStore.packages)
const searchResults = computed(() => searchStore.repositories)

function addDependency() {
  dependencies.value.push({
    idx: packageIdx,
    language: language.value,
    name: dependencyName.value,
    id: selectedDependency.value.id,
    minVersion: minVersion.value,
    maxVersion: maxVersion.value,
  })
  packageIdx++
  language.value = ''
  dependencyName.value = ''
  selectedDependency.value = {id: 0, nme: ''}
  minVersion.value = ''
  maxVersion.value = ''
}
function removeDependency(id: number) {
  dependencies.value = dependencies.value.filter(p => p.id !== id)
}
function search() {
  const query = {
    name: repoName.value,
    description: description.value,
    packages: dependencies.value.map(p => {
      return {
        id: p.id,
        minVersion: p.minVersion,
        maxVersion: p.maxVersion,
      }
    })
  }
  searchStore.search(query)
}
function selectPackage(selection: Package) {
  selectedDependency.value = selection
}

// todo throttle
// todo select lib
watch(dependencyName, () => {
  if (dependencyName.value.length < 3) {
    return
  }
  searchStore.packageAutocomplete({
    language: language.value,
    text: dependencyName.value,
  })
})
</script>

<template>
  <div class="container">
    <form class="row">
      <div class="p-3 app-block col-md-9 mb-2 mx-auto">
        <h2>Repository</h2>
        <div class="form-group mb-2">
          <input class="form-control" id="repoName" type="text" placeholder="Repository title" v-model="repoName" />
        </div>
        <div class="form-group mb-2">
          <input class="form-control" id="description" type="text" placeholder="Repository description" v-model="description" />
        </div>
        <h2>Dependencies</h2>
        <div v-if="shownPackages.length === 0">
          Aucun
        </div>
        <div v-else>
          <div  v-for="dependency in shownPackages" :key="dependency.idx" class="form-group">
            <span class="badge bg-info">{{ dependency.language }}</span>
            <span>{{ dependency.name }}: {{ dependency.minVersion }} {{ dependency.maxVersion }}</span>
            <font-awesome icon="trash-can" class="cp danger" @click="removeDependency(dependency.id)" />
          </div>
        </div>
        <div class="form-group">
          <input class="btn btn-primary fa-pull-right" type="submit" value="Search" @click.prevent="search" />
        </div>
      </div>

      <div class="p-3 app-block col-md-9 mx-auto">
        <h2>Add dependency</h2>
        <div class="row mb-2">
          <div class="form-group col-md-3 mb-md-0 mb-2">
            <select v-model="language" class="form-select">
              <option value="" disabled="disabled">Language</option>
              <option v-for="option in languages" :key="option" :value="option">{{ option }}</option>
            </select>
          </div>
          <div class="form-group col-md-9">
            <input :disabled="language === ''" class="form-control" id="dependency-name" type="text" placeholder="Dependency name" v-model="dependencyName" />
            <div v-for="option in autocompleteResults" :key="option.id" @click="selectPackage(option.id)">{{ option.name }}</div>
          </div>
        </div>
        <div class="row mb-2">
          <div class="form-group col-md-6 mb-md-0 mb-2">
            <div class="input-group">
              <input :disabled="language === ''" class="form-control" id="min-version" type="text" placeholder="Dependency min version" v-model="minVersion" />
              <span class="input-group-text" title="Greater or equal"><font-awesome icon="question" /></span>
            </div>
          </div>
          <div class="form-group col-md-6">
            <div class="input-group">
              <input :disabled="language === ''" class="form-control" id="max-version" type="text" placeholder="Dependency max version" v-model="maxVersion" />
              <span class="input-group-text" title="Strictly lower"><font-awesome icon="question" /></span>
            </div>
          </div>
        </div>
        <div class="form-group">
          <input :disabled="dependencies.length > 4" class="btn btn-primary fa-pull-right" type="submit" value="Add" @click.prevent="addDependency" />
        </div>
      </div>
    </form>

    <div class="app-block" v-if="searchResults.length > 0">
      <div v-for="repo in searchResults" :key="repo.id">
        {{ repo.name }}
      </div>
    </div>

  </div>
</template>
