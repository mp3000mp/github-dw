<script lang="ts" setup>
import {useSecurityStore} from '@/stores/security'
import variables from '@/../config/variables.json'
import {useRouter} from 'vue-router'

const router = useRouter()
const securityStore = useSecurityStore()

async function logout() {
  await securityStore.logout()
  router.push({ name: 'home' })
}

const frontVersion = variables.APP_VERSION
</script>

<template>
  <header>
    <h1 class="d-inline"><router-link :to="{name: 'home'}">Github Finder</router-link></h1>
    <font-awesome v-if="securityStore.getIsAuth" @click="logout" class="ml-auto cp" icon="right-from-bracket" />
  </header>
  <router-view />
  <footer>
    <font-awesome icon="magic-wand-sparkles" /> Invoked from the magic kingdom by
    <a href="https://github.com/mp3000mp">
      mp3000
      <font-awesome icon="hand-sparkles" />
    </a>
    <div>v{{ frontVersion }}</div>
  </footer>
</template>
