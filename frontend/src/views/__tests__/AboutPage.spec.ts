import { mount } from '@vue/test-utils'
import AboutPage from '@/views/AboutPage.vue'

const globalStubs = { 'router-link': { template: '<a><slot /></a>' } }

describe('views/AboutPage.vue', () => {
  it('initially shows the placeholder span and hides the mailto link', () => {
    const wrapper = mount(AboutPage, { global: { stubs: globalStubs } })

    const trigger = wrapper.find('span.btn.cp')
    const mailto = wrapper.find('a[href="mailto:moussadedijon@gmail.com"]')

    expect(trigger.text()).toBe('my email')
    expect(trigger.classes()).toContain('shown')
    expect(mailto.classes()).toContain('hidden')
  })

  it('reveals the email address when the placeholder span is clicked', async () => {
    const wrapper = mount(AboutPage, { global: { stubs: globalStubs } })

    await wrapper.find('span.btn.cp').trigger('click')

    const trigger = wrapper.find('span.btn.cp')
    const mailto = wrapper.find('a[href="mailto:moussadedijon@gmail.com"]')

    expect(trigger.classes()).toContain('hidden')
    expect(mailto.classes()).toContain('shown')
    expect(mailto.text()).toBe('moussadedijon@gmail.com')
  })
})
