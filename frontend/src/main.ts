import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { createPinia } from 'pinia'
import config from '@/helpers/config'
import apiRegistry from './helpers/apiRegistry'
import FontAwesomeIcon from '@/utils/fontAwesome'

import './assets/style/app.scss'

apiRegistry.set('default', config.backendBaseUrl, async (status: number) => {
  if (status === 401 || status === 403) {
    if (router.currentRoute.value.name !== 'login') {
      console.log('redirect login')
      await router.push({ name: 'login' })
    }
  }
})

createApp(App)
  .component('font-awesome', FontAwesomeIcon)
  .use(createPinia())
  .use(router)
  .mount('#app')
