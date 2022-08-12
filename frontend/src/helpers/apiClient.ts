import { StoreRequest } from '@/stores/types'
import {CallbackOnError} from '@/helpers/apiRegistry'

interface ErrorResponseSchemas {
    message?: string;
    detail?: string;
}
interface ApiRequestConfig {
    headers?: {
        [key: string]: string;
    };
    data?: any; // eslint-disable-line
    urlParams?: {
        [key: string]: string;
    };
}

export class ApiClient {
    baseUrl: string;
    headers = {
        Accept: 'application/json',
        'Content-Type': 'application/json;charset=UTF-8',
    }
    onError: CallbackOnError;

    constructor (baseURL: string, onError: CallbackOnError) {
        this.baseUrl = baseURL
        this.onError = onError
    }

    private static generateUrl (url: string, urlParams: {[key: string]: string}): string {
        const regex = /{(.+?)}/g
        const matches = [...url.matchAll(regex)]
        for (const match of matches) {
            if (typeof urlParams[match[1]] !== 'undefined') {
                url = url.replace(`{${match[1]}}`, urlParams[match[1]])
            }
        }
        return url
    }

    public static generateErrorMessage (responseJson: ErrorResponseSchemas): string {
        return responseJson.message ||
            responseJson.detail ||
            'Unexpected error'
    }

    /**
     * if response status is different than 2xx, promise will be rejected with an error containing the response
     */
    async httpReq (request: StoreRequest, options: ApiRequestConfig = {}): Promise<any> { // eslint-disable-line
        const url = this.baseUrl + ApiClient.generateUrl(request.url, options.urlParams || {})
        const config = {
            body: options.data ? JSON.stringify(options.data) : null,
            credentials: 'include' as RequestCredentials,
            headers: {...this.headers, ...options.headers || {}},
            method: request.method,
        }

        request.start()
        let errorMsg = ''
        try {
            const response = await fetch(url, config)
            const responseJson = await response.json()

            if (!response.ok || response.status < 200 || response.status >= 300) {
                errorMsg = ApiClient.generateErrorMessage(responseJson)
                await this.onError(response.status)
                request.end(response.status, errorMsg)
                throw new Error(errorMsg)
            }

            request.end(response.status, responseJson.message || '')
            return responseJson
        } catch (err) {
            if (errorMsg === '') {
                errorMsg = String(err)
                await this.onError(500)
                request.end(500, errorMsg)
            }
            throw err
        }
    }
}
