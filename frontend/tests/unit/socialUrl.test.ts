import assert from 'node:assert/strict'
import { describe, it } from 'node:test'
import { isPublicProfileUrl } from '../../app/utils/socialUrl.ts'

describe('isPublicProfileUrl', () => {
  it('rejects generic social homepages', () => {
    assert.equal(isPublicProfileUrl('https://facebook.com/'), false)
    assert.equal(isPublicProfileUrl('https://www.linkedin.com'), false)
    assert.equal(isPublicProfileUrl('https://x.com/'), false)
  })

  it('accepts real profile paths', () => {
    assert.equal(isPublicProfileUrl('https://www.instagram.com/balajieventjjn/'), true)
    assert.equal(isPublicProfileUrl('https://www.youtube.com/@balajieventjjn'), true)
  })
})
