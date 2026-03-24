<template>
  <div style="display:flex;align-items:center;justify-content:center;height:100vh;font-family:Inter,sans-serif;">
    <div style="text-align:center;color:#64748b;">
      <div style="font-size:32px;margin-bottom:12px;">🔑</div>
      <div style="font-size:15px;">Signing you in…</div>
    </div>
  </div>
</template>

<script setup>
// This page handles the OAuth redirect — token is in the URL of the root page,
// not here. This page is a fallback in case routing sends /auth/callback here.
onMounted(() => {
  navigateTo('/')
})
</script>
