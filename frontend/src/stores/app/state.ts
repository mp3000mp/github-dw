import { AppState } from './types'
import { StoreRequest } from '@/stores/storeRequest'

const state = new AppState()
state.actionRequests = {
  getInfo: new StoreRequest('GET', '/api/info')
}
export { state }
