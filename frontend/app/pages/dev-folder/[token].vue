<template>
  <div class="folder-page">

    <!-- Toast notification -->
    <transition name="toast-fade">
      <div v-if="toastMsg" class="folder-toast">{{ toastMsg }}</div>
    </transition>

    <!-- Header -->
    <header class="folder-header">
      <div class="folder-header-inner">
        <div class="folder-header-brand">
          <div class="folder-brand-icon">🐛</div>
          <div class="folder-brand-name">QA Bug Tracker</div>
        </div>
        <div class="folder-header-eyebrow">
          DEV FOLDER<span v-if="folder && folder.project_name"> · {{ folder.project_name.toUpperCase() }}</span>
        </div>
        <div class="folder-header-title">{{ folder ? folder.developer_name : 'Developer Folder' }}</div>
        <div v-if="folder" class="folder-header-sub">{{ folder.developer_email }}</div>
      </div>
    </header>

    <!-- Google Gate (private folders) -->
    <div v-if="requiresAuth" class="folder-gate">
      <div class="folder-gate-card">
        <div style="font-size:40px;margin-bottom:12px;">🔒</div>
        <h2 style="margin:0 0 6px;font-size:18px;color:#1e293b;">Private Folder</h2>
        <p style="color:#64748b;font-size:13px;margin:0 0 24px;">
          This folder is private. Sign in with your Google account to verify access.
        </p>
        <div v-if="authError" style="color:#dc2626;font-size:12px;margin-bottom:14px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:8px 12px;">
          Your Google account does not match this folder. Please use the correct account.
        </div>
        <button class="folder-google-btn" @click="signInWithGoogle">
          <svg width="16" height="16" viewBox="0 0 48 48" style="flex-shrink:0;">
            <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
            <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
            <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
            <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.97 2.31-8.16 2.31-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
          </svg>
          Sign in with Google
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-else-if="loading" class="folder-loading">
      <div class="folder-spinner"></div>
      <p>Loading your tickets…</p>
    </div>


    <!-- Error -->
    <div v-else-if="error && !requiresAuth" class="folder-error">
      <div style="font-size:40px;margin-bottom:12px;">⚠️</div>
      <h2>Folder not found</h2>
      <p>{{ error }}</p>
    </div>

    <!-- Content -->
    <div v-else-if="folder" class="folder-content">

      <!-- Dev info bar -->
      <div class="folder-dev-bar">
        <div class="folder-dev-avatar">{{ initials(folder.developer_name) }}</div>
        <div>
          <div class="folder-dev-name">{{ folder.developer_name }}</div>
          <div class="folder-dev-email">{{ folder.developer_email }}</div>
        </div>
        <div class="folder-stats">
          <button :class="['folder-stat-pill', activeFilter === 'Pending' && 'folder-stat-pill--active']" style="background:#fef3c7;color:#92400e;" @click="toggleFilter('Pending')">{{ countByStatus('Pending') }} Pending</button>
          <button :class="['folder-stat-pill', activeFilter === 'Ongoing' && 'folder-stat-pill--active']" style="background:#dbeafe;color:#1e40af;" @click="toggleFilter('Ongoing')">{{ countByStatus('Ongoing') }} Ongoing</button>
          <button :class="['folder-stat-pill', activeFilter === 'Completed' && 'folder-stat-pill--active']" style="background:#dcfce7;color:#166534;" @click="toggleFilter('Completed')">{{ countByStatus('Completed') }} Done</button>
        </div>

        <!-- Dev controls: visibility + copy -->
        <div class="folder-dev-controls">
          <button
            :class="['folder-vis-btn', folder.visibility === 'public' ? 'folder-vis--public' : 'folder-vis--private']"
            :disabled="visibilityUpdating"
            @click="toggleVisibility"
          >
            <span v-if="visibilityUpdating">…</span>
            <span v-else-if="folder.visibility === 'public'">🌐 Public</span>
            <span v-else>🔒 Private</span>
          </button>
          <button class="folder-copy-btn" @click="copyLink">
            <span v-if="copySuccess">Copied!</span>
            <span v-else>Copy Link</span>
          </button>
        </div>
      </div>

      <!-- Search bar -->
      <div v-if="bugs.length" class="folder-search-wrap">
        <span class="folder-search-icon">🔍</span>
        <input
          v-model="searchQuery"
          class="folder-search-input"
          type="text"
          placeholder="Search tickets by title, #number, priority…"
        />
        <button v-if="searchQuery" class="folder-search-clear" @click="searchQuery = ''">✕</button>
      </div>

      <!-- Empty state -->
      <div v-if="!bugs.length" class="folder-empty">
        <div style="font-size:40px;margin-bottom:12px;">📭</div>
        <p>No tickets assigned yet.</p>
      </div>

      <!-- No results from search -->
      <div v-else-if="searchQuery && !groupedBugs.length" class="folder-empty">
        <div style="font-size:36px;margin-bottom:12px;">🔎</div>
        <p>No tickets match "<strong>{{ searchQuery }}</strong>"</p>
      </div>

      <!-- Ticket groups by project -->
      <div v-else class="folder-groups">

        <div v-for="project in groupedBugs" :key="project.id" class="folder-project-section">

          <!-- Project header -->
          <div class="folder-project-header">
            <span class="folder-project-icon">📁</span>
            <span class="folder-project-name">{{ project.name }}</span>
            <span class="folder-project-total">{{ project.total }} ticket{{ project.total !== 1 ? 's' : '' }}</span>
          </div>

          <div v-for="group in project.groups" :key="group.status" class="folder-group">
            <div class="folder-group-header">
              <span :class="['folder-group-badge', statusClass(group.status)]">{{ group.status }}</span>
              <span class="folder-group-count">{{ group.bugs.length }} ticket{{ group.bugs.length !== 1 ? 's' : '' }}</span>
            </div>

            <div class="folder-ticket-list">
              <a
                v-for="bug in group.bugs"
                :key="bug.id"
                :href="ticketUrl(bug)"
                class="folder-ticket-card"
              >
                <div class="folder-ticket-top">
                  <span class="folder-ticket-seq">#{{ bug.sequence }}</span>
                  <span :class="['folder-ticket-priority', priorityClass(bug.priority)]">{{ bug.priority }}</span>
                  <span v-if="bug.scenario_type" class="folder-ticket-scenario">{{ bug.scenario_type }}</span>
                </div>
                <div class="folder-ticket-title">{{ bug.title }}</div>
                <div v-if="bug.subtitles && bug.subtitles.length" class="folder-ticket-subtitles">
                  <span v-for="(sub, i) in bug.subtitles.slice(0,2)" :key="i" class="folder-ticket-subtitle-chip">
                    {{ typeof sub === 'string' ? sub : sub.text }}
                  </span>
                  <span v-if="bug.subtitles.length > 2" class="folder-ticket-subtitle-chip" style="background:#f1f5f9;color:#64748b;">
                    +{{ bug.subtitles.length - 2 }} more
                  </span>
                </div>
                <div class="folder-ticket-footer">
                  <span v-if="bug.date_to_accomplish" class="folder-ticket-due">
                    📅 {{ formatDate(bug.date_to_accomplish) }}
                    <span v-if="dueDaysLabel(bug)" class="folder-due-pill" :style="{ background: dueDaysLabel(bug).color + '18', color: dueDaysLabel(bug).color }">
                      {{ dueDaysLabel(bug).text }}
                    </span>
                  </span>
                  <span v-if="bug.dev_status" class="folder-ticket-devstatus">{{ bug.dev_status }}</span>
                </div>
              </a>
            </div>
          </div>

        </div>

      </div>


    </div>

  </div>
