import { mount, flushPromises } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import LoginPage from '@/views/LoginPage.vue'
import { useSecurityStore } from '@/stores/security'
import { Me } from '@/stores/security/types'

const pushMock = vi.fn()
vi.mock('vue-router', () => ({
  useRouter: () => ({ push: pushMock })
}))

function buildWrapper() {
  return mount(LoginPage, {
    global: {
      plugins: [createTestingPinia({ createSpy: vi.fn })]
    }
  })
}

describe('views/LoginPage.vue', () => {
  beforeEach(() => {
    pushMock.mockClear()
  })

  it('submits credentials, calls store.login, and redirects to admin when auth succeeds', async () => {
    const wrapper = buildWrapper()
    const store = useSecurityStore()
    store.login = vi.fn(async () => {
      const me = new Me()
      me.roles = ['ROLE_ADMIN']
      me.username = 'alice'
      store.me = me
    })

    await wrapper.find('input#username').setValue('alice')
    await wrapper.find('input#password').setValue('s3cret')
    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()

    expect(store.login).toHaveBeenCalledWith({ username: 'alice', password: 's3cret' })
    expect(pushMock).toHaveBeenCalledWith({ name: 'admin' })
  })

  it('does nothing when a login request is already loading', async () => {
    const wrapper = buildWrapper()
    const store = useSecurityStore()
    store.actionRequests.login.isLoading = true
    store.login = vi.fn()

    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()

    expect(store.login).not.toHaveBeenCalled()
    expect(pushMock).not.toHaveBeenCalled()
    expect((wrapper.get('input[type="submit"]').element as HTMLInputElement).disabled).toBe(true)
  })
})
