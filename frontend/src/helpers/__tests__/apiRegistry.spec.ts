import apiRegistry from '@/helpers/apiRegistry'
import { ApiClient } from '@/helpers/apiClient'

describe('helpers/apiRegistry.ts', () => {
  it('stores and retrieves an ApiClient by key', () => {
    const onError = vi.fn()
    apiRegistry.set('foo', 'http://example.test', onError)

    const client = apiRegistry.get('foo')
    expect(client).toBeInstanceOf(ApiClient)
    expect(client.baseUrl).toBe('http://example.test')
    expect(client.onError).toBe(onError)
  })

  it('defaults to the "default" key when no api name is given', () => {
    const onError = vi.fn()
    apiRegistry.set('default', 'http://default.test', onError)

    const client = apiRegistry.get()
    expect(client).toBeInstanceOf(ApiClient)
    expect(client.baseUrl).toBe('http://default.test')
  })
})
