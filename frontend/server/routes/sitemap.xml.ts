export default defineEventHandler(async (event) => {
  const origin = laravelWebOrigin()
  if (!origin) {
    throw createError({ statusCode: 503, statusMessage: 'API origin is not configured' })
  }

  const body = await $fetch<string>(`${origin}/sitemap.xml`, { responseType: 'text' })
  setHeader(event, 'content-type', 'application/xml; charset=UTF-8')
  return body
})
