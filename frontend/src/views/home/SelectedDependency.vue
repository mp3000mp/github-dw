<script lang="ts" setup>
import {onMounted, Ref, ref, watch} from 'vue'
import {validVersion, validVersions} from '@/utils/version'
import tooltipPopper from 'vue3-popper'
import {LanguageColorEnum, Dependency} from '@/stores/search/types'

const props = defineProps<{
  dependency: Dependency;
}>()
const emit = defineEmits(['remove', 'update-versions'])

const minVersionEdit = ref(false)
const minVersionValue = ref('') as Ref<string|null>
const maxVersionEdit = ref(false)
const maxVersionValue = ref('') as Ref<string|null>

function updateVersions(minVersion: string, maxVersion: string) {
  console.log(minVersion)
  console.log(maxVersion)
  if (validVersions(minVersion, maxVersion)) {
    emit('update-versions', {id: props.dependency.id, minVersion, maxVersion})
  }
  minVersionEdit.value = false
  maxVersionEdit.value = false
}

onMounted(() => {
  minVersionValue.value = props.dependency.minVersion
  maxVersionValue.value = props.dependency.maxVersion
})

watch(minVersionValue, (newValue, oldValue) => {
  if (!validVersion(minVersionValue.value)) {
    minVersionValue.value = oldValue
    return
  }
})
watch(maxVersionValue, (newValue, oldValue) => {
  if (!validVersion(maxVersionValue.value)) {
    maxVersionValue.value = oldValue
    return
  }
})
</script>

<template>
  <span class="badge mx-1" :style="{'background-color': LanguageColorEnum[dependency.language], color: dependency.language === 'Javascript' ? '#000000' : 'inherit'}">
    <tooltip-popper :content="dependency.language" :hover="true" :arrow="true">
      <span>{{ dependency.name }}</span>
    </tooltip-popper>
    <span v-if="dependency.minVersion">&nbsp;&gt;=&nbsp;</span>
    <span v-if="dependency.minVersion && !minVersionEdit" @click="minVersionEdit = true" class="cp">{{ dependency.minVersion }}</span>
    <input
      v-if="dependency.minVersion && minVersionEdit"
      autocomplete="off"
      class="editable-version"
      id="min-version"
      type="text"
      v-model="minVersionValue"
      @blur="updateVersions(minVersionValue, dependency.maxVersion)"
    />
    <span v-if="dependency.maxVersion">&nbsp;&lt;&nbsp;</span>
    <span v-if="dependency.maxVersion && !maxVersionEdit" @click="maxVersionEdit = true" class="cp">{{ dependency.maxVersion }}</span>
    <input
      v-if="dependency.maxVersion && maxVersionEdit"
      autocomplete="off"
      class="editable-version"
      id="max-version"
      type="text"
      v-model="maxVersionValue"
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
</style>
