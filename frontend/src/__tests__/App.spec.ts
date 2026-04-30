import { mount, flushPromises, RouterLinkStub } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'

const pushMock = vi.fn()
vi.mock('vue-router', () => ({
  useRouter: () => ({ push: pushMock })
}))

import App from '@/App.vue'
import { useSecurityStore } from '@/stores/security'
import { Me } from '@/stores/security/types'

const globalStubs = {
  RouterLink: RouterLinkStub,
  RouterView: { template: '<div class="router-view-stub" />' },
  'font-awesome': true
}

describe('App.vue', () => {
  beforeEach(() => {
    pushMock.mockClear()
  })

  it('logs out and redirects home when the authenticated user clicks logout', async () => {
    const wrapper = mount(App, {
      global: {
        plugins: [createTestingPinia({ createSpy: vi.fn })],
        stubs: globalStubs
      }
    })

    const store = useSecurityStore()
    const me = new Me()
    me.roles = ['ROLE_ADMIN']
    me.username = 'alice'
    store.me = me
    await wrapper.vm.$nextTick()

    await wrapper.get('font-awesome-stub').trigger('click')
    await flushPromises()

    expect(store.logout).toHaveBeenCalledTimes(1)
    expect(pushMock).toHaveBeenCalledWith({ name: 'home' })
  })
})
