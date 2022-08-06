import { defineStore } from 'pinia'
import { state } from './state'
import apiRegistry from '@/helpers/apiRegistry'
import {SearchQuery} from '@/stores/search/types'

interface AutocompletePayload {
    language: string;
    text: string;
}

// todo: define actions in separate file when thie issue is closed: https://github.com/vuejs/pinia/issues/802
export const useSearchStore = defineStore('search', {
    state: () => state,
    actions: {
        async packageAutocomplete (payload: AutocompletePayload) {
            this.packageOptions = await apiRegistry.get().httpReq(this.actionRequests.packageAutocomplete, {data: payload})
        },
        resetPackageOptions () {
            this.packageOptions = []
        },
        async search (search: SearchQuery) {
            const responseJson = await apiRegistry.get().httpReq(this.actionRequests.search, {data: search})
            this.totalRepositories = responseJson.total
            this.repositories = responseJson.results
        },
    }
})
