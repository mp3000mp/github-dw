import { StoreRequest } from '@/stores/storeRequest'

describe('stores/storeRequest.ts', () => {
  it('initializes with default state', () => {
    const r = new StoreRequest('GET', '/foo')
    expect(r.method).toBe('GET')
    expect(r.url).toBe('/foo')
    expect(r.callCount).toBe(0)
    expect(r.isLoading).toBe(false)
    expect(r.isError).toBe(false)
    expect(r.message).toBe('')
    expect(r.status).toBeNull()
  })

  it('start() resets, increments callCount and flags loading', () => {
    const r = new StoreRequest('POST', '/foo')
    r.end(500, 'boom')
    r.start()
    expect(r.callCount).toBe(1)
    expect(r.isLoading).toBe(true)
    expect(r.isError).toBe(false)
    expect(r.message).toBe('')
    expect(r.status).toBe(0)

    r.start()
    expect(r.callCount).toBe(2)
  })

  it('end() with a 2xx status leaves isError false', () => {
    const r = new StoreRequest('GET', '/foo')
    r.start()
    r.end(204, 'done')
    expect(r.status).toBe(204)
    expect(r.message).toBe('done')
    expect(r.isLoading).toBe(false)
    expect(r.isError).toBe(false)
  })

  it.each([100, 199, 300, 404, 500])('end() with status %s flags isError', (status) => {
    const r = new StoreRequest('GET', '/foo')
    r.start()
    r.end(status, 'msg')
    expect(r.isError).toBe(true)
  })

  it('reset() clears transient state but not callCount', () => {
    const r = new StoreRequest('GET', '/foo')
    r.start()
    r.end(500, 'boom')
    r.reset()
    expect(r.status).toBe(0)
    expect(r.message).toBe('')
    expect(r.isLoading).toBe(false)
    expect(r.isError).toBe(false)
    expect(r.callCount).toBe(1)
  })
})
