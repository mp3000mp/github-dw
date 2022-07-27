import { AppState } from './types'
import { StoreRequest } from '@/stores/types'

const state = new AppState()
state.actionRequest = {
    getInfo: new StoreRequest('GET', '/api/info')
}
export { state }
