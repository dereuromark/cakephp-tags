import { defineConfig } from 'vitepress'

const sidebar = [
  { text: 'Guide', items: [
    { text: 'Installation', link: '/guide/installation' },
    { text: 'Getting started', link: '/guide/getting-started' },
    { text: 'Behavior', link: '/guide/behavior' },
    { text: 'Finding records', link: '/guide/finders' },
    { text: 'Admin interface', link: '/guide/admin' },
    { text: 'Helper and assets', link: '/guide/helper' },
    { text: 'Application integrations', link: '/guide/integrations' },
  ] },
  { text: 'Reference', items: [
    { text: 'Configuration', link: '/reference/configuration' },
    { text: 'Upgrading', link: '/reference/upgrading' },
  ] },
]

export default defineConfig({
  title: 'CakePHP Tags',
  description: 'Tagging, colored labels and an admin interface for CakePHP.',
  base: '/cakephp-tags/',
  cleanUrls: true,
  lastUpdated: true,
  srcExclude: ['README.md'],
  sitemap: { hostname: 'https://dereuromark.github.io/cakephp-tags/' },
  head: [['link', { rel: 'icon', href: '/cakephp-tags/favicon.svg', type: 'image/svg+xml' }]],
  themeConfig: {
    logo: '/logo.svg',
    nav: [
      { text: 'Installation', link: '/guide/installation' },
      { text: 'Getting started', link: '/guide/getting-started' },
      { text: 'Reference', link: '/reference/configuration' },
    ],
    sidebar,
    socialLinks: [{ icon: 'github', link: 'https://github.com/dereuromark/cakephp-tags' }],
    search: { provider: 'local' },
    editLink: { pattern: 'https://github.com/dereuromark/cakephp-tags/edit/cake6/docs/:path' },
    footer: { message: 'Released under the MIT License.', copyright: 'Copyright Mark Scherer' },
  },
})
