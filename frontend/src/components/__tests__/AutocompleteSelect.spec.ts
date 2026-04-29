import { mount } from '@vue/test-utils'
import AutocompleteSelect from '@/components/AutocompleteSelect.vue'

describe('components/AutocompleteSelect.vue', () => {
  beforeEach(() => {
    vi.useFakeTimers()
  })
  afterEach(() => {
    vi.useRealTimers()
  })

  it('emits search after the throttle delay when input length >= 3', async () => {
    const wrapper = mount(AutocompleteSelect, {
      props: {
        inputId: 'q',
        isLoading: false,
        modelValue: '',
        options: [],
        throttle: 100
      }
    })

    const input = wrapper.find('input')
    await input.setValue('foo')
    expect(wrapper.emitted('update:modelValue')).toBeTruthy()
    expect(wrapper.emitted('search')).toBeFalsy()

    await wrapper.setProps({ modelValue: 'foo' })
    vi.advanceTimersByTime(150)
    expect(wrapper.emitted('search')).toBeTruthy()
    expect(wrapper.emitted('search')![0]).toEqual(['foo'])
  })

  it('emits update:modelValue and select-option when an option is picked', async () => {
    const options = [
      { label: 'Alpha', value: 1 },
      { label: 'Beta', value: 2 }
    ]
    const wrapper = mount(AutocompleteSelect, {
      props: {
        inputId: 'q',
        isLoading: false,
        modelValue: 'a',
        options
      }
    })

    await wrapper.find('input').trigger('focus')
    const items = wrapper.findAll('li.option')
    expect(items).toHaveLength(2)
    await items[1].trigger('mousedown')

    expect(wrapper.emitted('update:modelValue')).toBeTruthy()
    expect(wrapper.emitted('update:modelValue')![0]).toEqual(['Beta'])
    expect(wrapper.emitted('select-option')![0]).toEqual([2])
  })
})
