// @ts-check
import withNuxt from './.nuxt/eslint.config.mjs'

export default withNuxt(
  {
    name: 'app/ignores',
    ignores: [
      'reference/**',
      'reference/bootstrap-master/**',
      'docs/**',
      '.output/**',
      'public/**'
    ]
  }
)
