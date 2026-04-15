export default defineNuxtConfig({
  compatibilityDate: '2024-11-01',
  ssr: false,
  devServer: {
    host: '0.0.0.0',
    port: 3000
  },
  devtools: { enabled: true },
  runtimeConfig: {
    public: {
      apiBase: 'http://localhost:8000/api'
    }
  },
  css: ['~/assets/css/main.css'],
})
