import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'

import LastErrors from '@/views/admin/LastErrors.vue'
import { useAdminStore } from '@/stores/admin'

function buildWrapper() {
  return mount(LastErrors, {
    global: { plugins: [createTestingPinia({ createSpy: vi.fn })] }
  })
}

describe('views/admin/LastErrors.vue', () => {
  it('renders empty headers with zero counts when no errors', () => {
    const wrapper = buildWrapper()
    expect(wrapper.text()).toContain('Routine2 (0)')
    expect(wrapper.text()).toContain('Routine3 (0)')
    expect(wrapper.findAll('li')).toHaveLength(0)
  })

  it('renders one li per error with date, message and url', async () => {
    const wrapper = buildWrapper()
    const store = useAdminStore()
    store.errors = {
      routine2: [{ date: '2024-06-15T12:00:00Z', error: 'boom', url: 'https://example.com/a' }],
      routine3: [
        {
          date: '2024-06-16T08:30:00Z',
          error: 'crash',
          url: 'https://example.com/b',
          path: '/lib/foo'
        },
        {
          date: '2024-06-17T09:00:00Z',
          error: 'oops',
          url: 'https://example.com/c',
          path: '/lib/bar'
        }
      ]
    }
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('Routine2 (1)')
    expect(wrapper.text()).toContain('Routine3 (2)')

    const items = wrapper.findAll('li')
    expect(items).toHaveLength(3)
    expect(items[0].text()).toContain('boom')
    expect(items[0].text()).toContain('https://example.com/a')
    expect(items[1].text()).toContain('crash')
    expect(items[1].text()).toContain('/lib/foo')
    expect(items[2].text()).toContain('oops')
    expect(items[2].text()).toContain('/lib/bar')
  })
})
