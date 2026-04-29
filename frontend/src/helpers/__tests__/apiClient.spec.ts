import { ApiClient } from '@/helpers/apiClient'
import { StoreRequest } from '@/stores/storeRequest'

describe('helpers/apiClient.ts', () => {
  afterEach(() => {
    vi.restoreAllMocks()
  })

  it('resolves with parsed json on a successful request and updates the StoreRequest', async () => {
    const fetchMock = vi.fn().mockResolvedValue({
      ok: true,
      status: 200,
      json: async () => ({ message: 'ok', value: 42 })
    })
    vi.stubGlobal('fetch', fetchMock)

    const onError = vi.fn()
    const client = new ApiClient('http://api.test', onError)
    const request = new StoreRequest('GET', '/items/{id}')

    const result = await client.httpReq<{ value: number }>(request, {
      urlParams: { id: '7' }
    })

    expect(result).toEqual({ message: 'ok', value: 42 })
    expect(fetchMock).toHaveBeenCalledWith(
      'http://api.test/items/7',
      expect.objectContaining({ method: 'GET', credentials: 'include' })
    )
    expect(request.status).toBe(200)
    expect(request.isError).toBe(false)
    expect(request.isLoading).toBe(false)
    expect(onError).not.toHaveBeenCalled()
  })

  it('rejects, calls onError and flags the StoreRequest as errored on non-2xx', async () => {
    const fetchMock = vi.fn().mockResolvedValue({
      ok: false,
      status: 404,
      json: async () => ({ message: 'not found' })
    })
    vi.stubGlobal('fetch', fetchMock)

    const onError = vi.fn()
    const client = new ApiClient('http://api.test', onError)
    const request = new StoreRequest('POST', '/things')

    await expect(client.httpReq(request, { data: { a: 1 } })).rejects.toThrow('not found')
    expect(onError).toHaveBeenCalledWith(404)
    expect(request.status).toBe(404)
    expect(request.isError).toBe(true)
    expect(request.message).toBe('not found')
  })
})
