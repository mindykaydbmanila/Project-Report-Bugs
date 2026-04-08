<template>
  <div class="login-page">
    <!-- Animated gradient blobs -->
    <div class="login-blob login-blob--1"></div>
    <div class="login-blob login-blob--2"></div>
    <div class="login-blob login-blob--3"></div>
    <div class="login-card">

      <div class="login-logo">
        <div class="app-logo-icon">🐛</div>
        <div>
          <div class="login-app-name">QA Bug Tracker</div>
          <span class="app-logo-subtitle" style="color:#64748b;">Quality Assurance Dashboard</span>
        </div>
      </div>

      <p class="login-tagline">Sign in to manage projects and track bugs.</p>

      <div v-if="authError" class="login-error">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        {{ errorMessage }}
      </div>

      <button class="btn-google-signin login-google-btn" @click="login">
        <svg width="16" height="16" viewBox="0 0 48 48">
          <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
          <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
          <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
          <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.97 2.31-8.16 2.31-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
        </svg>
        Sign in with Google
      </button>

    </div>
  </div>
</template>

<script setup>
const config  = useRuntimeConfig()
const route   = useRoute()
const apiBase = config.public.apiBase.replace('/api', '')

const authError    = computed(() => !!route.query.auth_error)
const errorMessage = computed(() => {
  const code = route.query.auth_error
  if (code === 'access_denied') return 'Access was denied. Please try again.'
  return 'Google sign-in failed. Please try again.'
})

const login = () => { window.location.href = `${apiBase}/api/auth/google` }

onMounted(() => {
  const token = localStorage.getItem('auth_token')
  if (token) navigateTo('/')
})
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 60%, #312e81 100%);
  padding: 24px;
  overflow: hidden;
  position: relative;
}

/* ── Animated blobs ── */
.login-blob {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.35;
  pointer-events: none;
}
.login-blob--1 {
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, #6366f1, #4f46e5);
  top: -160px;
  left: -140px;
  animation: blob-drift-1 6s ease-in-out infinite;
}
.login-blob--2 {
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, #7c3aed, #5b21b6);
  bottom: -120px;
  right: -100px;
  animation: blob-drift-2 8s ease-in-out infinite;
}
.login-blob--3 {
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, #2563eb, #1d4ed8);
  top: 50%;
  left: 55%;
  transform: translate(-50%, -50%);
  animation: blob-drift-3 5s ease-in-out infinite;
}

@keyframes blob-drift-1 {
  0%, 100% { transform: translate(0, 0) scale(1); }
  33%       { transform: translate(60px, 40px) scale(1.08); }
  66%       { transform: translate(-30px, 70px) scale(0.95); }
}
@keyframes blob-drift-2 {
  0%, 100% { transform: translate(0, 0) scale(1); }
  33%       { transform: translate(-50px, -60px) scale(1.1); }
  66%       { transform: translate(40px, -30px) scale(0.92); }
}
@keyframes blob-drift-3 {
  0%, 100% { transform: translate(-50%, -50%) scale(1); }
  50%       { transform: translate(-50%, -50%) scale(1.15); }
}

.login-card {
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 24px 64px rgba(0,0,0,.32);
  position: relative;
  z-index: 1;
  padding: 44px 40px;
  width: 100%;
  max-width: 400px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 20px;
  text-align: center;
}

.login-logo {
  display: flex;
  align-items: center;
  gap: 12px;
}

.login-app-name {
  font-size: 18px;
  font-weight: 800;
  color: #1e293b;
  letter-spacing: -0.3px;
  line-height: 1.2;
}

.login-tagline {
  font-size: 13px;
  color: #64748b;
  margin: 0;
}

.login-error {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #dc2626;
  border-radius: 10px;
  padding: 10px 14px;
  font-size: 13px;
  width: 100%;
  text-align: left;
  box-sizing: border-box;
}

.login-google-btn {
  width: 100%;
  justify-content: center;
  padding: 11px 20px;
  font-size: 14px;
}
</style>
