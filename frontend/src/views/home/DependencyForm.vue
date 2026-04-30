<script lang="ts" setup>
import { computed, ref } from 'vue'
import { formatVersion, validVersion, validVersions } from '@/utils/version'
import { useSearchStore } from '@/stores/search'
import AutocompleteSelect from '@/components/AutocompleteSelect.vue'
import type { Dependency } from '@/stores/search/types'
import tooltipPopper from 'vue3-popper'

const props = defineProps<{
  dependencies: Dependency[]
}>()
const emit = defineEmits(['add'])

const searchStore = useSearchStore()

const languages = ref(['Go', 'Javascript', 'PHP', 'Python'])

const language = ref('')
const dependencyName = ref('')
const selectedDependencyId = ref(0)
const minVersion = ref('')
const maxVersion = ref('')

const autocompleteRequest = computed(() => searchStore.actionRequests.packageAutocomplete)
const autocompleteOptions = computed(() =>
  searchStore.packageOptions.map((ar) => ({ value: ar.id, label: ar.name }))
)
const isAlreadyInSearch = computed(
  () => props.dependencies.findIndex((d) => d.id === selectedDependencyId.value) >= 0
)
const maxDependenciesReached = computed(() => props.dependencies.length >= 4)
const validMinVersion = computed(() => validVersion(minVersion.value))
const validMaxVersion = computed(() => validVersion(maxVersion.value))
const areValidVersions = computed(() => {
  if (!validVersion(minVersion.value) || !validVersion(maxVersion.value)) {
    return true
  }
  return validVersions(minVersion.value, maxVersion.value)
})
const canAddDependency = computed(() => {
  return (
    selectedDependencyId.value > 0 &&
    !maxDependenciesReached.value &&
    !isAlreadyInSearch.value &&
    areValidVersions.value &&
    validMinVersion.value &&
    validMaxVersion.value
  )
})

function resetFields() {
  dependencyName.value = ''
  selectedDependencyId.value = 0
  searchStore.resetPackageOptions()
  minVersion.value = ''
  maxVersion.value = ''
}
function addDependency() {
  const selectedOption = autocompleteOptions.value.find(
    (o) => o.value === selectedDependencyId.value
  )
  if (!selectedOption) {
    return
  }
  emit('add', {
    language: language.value,
    name: selectedOption.label,
    id: selectedDependencyId.value,
    minVersion: formatVersion(minVersion.value),
    maxVersion: formatVersion(maxVersion.value)
  })
  resetFields()
}
function selectDependency(id: number) {
  selectedDependencyId.value = id
}
async function searchDependencies(search: string) {
  await searchStore.packageAutocomplete({
    language: language.value,
    text: search
  })
}
function onLanguageChange() {
  resetFields()
}
</script>

<template>
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
          <option value="" disabled>Language *</option>
          <option v-for="option in languages" :key="option" :value="option">
            {{ option }}
          </option>
        </select>
      </div>
      <div class="form-group col-md-9">
        <autocomplete-select
          :disabled="language === '' || maxDependenciesReached"
          :input-id="'dependency-name'"
          :is-loading="autocompleteRequest.isLoading"
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
            :class="validMinVersion ? [] : ['is-invalid']"
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
            :class="validMaxVersion ? [] : ['is-invalid']"
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
      <input
        :disabled="!canAddDependency"
        class="btn fa-pull-right"
        type="submit"
        value="Add"
        @click.prevent="addDependency"
      />
      <div v-if="isAlreadyInSearch" class="danger mb-0 p-2">
        This dependency is already selected.
      </div>
      <div v-if="!areValidVersions" class="danger mb-0 p-2">
        Max version must be greater than min version.
      </div>
      <div v-if="maxDependenciesReached" class="warning mb-0 p-2">
        You cannot search for more than 4 dependencies.
      </div>
    </div>
  </div>
</template>
