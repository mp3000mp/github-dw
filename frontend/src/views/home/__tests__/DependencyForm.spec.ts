import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'

vi.mock('vue3-popper', () => ({
  default: { template: '<span><slot /></span>' }
}))

import DependencyForm from '@/views/home/DependencyForm.vue'
import { useSearchStore } from '@/stores/search'
import type { Dependency } from '@/stores/search/types'

const childStubs = {
  AutocompleteSelect: {
    name: 'AutocompleteSelect',
    props: ['modelValue', 'disabled', 'options'],
    emits: ['select-option', 'search', 'reset-options', 'update:modelValue'],
    template: '<div class="autocomplete-stub" />'
  },
  'font-awesome': true
}

function buildWrapper(dependencies: Dependency[] = []) {
  return mount(DependencyForm, {
    props: { dependencies },
    global: {
      plugins: [createTestingPinia({ createSpy: vi.fn })],
      stubs: childStubs
    }
  })
}

describe('views/home/DependencyForm.vue', () => {
  it('emits add with the selected dependency payload and resets fields', async () => {
    const wrapper = buildWrapper()
    const store = useSearchStore()
    store.packageOptions = [{ id: 99, name: 'cobra' }]

    await wrapper.find('select').setValue('Go')

    const autocomplete = wrapper.findComponent({ name: 'AutocompleteSelect' })
    autocomplete.vm.$emit('select-option', 99)
    await wrapper.vm.$nextTick()

    await wrapper.get('input#min-version').setValue('1.0.0')
    await wrapper.get('input#max-version').setValue('2.0.0')

    await wrapper.get('input[type="submit"]').trigger('click')

    expect(wrapper.emitted('add')).toBeTruthy()
    expect(wrapper.emitted('add')![0]).toEqual([
      { language: 'Go', name: 'cobra', id: 99, minVersion: '1.0.0', maxVersion: '2.0.0' }
    ])
  })

  it('disables the add button and shows a warning when 4 dependencies are already selected', () => {
    const deps: Dependency[] = Array.from({ length: 4 }, (_, i) => ({
      idx: i,
      language: 'Go',
      name: `pkg${i}`,
      id: i + 1,
      minVersion: null,
      maxVersion: null
    }))
    const wrapper = buildWrapper(deps)

    const submit = wrapper.get('input[type="submit"]')
    expect(submit.attributes('disabled')).toBeDefined()
    expect(wrapper.text()).toContain('You cannot search for more than 4 dependencies.')
  })
})
