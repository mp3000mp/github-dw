import { createApp } from 'vue'
import App from './App.vue'
import './registerServiceWorker'
import router from './router'
import { createPinia } from 'pinia'
import variables from '../config/variables.json'
import apiRegistry from './helpers/apiRegistry'
import FontAwesomeIcon from '@/utils/fontAwesome'

import './assets/style/app.scss'

apiRegistry.set('default', variables.URL, (status: number) => {
    if (status === 401 || status === 403) {
        if (router.currentRoute.value.name !== 'login') {
            console.log('redirect login') // eslint-disable-line
            router.push({ path: '/login' })
        }
    }
})

createApp(App)
    .component('font-awesome', FontAwesomeIcon)
    .use(createPinia())
    .use(router)
    .mount('#app')
