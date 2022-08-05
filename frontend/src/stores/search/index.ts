import { defineStore } from 'pinia'
import { state } from './state'
import apiRegistry from '@/helpers/apiRegistry'
import { ApiClient } from '@/helpers/apiClient'
import {AxiosError} from 'axios'
import {Search} from '@/stores/search/types'

interface AutocompletePayload {
    language: string;
    text: string;
}

// todo: define actions in separate file when thie issue is closed: https://github.com/vuejs/pinia/issues/802
export const useSearchStore = defineStore('search', {
    state: () => state,
    actions: {
        async packageAutocomplete (payload: AutocompletePayload) {
            try {
                const response = await apiRegistry.get().httpReq(this.actionRequests.packageAutocomplete, {data: payload})
                this.packageOptions = response.data
            } catch (err) {
                return Promise.reject(ApiClient.generateErrorMessage(<AxiosError|Error>err))
            }
        },
        resetPackageOptions () {
            this.packageOptions = []
        },
        async search (search: Search) {
            try {
                const response = await apiRegistry.get().httpReq(this.actionRequests.search, {data: search})
                this.totalRepositories = response.data.total
                this.repositories = response.data.results
            } catch (err) {
                return Promise.reject(ApiClient.generateErrorMessage(<AxiosError|Error>err))
            }
        },
    }
})
