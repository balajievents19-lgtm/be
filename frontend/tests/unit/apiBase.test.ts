import assert from 'node:assert/strict'
import { describe, it } from 'node:test'
import { isLoopbackApiUrl, resolveApiBase, resolveLaravelWebOrigin } from '../../app/utils/apiBase.ts'

describe('resolveApiBase', () => {
  it('uses internal Laravel loopback on the server in production', () => {
    assert.equal(
      resolveApiBase({
        isServer: true,
        isDev: false,
        publicBase: '/api',
        internalBase: 'http://127.0.0.1:8000/api'
      }),
      'http://127.0.0.1:8000/api'
    )
  })

  it('never exposes loopback Laravel to the production browser', () => {
    assert.equal(
      resolveApiBase({
        isServer: false,
        isDev: false,
        publicBase: 'http://127.0.0.1:8000/api',
        internalBase: 'http://127.0.0.1:8000/api'
      }),
      '/api'
    )
    assert.equal(
      resolveApiBase({
        isServer: false,
        isDev: false,
        publicBase: 'https://www.balajiroyalevents.com/api',
        internalBase: 'http://127.0.0.1:8000/api'
      }),
      '/api'
    )
  })

  it('keeps localhost Laravel for local nuxt dev', () => {
    assert.equal(
      resolveApiBase({
        isServer: false,
        isDev: true,
        publicBase: 'http://localhost:8000/api',
        internalBase: ''
      }),
      'http://localhost:8000/api'
    )
  })
})

describe('resolveLaravelWebOrigin', () => {
  it('strips /api from absolute origins and treats /api as same-origin', () => {
    assert.equal(resolveLaravelWebOrigin('http://127.0.0.1:8000/api'), 'http://127.0.0.1:8000')
    assert.equal(resolveLaravelWebOrigin('/api'), '')
  })
})

describe('isLoopbackApiUrl', () => {
  it('detects loopback API hosts', () => {
    assert.equal(isLoopbackApiUrl('http://127.0.0.1:8000/api'), true)
    assert.equal(isLoopbackApiUrl('https://www.balajiroyalevents.com/api'), false)
  })
})
