import {formatVersion, validVersion, validVersions} from '@/utils/version'

describe('utils/version.ts', () => {
    it.each<[string|null, string|null]>([
        [null, null],
        ['', null],
        ['1', '1.0.0'],
        ['1.', '1.0.0'],
        ['1.2', '1.2.0'],
        ['1.2.', '1.2.0'],
        ['1.2.3', '1.2.3'],
    ])('function formatVersion with input %s', (input, expected) => {
        expect(formatVersion(input)).toEqual(expected)
    })

    it.each<string|null>([
        null,
        '',
        '0.1',
        '1',
        '1.',
        '1.2',
        '1.2.',
        '1.2.3',
        ])('function validVersion truthy with input %s', (input) => {
        expect(validVersion(input)).toBeTruthy()
    })
    it.each<string>([
        '0',
        '.1',
        'a',
        '1.a',
    ])('function validVersion falsy with input %s', (input) => {
        expect(validVersion(input)).toBeFalsy()
    })

    it.each<[string|null, string|null]>([
        [null, null],
        ['1', null],
        [null, '1.0.0'],
        ['1', '2'],
    ])('function validVersions truthy with input %s - %s', (minVersion, maxVersion) => {
        expect(validVersions(minVersion, maxVersion)).toBeTruthy()
    })
    it.each<[string|null, string|null]>([
        ['2', '1'],
        ['1.2', '1.1'],
        ['1.2.3', '1.2.2'],
    ])('function validVersions falsy with input %s - %s', (minVersion, maxVersion) => {
        expect(validVersions(minVersion, maxVersion)).toBeFalsy()
    })
})
