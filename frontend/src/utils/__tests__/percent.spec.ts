import { percent } from '@/utils/percent'

describe('utils/percent.ts', () => {
  it.each<[number, number, string]>([
    [0, 100, '0%'],
    [50, 100, '50%'],
    [100, 100, '100%'],
    [1, 3, '33.33%'],
    [2, 3, '66.67%'],
    [25, 200, '12.5%']
  ])('percent(%i, %i) returns %s', (part, total, expected) => {
    expect(percent(part, total)).toBe(expected)
  })

  it('returns "NA" when total is zero', () => {
    expect(percent(0, 0)).toBe('NA')
    expect(percent(5, 0)).toBe('NA')
  })
})
