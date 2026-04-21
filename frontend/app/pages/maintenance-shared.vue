<template>
  <div class="ms-page">

    <!-- ── Minimal header ── -->
    <header class="ms-header">
      <div class="ms-header-inner">
        <div class="ms-logo">
          <div class="app-logo-icon" style="background:linear-gradient(135deg,#34d399,#10b981);font-size:20px;width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;">🔧</div>
          <div>
            <div class="ms-logo-name">Maintenance Tracker</div>
            <div class="ms-logo-sub">Shared Projects</div>
          </div>
        </div>

        <div class="ms-header-right">
          <template v-if="currentUser">
            <div class="ms-user" ref="userMenuRef" @click.stop="userMenuOpen = !userMenuOpen">
              <img v-if="currentUser.avatar" :src="currentUser.avatar" class="ms-avatar" :alt="currentUser.name" />
              <span v-else class="ms-avatar ms-avatar-initials">{{ currentUser.name?.[0]?.toUpperCase() }}</span>
              <span class="ms-user-name">{{ currentUser.name }}</span>
              <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="opacity:.5"><polyline points="6 9 12 15 18 9"/></svg>
              <div v-if="userMenuOpen" class="ms-user-dropdown">
                <div class="ms-user-dropdown-info">
                  <img v-if="currentUser.avatar" :src="currentUser.avatar" class="ms-dropdown-avatar" :alt="currentUser.name" />
                  <span v-else class="ms-dropdown-avatar ms-avatar-initials" style="font-size:16px;">{{ currentUser.name?.[0]?.toUpperCase() }}</span>
                  <div>
                    <div style="font-weight:600;color:#1e293b;font-size:14px;">{{ currentUser.name }}</div>
                    <div style="font-size:12px;color:#64748b;">{{ currentUser.email }}</div>
                  </div>
                </div>
                <div class="ms-user-dropdown-divider"></div>
                <button class="ms-user-dropdown-item ms-signout" @click.stop="logout">
                  <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                  Sign out
                </button>
              </div>
            </div>
          </template>
          <button v-else class="btn-google-signin" @click="login">
            <svg width="16" height="16" viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.97 2.31-8.16 2.31-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg>
            Sign in with Google
          </button>
        </div>
      </div>
    </header>

    <!-- ── Sign-in gate (not authenticated) ── -->
    <div v-if="!authToken && !loading" class="ms-gate">
      <div class="ms-gate-card">
        <div class="ms-gate-icon">🔧</div>
        <h2 class="ms-gate-title">Access your projects</h2>
        <p class="ms-gate-sub">Sign in with the Google account that was invited to the maintenance project.</p>
        <button class="btn-google-signin ms-gate-btn" @click="login">
          <svg width="18" height="18" viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.97 2.31-8.16 2.31-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg>
          Sign in with Google
        </button>
      </div>
    </div>

    <!-- ── Loading ── -->
    <div v-else-if="loading" class="ms-loading">
      <div class="ms-spinner"></div>
      <p>Loading projects…</p>
    </div>

    <!-- ── Projects list ── -->
    <div v-else class="ms-content">

      <!-- Page title -->
      <div class="ms-page-title-row">
        <div>
          <h1 class="ms-page-title">Maintenance Projects</h1>
          <p class="ms-page-sub">{{ `${sharedProjects.length} project${sharedProjects.length !== 1 ? 's' : ''} shared with you` }}</p>
        </div>
        <!-- Search -->
        <div class="ms-search-wrap">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          <input v-model="search" class="ms-search-input" placeholder="Search projects…" />
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="sharedProjects.length === 0 && !search" class="ms-empty">
        <div class="ms-empty-icon">🔧</div>
        <div class="ms-empty-title">No shared projects yet</div>
        <div class="ms-empty-sub">Projects shared with you will appear here.</div>
      </div>

      <div v-else-if="filteredProjects.length === 0" class="ms-empty">
        <div class="ms-empty-icon">🔍</div>
        <div class="ms-empty-title">No projects match "{{ search }}"</div>
        <button class="btn btn-ghost btn-sm" style="margin-top:12px;" @click="search = ''">Clear search</button>
      </div>

      <!-- Project grid -->
      <div v-else class="ms-projects-grid">
        <div
          v-for="p in filteredProjects"
          :key="p.id"
          class="ms-project-card"
          :class="{ 'ms-card--highlighted': highlightId === p.id }"
          :style="{ '--pc-color': p.color || '#10b981' }"
          :ref="el => { if (highlightId === p.id && el) highlightEl = el }"
          @click="openProject(p)"
        >
          <div class="ms-card-accent"></div>
          <div class="ms-card-body">
            <div class="ms-card-top">
              <div class="ms-card-icon" :style="{ background: (p.color||'#10b981')+'18', color: p.color||'#10b981' }">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
              </div>
              <div class="ms-card-name-block">
                <h3 class="ms-card-name">{{ p.name }}</h3>
                <div class="ms-card-meta">
                  <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                  <span v-if="p.contract_start || p.contract_end">
                    {{ p.contract_start ? p.contract_start.slice(0,10) : '?' }} → {{ p.contract_end ? p.contract_end.slice(0,10) : '?' }}
                  </span>
                  <span v-else style="font-style:italic;">No contract set</span>
                </div>
              </div>
              <div class="ms-card-badges">
                <span v-if="p.is_active !== false" class="ms-badge ms-badge-active">ACTIVE</span>
                <span v-else class="ms-badge ms-badge-inactive">INACTIVE</span>
              </div>
            </div>

            <p v-if="p.description" class="ms-card-desc">{{ p.description }}</p>

            <div class="ms-card-stats">
              <div class="ms-stat">
                <div class="ms-stat-val">{{ p.tickets_count ?? 0 }}</div>
                <div class="ms-stat-label">Total</div>
              </div>
              <div class="ms-stat">
                <div class="ms-stat-val" style="color:#f59e0b;">{{ p.pending_count ?? 0 }}</div>
                <div class="ms-stat-label">Pending</div>
              </div>
              <div class="ms-stat">
                <div class="ms-stat-val" style="color:#3b82f6;">{{ p.in_progress_count ?? 0 }}</div>
                <div class="ms-stat-label">In Progress</div>
              </div>
              <div class="ms-stat">
                <div class="ms-stat-val" style="color:#22c55e;">{{ p.completed_count ?? 0 }}</div>
                <div class="ms-stat-label">Completed</div>
              </div>
            </div>

            <div class="ms-card-footer">
              <button class="ms-open-btn" @click.stop="openProject(p)">
                Open project
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
              </button>
            </div>
          </div>
        </div>
      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'

