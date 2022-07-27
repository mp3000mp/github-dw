import { createApp } from 'vue'
import App from './App.vue'
import './registerServiceWorker'
import router from './router'
import { createPinia } from 'pinia'
import variables from '../config/variables.json'
import apiRegistry from './helpers/apiRegistry'
import {AxiosResponse} from 'axios'

import { library } from '@fortawesome/fontawesome-svg-core'
import { faHandSparkles, faMagic } from '@fortawesome/free-solid-svg-icons'
library.add(faHandSparkles, faMagic)
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

import './assets/style/app.scss'

apiRegistry.set('default', variables.URL, (error: AxiosResponse) => {
    if (error.status === 401 || error.status === 403) {
        console.log('redirect login')
        router.push({ path: '/login' })
    }
})

createApp(App)
    .component('font-awesome', FontAwesomeIcon)
    .use(createPinia())
    .use(router)
    .mount('#app')
