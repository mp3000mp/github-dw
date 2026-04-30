export function percent(part: number, total: number): string {
  if (total === 0) {
    return 'NA'
  }
  return Math.round((part / total) * 100 * 100) / 100 + '%'
}
