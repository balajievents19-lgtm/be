import { describe, it } from 'node:test'
import assert from 'node:assert/strict'
import { isSafeEmbedUrl, isSafeOpenUrl } from '../../app/utils/externalMedia.ts'

describe('externalMedia utils', () => {
  it('allows only known embed hosts', () => {
    assert.equal(isSafeEmbedUrl('https://www.youtube-nocookie.com/embed/abc'), true)
    assert.equal(isSafeEmbedUrl('https://player.vimeo.com/video/123'), true)
    assert.equal(isSafeEmbedUrl('https://evil.example/embed'), false)
    assert.equal(isSafeEmbedUrl('javascript:alert(1)'), false)
  })

  it('allows http(s) open links only', () => {
    assert.equal(isSafeOpenUrl('https://instagram.com/p/x'), true)
    assert.equal(isSafeOpenUrl('javascript:alert(1)'), false)
    assert.equal(isSafeOpenUrl('https://www.youtube.com/watch?v=dQw4w9WgXcQ'), false)
    assert.equal(isSafeOpenUrl('https://youtu.be/dQw4w9WgXcQ'), false)
  })
})
