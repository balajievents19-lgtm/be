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
  },
  {
    // Public CMS HTML may only be rendered via SharedSafeHtml after sanitization.
    // Keep vue/no-v-html enabled everywhere else.
    name: 'app/safe-html-boundary',
    files: ['app/components/shared/SafeHtml.vue'],
    rules: {
      'vue/no-v-html': 'off'
    }
  }
)