</template>

<script setup>
const route      = useRoute()
const config     = useRuntimeConfig()
const api        = config.public.apiBase

useHead({
  style: [{
    innerHTML: `
      body{margin:0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#f8fafc;}
      .folder-page{min-height:100vh;background:#f8fafc;}
      .folder-header{background:linear-gradient(135deg,#4338ca 0%,#5b21b6 100%);padding:28px 24px 24px;}
      .folder-header-inner{max-width:900px;margin:0 auto;}
      .folder-header-eyebrow{font-size:11px;font-weight:700;letter-spacing:.12em;color:#c4b5fd;text-transform:uppercase;margin-bottom:8px;}
      .folder-header-title{font-size:26px;font-weight:800;color:#fff;margin-bottom:4px;line-height:1.2;}
      .folder-header-sub{font-size:13px;color:#ddd6fe;}
      .folder-loading{display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:calc(100vh - 120px);color:#64748b;gap:14px;}
      .folder-loading p{margin:0;font-size:14px;}
      .folder-spinner{width:36px;height:36px;border:3px solid #e2e8f0;border-top-color:#6366f1;border-radius:50%;animation:spin .7s linear infinite;}
      @keyframes spin{to{transform:rotate(360deg);}}
    `
  }]
})

const token      = route.params.token
const loading    = ref(true)
const error      = ref(null)
const folder     = ref(null)
const bugs       = ref([])
const requiresAuth = ref(false)
const authError  = ref('')

