import assert from 'node:assert/strict'
import { describe, it } from 'node:test'
import { rewritePublicStorageUrls, toPublicMediaUrl } from '../../app/utils/publicMediaUrl.ts'

describe('toPublicMediaUrl', () => {
  it('rewrites APP_URL storage links to same-origin paths', () => {
    assert.equal(
      toPublicMediaUrl('https://www.balajiroyalevents.com/storage/hero-slides/desktop/slider-img.jpg'),
      '/storage/hero-slides/desktop/slider-img.jpg'
    )
    assert.equal(
      toPublicMediaUrl('http://127.0.0.1:8000/storage/services/featured/photo.jpg?v=2'),
      '/storage/services/featured/photo.jpg?v=2'
    )
  })

  it('keeps relative site assets and external media URLs', () => {
    assert.equal(toPublicMediaUrl('/storage/logo.png'), '/storage/logo.png')
    assert.equal(toPublicMediaUrl('/images/heading-blackBgimg.png'), '/images/heading-blackBgimg.png')
    assert.equal(
      toPublicMediaUrl('https://www.youtube.com/watch?v=abc'),
      'https://www.youtube.com/watch?v=abc'
    )
    assert.equal(
      toPublicMediaUrl('https://www.instagram.com/p/xyz/'),
      'https://www.instagram.com/p/xyz/'
    )
  })
})

describe('rewritePublicStorageUrls', () => {
  it('rewrites nested storage fields without touching slugs or embeds', () => {
    const rewritten = rewritePublicStorageUrls({
      slug: 'wedding-planning',
      desktop_image: 'https://www.balajiroyalevents.com/storage/hero-slides/desktop/slider-img.jpg',
      embed_url: 'https://www.youtube-nocookie.com/embed/abc'
    })

    assert.deepEqual(rewritten, {
      slug: 'wedding-planning',
      desktop_image: '/storage/hero-slides/desktop/slider-img.jpg',
      embed_url: 'https://www.youtube-nocookie.com/embed/abc'
    })
  })
})