const config  = useRuntimeConfig()
const apiBase = config.public.apiBase.replace('/api', '')
const route   = useRoute()
const router  = useRouter()

// ── Auth ─────────────────────────────────────────────────────────────────────
const authToken   = ref(null)
const currentUser = ref(null)
const userMenuOpen = ref(false)
const userMenuRef  = ref(null)

const apiFetch = (url, options = {}) => {
  if (authToken.value) {
    options.headers = { Authorization: `Bearer ${authToken.value}`, ...(options.headers || {}) }
  }
  return $fetch(url, options)
}

const login = () => {
  // Store return path so callback knows where to redirect
  localStorage.setItem('auth_return', window.location.pathname + window.location.search)
  window.location.href = `${apiBase}/api/auth/google`
}

const logout = async () => {
  try { await apiFetch(`${config.public.apiBase}/auth/logout`, { method: 'POST' }) } catch {}
  authToken.value = null
  localStorage.removeItem('auth_token')
  currentUser.value = null
  userMenuOpen.value = false
}

const fetchCurrentUser = async () => {
  try {
    const user = await apiFetch(`${config.public.apiBase}/auth/me`)
    currentUser.value = (user && user.id) ? user : null
    if (!currentUser.value) { authToken.value = null; localStorage.removeItem('auth_token') }
  } catch {
    authToken.value = null
    localStorage.removeItem('auth_token')
    currentUser.value = null
  }
}

// ── Projects ─────────────────────────────────────────────────────────────────
const loading  = ref(false)
const projects = ref([])
const search   = ref('')

// Show only projects explicitly shared with this user (not owned)
// ?project=ID just highlights/scrolls to that card — all shared projects still show
const sharedProjects = computed(() => projects.value.filter(p => p.my_permission !== 'owner'))

const filteredProjects = computed(() => {
  const q = search.value.trim().toLowerCase()
  if (!q) return sharedProjects.value
  return sharedProjects.value.filter(p =>
    p.name.toLowerCase().includes(q) || (p.description || '').toLowerCase().includes(q)
  )
})

