import { AdminState } from './types'
import { StoreRequest } from '@/stores/types'

const state = new AdminState()
state.actionRequests = {
    getAll: new StoreRequest('GET', '/api/package-type-files'),
    getStats: new StoreRequest('GET', '/api/admin/stats'),
    setPriority: new StoreRequest('PUT', '/api/package-type-files/{id}/priority'),
}
export { state }
