export default defineEventHandler(async (event) => {
  const origin = laravelWebOrigin()
  if (!origin) {
    throw createError({ statusCode: 503, statusMessage: 'API origin is not configured' })
  }

  const body = await $fetch<string>(`${origin}/robots.txt`, { responseType: 'text' })
  setHeader(event, 'content-type', 'text/plain; charset=UTF-8')
  return body
})
