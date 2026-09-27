/**
 * Image-only copy deterrents. Does not register document-level dragstart/drop
 * listeners so CMS uploads, forms, and page drag-and-drop keep working.
 *
 * Must start after hydrate: mutating img DOM (data-img-protect-bound) during
 * plugin setup would diverge from SSR markup and trigger a hydration mismatch.
 */
export default defineNuxtPlugin(() => {
  const protectImage = (img: HTMLImageElement) => {
    if (img.dataset.allowDrag === 'true' || img.dataset.imgProtectBound === '1') {
      return
    }

    img.dataset.imgProtectBound = '1'
    img.setAttribute('draggable', 'false')
    img.draggable = false
    img.addEventListener('dragstart', (event) => {
      event.preventDefault()
    })
  }

  const scan = (root: ParentNode) => {
    root.querySelectorAll('img').forEach(protectImage)
  }

  const onContextMenu = (event: Event) => {
    const target = event.target
    if (!(target instanceof Element)) {
      return
    }
    if (target instanceof HTMLImageElement || target.closest('img')) {
      event.preventDefault()
    }
  }

  const start = () => {
    document.addEventListener('contextmenu', onContextMenu)
    scan(document)

    const observer = new MutationObserver((mutations) => {
      for (const mutation of mutations) {
        for (const node of mutation.addedNodes) {
          if (node instanceof HTMLImageElement) {
            protectImage(node)
          } else if (node instanceof Element) {
            scan(node)
          }
        }
      }
    })

    observer.observe(document.documentElement, {
      childList: true,
      subtree: true
    })
  }

  onNuxtReady(start)
})
