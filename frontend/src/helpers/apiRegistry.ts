import { ApiClient } from '@/helpers/apiClient'

export type CallbackOnError = (status: number) => void

class ApiClientRegistry {
  private registry: {
    [key: string]: ApiClient
  } = {}

  set(api: string, baseURL: string, onError: CallbackOnError) {
    this.registry[api] = new ApiClient(baseURL, onError)
  }

  get(api = 'default'): ApiClient {
    return this.registry[api]
  }
}

const apiRegistry = new ApiClientRegistry()
export default apiRegistry
