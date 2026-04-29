<template>
  <div class="sf-page">

    <!-- ── Header ── -->
    <header class="sf-header">
      <div class="sf-header-inner">
        <div class="sf-logo">
          <NuxtLink to="/" class="sf-back-btn" title="Back to Bug Tracker">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
          </NuxtLink>
          <div class="sf-logo-icon">🔗</div>
          <div>
            <div class="sf-logo-name">Shared with Me</div>
            <div class="sf-logo-sub">Bug Tracker — Project Access</div>
          </div>
        </div>

        <div class="sf-header-right">
          <template v-if="currentUser">
            <div class="sf-user" ref="userMenuRef" @click.stop="userMenuOpen = !userMenuOpen">
              <img v-if="currentUser.avatar" :src="currentUser.avatar" class="sf-avatar" :alt="currentUser.name" />
              <span v-else class="sf-avatar sf-avatar-initials">{{ currentUser.name?.[0]?.toUpperCase() }}</span>
              <span class="sf-user-name">{{ currentUser.name }}</span>
              <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="opacity:.5"><polyline points="6 9 12 15 18 9"/></svg>
              <div v-if="userMenuOpen" class="sf-user-dropdown">
                <div class="sf-user-dropdown-info">
                  <img v-if="currentUser.avatar" :src="currentUser.avatar" class="sf-dropdown-avatar" :alt="currentUser.name" />
                  <span v-else class="sf-dropdown-avatar sf-avatar-initials" style="font-size:16px;">{{ currentUser.name?.[0]?.toUpperCase() }}</span>
                  <div>
                    <div style="font-weight:600;color:#1e293b;font-size:14px;">{{ currentUser.name }}</div>
                    <div style="font-size:12px;color:#64748b;">{{ currentUser.email }}</div>
                  </div>
                </div>
                <div style="border-top:1px solid #f1f5f9;margin:8px 0;"></div>
                <button class="sf-dropdown-signout" @click.stop="logout">
                  <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                  Sign out
                </button>
              </div>
            </div>
          </template>
          <button v-else class="sf-signin-btn" @click="login">
            <svg width="15" height="15" viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.97 2.31-8.16 2.31-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg>
            Sign in with Google
          </button>
        </div>
      </div>
    </header>

    <!-- ── Auth gate ── -->
    <div v-if="!authToken && !loading" class="sf-gate">
      <div class="sf-gate-card">
        <div class="sf-gate-icon">🔗</div>
        <h2 class="sf-gate-title">Sign in to view shared projects</h2>
        <p class="sf-gate-sub">Sign in with the Google account that was invited to access these projects.</p>
        <button class="sf-signin-btn sf-gate-btn" @click="login">
          <svg width="18" height="18" viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.97 2.31-8.16 2.31-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg>
          Sign in with Google
        </button>
      </div>
    </div>

    <!-- ── Loading ── -->
    <div v-else-if="loading" class="sf-loading">
      <div class="sf-spinner"></div>
      <p>Loading shared projects…</p>
    </div>

    <!-- ── Main content ── -->
    <div v-else class="sf-content">

      <div class="sf-title-row">
        <div>
          <h1 class="sf-page-title">Shared with Me</h1>
          <p class="sf-page-sub">
            {{ filteredProjects.length }} project{{ filteredProjects.length !== 1 ? 's' : '' }} shared with you
          </p>
        </div>
        <div class="sf-search-wrap">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          <input v-model="search" class="sf-search-input" placeholder="Search projects…" />
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="!sharedProjects.length" class="sf-empty">
        <div class="sf-empty-icon">🔗</div>
        <div class="sf-empty-title">No shared projects yet</div>
        <div class="sf-empty-sub">Projects that others share with you will appear here.</div>
        <NuxtLink to="/" class="sf-empty-btn">Go to Bug Tracker</NuxtLink>
      </div>

      <!-- No search results -->
      <div v-else-if="!filteredProjects.length" class="sf-empty">
        <div class="sf-empty-icon">🔍</div>
        <div class="sf-empty-title">No results</div>
        <div class="sf-empty-sub">No projects match "{{ search }}".</div>
        <button class="sf-empty-btn" @click="search = ''">Clear search</button>
      </div>

      <!-- Projects grid -->
      <div v-else class="sf-grid">
        <div v-for="project in filteredProjects" :key="project.id" class="sf-card">
          <div class="sf-card-stripe" :style="{ background: project.color || '#6366f1' }"></div>
          <div class="sf-card-body">

            <!-- Project header -->
            <div class="sf-card-head">
              <div class="sf-card-icon" :style="{ background: (project.color || '#6366f1') + '22', color: project.color || '#6366f1' }">📁</div>
              <div class="sf-card-info">
                <div class="sf-card-name">{{ project.name }}</div>
                <div v-if="project.description" class="sf-card-desc">{{ project.description }}</div>
              </div>
              <span :class="['sf-perm-badge', project.my_permission === 'editor' ? 'sf-perm-edit' : 'sf-perm-view']">
                {{ project.my_permission === 'editor' ? 'Editor' : 'Viewer' }}
              </span>
            </div>

            <!-- Bug stats -->
            <div class="sf-stats">
              <span class="sf-stat sf-stat-total">{{ project.bugs_count }} bugs</span>
              <span v-if="project.pending_count" class="sf-stat sf-stat-pending">{{ project.pending_count }} pending</span>
              <span v-if="project.ongoing_count" class="sf-stat sf-stat-ongoing">{{ project.ongoing_count }} ongoing</span>
              <span v-if="project.completed_count" class="sf-stat sf-stat-done">{{ project.completed_count }} done</span>
              <span v-if="project.critical_count" class="sf-stat sf-stat-critical">{{ project.critical_count }} critical</span>
              <span :class="['sf-stat', project.is_active ? 'sf-stat-active' : 'sf-stat-inactive']">
                {{ project.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>

            <!-- Shared by -->
            <div class="sf-shared-by">
              <img v-if="project.invited_by_avatar" :src="project.invited_by_avatar" class="sf-sharer-avatar" :alt="project.invited_by_name" />
              <span v-else class="sf-sharer-avatar sf-sharer-initials">{{ project.invited_by_name?.[0]?.toUpperCase() }}</span>
              <span class="sf-shared-by-text">Shared by <strong>{{ project.invited_by_name }}</strong></span>
              <span v-if="project.accepted_at" class="sf-accepted-date">· {{ formatDate(project.accepted_at) }}</span>
            </div>

            <!-- Actions -->
            <div class="sf-card-actions">
              <NuxtLink :to="`/project/${project.id}`" class="sf-btn-open">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                Open Project
              </NuxtLink>
              <button
                class="sf-btn-leave"
                :disabled="leavingId === project.id"
                @click="confirmLeave(project)"
              >
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                {{ leavingId === project.id ? 'Leaving…' : 'Leave' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Leave confirmation modal ── -->
    <Transition name="sf-fade">
      <div v-if="leaveTarget" class="sf-modal-overlay" @click.self="leaveTarget = null">
        <div class="sf-modal">
          <div class="sf-modal-head">
            <h3>Leave "{{ leaveTarget.name }}"?</h3>
            <button class="sf-modal-close" @click="leaveTarget = null">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
          </div>
          <div class="sf-modal-body">
            <p>You will lose access to this project. The project owner can re-invite you if needed.</p>
          </div>
          <div class="sf-modal-footer">
            <button class="sf-btn-cancel" @click="leaveTarget = null">Cancel</button>
            <button class="sf-btn-confirm-leave" :disabled="!!leavingId" @click="doLeave">
              {{ leavingId ? 'Leaving…' : 'Leave Project' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- ── Toast ── -->
    <Transition name="sf-toast">
      <div v-if="toast.show" class="sf-toast" :class="'sf-toast-' + toast.type">
        <svg v-if="toast.type === 'success'" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        <svg v-else width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        {{ toast.message }}
      </div>
    </Transition>

  </div>
</template>

<script setup>
const config    = useRuntimeConfig()
const apiBase   = config.public.apiBase
const authToken = ref(null)
const currentUser = ref(null)

const loading       = ref(true)
const sharedProjects = ref([])
const search        = ref('')
const leavingId     = ref(null)
const leaveTarget   = ref(null)
const userMenuOpen  = ref(false)
const userMenuRef   = ref(null)

const toast = reactive({ show: false, message: '', type: 'success' })
let toastTimer = null

const showToast = (message, type = 'success') => {
  if (toastTimer) clearTimeout(toastTimer)
  toast.message = message
  toast.type    = type
  toast.show    = true
  toastTimer    = setTimeout(() => { toast.show = false }, 3000)
}

const apiFetch = (url, options = {}) => {
  if (authToken.value) {
    options.headers = { Authorization: `Bearer ${authToken.value}`, ...(options.headers || {}) }
  }
  return $fetch(url, options)
}

const authRedirectBase = apiBase.replace(/\/api$/, '')
const login  = () => { window.location.href = `${authRedirectBase}/api/auth/google` }
const logout = async () => {
  try { await apiFetch(`${apiBase}/auth/logout`, { method: 'POST' }) } catch {}
  authToken.value = null
  localStorage.removeItem('auth_token')
  currentUser.value = null
  navigateTo('/login')
}

const fetchCurrentUser = async () => {
  try {
    const user = await apiFetch(`${apiBase}/auth/me`)
    currentUser.value = (user && user.id) ? user : null
    if (!currentUser.value) {
      authToken.value = null
      localStorage.removeItem('auth_token')
    }
  } catch {
    authToken.value = null
    localStorage.removeItem('auth_token')
    currentUser.value = null
  }
}

const fetchSharedProjects = async () => {
  try {
    sharedProjects.value = await apiFetch(`${apiBase}/my-shared-projects`)
  } catch {
    sharedProjects.value = []
  }
}

const filteredProjects = computed(() => {
  if (!search.value.trim()) return sharedProjects.value
  const q = search.value.toLowerCase()
  return sharedProjects.value.filter(p =>
    p.name.toLowerCase().includes(q) || (p.description || '').toLowerCase().includes(q)
  )
})

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const confirmLeave = (project) => {
  leaveTarget.value = project
}

const doLeave = async () => {
  if (!leaveTarget.value || leavingId.value) return
  leavingId.value = leaveTarget.value.id
  const projectName = leaveTarget.value.name
  const projectId = leaveTarget.value.id
  leaveTarget.value = null
  try {
    await apiFetch(`${apiBase}/projects/${projectId}/leave`, { method: 'DELETE' })
    sharedProjects.value = sharedProjects.value.filter(p => p.id !== projectId)
    showToast(`Left "${projectName}"`)
  } catch (e) {
    showToast(e?.data?.error || 'Failed to leave project', 'error')
  } finally {
    leavingId.value = null
  }
}

onMounted(async () => {
  // Restore auth token
  const urlParams = new URLSearchParams(window.location.search)
  const token = urlParams.get('token')
  if (token) {
    authToken.value = token
    localStorage.setItem('auth_token', token)
    window.history.replaceState({}, '', '/shared-folders')
  } else {
    const stored = localStorage.getItem('auth_token')
    if (stored) authToken.value = stored
  }

  if (authToken.value) await fetchCurrentUser()

  if (currentUser.value) {
    await fetchSharedProjects()
  }

  loading.value = false

  // Close user menu on outside click
  const onOutsideClick = (e) => {
    if (userMenuRef.value && !userMenuRef.value.contains(e.target)) {
      userMenuOpen.value = false
    }
  }
  document.addEventListener('click', onOutsideClick)
  onUnmounted(() => document.removeEventListener('click', onOutsideClick))
})
</script>

<style scoped>
.sf-page { min-height: 100vh; background: #f8fafc; font-family: 'Inter', system-ui, sans-serif; }

/* Header */
.sf-header { background: #fff; border-bottom: 1px solid #e2e8f0; position: sticky; top: 0; z-index: 50; }
.sf-header-inner { max-width: 1100px; margin: 0 auto; padding: 0 24px; height: 60px; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
.sf-logo { display: flex; align-items: center; gap: 12px; }
.sf-back-btn { display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; color: #64748b; text-decoration: none; transition: background .15s, color .15s; }
.sf-back-btn:hover { background: #f1f5f9; color: #1e293b; }
.sf-logo-icon { width: 36px; height: 36px; border-radius: 10px; background: linear-gradient(135deg, #6366f1, #4f46e5); font-size: 18px; display: flex; align-items: center; justify-content: center; }
.sf-logo-name { font-size: 15px; font-weight: 700; color: #1e293b; line-height: 1.2; }
.sf-logo-sub { font-size: 11px; color: #94a3b8; }
.sf-header-right { display: flex; align-items: center; gap: 10px; }

/* User menu */
.sf-user { position: relative; display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 6px 10px; border-radius: 8px; transition: background .15s; }
.sf-user:hover { background: #f1f5f9; }
.sf-avatar { width: 28px; height: 28px; border-radius: 50%; object-fit: cover; }
.sf-avatar-initials { background: #6366f1; color: #fff; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; }
.sf-user-name { font-size: 13px; font-weight: 600; color: #1e293b; }
.sf-user-dropdown { position: absolute; top: calc(100% + 6px); right: 0; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,.1); padding: 10px; min-width: 200px; z-index: 100; }
.sf-user-dropdown-info { display: flex; align-items: center; gap: 10px; padding: 4px 2px 8px; }
.sf-dropdown-avatar { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; }
.sf-dropdown-signout { display: flex; align-items: center; gap: 8px; width: 100%; padding: 8px 10px; border: none; background: none; cursor: pointer; font-size: 13px; color: #ef4444; border-radius: 6px; transition: background .15s; }
.sf-dropdown-signout:hover { background: #fef2f2; }

/* Sign-in button */
.sf-signin-btn { display: flex; align-items: center; gap: 8px; padding: 8px 16px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; font-weight: 600; color: #1e293b; cursor: pointer; transition: box-shadow .15s; }
.sf-signin-btn:hover { box-shadow: 0 2px 8px rgba(0,0,0,.1); }

/* Auth gate */
.sf-gate { display: flex; align-items: center; justify-content: center; min-height: calc(100vh - 60px); padding: 40px 24px; }
.sf-gate-card { background: #fff; border-radius: 16px; padding: 48px 40px; text-align: center; box-shadow: 0 1px 4px rgba(0,0,0,.08); max-width: 400px; width: 100%; }
.sf-gate-icon { font-size: 48px; margin-bottom: 16px; }
.sf-gate-title { font-size: 20px; font-weight: 700; color: #1e293b; margin: 0 0 8px; }
.sf-gate-sub { font-size: 14px; color: #64748b; margin: 0 0 28px; line-height: 1.6; }
.sf-gate-btn { margin: 0 auto; }

/* Loading */
.sf-loading { display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: calc(100vh - 60px); gap: 16px; color: #64748b; font-size: 14px; }
.sf-spinner { width: 36px; height: 36px; border: 3px solid #e2e8f0; border-top-color: #6366f1; border-radius: 50%; animation: sf-spin .7s linear infinite; }
@keyframes sf-spin { to { transform: rotate(360deg); } }

/* Content */
.sf-content { max-width: 1100px; margin: 0 auto; padding: 32px 24px; }

.sf-title-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 28px; flex-wrap: wrap; }
.sf-page-title { font-size: 24px; font-weight: 800; color: #1e293b; margin: 0 0 4px; }
.sf-page-sub { font-size: 13px; color: #64748b; margin: 0; }

.sf-search-wrap { position: relative; display: flex; align-items: center; }
.sf-search-wrap svg { position: absolute; left: 10px; color: #94a3b8; pointer-events: none; }
.sf-search-input { padding: 8px 12px 8px 32px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; color: #1e293b; background: #fff; outline: none; transition: border-color .15s, box-shadow .15s; width: 220px; }
.sf-search-input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.12); }

/* Empty */
.sf-empty { text-align: center; padding: 80px 24px; }
.sf-empty-icon { font-size: 48px; margin-bottom: 16px; }
.sf-empty-title { font-size: 18px; font-weight: 700; color: #1e293b; margin-bottom: 8px; }
.sf-empty-sub { font-size: 14px; color: #64748b; margin-bottom: 24px; }
.sf-empty-btn { display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; background: #6366f1; color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; text-decoration: none; transition: background .15s; }
.sf-empty-btn:hover { background: #4f46e5; }

/* Grid */
.sf-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; }

/* Card */
.sf-card { background: #fff; border-radius: 14px; border: 1px solid #e8ecf0; box-shadow: 0 1px 3px rgba(0,0,0,.06); overflow: hidden; transition: box-shadow .2s, transform .2s; display: flex; flex-direction: column; }
.sf-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.1); transform: translateY(-2px); }
.sf-card-stripe { height: 5px; flex-shrink: 0; }
.sf-card-body { padding: 20px; display: flex; flex-direction: column; gap: 14px; flex: 1; }
.sf-card-head { display: flex; align-items: flex-start; gap: 12px; }
.sf-card-icon { width: 40px; height: 40px; border-radius: 10px; font-size: 20px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.sf-card-info { flex: 1; min-width: 0; }
.sf-card-name { font-size: 15px; font-weight: 700; color: #1e293b; margin-bottom: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sf-card-desc { font-size: 12px; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.sf-perm-badge { font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 99px; letter-spacing: .3px; flex-shrink: 0; }
.sf-perm-edit { background: #ede9fe; color: #7c3aed; }
.sf-perm-view { background: #e0f2fe; color: #0369a1; }

.sf-stats { display: flex; flex-wrap: wrap; gap: 6px; }
.sf-stat { font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 6px; }
.sf-stat-total    { background: #f1f5f9; color: #475569; }
.sf-stat-pending  { background: #fef3c7; color: #d97706; }
.sf-stat-ongoing  { background: #dbeafe; color: #1d4ed8; }
.sf-stat-done     { background: #dcfce7; color: #16a34a; }
.sf-stat-critical { background: #fee2e2; color: #dc2626; }
.sf-stat-active   { background: #e0e7ff; color: #4338ca; }
.sf-stat-inactive { background: #fee2e2; color: #dc2626; }

.sf-shared-by { display: flex; align-items: center; gap: 8px; font-size: 12px; color: #64748b; }
.sf-sharer-avatar { width: 22px; height: 22px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
.sf-sharer-initials { background: #94a3b8; color: #fff; font-size: 10px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; }
.sf-shared-by-text strong { color: #334155; }
.sf-accepted-date { color: #94a3b8; }

.sf-card-actions { display: flex; gap: 8px; margin-top: auto; }
.sf-btn-open { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; background: #6366f1; color: #fff; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; transition: background .15s; flex: 1; justify-content: center; }
.sf-btn-open:hover { background: #4f46e5; }
.sf-btn-leave { display: inline-flex; align-items: center; gap: 6px; padding: 8px 12px; background: #fff; color: #ef4444; border: 1px solid #fecaca; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: background .15s, border-color .15s; }
.sf-btn-leave:hover:not(:disabled) { background: #fef2f2; border-color: #ef4444; }
.sf-btn-leave:disabled { opacity: .5; cursor: not-allowed; }

/* Leave modal */
.sf-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.4); display: flex; align-items: center; justify-content: center; z-index: 200; padding: 16px; }
.sf-modal { background: #fff; border-radius: 14px; width: 100%; max-width: 420px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,.15); }
.sf-modal-head { display: flex; align-items: center; justify-content: space-between; padding: 20px 24px 16px; border-bottom: 1px solid #f1f5f9; }
.sf-modal-head h3 { font-size: 16px; font-weight: 700; color: #1e293b; margin: 0; }
.sf-modal-close { background: none; border: none; cursor: pointer; color: #94a3b8; padding: 4px; border-radius: 6px; display: flex; align-items: center; }
.sf-modal-close:hover { background: #f1f5f9; color: #475569; }
.sf-modal-body { padding: 20px 24px; font-size: 14px; color: #64748b; line-height: 1.6; }
.sf-modal-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 24px; border-top: 1px solid #f1f5f9; }
.sf-btn-cancel { padding: 8px 16px; background: #f1f5f9; color: #475569; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: background .15s; }
.sf-btn-cancel:hover { background: #e2e8f0; }
.sf-btn-confirm-leave { padding: 8px 16px; background: #ef4444; color: #fff; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: background .15s; }
.sf-btn-confirm-leave:hover:not(:disabled) { background: #dc2626; }
.sf-btn-confirm-leave:disabled { opacity: .5; cursor: not-allowed; }

/* Toast */
.sf-toast { position: fixed; bottom: 24px; right: 24px; display: flex; align-items: center; gap: 8px; padding: 12px 18px; border-radius: 10px; font-size: 13px; font-weight: 600; box-shadow: 0 4px 16px rgba(0,0,0,.12); z-index: 300; }
.sf-toast-success { background: #1e293b; color: #fff; }
.sf-toast-error   { background: #ef4444; color: #fff; }

/* Transitions */
.sf-fade-enter-active, .sf-fade-leave-active { transition: opacity .2s; }
.sf-fade-enter-from, .sf-fade-leave-to { opacity: 0; }
.sf-toast-enter-active, .sf-toast-leave-active { transition: opacity .25s, transform .25s; }
.sf-toast-enter-from, .sf-toast-leave-to { opacity: 0; transform: translateY(8px); }
</style>
