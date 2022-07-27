import { ApiClient } from '@/helpers/apiClient'
import {AxiosResponse} from 'axios'

export type CallbackOnError = (error: AxiosResponse) => void

class ApiClientRegistry {
    private registry: {
        [key: string]: ApiClient;
    } = {};

    set (api: string, baseURL: string, onError: CallbackOnError) {
        this.registry[api] = new ApiClient(baseURL, onError)
    }

    get (api = 'default'): ApiClient {
        return this.registry[api]
    }
}

const apiRegistry = new ApiClientRegistry()
export default apiRegistry
