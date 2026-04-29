import { mount, flushPromises } from '@vue/test-utils'

vi.mock('vue3-popper', () => ({
  default: { template: '<span><slot /></span>' }
}))

import SelectedDependency from '@/views/home/SelectedDependency.vue'
import type { Dependency } from '@/stores/search/types'

const globalStubs = { 'font-awesome': true }

function makeDep(overrides: Partial<Dependency> = {}): Dependency {
  return {
    idx: 0,
    language: 'Go',
    name: 'cobra',
    id: 42,
    minVersion: '1.0.0',
    maxVersion: '2.0.0',
    ...overrides
  }
}

describe('views/home/SelectedDependency.vue', () => {
  it('emits update-versions on blur after editing the min version with a valid value', async () => {
    const wrapper = mount(SelectedDependency, {
      props: { dependency: makeDep() },
      global: { stubs: globalStubs }
    })

    const minSpan = wrapper.findAll('span.cp').find((s) => s.text() === '1.0.0')!
    await minSpan.trigger('click')

    const input = wrapper.get('input#min-version')
    await input.setValue('1.5.0')
    await input.trigger('blur')
    await flushPromises()

    expect(wrapper.emitted('update-versions')).toBeTruthy()
    expect(wrapper.emitted('update-versions')![0]).toEqual([
      { id: 42, minVersion: '1.5.0', maxVersion: '2.0.0' }
    ])
  })

  it('emits remove with the dependency id when the trash icon is clicked', async () => {
    const wrapper = mount(SelectedDependency, {
      props: { dependency: makeDep({ id: 7 }) },
      global: { stubs: globalStubs }
    })

    await wrapper.get('font-awesome-stub').trigger('click')

    expect(wrapper.emitted('remove')).toBeTruthy()
    expect(wrapper.emitted('remove')![0]).toEqual([7])
  })
})
