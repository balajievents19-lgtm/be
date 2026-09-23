import assert from 'node:assert/strict'
import { readFileSync } from 'node:fs'
import { dirname, join } from 'node:path'
import { fileURLToPath } from 'node:url'
import { describe, it } from 'node:test'

const root = join(dirname(fileURLToPath(import.meta.url)), '../..')

describe('gallery uniform frame', () => {
  it('uses a 4/5 aspect-ratio cover crop, not stretch', () => {
    const css = readFileSync(join(root, 'app/assets/css/main.css'), 'utf8')
    assert.match(css, /\.gallery-image-wrapper\s*\{[^}]*aspect-ratio:\s*4\s*\/\s*5/s)
    assert.match(css, /\.gallery-image-wrapper img\s*\{[^}]*object-fit:\s*cover/s)
    assert.doesNotMatch(css, /\.gallery-image-wrapper img\s*\{[^}]*object-fit:\s*fill/s)
  })

  it('applies the shared wrapper on gallery photo grids', () => {
    const grid = readFileSync(join(root, 'app/components/gallery/GalleryGrid.vue'), 'utf8')
    const category = readFileSync(join(root, 'app/components/gallery/GalleryCategoryView.vue'), 'utf8')
    const service = readFileSync(join(root, 'app/components/services/ServiceGallery.vue'), 'utf8')

    for (const source of [grid, category, service]) {
      assert.match(source, /gallery-image-wrapper/)
      assert.doesNotMatch(source, /h-auto w-full/)
    }
  })
})
