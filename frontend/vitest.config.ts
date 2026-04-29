import { fileURLToPath } from 'node:url'
import { mergeConfig, defineConfig, coverageConfigDefaults } from 'vitest/config'
import viteConfig from './vite.config'

export default mergeConfig(
    viteConfig,
    defineConfig({
        test: {
            globals: true,
            environment: 'jsdom',
            root: fileURLToPath(new URL('./', import.meta.url)),
            coverage: {
                provider: 'v8',
                reporter: ['text', 'lcov'],
                include: [
                    'src/**/*.{ts,vue}'
                ],
                exclude: [
                    ...coverageConfigDefaults.exclude,
                    '*.config.ts',
                    'src/**/__tests__/**',
                ],
            },
        }
    })
)
