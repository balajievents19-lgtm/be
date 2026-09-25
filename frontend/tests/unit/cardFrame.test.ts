import assert from 'node:assert/strict'
import { readFileSync } from 'node:fs'
import { dirname, join } from 'node:path'
import { fileURLToPath } from 'node:url'
import { describe, it } from 'node:test'

const root = join(dirname(fileURLToPath(import.meta.url)), '../..')

describe('card media frame', () => {
  it('reserves a 1/1 contain frame and does not crop with cover', () => {
    const css = readFileSync(join(root, 'app/assets/css/main.css'), 'utf8')
    assert.match(css, /\.card-media-frame\s*\{[^}]*aspect-ratio:\s*1\s*\/\s*1/s)
    assert.match(css, /\.card-media-frame > :is\([^)]*img[^)]*\)\s*\{[^}]*object-fit:\s*contain/s)
    assert.doesNotMatch(css, /\.card-media-frame[^}]*object-fit:\s*cover/s)
  })

  it('keeps gallery photo grids on the 4/5 wrapper', () => {
    const css = readFileSync(join(root, 'app/assets/css/main.css'), 'utf8')
    assert.match(css, /\.gallery-image-wrapper\s*\{[^}]*aspect-ratio:\s*4\s*\/\s*5/s)
  })

  it('applies the shared 1/1 frame on non-gallery card grids', () => {
    const files = [
      'app/pages/packages.vue',
      'app/pages/events.vue',
      'app/pages/blog/index.vue',
      'app/components/home/EventsOverview.vue',
      'app/components/home/LatestNews.vue',
      'app/components/home/Gallery.vue',
      'app/components/gallery/GalleryCategories.vue',
      'app/components/media/ExternalMediaCard.vue'
    ]

    for (const file of files) {
      const source = readFileSync(join(root, file), 'utf8')
      assert.match(source, /card-media-frame/, file)
      assert.doesNotMatch(source, /aspect-\[16\/10\]/, file)
      assert.doesNotMatch(source, /aspect-\[4\/3\]/, file)
      assert.doesNotMatch(source, /aspect-video/, file)
    }
  })
})
