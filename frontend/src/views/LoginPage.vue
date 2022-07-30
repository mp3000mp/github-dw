<script lang="ts" setup>
import {ref, computed, onMounted} from 'vue'
import { useSecurityStore } from '@/stores/security'
import { useRouter } from 'vue-router'

const router = useRouter()
const securityStore = useSecurityStore()

const securityRequests = computed(() => securityStore.actionRequests)

const password = ref('')
const username = ref('')

async function login () {
  if (securityRequests.value.login.loading) {
    return
  }

  await securityStore.login({
    username: username.value,
    password: password.value
  })

  if (securityStore.getIsAuth) {
    router.push({ name: 'admin' })
  }
}

onMounted(() => {
  if (securityStore.getIsAuth) {
    router.push({ name: 'admin' })
  }
})
</script>

<template>
  <div class="container-fluid">
    <div class="row">
      <form @submit.prevent="login" class="app-block col-auto m-auto p-3" id="login-form">
        <div class="form-group mb-2">
          <input required="required" class="form-control" id="username" type="text" placeholder="Username" v-model="username" />
        </div>
        <div class="form-group mb-2">
          <input required="required" class="form-control" id="password" type="password" placeholder="Password" v-model="password" />
        </div>
        <div class="form-group">
          <input class="btn btn-primary fa-pull-right" type="submit" :disabled="securityRequests.login.loading" value="Log in" />
          <div v-if="securityRequests.login.message.length > 0" class="alert alert-danger mt-2">{{ securityRequests.login.message }}</div>
        </div>
      </form>
    </div>
  </div>
</template>
