import { Me, SecurityState } from './types'
import { StoreRequest } from '@/stores/types'

function initMe (): Me {
    const json = localStorage.getItem('me')
    if (json === null) {
        return new Me()
    }
    return JSON.parse(json)
}

const state = new SecurityState()
state.me = initMe()
state.actionRequest = {
    getMe: new StoreRequest('GET', '/api/me'),
    login: new StoreRequest('POST', '/api/logincheck', false),
}
export { state }
