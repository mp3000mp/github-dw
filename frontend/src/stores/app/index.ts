import { defineStore } from 'pinia'
import { state } from './state'
import apiRegistry from '@/helpers/apiRegistry'
import { ApiClient } from '@/helpers/apiClient'
import {AxiosError} from 'axios'

// todo: define actions in separate file when thie issue is closed: https://github.com/vuejs/pinia/issues/802
export const useAppStore = defineStore('app', {
    state: () => state,
    actions: {
        async getInfo () {
            try {
                const response = await apiRegistry.get().httpReq(this.actionRequests.getInfo)
                this.version = response.data.version
            } catch (err) {
                return Promise.reject(ApiClient.generateErrorMessage(<AxiosError|Error>err))
            }
        }
    }
})
