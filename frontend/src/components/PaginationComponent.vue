<script lang="ts" setup>
import { computed } from 'vue'

interface Link {
  isLink: boolean
  page: number
}

const props = withDefaults(
  defineProps<{
    currentPage: number
    displayedLinks?: number
    maxPage: number
  }>(),
  {
    displayedLinks: 2
  }
)
const emit = defineEmits(['select-page'])

const links = computed(() => {
  const items = [] as Link[]
  let i = Math.max(props.currentPage - props.displayedLinks, 1)
  const max = props.currentPage + props.displayedLinks
  while (i <= max && i <= props.maxPage) {
    items.push({
      isLink: i !== props.currentPage,
      page: i
    })
    i++
  }
  return items
})

function goto(page: number) {
  if (page === props.currentPage) {
    return
  }
  emit('select-page', page)
}
</script>

<template>
  <div class="pages">
    <strong>Page: </strong>
    <span class="mx-1" v-if="maxPage === 0">0</span>
    <span class="mx-1 link no-deco" v-if="currentPage - displayedLinks > 1" @click="goto(1)"
      >1</span
    >
    <span class="mx-1" v-if="currentPage - displayedLinks > 2">...</span>
    <span
      class="mx-1 no-deco"
      :class="{ link: link.isLink }"
      v-for="link in links"
      :key="link.page"
      @click="goto(link.page)"
      >{{ link.page }}</span
    >
    <span class="mx-1" v-if="currentPage + displayedLinks < maxPage - 1">...</span>
    <span
      class="mx-1 link no-deco"
      v-if="currentPage + displayedLinks < maxPage"
      @click="goto(maxPage)"
      >{{ maxPage }}</span
    >
  </div>
</template>
