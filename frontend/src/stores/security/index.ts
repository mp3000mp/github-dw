import { state } from './state'
import { defineStore } from 'pinia'
import apiRegistry from '@/helpers/apiRegistry'
import { Me } from '@/stores/security/types'

interface LoginPayload {
    username: string;
    password: string;
}

// todo: refactor localstorage functions in utils
function setMe (me: Me|null) {
    if (me === null) {
        sessionStorage.removeItem('me')
    }
    sessionStorage.setItem('me', JSON.stringify(me || (new Me())))
}

// todo: define actions in separate file when thie issue is closed: https://github.com/vuejs/pinia/issues/802
export const useSecurityStore = defineStore('security', {
    state: () => state,
    getters: {
        getIsAuth: (state) => !state.me.roles.includes('ROLE_ANONYMOUS'),
        getRoles: (state) => state.me.roles
    },
    actions: {
        async login (data: LoginPayload) {
            try {
                const response = await apiRegistry.get().httpReq(this.actionRequests.login, { data })
                this.me = response.data.me
            } catch (err) {
                this.me = new Me()
            } finally {
                setMe(this.me)
            }
        },
        async logout () {
            try {
                await apiRegistry.get().httpReq(this.actionRequests.logout)
            } finally {
                this.me = new Me()
                setMe(this.me)
            }
        },
        async getMe () {
            try {
                const response = await apiRegistry.get().httpReq(this.actionRequests.getMe)
                this.me = response.data
            } catch (err) {
                this.me = new Me()
            } finally {
                setMe(this.me)
            }
        }
    }
})