// Folder controls
const visibilityUpdating = ref(false)
const copySuccess        = ref(false)
const toastMsg           = ref('')
let toastTimer           = null

function showToast(msg) {
  toastMsg.value = msg
  clearTimeout(toastTimer)
  toastTimer = setTimeout(() => { toastMsg.value = '' }, 3000)
}

async function loadFolder(email = null) {
  loading.value      = true
  error.value        = null
  requiresAuth.value = false

  try {
    const url = `${api}/dev-folders/${token}/bugs` + (email ? `?email=${encodeURIComponent(email)}` : '')
    const data = await $fetch(url)
    folder.value = data.folder
    bugs.value   = data.bugs
    sessionStorage.setItem('devFolderToken', token)
    sessionStorage.setItem('devFolderEmail', data.folder.developer_email)
    sessionStorage.setItem('devFolderName', data.folder.developer_name)
  } catch (e) {
    if (e?.data?.requires_auth) {
      requiresAuth.value = true
      folder.value = { developer_name: e.data.developer_name }
    } else {
      error.value = e?.data?.message || 'Folder not found.'
    }
  } finally {
    loading.value = false
  }
}

function signInWithGoogle() {
  const base = config.public.apiBase.replace('/api', '')
  window.location.href = `${base}/api/auth/google/dev-folder/${token}`
}

async function toggleVisibility() {
  if (!folder.value || visibilityUpdating.value) return
  visibilityUpdating.value = true
  const next = folder.value.visibility === 'public' ? 'private' : 'public'
  try {
    await $fetch(`${api}/dev-folders/${token}`, {
      method: 'PATCH',
      body: { visibility: next },
    })
    folder.value = { ...folder.value, visibility: next }
    showToast(next === 'public'
      ? '🌐 Folder is now Public — anyone with the link can view your tickets.'
      : '🔒 Folder is now Private — email verification required to access.')
  } catch (e) {
    console.error('Failed to update visibility', e)
    showToast('Failed to update visibility. Please try again.')
  } finally {
    visibilityUpdating.value = false
  }
}

async function copyLink() {
  const url = typeof window !== 'undefined' ? window.location.href : ''
  try {
    await navigator.clipboard.writeText(url)
    copySuccess.value = true
    setTimeout(() => { copySuccess.value = false }, 2000)
  } catch {
    copySuccess.value = false
  }
}

const STATUS_ORDER  = ['Ongoing', 'Pending', 'Out of Scope', 'Completed']
const activeFilter  = ref(null)
const searchQuery   = ref('')
const showDoneSection = ref(true)

