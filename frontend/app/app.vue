<template>
  <div>
    <Transition name="loader-fade">
      <div v-if="pageLoading" class="page-loader">
        <div class="loader-orb-ring">
          <span class="loader-orb loader-orb--1"></span>
          <span class="loader-orb loader-orb--2"></span>
          <span class="loader-orb loader-orb--3"></span>
        </div>
        <div class="loader-bug">🐛</div>
        <div class="loader-dots">
          <span></span><span></span><span></span>
        </div>
      </div>
    </Transition>
    <NuxtPage />
  </div>
</template>

<script setup>
// Shared with index.vue via useState — internal view switches also set this
const pageLoading = useState('appLoading', () => false)
let loadingTimer = null

const nuxtApp = useNuxtApp()
nuxtApp.hook('page:start', () => {
  clearTimeout(loadingTimer)
  pageLoading.value = true
})
nuxtApp.hook('page:finish', () => {
  loadingTimer = setTimeout(() => { pageLoading.value = false }, 300)
})
</script>

<style>
/* ── Full-page loader ─────────────────────────────────────────── */
.page-loader {
  position: fixed;
  inset: 0;
  z-index: 9999;
  background: rgba(15, 23, 42, 0.45);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 20px;
}

/* Orbiting ring */
.loader-orb-ring {
  position: absolute;
  width: 90px;
  height: 90px;
  animation: ring-spin 2s linear infinite;
}
.loader-orb {
  position: absolute;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  top: 50%;
  left: 50%;
  transform-origin: -35px 0;
}
.loader-orb--1 { background: #818cf8; animation: orb-pulse 1.6s ease-in-out infinite 0s; }
.loader-orb--2 { background: #a78bfa; animation: orb-pulse 1.6s ease-in-out infinite 0.53s; transform: rotate(120deg) translateX(-35px) translateY(-50%); }
.loader-orb--3 { background: #60a5fa; animation: orb-pulse 1.6s ease-in-out infinite 1.06s; transform: rotate(240deg) translateX(-35px) translateY(-50%); }

/* Bug icon */
.loader-bug {
  font-size: 40px;
  animation: bug-bounce 1.2s ease-in-out infinite;
  filter: drop-shadow(0 0 16px rgba(129, 140, 248, 0.7));
  z-index: 1;
}

/* Bouncing dots */
.loader-dots {
  display: flex;
  gap: 8px;
}
.loader-dots span {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #818cf8;
  animation: dot-bounce 1.2s ease-in-out infinite;
}
.loader-dots span:nth-child(2) { animation-delay: 0.2s; background: #a78bfa; }
.loader-dots span:nth-child(3) { animation-delay: 0.4s; background: #60a5fa; }

/* Keyframes */
@keyframes ring-spin {
  to { transform: rotate(360deg); }
}
@keyframes orb-pulse {
  0%, 100% { opacity: 0.4; transform: translateX(-35px) translateY(-50%) scale(0.8); }
  50%       { opacity: 1;   transform: translateX(-35px) translateY(-50%) scale(1.2); }
}
@keyframes bug-bounce {
  0%, 100% { transform: translateY(0) scale(1); }
  50%       { transform: translateY(-10px) scale(1.1); }
}
@keyframes dot-bounce {
  0%, 80%, 100% { transform: translateY(0); opacity: 0.5; }
  40%            { transform: translateY(-8px); opacity: 1; }
}

/* Transition */
.loader-fade-enter-active,
.loader-fade-leave-active { transition: opacity 0.25s ease; }
.loader-fade-enter-from,
.loader-fade-leave-to    { opacity: 0; }
</style>
