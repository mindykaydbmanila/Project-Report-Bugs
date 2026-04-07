<template>
  <div class="folder-page">

    <!-- Toast notification -->
    <transition name="toast-fade">
      <div v-if="toastMsg" class="folder-toast">{{ toastMsg }}</div>
    </transition>

    <!-- Header — dark-green mailtrap style -->
    <header class="folder-header">
      <div class="folder-header-inner">
        <div class="folder-header-eyebrow">
          DEV FOLDER<span v-if="folder && folder.project_name"> · {{ folder.project_name.toUpperCase() }}</span>
        </div>
        <div class="folder-header-title">{{ folder ? folder.developer_name : 'Developer Folder' }}</div>
        <div v-if="folder" class="folder-header-sub">{{ folder.developer_email }}</div>
      </div>
    </header>

    <!-- Email Gate (private folders) -->
    <div v-if="requiresAuth" class="folder-gate">
      <div class="folder-gate-card">
        <div style="font-size:40px;margin-bottom:12px;">🔒</div>
        <h2 style="margin:0 0 6px;font-size:18px;color:#1e293b;">Private Folder</h2>
        <p style="color:#64748b;font-size:13px;margin:0 0 20px;">
          This folder is private. Enter your email address to access your tickets.
        </p>
        <input
          v-model="emailInput"
          type="email"
          placeholder="your@email.com"
          class="folder-gate-input"
          @keydown.enter="verifyEmail"
        />
        <div v-if="authError" style="color:#dc2626;font-size:12px;margin-top:6px;">{{ authError }}</div>
        <button class="folder-gate-btn" @click="verifyEmail">Access My Tickets</button>
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
          <span class="folder-stat-pill" style="background:#fef3c7;color:#92400e;">{{ countByStatus('Pending') }} Pending</span>
          <span class="folder-stat-pill" style="background:#dbeafe;color:#1e40af;">{{ countByStatus('Ongoing') }} Ongoing</span>
          <span class="folder-stat-pill" style="background:#dcfce7;color:#166534;">{{ countByStatus('Completed') }} Done</span>
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

      <!-- Empty state -->
      <div v-if="!bugs.length" class="folder-empty">
        <div style="font-size:40px;margin-bottom:12px;">📭</div>
        <p>No tickets assigned yet.</p>
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
                target="_blank"
                rel="noopener"
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
const route  = useRoute()
const config = useRuntimeConfig()
const api    = config.public.apiBase

const token      = route.params.token
const loading    = ref(true)
const error      = ref(null)
const folder     = ref(null)
const bugs       = ref([])
const requiresAuth = ref(false)
const emailInput = ref('')
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

async function verifyEmail() {
  authError.value = ''
  if (!emailInput.value) {
    authError.value = 'Please enter your email.'
    return
  }
  loading.value      = true
  requiresAuth.value = false
  try {
    const url = `${api}/dev-folders/${token}/bugs?email=${encodeURIComponent(emailInput.value)}`
    const data = await $fetch(url)
    folder.value = data.folder
    bugs.value   = data.bugs
  } catch (e) {
    if (e?.data?.requires_auth) {
      requiresAuth.value = true
      authError.value = 'Email does not match. Try again.'
    } else {
      error.value = e?.data?.message || 'Something went wrong.'
    }
  } finally {
    loading.value = false
  }
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

const STATUS_ORDER = ['Ongoing', 'Pending', 'Out of Scope', 'Completed']

const groupedBugs = computed(() => {
  const byProject = {}
  for (const bug of bugs.value) {
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

function countByStatus(status) {
  return bugs.value.filter(b => b.status === status).length
}

function initials(name) {
  if (!name) return '?'
  return name.split(' ').map(p => p[0]).join('').toUpperCase().slice(0, 2)
}

function ticketUrl(bug) {
  const base = typeof window !== 'undefined' ? window.location.origin : 'http://localhost:3000'
  return `${base}/ticket/${bug.id}`
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

onMounted(() => loadFolder())
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
.folder-gate-input { width: 100%; box-sizing: border-box; padding: 10px 14px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 14px; outline: none; margin-bottom: 4px; }
.folder-gate-input:focus { border-color: #6366f1; }
.folder-gate-btn { margin-top: 14px; width: 100%; padding: 10px; background: #4f46e5; color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; }
.folder-gate-btn:hover { background: #4338ca; }

/* ── Loading / Error ────────────────────────────────────────────────────── */
.folder-loading, .folder-error { text-align: center; padding: 80px 24px; color: #64748b; }
.folder-spinner { width: 32px; height: 32px; border: 3px solid #e2e8f0; border-top-color: #6366f1; border-radius: 50%; animation: spin 0.7s linear infinite; margin: 0 auto 16px; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Content ────────────────────────────────────────────────────────────── */
.folder-content { max-width: 900px; margin: 0 auto; padding: 24px 20px; }

/* ── Dev bar ────────────────────────────────────────────────────────────── */
.folder-dev-bar { display: flex; align-items: center; gap: 14px; background: #fff; border-radius: 14px; padding: 16px 20px; margin-bottom: 24px; box-shadow: 0 1px 6px rgba(0,0,0,.06); flex-wrap: wrap; }
.folder-dev-avatar { width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg,#4338ca,#7c3aed); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 16px; flex-shrink: 0; }
.folder-dev-name { font-weight: 700; font-size: 15px; color: #1e293b; }
.folder-dev-email { font-size: 12px; color: #64748b; }
.folder-stats { display: flex; gap: 8px; flex-wrap: wrap; }
.folder-stat-pill { font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 20px; }

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
</style>
