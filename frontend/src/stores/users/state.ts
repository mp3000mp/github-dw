import { UserState } from './types'
import { StoreRequest } from '@/stores/storeRequest'

const state = new UserState()
state.actionRequests = {
  getAll: new StoreRequest('GET', '/api/users')
}
export { state }
