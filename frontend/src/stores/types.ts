type Method = 'GET' | 'POST' | 'DELETE' | 'PUT' | 'PATCH'

export class StoreRequest {
  callCount = 0
  isError = false
  isLoading = false
  message = ''
  method: Method = 'GET'
  status: number | null = null

  url: string

  constructor(method: Method, url: string) {
    this.method = method
    this.url = url
  }

  public reset() {
    this.status = 0
    this.message = ''
    this.isLoading = false
    this.isError = false
  }

  public start() {
    this.reset()
    this.callCount++
    this.isLoading = true
  }

  public end(status: number, message: string) {
    this.isLoading = false
    this.status = status
    this.message = message
    this.isError = status < 200 || status >= 300
  }
}

export abstract class AbstractState {
  actionRequests: {
    [key: string]: StoreRequest
  } = {}
}
