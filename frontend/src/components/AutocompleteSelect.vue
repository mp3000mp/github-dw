<script lang="ts" setup>
import { computed, ref, watch } from 'vue'
import type { Ref } from 'vue'
import type { Option } from './types'

const props = withDefaults(
  defineProps<{
    disabled?: boolean
    inputId: string
    isLoading: boolean
    modelValue: string
    options: Option[]
    placeholder?: string
    throttle?: number
  }>(),
  {
    disabled: false,
    placeHolder: '',
    throttle: 350
  }
)
const emit = defineEmits(['reset-options', 'search', 'select-option', 'update:modelValue'])

const selectedOption = ref(null) as Ref<null | string | number>
const isFocused = ref(false)
const showOptions = computed(() => props.options.length > 0 && isFocused.value)

function selectOption(option: Option) {
  selectedOption.value = option.value
  emit('update:modelValue', option.label)
  emit('select-option', selectedOption.value)
}

let timeout: ReturnType<typeof setTimeout> | null = null
function onInput(value: string) {
  emit('update:modelValue', value)
  if (timeout !== null) {
    clearTimeout(timeout)
  }
  timeout = setTimeout(() => {
    if (props.modelValue.length < 3) {
      emit('reset-options')
      return
    }
    emit('search', props.modelValue)
  }, props.throttle)
}

watch(
  () => props.modelValue,
  () => {
    if (props.modelValue === '') {
      selectedOption.value = null
    }
  }
)
</script>

<template>
  <div class="acs">
    <input
      :disabled="disabled"
      autocomplete="off"
      class="form-control"
      :id="inputId"
      type="text"
      :placeholder="placeholder"
      :value="modelValue"
      @focus="isFocused = true"
      @blur="isFocused = false"
      @input="onInput(($event.target as HTMLInputElement).value)"
    />
    <ul v-if="showOptions || isLoading" class="acs-options">
      <li v-if="isLoading" class="acs-loading text-center">...</li>
      <li
        v-else
        v-for="option in options"
        :key="option.value"
        @mousedown="selectOption(option)"
        class="option"
        :class="{ selected: option.value === selectedOption }"
      >
        {{ option.label }}
      </li>
    </ul>
  </div>
</template>

<style lang="scss">
.acs {
  position: relative;
  width: 100%;
}
.acs-options {
  z-index: 1;
  padding: 0;
  list-style: none;
  position: absolute;
  margin-top: -5px;
  width: 100%;
  cursor: pointer;
  max-height: 160px;
  overflow-y: auto;
  background-color: #001229;
  border: 1px solid rgba(222, 222, 222, 0.5);
  border-bottom-left-radius: 6px;
  border-bottom-right-radius: 6px;
  .option {
    padding: 0.2rem 0.5rem;
    &:hover {
      background-color: #cc8800;
    }
    &.selected {
      background-color: #ffa90a;
    }
  }
}
</style>
