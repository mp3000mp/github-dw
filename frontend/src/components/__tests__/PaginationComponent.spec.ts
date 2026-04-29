import { mount } from '@vue/test-utils'
import PaginationComponent from '@/components/PaginationComponent.vue'

describe('components/PaginationComponent.vue', () => {
  it('renders ellipsis and edge links when current page is in the middle', () => {
    const wrapper = mount(PaginationComponent, {
      props: { currentPage: 10, maxPage: 20, displayedLinks: 2 }
    })
    const text = wrapper.text()
    expect(text).toContain('1')
    expect(text).toContain('20')
    expect(text.match(/\.\.\./g)?.length).toBe(2)
    for (const page of [8, 9, 10, 11, 12]) {
      expect(text).toContain(String(page))
    }
  })

  it('emits select-page when clicking a different page but not the current one', async () => {
    const wrapper = mount(PaginationComponent, {
      props: { currentPage: 3, maxPage: 5, displayedLinks: 2 }
    })
    const spans = wrapper.findAll('span.link')
    await spans[0].trigger('click')
    expect(wrapper.emitted('select-page')).toBeTruthy()
    expect(wrapper.emitted('select-page')!.length).toBe(1)

    const currentPageSpan = wrapper
      .findAll('span')
      .find((s) => s.text() === '3' && !s.classes('link'))
    await currentPageSpan!.trigger('click')
    expect(wrapper.emitted('select-page')!.length).toBe(1)
  })
})
