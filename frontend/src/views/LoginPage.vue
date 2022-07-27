<script lang="ts" setup>
import { ref, computed, watch } from 'vue'
import { useSecurityStore } from '@/stores/security'
import { useRouter } from 'vue-router'

const securityStore = useSecurityStore()
const router = useRouter()

const password = ref('')
const username = ref('')

const me = computed(() => securityStore.me)
const securityRequests = computed(() => securityStore.actionRequest)

function login () {
  securityStore.login({
    username: username.value,
    password: password.value
  })
}

watch(me, () => {
  router.push({ path: '/' })
})
</script>

<template>
  <div class="container-fluid text-center">
    <h1>Welcome</h1>
    <form @submit.prevent="login" class="d-flex flex-column basic-form" id="login-form">
      <label for="username" class="form-label"></label>
      <input required="required" class="form-control" id="username" type="text" placeholder="Username" v-model="username" />
      <label for="password" class="form-label"></label>
      <input required="required" class="form-control mb-2" id="password" type="password" placeholder="Password" v-model="password" />
      <input class="btn btn-primary" type="submit" value="Log in" />
      <span class="err">{{ securityRequests.login.message }}</span>
    </form>
  </div>
</template>
