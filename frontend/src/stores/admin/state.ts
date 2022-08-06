import { AdminState } from './types'
import { StoreRequest } from '@/stores/types'

const state = new AdminState()
state.actionRequests = {
    getAll: new StoreRequest('GET', '/api/package-type-files'),
    getErrors: new StoreRequest('GET', '/api/admin/errors'),
    getStats: new StoreRequest('GET', '/api/admin/stats'),
    getTimeline: new StoreRequest('GET', '/api/admin/timeline'),
    setPriority: new StoreRequest('PUT', '/api/package-type-files/{id}/priority'),
}
export { state }
