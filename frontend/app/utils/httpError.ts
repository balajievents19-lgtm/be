/** Status code from $fetch / ofetch / Nuxt errors. */
export const httpStatus = (error: unknown): number | undefined => {
  if (!error || typeof error !== 'object') {
    return undefined
  }

  const value = error as {
    statusCode?: number
    status?: number
    response?: { status?: number }
  }

  if (typeof value.statusCode === 'number') {
    return value.statusCode
  }
  if (typeof value.status === 'number') {
    return value.status
  }
  if (typeof value.response?.status === 'number') {
    return value.response.status
  }

  return undefined
}

export const isNotFoundError = (error: unknown): boolean => httpStatus(error) === 404
