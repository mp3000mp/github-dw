export function formatVersion(version: string | null): string | null {
  if (version === '' || version === null) {
    return null
  }
  if (version.substring(version.length - 1) === '.') {
    version += '0'
  }
  const tmp = version.split('.')
  while (tmp.length < 3) {
    tmp.push('0')
  }
  return tmp.join('.')
}

export function validVersion(version: string | null): boolean {
  const reg = /^[0-9]\d{0,4}(\.|(\.[0-9]\d{0,4})?(\.|(\.[0-9]\d{0,4}))?)$/
  const notReg = /^0{1,5}(\.|(\.0{1,5})?(\.|(\.0{1,5}))?)$/
  if (version === null || version === '') {
    return true
  }
  return version.match(reg) !== null && version.match(notReg) === null
}

function checkRange(minVersion: string | null, maxVersion: string | null): boolean {
  minVersion = formatVersion(minVersion)
  maxVersion = formatVersion(maxVersion)
  if (minVersion === null || maxVersion === null) {
    return true
  }
  const arrMin = minVersion.split('.').map((v) => Number(v))
  const arrMax = maxVersion.split('.').map((v) => Number(v))
  if (arrMin[0] < arrMax[0]) {
    return true
  }
  if (arrMin[0] === arrMax[0]) {
    if (arrMin[1] < arrMax[1]) {
      return true
    }
    if (arrMin[1] === arrMax[1]) {
      if (arrMin[2] < arrMax[2]) {
        return true
      }
    }
  }
  return false
}

export function validVersions(minVersion: string | null, maxVersion: string | null): boolean {
  if (!validVersion(minVersion) || !validVersion(maxVersion)) {
    return false
  }
  return checkRange(minVersion, maxVersion)
}
