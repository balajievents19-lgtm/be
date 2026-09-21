import { describe, it } from 'node:test'
import assert from 'node:assert/strict'
import { resolveServiceIcon } from '../../app/utils/serviceIcon.ts'

describe('resolveServiceIcon', () => {
  it('maps CMS Heroicon-like tokens to project icon classes', () => {
    assert.equal(resolveServiceIcon('calendar'), 'icon-calander')
    assert.equal(resolveServiceIcon('utensils'), 'icon-caterers')
    assert.equal(resolveServiceIcon('sparkles'), 'icon-flower-pot')
    assert.equal(resolveServiceIcon('briefcase'), 'icon-meeting')
  })

  it('maps by slug when icon token is unknown', () => {
    assert.equal(resolveServiceIcon(null, 'bridal-makeup'), 'icon-beauty')
    assert.equal(resolveServiceIcon('unknown-token', 'event-management'), 'icon-meeting')
  })

  it('prefers slug over ambiguous CMS tokens', () => {
    assert.equal(resolveServiceIcon('sparkle', 'bridal-makeup'), 'icon-beauty')
    assert.equal(resolveServiceIcon('flower', 'mehndi'), 'icon-mehandi')
  })

  it('preserves existing icon-* classes and falls back safely', () => {
    assert.equal(resolveServiceIcon('icon-camera'), 'icon-camera')
    assert.equal(resolveServiceIcon(undefined, undefined), 'icon-grid-view')
  })
})
