import { defineStore } from 'pinia'
import { state } from './state'
import apiRegistry from '@/helpers/apiRegistry'

// todo: define actions in separate file when thie issue is closed: https://github.com/vuejs/pinia/issues/802
export const useUsersStore = defineStore('users', {
    state: () => state,
    actions: {
        async getAll () {
            this.users = await apiRegistry.get().httpReq(this.actionRequests.getAll)
        },
    }
})