const fetchProjects = async () => {
  loading.value = true
  try {
    projects.value = await apiFetch(`${config.public.apiBase}/maintenance/projects`)
  } catch {
    projects.value = []
  } finally {
    loading.value = false
  }
}

const openProject = (p) => {
  router.push(`/maintenance?project=${p.id}&from=shared`)
}

// ── Highlight specific project from ?project= query param ────────────────────
const highlightId = computed(() => {
  const id = route.query.project
  return id ? Number(id) : null
})
const highlightEl = ref(null)

// ── Click outside to close user menu ─────────────────────────────────────────
const clickOutside = (e) => {
  if (userMenuRef.value && !userMenuRef.value.contains(e.target)) {
    userMenuOpen.value = false
  }
}

// ── Lifecycle ─────────────────────────────────────────────────────────────────
onMounted(async () => {
  // Read token stored after OAuth callback
  const stored = localStorage.getItem('auth_token')
  if (stored) authToken.value = stored

  if (authToken.value) {
    await fetchCurrentUser()
    await fetchProjects()

    // Scroll highlighted project into view
    if (highlightId.value) {
      await nextTick()
      if (highlightEl.value) {
        highlightEl.value.scrollIntoView({ behavior: 'smooth', block: 'center' })
      }
    }
  }

  document.addEventListener('click', clickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', clickOutside)
})
</script>

<style scoped>
/* ── Page shell ── */
.ms-page {
  min-height: 100vh;
  background: #f8fafc;
  font-family: Inter, system-ui, sans-serif;
}

/* ── Header ── */
.ms-header {
  background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
  border-bottom: 1px solid rgba(255,255,255,.06);
  padding: 0 24px;
  height: 56px;
  display: flex;
  align-items: center;
  position: sticky;
  top: 0;
  z-index: 50;
}
.ms-header-inner {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}
.ms-logo {
  display: flex;
  align-items: center;
  gap: 10px;
}
.ms-logo-name {
  font-size: 15px;
  font-weight: 700;
  color: #fff;
  line-height: 1.2;
}
.ms-logo-sub {
  font-size: 11px;
  color: #a7f3d0;
  line-height: 1;
}
.ms-header-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

/* ── User menu ── */
.ms-user {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 8px;
  transition: background .15s;
  position: relative;
}
.ms-user:hover { background: rgba(255,255,255,.08); }
.ms-avatar {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
}
.ms-avatar-initials {
  background: #4f46e5;
  color: #fff;
  font-size: 12px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
}
.ms-user-name {
  font-size: 13px;
  font-weight: 500;
  color: rgba(255,255,255,.9);
  max-width: 140px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.ms-user-dropdown {
  position: absolute;
  top: calc(100% + 6px);
  right: 0;
  min-width: 210px;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  box-shadow: 0 8px 24px rgba(0,0,0,.12);
  z-index: 100;
  overflow: hidden;
}
.ms-user-dropdown-info {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 14px 14px 10px;
}
.ms-dropdown-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
}
.ms-user-dropdown-divider { height: 1px; background: #f1f5f9; }
.ms-user-dropdown-item {
  display: flex;
  align-items: center;
  gap: 8px;
  width: 100%;
  padding: 10px 14px;
  font-size: 13px;
  color: #374151;
  background: none;
  border: none;
  cursor: pointer;
  text-align: left;
  transition: background .12s;
}
.ms-user-dropdown-item:hover { background: #f8fafc; }
.ms-signout { color: #ef4444; }
.ms-signout:hover { background: #fef2f2; }

/* ── Sign-in gate ── */
.ms-gate {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: calc(100vh - 56px);
  padding: 40px 24px;
}
.ms-gate-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 48px 40px;
  max-width: 400px;
  width: 100%;
  text-align: center;
  box-shadow: 0 4px 20px rgba(0,0,0,.06);
}
.ms-gate-icon { font-size: 40px; margin-bottom: 16px; }
.ms-gate-title { font-size: 22px; font-weight: 700; color: #1e293b; margin: 0 0 10px; }
.ms-gate-sub   { font-size: 14px; color: #64748b; margin: 0 0 28px; line-height: 1.5; }
.ms-gate-btn   { font-size: 14px; padding: 10px 22px; margin: 0 auto; }

/* ── Loading ── */
.ms-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: calc(100vh - 56px);
  gap: 12px;
  color: #64748b;
  font-size: 14px;
}
.ms-spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #e2e8f0;
  border-top-color: #4f46e5;
  border-radius: 50%;
  animation: ms-spin .7s linear infinite;
}
@keyframes ms-spin { to { transform: rotate(360deg); } }

/* ── Content area ── */
.ms-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 32px 24px 60px;
}

/* ── Page title row ── */
.ms-page-title-row {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 28px;
  flex-wrap: wrap;
}
.ms-page-title {
  font-size: 22px;
  font-weight: 800;
  color: #1e293b;
  margin: 0 0 4px;
}
.ms-page-sub {
  font-size: 13px;
  color: #94a3b8;
  margin: 0;
}
.ms-search-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 8px 12px;
  color: #94a3b8;
  min-width: 220px;
}
.ms-search-input {
  border: none;
  outline: none;
  font-size: 13px;
  color: #1e293b;
  background: transparent;
  width: 100%;
}
.ms-search-input::placeholder { color: #94a3b8; }

/* ── Empty states ── */
.ms-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 24px;
  text-align: center;
}
.ms-empty-icon  { font-size: 40px; margin-bottom: 14px; }
.ms-empty-title { font-size: 16px; font-weight: 600; color: #334155; margin-bottom: 6px; }
.ms-empty-sub   { font-size: 13px; color: #94a3b8; }

/* ── Projects grid ── */
.ms-projects-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 18px;
}

/* ── Project card ── */
.ms-project-card {
  background: #fff;
  border: 1.5px solid #e2e8f0;
  border-radius: 14px;
  overflow: hidden;
  cursor: pointer;
  transition: box-shadow .18s, border-color .18s, transform .15s;
  display: flex;
  flex-direction: column;
}
.ms-project-card:hover {
  box-shadow: 0 8px 28px rgba(0,0,0,.09);
  border-color: var(--pc-color, #10b981);
  transform: translateY(-2px);
}
.ms-card--highlighted {
  border-color: var(--pc-color, #4f46e5);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--pc-color, #4f46e5) 20%, transparent);
}
.ms-card-accent {
  height: 4px;
  background: var(--pc-color, #10b981);
}
.ms-card-body {
  padding: 18px 20px 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  flex: 1;
}
.ms-card-top {
  display: flex;
  align-items: flex-start;
  gap: 12px;
}
.ms-card-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.ms-card-name-block {
  flex: 1;
  min-width: 0;
}
.ms-card-name {
  font-size: 15px;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 4px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.ms-card-meta {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 11.5px;
  color: #94a3b8;
}
.ms-card-badges {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 4px;
  flex-shrink: 0;
}
.ms-badge {
  font-size: 10px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 99px;
  letter-spacing: .3px;
  white-space: nowrap;
}
.ms-badge-active   { background: #d1fae5; color: #065f46; }
.ms-badge-inactive { background: #fee2e2; color: #b91c1c; }
.ms-badge-perm     { background: #ede9fe; color: #5b21b6; }
.ms-perm-owner   { background: #fef3c7; color: #92400e; }
.ms-perm-edit    { background: #dbeafe; color: #1e40af; }
.ms-perm-comment { background: #f0fdf4; color: #15803d; }
.ms-perm-view    { background: #f1f5f9; color: #475569; }
.ms-card-desc {
  font-size: 13px;
  color: #64748b;
  margin: 0;
  line-height: 1.5;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* ── Stats row ── */
.ms-card-stats {
  display: flex;
  gap: 16px;
}
.ms-stat { text-align: center; }
.ms-stat-val   { font-size: 18px; font-weight: 800; color: #1e293b; line-height: 1.1; }
.ms-stat-label { font-size: 10px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: .4px; margin-top: 2px; }

/* ── Card footer ── */
.ms-card-footer {
  padding-top: 4px;
  border-top: 1px solid #f1f5f9;
  display: flex;
  justify-content: flex-end;
}
.ms-open-btn {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 12.5px;
  font-weight: 600;
  color: var(--pc-color, #10b981);
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px 0;
  transition: opacity .12s;
}
.ms-open-btn:hover { opacity: .75; }

/* ── Responsive ── */
@media (max-width: 640px) {
  .ms-content { padding: 20px 16px 40px; }
  .ms-page-title-row { flex-direction: column; }
  .ms-search-wrap { width: 100%; }
  .ms-projects-grid { grid-template-columns: 1fr; }
  .ms-gate-card { padding: 36px 24px; }
}
</style>
