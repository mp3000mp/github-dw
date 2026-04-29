<script lang="ts" setup>
import { computed, onMounted, ref } from 'vue'
import type { Ref } from 'vue'
import { validVersion, validVersions } from '@/utils/version'
import tooltipPopper from 'vue3-popper'
import { LanguageColorEnum } from '@/stores/search/types'
import type { Dependency } from '@/stores/search/types'

const props = defineProps<{
  dependency: Dependency
}>()
const emit = defineEmits(['remove', 'update-versions'])

const minVersionEdit = ref(false)
const minVersionValue = ref('') as Ref<string | null>
const maxVersionEdit = ref(false)
const maxVersionValue = ref('') as Ref<string | null>

const validMinVersion = computed(() => validVersion(minVersionValue.value))
const validMaxVersion = computed(() => validVersion(maxVersionValue.value))
const areValidVersions = computed(() => {
  if (!validVersion(minVersionValue.value) || !validVersion(maxVersionValue.value)) {
    return true
  }
  return validVersions(minVersionValue.value, maxVersionValue.value)
})

function updateVersions(minVersion: string | null, maxVersion: string | null) {
  if (validVersions(minVersion, maxVersion)) {
    emit('update-versions', { id: props.dependency.id, minVersion, maxVersion })
  }
  minVersionEdit.value = false
  maxVersionEdit.value = false
}

onMounted(() => {
  minVersionValue.value = props.dependency.minVersion
  maxVersionValue.value = props.dependency.maxVersion
})
</script>

<template>
  <span
    class="badge mx-1"
    :style="{
      'background-color': LanguageColorEnum[dependency.language as keyof typeof LanguageColorEnum],
      color: dependency.language === 'Javascript' ? '#000000' : 'inherit'
    }"
  >
    <tooltip-popper :content="dependency.language" :hover="true" :arrow="true">
      <span>{{ dependency.name }}</span>
    </tooltip-popper>
    <span v-if="dependency.minVersion">&nbsp;&gt;=&nbsp;</span>
    <span
      v-if="dependency.minVersion && !minVersionEdit"
      @click="minVersionEdit = true"
      class="cp"
      >{{ dependency.minVersion }}</span
    >
    <input
      v-if="dependency.minVersion && minVersionEdit"
      autocomplete="off"
      class="editable-version"
      id="min-version"
      type="text"
      v-model="minVersionValue"
      :class="validMinVersion && areValidVersions ? [] : ['is-invalid']"
      @blur="updateVersions(minVersionValue, dependency.maxVersion)"
    />
    <span v-if="dependency.maxVersion">&nbsp;&lt;&nbsp;</span>
    <span
      v-if="dependency.maxVersion && !maxVersionEdit"
      @click="maxVersionEdit = true"
      class="cp"
      >{{ dependency.maxVersion }}</span
    >
    <input
      v-if="dependency.maxVersion && maxVersionEdit"
      autocomplete="off"
      class="editable-version"
      id="max-version"
      type="text"
      v-model="maxVersionValue"
      :class="validMaxVersion && areValidVersions ? [] : ['is-invalid']"
      @blur="updateVersions(dependency.minVersion, maxVersionValue)"
    />
    <tooltip-popper content="Remove" :hover="true" :arrow="true" class="pl-1">
      <font-awesome icon="trash-can" class="cp danger" @click="$emit('remove', dependency.id)" />
    </tooltip-popper>
  </span>
</template>

<style lang="scss">
.editable-version {
  width: 40px;
  height: 15px;
}

input {
  &.is-invalid {
    border: 1px solid #b32727;
  }
}
</style>
