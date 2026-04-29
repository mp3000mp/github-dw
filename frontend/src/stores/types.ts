import type { StoreRequest } from '@/stores/storeRequest'

export abstract class AbstractState {
  actionRequests: {
    [key: string]: StoreRequest
  } = {}
}