function toggleFilter(status) {
  activeFilter.value = activeFilter.value === status ? null : status
}

const groupedBugs = computed(() => {
  const q = searchQuery.value.trim().toLowerCase()
  const byProject = {}
  for (const bug of bugs.value) {
    // When no filter: hide Completed (shown in Done section). When filter active: show only that status.
    if (activeFilter.value) {
      if (bug.status !== activeFilter.value) continue
    } else {
      if (bug.status === 'Completed') continue
    }
    // Search filter
    if (q) {
      const matchesSeq   = String(bug.sequence).includes(q)
      const matchesTitle = (bug.title || '').toLowerCase().includes(q)
      const matchesPrio  = (bug.priority || '').toLowerCase().includes(q)
      const matchesType  = (bug.scenario_type || '').toLowerCase().includes(q)
      if (!matchesSeq && !matchesTitle && !matchesPrio && !matchesType) continue
    }
    const key = bug.project_id ?? 0
    const name = bug.project?.name ?? 'No Project'
    if (!byProject[key]) byProject[key] = { id: key, name, statusGroups: {} }
    if (!byProject[key].statusGroups[bug.status]) byProject[key].statusGroups[bug.status] = []
    byProject[key].statusGroups[bug.status].push(bug)
  }
  return Object.values(byProject).map(p => ({
    id: p.id,
    name: p.name,
    total: Object.values(p.statusGroups).reduce((n, arr) => n + arr.length, 0),
    groups: STATUS_ORDER.filter(s => p.statusGroups[s]?.length).map(s => ({ status: s, bugs: p.statusGroups[s] })),
  }))
})

const completedGrouped = computed(() => {
  const q = searchQuery.value.trim().toLowerCase()
  const byProject = {}
  for (const bug of bugs.value) {
    if (bug.status !== 'Completed') continue
    if (q) {
      const matchesSeq   = String(bug.sequence).includes(q)
      const matchesTitle = (bug.title || '').toLowerCase().includes(q)
      const matchesPrio  = (bug.priority || '').toLowerCase().includes(q)
      const matchesType  = (bug.scenario_type || '').toLowerCase().includes(q)
      if (!matchesSeq && !matchesTitle && !matchesPrio && !matchesType) continue
    }
    const key = bug.project_id ?? 0
    const name = bug.project?.name ?? 'No Project'
    if (!byProject[key]) byProject[key] = { id: key, name, bugs: [] }
    byProject[key].bugs.push(bug)
  }
  return Object.values(byProject).map(p => ({ id: p.id, name: p.name, bugs: p.bugs }))
})

function countByStatus(status) {
  return bugs.value.filter(b => b.status === status).length
}

function initials(name) {
  if (!name) return '?'
  return name.split(' ').map(p => p[0]).join('').toUpperCase().slice(0, 2)
}

function ticketUrl(bug) {
  const base = typeof window !== 'undefined' ? window.location.origin : 'http://localhost:3000'
  return `${base}/ticket/${bug.id}?from=${token}`
}

function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

function dueDaysLabel(bug) {
  if (!bug.date_to_accomplish) return null
  const today = new Date(new Date().toDateString())
  const due   = new Date(bug.date_to_accomplish)
  const diff  = Math.round((due - today) / 86400000)
  if (diff < 0)   return { text: diff === -1 ? '1 day overdue' : `${Math.abs(diff)} days overdue`, color: '#dc2626' }
  if (diff === 0) return { text: 'Due today',   color: '#dc2626' }
  if (diff <= 2)  return { text: `${diff} day${diff === 1 ? '' : 's'} left`, color: '#dc2626' }
  if (diff <= 7)  return { text: `${diff} days left`, color: '#d97706' }
  return { text: `${diff} days left`, color: '#16a34a' }
}

function statusClass(s) {
  return { Pending: 'status-pending', Ongoing: 'status-ongoing', Completed: 'status-completed', 'Out of Scope': 'status-oos' }[s] || ''
}

