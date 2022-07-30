import { defineStore } from 'pinia'
import { state } from './state'
import apiRegistry from '@/helpers/apiRegistry'
import { ApiClient } from '@/helpers/apiClient'
import {AxiosError} from 'axios'

// todo: define actions in separate file when thie issue is closed: https://github.com/vuejs/pinia/issues/802
export const useAdminStore = defineStore('admin', {
    state: () => state,
    actions: {
        async getAll () {
            try {
                const response = await apiRegistry.get().httpReq(this.actionRequests.getAll)
                this.packageTypeFiles = response.data
            } catch (err) {
                return Promise.reject(ApiClient.generateErrorMessage(<AxiosError|Error>err))
            }
        },
        async getStats () {
            try {
                const response = await apiRegistry.get().httpReq(this.actionRequests.getStats)
                this.stats = response.data
            } catch (err) {
                return Promise.reject(ApiClient.generateErrorMessage(<AxiosError|Error>err))
            }
        },
        async setPriority (id: number) {
            try {
                const response = await apiRegistry.get().httpReq(this.actionRequests.setPriority, {urlParams: {id: String(id)}})
                this.packageTypeFiles = response.data
            } catch (err) {
                return Promise.reject(ApiClient.generateErrorMessage(<AxiosError|Error>err))
            }
        }
    }
})
