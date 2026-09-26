/**
 * Document responses must be text/html with an explicit charset.
 * Combined with X-Content-Type-Options: nosniff, a missing or non-HTML
 * Content-Type makes browsers show the markup as plain text.
 */
export default defineNitroPlugin((nitroApp) => {
  nitroApp.hooks.hook('render:html', (_html, { event }) => {
    setResponseHeader(event, 'Content-Type', 'text/html; charset=utf-8')
  })
})
