import assert from 'node:assert/strict'
import { readFileSync } from 'node:fs'
import { dirname, join } from 'node:path'
import { fileURLToPath } from 'node:url'
import { describe, it } from 'node:test'

const root = join(dirname(fileURLToPath(import.meta.url)), '../..')

describe('favicon head tags', () => {
  it('declares a PNG 48x48 icon when the CMS favicon is set', () => {
    const source = readFileSync(join(root, 'app/app.vue'), 'utf8')
    assert.match(source, /type:\s*'image\/png'/)
    assert.match(source, /sizes:\s*'48x48'/)
    assert.match(source, /image\/x-icon/)
  })
})
