<script lang="ts" setup>
import { onMounted } from 'vue'
import { useAppStore } from '@/stores/app'
import variables from '@/../config/variables.json'

const appStore = useAppStore()

const frontVersion = variables.APP_VERSION
onMounted(() => {
  appStore.getInfo()
})
</script>

<template>
  <header>
    <h1>Github Finder</h1>
  </header>
  <router-view />
  <footer>
    <font-aw icon="magic" /> Invoked from the magic kingdom by
    <a href="https://github.com/mp3000mp">
      mp3000
      <font-aw icon="hand-sparkles" />
    </a>
    <div>v{{ frontVersion }}</div>
  </footer>
</template>
