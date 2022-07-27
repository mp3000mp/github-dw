import { UserState } from './types'
import { StoreRequest } from '@/stores/types'

const state = new UserState()
state.actionRequest = {
    getAll: new StoreRequest('GET', '/api/users'),
}
export { state }