function priorityClass(p) {
  return { Critical: 'priority-critical', High: 'priority-high', Medium: 'priority-medium', Low: 'priority-low' }[p] || ''
}

onMounted(() => {
  const verifiedEmail = route.query.verified_email
  const oauthError    = route.query.auth_error

  if (oauthError) {
    requiresAuth.value = true
    authError.value    = 'true'
    loading.value      = false
    return
  }

  loadFolder(verifiedEmail || null)
})
</script>

<style scoped>
.folder-page { min-height: 100vh; background: #f8fafc; font-family: inherit; }

/* ── Toast ──────────────────────────────────────────────────────────────── */
.folder-toast {
  position: fixed;
  bottom: 28px;
  left: 50%;
  transform: translateX(-50%);
  background: #1e293b;
  color: #fff;
  font-size: 13px;
  font-weight: 500;
  padding: 12px 22px;
  border-radius: 10px;
  box-shadow: 0 6px 24px rgba(0,0,0,.18);
  z-index: 9999;
  white-space: nowrap;
  max-width: 90vw;
}
.toast-fade-enter-active, .toast-fade-leave-active { transition: opacity .25s, transform .25s; }
.toast-fade-enter-from, .toast-fade-leave-to { opacity: 0; transform: translateX(-50%) translateY(10px); }

/* ── Header (purple mailtrap style) ─────────────────────────────────────── */
.folder-header {
  background: linear-gradient(135deg, #4338ca 0%, #5b21b6 100%);
  padding: 28px 24px 24px;
}
.folder-header-inner {
  max-width: 900px;
  margin: 0 auto;
}
.folder-header-brand {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 14px;
}
.folder-brand-icon {
  font-size: 15px;
  line-height: 1;
}
.folder-brand-name {
  font-size: 12px;
  font-weight: 700;
  color: rgba(255,255,255,0.7);
  letter-spacing: .04em;
}
.folder-header-eyebrow {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: .12em;
  color: #c4b5fd;
  text-transform: uppercase;
  margin-bottom: 8px;
}
.folder-header-title {
  font-size: 26px;
  font-weight: 800;
  color: #fff;
  margin-bottom: 4px;
  line-height: 1.2;
}
.folder-header-sub {
  font-size: 13px;
  color: #ddd6fe;
}

/* ── Gate ───────────────────────────────────────────────────────────────── */
.folder-gate { display: flex; justify-content: center; align-items: center; min-height: calc(100vh - 100px); padding: 24px; }
.folder-gate-card { background: #fff; border-radius: 16px; padding: 40px 32px; text-align: center; max-width: 380px; width: 100%; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
.folder-google-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  width: 100%;
  padding: 11px 20px;
  background: #fff;
  border: 1.5px solid #e2e8f0;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  color: #1e293b;
  cursor: pointer;
  transition: background .15s, box-shadow .15s;
  box-shadow: 0 1px 3px rgba(0,0,0,.08);
}
.folder-google-btn:hover { background: #f8fafc; box-shadow: 0 2px 8px rgba(0,0,0,.12); }

/* ── Loading / Error ────────────────────────────────────────────────────── */
.folder-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: calc(100vh - 120px);
  color: #64748b;
  gap: 14px;
}
.folder-loading p { margin: 0; font-size: 14px; }
.folder-error { display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: calc(100vh - 120px); text-align: center; padding: 24px; color: #64748b; }
.folder-spinner { width: 36px; height: 36px; border: 3px solid #e2e8f0; border-top-color: #6366f1; border-radius: 50%; animation: spin 0.7s linear infinite; flex-shrink: 0; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Content ────────────────────────────────────────────────────────────── */
.folder-content { max-width: 900px; margin: 0 auto; padding: 24px 20px; }

/* ── Dev bar ────────────────────────────────────────────────────────────── */
.folder-dev-bar { display: flex; align-items: center; gap: 14px; background: #fff; border-radius: 14px; padding: 16px 20px; margin-bottom: 24px; box-shadow: 0 1px 6px rgba(0,0,0,.06); flex-wrap: wrap; }
.folder-dev-avatar { width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg,#4338ca,#7c3aed); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 16px; flex-shrink: 0; }
.folder-dev-name { font-weight: 700; font-size: 15px; color: #1e293b; }
.folder-dev-email { font-size: 12px; color: #64748b; }
.folder-stats { display: flex; gap: 8px; flex-wrap: wrap; }
.folder-stat-pill { font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 20px; border: none; cursor: pointer; transition: box-shadow .15s, outline .15s; }
.folder-stat-pill:hover { box-shadow: 0 0 0 2px currentColor; }
.folder-stat-pill--active { outline: 2px solid currentColor; outline-offset: 1px; }

/* ── Dev controls ───────────────────────────────────────────────────────── */
.folder-dev-controls { display: flex; gap: 8px; margin-left: auto; align-items: center; flex-wrap: wrap; }

.folder-vis-btn {
  padding: 6px 14px;
  border-radius: 20px;
  border: none;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: opacity .15s;
}
.folder-vis-btn:disabled { opacity: .6; cursor: default; }
.folder-vis--public  { background: #dcfce7; color: #15803d; }
.folder-vis--public:hover:not(:disabled)  { background: #bbf7d0; }
.folder-vis--private { background: #f1f5f9; color: #475569; }
.folder-vis--private:hover:not(:disabled) { background: #e2e8f0; }

.folder-copy-btn {
  padding: 6px 14px;
  border-radius: 20px;
  border: 1.5px solid #6366f1;
  color: #6366f1;
  background: #fff;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: background .15s, color .15s;
}
.folder-copy-btn:hover { background: #6366f1; color: #fff; }

/* ── Search ─────────────────────────────────────────────────────────────── */
.folder-search-wrap {
  position: relative;
  display: flex;
  align-items: center;
  margin-bottom: 20px;
}
.folder-search-icon {
  position: absolute;
  left: 14px;
  font-size: 14px;
  pointer-events: none;
  line-height: 1;
}
.folder-search-input {
  width: 100%;
  padding: 10px 40px 10px 38px;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  font-size: 13px;
  color: #1e293b;
  background: #fff;
  box-shadow: 0 1px 4px rgba(0,0,0,.05);
  outline: none;
  transition: border-color .15s, box-shadow .15s;
  box-sizing: border-box;
}
.folder-search-input:focus {
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99,102,241,.12);
}
.folder-search-input::placeholder { color: #94a3b8; }
.folder-search-clear {
  position: absolute;
  right: 12px;
  background: none;
  border: none;
  color: #94a3b8;
  font-size: 13px;
  cursor: pointer;
  padding: 2px 4px;
  border-radius: 4px;
  line-height: 1;
}
.folder-search-clear:hover { color: #64748b; background: #f1f5f9; }

/* ── Empty ──────────────────────────────────────────────────────────────── */
.folder-empty { text-align: center; padding: 60px 24px; color: #94a3b8; }

/* ── Project sections ───────────────────────────────────────────────────── */
.folder-project-section { margin-bottom: 36px; }
.folder-project-header {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 16px;
  background: linear-gradient(135deg, #4338ca 0%, #5b21b6 100%);
  border-radius: 10px;
  margin-bottom: 16px;
}
.folder-project-icon { font-size: 14px; }
.folder-project-name { font-size: 13px; font-weight: 700; color: #fff; flex: 1; }
.folder-project-total { font-size: 11px; color: #c4b5fd; font-weight: 600; }

/* ── Groups ─────────────────────────────────────────────────────────────── */
.folder-group { margin-bottom: 20px; padding-left: 4px; }
.folder-group-header { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
.folder-group-badge { font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 20px; }
.folder-group-count { font-size: 12px; color: #94a3b8; }
.status-pending   { background: #fef3c7; color: #92400e; }
.status-ongoing   { background: #dbeafe; color: #1e40af; }
.status-completed { background: #dcfce7; color: #166534; }
.status-oos       { background: #f1f5f9; color: #64748b; }

/* ── Ticket cards ───────────────────────────────────────────────────────── */
.folder-ticket-list { display: flex; flex-direction: column; gap: 10px; }
.folder-ticket-card { background: #fff; border-radius: 12px; padding: 16px 18px; text-decoration: none; color: inherit; box-shadow: 0 1px 4px rgba(0,0,0,.06); border: 1.5px solid #f1f5f9; transition: border-color .15s, box-shadow .15s; display: block; }
.folder-ticket-card:hover { border-color: #6366f1; box-shadow: 0 4px 16px rgba(99,102,241,.12); }

.folder-ticket-top { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; flex-wrap: wrap; }
.folder-ticket-seq { font-size: 11px; color: #94a3b8; font-weight: 600; }
.folder-ticket-priority { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 20px; }
.priority-critical { background: #fee2e2; color: #991b1b; }
.priority-high     { background: #ffedd5; color: #9a3412; }
.priority-medium   { background: #fef3c7; color: #92400e; }
.priority-low      { background: #dcfce7; color: #166534; }
.folder-ticket-scenario { font-size: 10px; color: #6366f1; background: #eef2ff; padding: 2px 8px; border-radius: 20px; font-weight: 600; }

.folder-ticket-title { font-size: 14px; font-weight: 600; color: #1e293b; margin-bottom: 6px; line-height: 1.4; }

.folder-ticket-subtitles { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 8px; }
.folder-ticket-subtitle-chip { font-size: 11px; background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; border-radius: 6px; padding: 2px 8px; }

.folder-ticket-footer { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.folder-ticket-due { font-size: 11px; color: #64748b; display: flex; align-items: center; gap: 6px; }
.folder-due-pill { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 20px; }
.folder-ticket-devstatus { font-size: 11px; background: #f1f5f9; color: #475569; padding: 2px 8px; border-radius: 20px; margin-left: auto; }

/* ── Done section ───────────────────────────────────────────────────────── */
.folder-done-section { margin-top: 8px; }

.folder-done-toggle {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
  background: #f0fdf4;
  border: 1.5px solid #bbf7d0;
  border-radius: 12px;
  padding: 12px 18px;
  cursor: pointer;
  font-family: inherit;
  transition: background .15s, border-color .15s;
}
.folder-done-toggle:hover { background: #dcfce7; border-color: #86efac; }

.folder-done-check { font-size: 14px; color: #16a34a; font-weight: 800; }
.folder-done-label { font-size: 14px; font-weight: 700; color: #15803d; flex: 1; text-align: left; }
.folder-done-count-badge { font-size: 11px; font-weight: 700; background: #16a34a; color: #fff; padding: 2px 9px; border-radius: 20px; }
.folder-done-chevron { color: #16a34a; transition: transform .2s; flex-shrink: 0; }

.folder-done-body { padding-top: 14px; display: flex; flex-direction: column; gap: 20px; }

.folder-done-project-header {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 14px;
  background: #f0fdf4;
  border-radius: 8px;
  margin-bottom: 10px;
}
.folder-done-project-name { font-size: 12px; font-weight: 700; color: #166534; flex: 1; }
.folder-done-project-count { font-size: 11px; color: #86efac; font-weight: 600; color: #15803d; }

.folder-ticket-card--done { opacity: 0.75; border-color: #dcfce7; }
.folder-ticket-card--done:hover { opacity: 1; border-color: #16a34a; box-shadow: 0 4px 16px rgba(22,163,74,.1); }

.folder-done-tick { font-size: 11px; color: #16a34a; font-weight: 800; }
.folder-ticket-title--done { color: #64748b; text-decoration: line-through; text-decoration-color: #94a3b8; }
</style>
