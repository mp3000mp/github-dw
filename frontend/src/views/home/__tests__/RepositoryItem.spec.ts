import { mount } from '@vue/test-utils'

vi.mock('vue3-popper', () => ({
  default: { template: '<span><slot /></span>' }
}))

import RepositoryItem from '@/views/home/RepositoryItem.vue'
import type { Repository } from '@/stores/search/types'

const globalStubs = {
  'font-awesome': true
}

function makeRepo(overrides: Partial<Repository> = {}): Repository {
  return {
    id: 1,
    name: 'repo',
    username: 'user',
    mainLanguage: 'Go',
    url: 'https://github.com/user/repo',
    fullName: 'user/repo',
    description: 'a description',
    licenceName: 'MIT',
    forksCount: 4,
    openIssuesCount: 2,
    stargazersCount: 12,
    createdAt: '2024-01-01T00:00:00Z',
    pushedAt: '2024-06-15T12:00:00Z',
    topics: [
      { id: 1, topic: 'go' },
      { id: 2, topic: 'cli' }
    ],
    ...overrides
  }
}

describe('views/home/RepositoryItem.vue', () => {
  it('renders the repository name, counts, license and topics', () => {
    const wrapper = mount(RepositoryItem, {
      props: { repository: makeRepo() },
      global: { stubs: globalStubs }
    })

    const link = wrapper.find('a')
    expect(link.attributes('href')).toBe('https://github.com/user/repo')
    expect(link.text()).toBe('user/repo')

    const text = wrapper.text()
    expect(text).toContain('MIT')
    expect(text).toContain('2024-06-15')
    expect(text).toContain('12 stars')
    expect(text).toContain('4 forks')
    expect(text).toContain('2 issues')

    const badges = wrapper.findAll('.badge')
    expect(badges.map((b) => b.text())).toEqual(['go', 'cli'])
  })

  it('falls back to defaults when license is missing and counts are null', () => {
    const wrapper = mount(RepositoryItem, {
      props: {
        repository: makeRepo({
          licenceName: null as unknown as string,
          stargazersCount: null as unknown as number,
          forksCount: null as unknown as number,
          openIssuesCount: null as unknown as number,
          topics: []
        })
      },
      global: { stubs: globalStubs }
    })

    const text = wrapper.text()
    expect(text).toContain('Unknown')
    expect(text).toContain('0 stars')
    expect(text).toContain('0 forks')
    expect(text).toContain('0 issues')
    expect(wrapper.findAll('.badge')).toHaveLength(0)
  })
})
