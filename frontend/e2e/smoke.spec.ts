import { expect, test } from '@playwright/test'

const pages = [
  { path: '/', title: /Balaji Events/i },
  { path: '/about', heading: /About/i },
  { path: '/services', heading: /Services/i },
  { path: '/gallery', heading: /Gallery/i },
  { path: '/blog', heading: /Blog|Latest|News/i },
  { path: '/faq', heading: /FAQ/i },
  { path: '/contact', heading: /Contact/i }
] as const

for (const pageDef of pages) {
  test(`smoke ${pageDef.path} renders`, async ({ page }) => {
    const response = await page.goto(pageDef.path, { waitUntil: 'domcontentloaded' })
    expect(response?.ok() || response?.status() === 200).toBeTruthy()

    await expect(page.locator('#main-content, main').first()).toBeVisible()

    if ('title' in pageDef) {
      await expect(page).toHaveTitle(pageDef.title)
    }

    if ('heading' in pageDef) {
      await expect(page.getByRole('heading').first()).toBeVisible()
    }

    // Basic SEO signals present in SSR HTML
    const metaDescription = page.locator('meta[name="description"]')
    await expect(metaDescription.first()).toHaveAttribute('content', /.+/)

    const ogTitle = page.locator('meta[property="og:title"]')
    await expect(ogTitle.first()).toHaveCount(1)
  })
}

test('homepage has skip link and navigation', async ({ page }) => {
  await page.goto('/')
  await expect(page.getByRole('link', { name: /skip to main content/i })).toBeAttached()
  await expect(page.locator('#nav-main, nav[aria-label="Main navigation"]').first()).toBeVisible()
})
