import assert from 'node:assert/strict'
import { describe, it } from 'node:test'
import { sanitizeCmsHtml, sanitizeMapEmbed } from '../../app/utils/sanitizeHtml.ts'

describe('sanitizeCmsHtml', () => {
  it('strips script tags', () => {
    const out = sanitizeCmsHtml('<p>Hi</p><script>alert(1)</script>')
    assert.equal(out.includes('<script'), false)
    assert.equal(out.includes('alert'), false)
    assert.match(out, /Hi/)
  })

  it('strips img onerror', () => {
    const out = sanitizeCmsHtml('<img src=x onerror=alert(1)>')
    assert.equal(out.toLowerCase().includes('onerror'), false)
    assert.equal(out.toLowerCase().includes('alert'), false)
  })

  it('removes javascript: links', () => {
    const out = sanitizeCmsHtml('<a href="javascript:alert(1)">bad</a>')
    assert.equal(out.toLowerCase().includes('javascript:'), false)
    assert.match(out, /bad/)
  })

  it('strips onclick handlers', () => {
    const out = sanitizeCmsHtml('<div onclick="alert(1)">bad</div>')
    assert.equal(out.toLowerCase().includes('onclick'), false)
    assert.equal(out.toLowerCase().includes('alert'), false)
    assert.match(out, /bad/)
  })

  it('preserves safe formatting and https links', () => {
    const out = sanitizeCmsHtml(
      '<p>Hello</p><strong>Wedding</strong><ul><li>Service</li></ul><a href="https://example.com">Safe link</a>'
    )
    assert.match(out, /<p>Hello<\/p>/)
    assert.match(out, /<strong>Wedding<\/strong>/)
    assert.match(out, /<ul><li>Service<\/li><\/ul>/)
    assert.match(out, /href="https:\/\/example\.com"/)
    assert.match(out, /Safe link/)
  })
})

describe('sanitizeMapEmbed', () => {
  it('builds iframe from allowed maps URL', () => {
    const out = sanitizeMapEmbed('https://www.google.com/maps/embed?pb=test')
    assert.match(out, /^<iframe\b/i)
    assert.match(out, /src="https:\/\/www\.google\.com\/maps\/embed\?pb=test"/)
    assert.equal(out.includes('<script'), false)
  })

  it('rejects javascript map URLs', () => {
    assert.equal(sanitizeMapEmbed('javascript:alert(1)'), '')
  })

  it('rejects non-map iframe hosts and scripts', () => {
    const dirty = '<iframe src="https://evil.example/x"></iframe><script>alert(1)</script>'
    assert.equal(sanitizeMapEmbed(dirty), '')
  })

  it('keeps google maps iframe src only', () => {
    const dirty = '<iframe src="https://maps.google.com/maps?q=jaipur" onclick="alert(1)"></iframe>'
    const out = sanitizeMapEmbed(dirty)
    assert.match(out, /src="https:\/\/maps\.google\.com\/maps\?q=jaipur"/)
    assert.equal(out.toLowerCase().includes('onclick'), false)
    assert.equal(out.includes('<script'), false)
  })
})
