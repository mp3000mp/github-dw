import { Me, SecurityState } from './types'
import { StoreRequest } from '@/stores/types'

function initMe(): Me {
  const json = sessionStorage.getItem('me')
  if (json === null) {
    return new Me()
  }
  return JSON.parse(json)
}

const state = new SecurityState()
state.me = initMe()
state.actionRequests = {
  getMe: new StoreRequest('GET', '/api/me'),
  login: new StoreRequest('POST', '/api/login'),
  logout: new StoreRequest('GET', '/api/logout')
}
export { state }
