export default defineNuxtRouteMiddleware((to) => {
  if (!import.meta.client) return   // skip during SSR — localStorage not available
  if (to.query.token) return        // OAuth callback lands on /?token=xxx — let index.vue handle it
  if (to.query.auth_error) return   // forward auth errors so index.vue can redirect to /login

  const token = localStorage.getItem('auth_token')
  if (!token) return navigateTo('/login')
})
