import { createApp } from 'vue'
import App from './App.vue'
import './registerServiceWorker'
import router from './router'
import { createPinia } from 'pinia'
import variables from '../config/variables.json'
import apiRegistry from './helpers/apiRegistry'
import {AxiosResponse} from 'axios'
import FontAwesomeIcon from '@/utils/fontAwesome'

import './assets/style/app.scss'

apiRegistry.set('default', variables.URL, (error: AxiosResponse) => {
    if (error.status === 401 || error.status === 403) {
        console.log('redirect login') // eslint-disable-line
        router.push({ path: '/login' })
    }
})

createApp(App)
    .component('font-awesome', FontAwesomeIcon)
    .use(createPinia())
    .use(router)
    .mount('#app')
