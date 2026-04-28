import { SearchState } from './types'
import { StoreRequest } from '@/stores/types'

const state = new SearchState()
state.actionRequests = {
  search: new StoreRequest('POST', '/api/repositories/search'),
  packageAutocomplete: new StoreRequest('POST', '/api/packages/autocomplete')
}
export { state }
