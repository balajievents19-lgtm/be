import assert from 'node:assert/strict'
import { describe, it } from 'node:test'
import { isSafeGalleryEmbedUrl, isSafeGalleryOpenUrl } from '../../app/utils/galleryVideo.ts'

describe('isSafeGalleryEmbedUrl', () => {
  it('allows known embed hosts and rejects arbitrary pages', () => {
    assert.equal(isSafeGalleryEmbedUrl('https://www.youtube-nocookie.com/embed/abc'), true)
    assert.equal(isSafeGalleryEmbedUrl('https://www.instagram.com/reel/AbC/embed/'), true)
    assert.equal(isSafeGalleryEmbedUrl('https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Ffacebook.com%2Fwatch'), true)
    assert.equal(isSafeGalleryEmbedUrl('https://example.com/video.mp4'), false)
    assert.equal(isSafeGalleryEmbedUrl('https://www.instagram.com/reel/AbC/'), false)
    assert.equal(isSafeGalleryOpenUrl('https://example.com/our-film'), true)
    assert.equal(isSafeGalleryOpenUrl('javascript:alert(1)'), false)
  })
})
