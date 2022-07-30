import { UserState } from './types'
import { StoreRequest } from '@/stores/types'

const state = new UserState()
state.actionRequests = {
    getAll: new StoreRequest('GET', '/api/users'),
}
export { state }
