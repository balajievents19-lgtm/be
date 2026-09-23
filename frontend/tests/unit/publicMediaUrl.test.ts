import assert from 'node:assert/strict'
import { describe, it } from 'node:test'
import { rewritePublicStorageUrls, toAbsoluteSeoMediaUrl, toPublicMediaUrl } from '../../app/utils/publicMediaUrl.ts'

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
    assert.equal(toPublicMediaUrl('/protected-media/opaque-token'), '/protected-media/opaque-token')
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

describe('toAbsoluteSeoMediaUrl', () => {
  it('prefixes origin-relative storage paths for OG and JSON-LD', () => {
    assert.equal(
      toAbsoluteSeoMediaUrl(
        '/protected-media/opaque-token',
        'https://www.balajiroyalevents.com'
      ),
      'https://www.balajiroyalevents.com/protected-media/opaque-token'
    )
    assert.equal(
      toAbsoluteSeoMediaUrl(
        '/storage/settings/brand/logo.png',
        'https://www.balajiroyalevents.com'
      ),
      'https://www.balajiroyalevents.com/storage/settings/brand/logo.png'
    )
    assert.equal(
      toAbsoluteSeoMediaUrl(
        'https://www.balajiroyalevents.com/storage/settings/brand/logo.png',
        'https://www.balajiroyalevents.com'
      ),
      'https://www.balajiroyalevents.com/storage/settings/brand/logo.png'
    )
  })
})

describe('rewritePublicStorageUrls', () => {
  it('rewrites nested storage fields without touching slugs or embeds', () => {
    const rewritten = rewritePublicStorageUrls({
      slug: 'wedding-planning',
      desktop_image: 'https://www.balajiroyalevents.com/storage/hero-slides/desktop/slider-img.jpg',
      embed_url: 'https://www.youtube-nocookie.com/embed/abc'
    }) as { slug: string, desktop_image: string, embed_url: string }
    assert.equal(rewritten.slug, 'wedding-planning')
    assert.equal(rewritten.desktop_image, '/storage/hero-slides/desktop/slider-img.jpg')
    assert.equal(rewritten.embed_url, 'https://www.youtube-nocookie.com/embed/abc')
  })

  it('leaves Blob download bodies unchanged', () => {
    const blob = new Blob([new Uint8Array([255, 216, 255])], { type: 'image/jpeg' })
    assert.equal(rewritePublicStorageUrls(blob), blob)
  })
})
