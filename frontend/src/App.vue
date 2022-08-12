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
  <header class="row align-items-center">
    <h1 class="col-auto mr-auto"><router-link :to="{name: 'home'}">Github Finder</router-link></h1>
    <div class="col-auto" v-if="securityStore.getIsAuth">
      <router-link :to="{name: 'admin'}" class="mr-3">Admin</router-link>
      <font-awesome @click="logout" class="cp" icon="right-from-bracket" />
    </div>
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
