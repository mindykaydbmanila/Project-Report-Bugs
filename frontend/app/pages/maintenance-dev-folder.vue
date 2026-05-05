<template>
  <div class="mdf-page">

    <!-- Toast -->
    <Transition name="mdf-toast-fade">
      <div v-if="toast" class="mdf-toast">{{ toast }}</div>
    </Transition>

    <!-- Header -->
    <header class="mdf-header">
      <div class="mdf-header-inner">
        <div style="display:flex;align-items:center;gap:14px;">
          <div class="mdf-header-logo">
            <div style="font-size:20px;">🔧</div>
            <div>
              <div style="font-weight:700;color:#fff;font-size:15px;">Maintenance Tracker</div>
              <div style="font-size:11px;color:#a7f3d0;">Dev Folder</div>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Loading -->
    <div v-if="loading" class="mdf-loading">
      <div class="mdf-spinner"></div>
      <p>Loading tickets…</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="mdf-error">
      <div style="font-size:40px;margin-bottom:12px;">⚠️</div>
      <h2>Something went wrong</h2>
      <p>{{ error }}</p>
    </div>

    <!-- No email provided -->
    <div v-else-if="!email" class="mdf-error">
      <div style="font-size:40px;margin-bottom:12px;">📭</div>
      <h2>No email specified</h2>
      <p>Please access this page via the assigned developer link in a maintenance ticket.</p>
    </div>

    <!-- Content -->
    <div v-else class="mdf-content">

      <!-- Dev info bar -->
      <div class="mdf-dev-bar">
        <div class="mdf-dev-avatar">{{ initials(email) }}</div>

        <!-- Email + stat pills grouped together -->
        <div class="mdf-dev-info">
          <div class="mdf-dev-email-row">
            <span class="mdf-dev-email">{{ email }}</span>
            <div class="mdf-stats">
              <button
                v-for="pill in statusPills"
                :key="pill.status"
                :class="['mdf-stat-pill', activeFilter === pill.status ? 'mdf-stat-pill--active' : '']"
                :style="activeFilter === pill.status ? pill.activeStyle : pill.style"
                :title="activeFilter === pill.status ? 'Click to show all' : `Filter: ${pill.status}`"
                @click="toggleFilter(pill.status)"
              >
                <span class="mdf-stat-count">{{ countByStatus(pill.status) }}</span>
                {{ pill.label }}
                <svg v-if="activeFilter === pill.status" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="margin-left:2px;opacity:.7;"><path d="M18 6L6 18M6 6l12 12"/></svg>
              </button>
            </div>
          </div>
          <div class="mdf-dev-sub">Maintenance Ticket Folder</div>
        </div>

        <!-- Actions: Visibility + Share (icon only) -->
        <div class="mdf-bar-actions">
          <button
            :class="['mdf-vis-btn', visibility === 'public' ? 'mdf-vis-pub' : 'mdf-vis-priv']"
            :title="visibility === 'public' ? 'Click to make private' : 'Click to make public'"
            @click="toggleVisibility"
          >
            <svg v-if="visibility === 'public'" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
            <svg v-else width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            {{ visibility === 'public' ? 'Public' : 'Private' }}
          </button>
          <button class="mdf-share-btn" @click="shareFolder" title="Copy shareable link">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
          </button>
        </div>
      </div>

      <!-- Search bar -->
      <div v-if="tickets.length" class="mdf-search-wrap">
        <span class="mdf-search-icon">🔍</span>
        <input
          v-model="searchQuery"
          class="mdf-search-input"
          type="text"
          placeholder="Search by ticket number, client, request…"
        />
        <button v-if="searchQuery" class="mdf-search-clear" @click="searchQuery = ''">✕</button>
      </div>

      <!-- Filter bar -->
      <div v-if="tickets.length" class="mdf-filter-bar">
        <select v-model="activeDevStatusFilter" class="mdf-filter-select">
          <option value="">Dev Status</option>
          <option value="Not Started">Not Started</option>
          <option value="In Progress">In Progress</option>
          <option value="Ready for QA">Ready for QA</option>
          <option value="Blocked">Blocked</option>
        </select>
        <button v-if="activeDevStatusFilter" class="mdf-filter-clear-btn" @click="activeDevStatusFilter = ''">
          ✕ Clear filter
        </button>
      </div>

      <!-- Active status filter banner -->
      <div v-if="activeFilter" class="mdf-filter-banner">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
        Showing <strong>{{ activeFilter }}</strong> tickets only &nbsp;—&nbsp;
        <button class="mdf-filter-clear" @click="activeFilter = null">Clear filter</button>
      </div>

      <!-- Empty state -->
      <div v-if="!tickets.length" class="mdf-empty">
        <div style="font-size:40px;margin-bottom:12px;">📭</div>
        <p>No maintenance tickets assigned yet.</p>
      </div>

      <!-- No results for search -->
      <div v-else-if="searchQuery && !filteredTickets.length" class="mdf-empty">
        <div style="font-size:36px;margin-bottom:12px;">🔎</div>
        <p>No tickets match "<strong>{{ searchQuery }}</strong>"</p>
      </div>

      <!-- No results for filter -->
      <div v-else-if="filteredTickets.length === 0" class="mdf-empty">
        <div style="font-size:36px;margin-bottom:12px;">🔍</div>
        <p>No <strong>{{ activeFilter || activeDevStatusFilter }}</strong> tickets.</p>
        <button class="mdf-filter-clear" style="margin-top:10px;font-size:13px;" @click="activeFilter = null; activeDevStatusFilter = ''">Show all tickets</button>
      </div>

      <!-- Ticket groups by project -->
      <div v-else class="mdf-groups">
        <div v-for="project in groupedTickets" :key="project.id" class="mdf-project-section">
          <template v-for="pd in [getProjectData(project)]" :key="project.id + '_pd'">

          <!-- Project header -->
          <div class="mdf-project-header">
            <span class="mdf-project-icon">📁</span>
            <span class="mdf-project-name">{{ project.name }}</span>
            <span class="mdf-project-total">{{ project.total }} ticket{{ project.total !== 1 ? 's' : '' }}</span>
          </div>

          <div v-for="group in pd.groups" :key="group.status" class="mdf-group">
            <div class="mdf-group-header">
              <span :class="['mdf-group-badge', statusClass(group.status)]">{{ group.status }}</span>
              <span class="mdf-group-count">{{ group.tickets.length }} ticket{{ group.tickets.length !== 1 ? 's' : '' }}</span>
            </div>

            <div class="mdf-ticket-list">
              <a
                v-for="t in group.tickets"
                :key="t.id"
                :href="ticketUrl(t)"
                target="_blank"
                rel="noopener"
                class="mdf-ticket-card"
              >
                <div class="mdf-ticket-top">
                  <span class="mdf-ticket-num">{{ t.ticket_number }}</span>
                  <span v-if="t.dev_status" :class="['mdf-dev-status', devStatusClass(t.dev_status)]">{{ t.dev_status }}</span>
                  <span v-if="isDevAssigned(t)" class="mdf-role-chip mdf-role-dev">Dev</span>
                  <span v-if="isQaAssigned(t)" class="mdf-role-chip mdf-role-qa">QA</span>
                </div>
                <div class="mdf-ticket-client">{{ t.client }}</div>
                <div class="mdf-ticket-request">{{ stripHtml(t.request) }}</div>
                <div class="mdf-ticket-footer">
                  <span v-if="t.target_date" class="mdf-ticket-due">
                    📅 {{ formatDate(t.target_date) }}
                    <span v-if="dueDaysLabel(t)" class="mdf-due-pill" :style="{ background: dueDaysLabel(t).color + '18', color: dueDaysLabel(t).color }">
                      {{ dueDaysLabel(t).text }}
                    </span>
                  </span>
                  <span v-if="t.project" class="mdf-ticket-project-chip">{{ t.project.name }}</span>
                </div>
              </a>
            </div>
          </div>

          <!-- Per-project pagination -->
          <div v-if="pd.totalPages > 1" class="mdf-pagination">
            <div class="mdf-pagination-info">
              Showing {{ pd.showingFrom }}–{{ pd.showingTo }} of {{ project.total }} ticket{{ project.total !== 1 ? 's' : '' }}
            </div>
            <div class="mdf-pagination-controls">
              <button class="mdf-page-btn" :disabled="pd.currentPage === 1" @click="setProjectPage(project.id, pd.currentPage - 1)">‹</button>
              <template v-for="(p, i) in getProjectPageNums(pd.currentPage, pd.totalPages)" :key="i">
                <span v-if="p === '...'" class="mdf-page-ellipsis">…</span>
                <button v-else :class="['mdf-page-btn', pd.currentPage === p && 'mdf-page-btn--active']" @click="setProjectPage(project.id, p)">{{ p }}</button>
              </template>
              <button class="mdf-page-btn" :disabled="pd.currentPage === pd.totalPages" @click="setProjectPage(project.id, pd.currentPage + 1)">›</button>
            </div>
          </div>

          </template>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
const route  = useRoute()
const config = useRuntimeConfig()
const api    = config.public.apiBase

const email   = computed(() => route.query.email || '')
const loading = ref(true)
const error   = ref(null)
const tickets = ref([])

// ── Toast ─────────────────────────────────────────────────────────────────────
const toast = ref(null)
let toastTimer = null
function showToast(msg) {
  toast.value = msg
  if (toastTimer) clearTimeout(toastTimer)
  toastTimer = setTimeout(() => { toast.value = null }, 2400)
}

// ── Visibility (synced with index.vue via same localStorage key) ──────────────
const visibility = ref('public')

function loadVisibility() {
  try {
    const stored = localStorage.getItem('maint_dev_visibilities')
    if (stored && email.value) {
      const map = JSON.parse(stored)
      visibility.value = map[email.value] || 'public'
    }
  } catch {}
}

function toggleVisibility() {
  const next = visibility.value === 'public' ? 'private' : 'public'
  visibility.value = next
  try {
    const stored = localStorage.getItem('maint_dev_visibilities')
    const map = stored ? JSON.parse(stored) : {}
    map[email.value] = next
    localStorage.setItem('maint_dev_visibilities', JSON.stringify(map))
  } catch {}
  showToast(`Folder set to ${next}`)
}

// ── Share ─────────────────────────────────────────────────────────────────────
async function shareFolder() {
  const url = typeof window !== 'undefined' ? window.location.href : ''
  try {
    await navigator.clipboard.writeText(url)
    showToast('Link copied to clipboard!')
  } catch {
    showToast('Could not copy link')
  }
}

// ── Status filter ─────────────────────────────────────────────────────────────
const activeFilter        = ref(null)
const activeDevStatusFilter = ref('')
const searchQuery         = ref('')

const statusPills = [
  {
    status:      'Pending',
    label:       'Pending',
    style:       'background:#fef3c7;color:#92400e;',
    activeStyle: 'background:#f59e0b;color:#fff;box-shadow:0 0 0 2px #f59e0b44;',
  },
  {
    status:      'In Progress',
    label:       'In Progress',
    style:       'background:#dbeafe;color:#1e40af;',
    activeStyle: 'background:#2563eb;color:#fff;box-shadow:0 0 0 2px #2563eb44;',
  },
  {
    status:      'Completed',
    label:       'Done',
    style:       'background:#dcfce7;color:#166534;',
    activeStyle: 'background:#16a34a;color:#fff;box-shadow:0 0 0 2px #16a34a44;',
  },
]

function toggleFilter(status) {
  activeFilter.value = activeFilter.value === status ? null : status
}

// ── Pagination ────────────────────────────────────────────────────────────────
const PAGE_SIZE    = 6
const projectPages = reactive({})

watch([searchQuery, activeFilter, activeDevStatusFilter], () => {
  for (const k in projectPages) delete projectPages[k]
})

function setProjectPage(projectId, page) {
  projectPages[projectId] = page
}

function getProjectPageNums(current, total) {
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  const pages = [1]
  if (current > 3) pages.push('...')
  for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) pages.push(i)
  if (current < total - 2) pages.push('...')
  if (total > 1) pages.push(total)
  return pages
}

function getProjectData(project) {
  const page  = projectPages[project.id] || 1
  const start = (page - 1) * PAGE_SIZE
  const slice = project.allTickets.slice(start, start + PAGE_SIZE)
  const byStatus = {}
  for (const t of slice) {
    if (!byStatus[t.status]) byStatus[t.status] = []
    byStatus[t.status].push(t)
  }
  return {
    groups:      STATUS_ORDER.filter(s => byStatus[s]?.length).map(s => ({ status: s, tickets: byStatus[s] })),
    currentPage: page,
    totalPages:  Math.max(1, Math.ceil(project.allTickets.length / PAGE_SIZE)),
    showingFrom: project.allTickets.length === 0 ? 0 : start + 1,
    showingTo:   Math.min(page * PAGE_SIZE, project.allTickets.length),
  }
}

// ── Filtered + grouped tickets ────────────────────────────────────────────────
const filteredTickets = computed(() => {
  const q = searchQuery.value.trim().toLowerCase()
  return tickets.value.filter(t => {
    if (activeFilter.value) {
      if (activeFilter.value === 'In Progress') {
        if (t.status !== 'In Progress' && t.dev_status !== 'In Progress') return false
      } else {
        if (t.status !== activeFilter.value) return false
      }
    }
    if (activeDevStatusFilter.value) {
      const ds = t.dev_status || 'Not Started'
      if (ds !== activeDevStatusFilter.value) return false
    }
    if (q) {
      return (t.ticket_number || '').toLowerCase().includes(q)
        || (t.client || '').toLowerCase().includes(q)
        || stripHtml(t.request || '').toLowerCase().includes(q)
    }
    return true
  })
})

useHead({
  title: computed(() => email.value ? `Dev Folder · ${email.value}` : 'Maintenance Dev Folder'),
})

const STATUS_ORDER = ['In Progress', 'Pending', 'On Hold', 'Completed', 'Cancelled']

const groupedTickets = computed(() => {
  const byProject = {}
  for (const t of filteredTickets.value) {
    const key  = t.maintenance_project_id ?? 0
    const name = t.project?.name ?? 'No Project'
    if (!byProject[key]) byProject[key] = { id: key, name, allTickets: [] }
    byProject[key].allTickets.push(t)
  }
  return Object.values(byProject).map(p => ({
    id:         p.id,
    name:       p.name,
    total:      p.allTickets.length,
    allTickets: p.allTickets,
  }))
})

function countByStatus(status) {
  if (status === 'In Progress') {
    return tickets.value.filter(t => t.status === 'In Progress' || t.dev_status === 'In Progress').length
  }
  return tickets.value.filter(t => t.status === status).length
}

function stripHtml(html) {
  if (!html) return ''
  return html.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim()
}

function initials(emailStr) {
  if (!emailStr) return '?'
  const local = emailStr.split('@')[0]
  return local.slice(0, 2).toUpperCase()
}

function ticketUrl(t) {
  const base = typeof window !== 'undefined' ? window.location.origin : 'http://localhost:3000'
  return `${base}/maintenance-ticket/${t.id}?from=folder&email=${encodeURIComponent(email.value)}`
}

function isDevAssigned(t) {
  return (t.assigned_devs || []).includes(email.value)
}

function isQaAssigned(t) {
  return (t.assigned_qa || []).includes(email.value)
}

function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

function dueDaysLabel(t) {
  if (!t.target_date) return null
  if (t.status === 'Completed' || t.status === 'Cancelled') return null
  const today = new Date(new Date().toDateString())
  const due   = new Date(t.target_date)
  const diff  = Math.round((due - today) / 86400000)
  if (diff < 0)   return { text: `${Math.abs(diff)} day${Math.abs(diff) === 1 ? '' : 's'} overdue`, color: '#dc2626' }
  if (diff === 0) return { text: 'Due today', color: '#dc2626' }
  if (diff <= 2)  return { text: `${diff} day${diff === 1 ? '' : 's'} left`, color: '#dc2626' }
  if (diff <= 7)  return { text: `${diff} days left`, color: '#d97706' }
  return { text: `${diff} days left`, color: '#16a34a' }
}

function statusClass(s) {
  return {
    'Pending':     'status-pending',
    'In Progress': 'status-ongoing',
    'On Hold':     'status-hold',
    'Completed':   'status-completed',
    'Cancelled':   'status-cancelled',
  }[s] || ''
}

function devStatusClass(s) {
  return {
    'Not Started':  'ds-not-started',
    'In Progress':  'ds-in-progress',
    'Ready for QA': 'ds-ready',
    'Blocked':      'ds-blocked',
  }[s] || ''
}

async function loadTickets() {
  if (!email.value) { loading.value = false; return }
  loading.value = true
  error.value   = null
  try {
    const data = await $fetch(`${api}/maintenance-dev-folder?email=${encodeURIComponent(email.value)}`)
    tickets.value = data.tickets
  } catch (e) {
    error.value = e?.data?.error || 'Failed to load tickets.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadTickets()
  loadVisibility()
})
</script>

<style scoped>
.mdf-page { min-height: 100vh; background: #f1f5f9; font-family: Inter, Arial, sans-serif; }

/* Toast */
.mdf-toast {
  position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%);
  background: #1e293b; color: #fff; font-size: 13px; font-weight: 500;
  padding: 10px 20px; border-radius: 10px; z-index: 9999;
  box-shadow: 0 4px 20px rgba(0,0,0,.18); white-space: nowrap; pointer-events: none;
}
.mdf-toast-fade-enter-active, .mdf-toast-fade-leave-active { transition: opacity .25s, transform .25s; }
.mdf-toast-fade-enter-from, .mdf-toast-fade-leave-to { opacity: 0; transform: translateX(-50%) translateY(8px); }

/* Header */
.mdf-header { background: linear-gradient(135deg, #064e3b 0%, #065f46 60%, #047857 100%); padding: 0; }
.mdf-header-inner { max-width: 960px; margin: 0 auto; padding: 16px 24px; display: flex; align-items: center; }
.mdf-header-logo { display: flex; align-items: center; gap: 10px; }
.mdf-back-link { display: inline-flex; align-items: center; gap: 5px; color: rgba(255,255,255,0.8); font-size: 13px; font-weight: 500; text-decoration: none; padding: 5px 10px; border-radius: 6px; transition: background 0.15s; }
.mdf-back-link:hover { background: rgba(255,255,255,0.12); color: #fff; }

/* Loading / Error */
.mdf-loading { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 80px; gap: 16px; color: #64748b; }
.mdf-loading p { margin: 0; font-size: 14px; }
.mdf-spinner { width: 36px; height: 36px; border: 3px solid #e2e8f0; border-top-color: #059669; border-radius: 50%; animation: spin .7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.mdf-error { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 80px; text-align: center; color: #64748b; }
.mdf-error h2 { margin-bottom: 8px; color: #1e293b; }

/* Content */
.mdf-content { max-width: 960px; margin: 0 auto; padding: 24px 20px; }

/* Dev bar */
.mdf-dev-bar {
  display: flex; align-items: center; gap: 14px;
  background: #fff; border-radius: 14px; padding: 16px 20px;
  margin-bottom: 16px; box-shadow: 0 1px 6px rgba(0,0,0,.06);
  flex-wrap: wrap;
}
.mdf-dev-avatar {
  width: 48px; height: 48px; border-radius: 50%;
  background: linear-gradient(135deg, #064e3b, #059669);
  color: #fff; display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 16px; flex-shrink: 0;
}
.mdf-dev-info { flex: 1; min-width: 0; }
.mdf-dev-email-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.mdf-dev-email { font-weight: 700; font-size: 15px; color: #1e293b; white-space: nowrap; }
.mdf-dev-sub { font-size: 12px; color: #64748b; margin-top: 3px; }

/* Stat pills */
.mdf-stats { display: flex; gap: 6px; flex-wrap: wrap; }
.mdf-stat-pill {
  font-size: 11px; font-weight: 600; padding: 5px 11px; border-radius: 20px;
  border: none; cursor: pointer; display: flex; align-items: center; gap: 4px;
  transition: all .15s; white-space: nowrap;
}
.mdf-stat-pill:hover { filter: brightness(.93); }
.mdf-stat-count { font-weight: 800; }

/* Bar actions */
.mdf-bar-actions { display: flex; gap: 8px; flex-wrap: wrap; margin-left: auto; }

.mdf-vis-btn {
  display: inline-flex; align-items: center; gap: 5px;
  font-size: 11px; font-weight: 700; padding: 6px 12px; border-radius: 8px;
  border: none; cursor: pointer; transition: all .15s; white-space: nowrap;
}
.mdf-share-btn {
  display: inline-flex; align-items: center; justify-content: center;
  width: 32px; height: 32px; border-radius: 8px;
  border: none; cursor: pointer; transition: all .15s; flex-shrink: 0;
}
.mdf-vis-pub  { background: #d1fae5; color: #065f46; }
.mdf-vis-pub:hover  { background: #a7f3d0; }
.mdf-vis-priv { background: #fee2e2; color: #991b1b; }
.mdf-vis-priv:hover { background: #fecaca; }
.mdf-share-btn { background: #ede9fe; color: #5b21b6; }
.mdf-share-btn:hover { background: #ddd6fe; }

/* Filter banner */
.mdf-filter-banner {
  display: flex; align-items: center; gap: 6px;
  font-size: 12px; color: #475569; background: #f8fafc;
  border: 1px solid #e2e8f0; border-radius: 8px;
  padding: 8px 14px; margin-bottom: 16px;
}
.mdf-filter-banner strong { color: #1e293b; }
.mdf-filter-clear {
  background: none; border: none; cursor: pointer;
  color: #059669; font-size: 12px; font-weight: 700; padding: 0; text-decoration: underline;
}
.mdf-filter-clear:hover { color: #047857; }

/* Empty */
.mdf-empty { text-align: center; padding: 60px 24px; color: #94a3b8; }

/* Project sections */
.mdf-project-section { margin-bottom: 36px; }
.mdf-project-header { display: flex; align-items: center; gap: 10px; padding: 10px 16px; background: linear-gradient(135deg, #064e3b 0%, #059669 100%); border-radius: 10px; margin-bottom: 16px; }
.mdf-project-icon { font-size: 14px; }
.mdf-project-name { font-size: 13px; font-weight: 700; color: #fff; flex: 1; }
.mdf-project-total { font-size: 11px; color: #a7f3d0; font-weight: 600; }

/* Groups */
.mdf-group { margin-bottom: 20px; padding-left: 4px; }
.mdf-group-header { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
.mdf-group-badge { font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 20px; }
.mdf-group-count { font-size: 12px; color: #94a3b8; }
.status-pending   { background: #fef3c7; color: #92400e; }
.status-ongoing   { background: #dbeafe; color: #1e40af; }
.status-hold      { background: #fff7ed; color: #c2410c; }
.status-completed { background: #dcfce7; color: #166534; }
.status-cancelled { background: #f1f5f9; color: #64748b; }

/* Ticket cards */
.mdf-ticket-list { display: flex; flex-direction: column; gap: 10px; }
.mdf-ticket-card { background: #fff; border-radius: 12px; padding: 16px 18px; text-decoration: none; color: inherit; box-shadow: 0 1px 4px rgba(0,0,0,.06); border: 1.5px solid #f1f5f9; transition: border-color .15s, box-shadow .15s; display: block; }
.mdf-ticket-card:hover { border-color: #059669; box-shadow: 0 4px 16px rgba(5,150,105,.12); }

.mdf-ticket-top { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; flex-wrap: wrap; }
.mdf-ticket-num { font-size: 12px; font-weight: 700; color: #059669; background: #d1fae5; padding: 2px 8px; border-radius: 6px; }
.mdf-ticket-client { font-size: 15px; font-weight: 700; color: #1e293b; margin-bottom: 4px; }
.mdf-ticket-request { font-size: 13px; color: #475569; line-height: 1.5; margin-bottom: 8px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

/* Dev status chips */
.mdf-dev-status { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 20px; }
.ds-not-started { background: #f1f5f9; color: #64748b; }
.ds-in-progress { background: #dbeafe; color: #1d4ed8; }
.ds-ready       { background: #dcfce7; color: #15803d; }
.ds-blocked     { background: #fee2e2; color: #b91c1c; }

/* Role chips */
.mdf-role-chip { font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 20px; }
.mdf-role-dev { background: #ede9fe; color: #6d28d9; }
.mdf-role-qa  { background: #fce7f3; color: #9d174d; }

/* Footer */
.mdf-ticket-footer { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.mdf-ticket-due { font-size: 11px; color: #64748b; display: flex; align-items: center; gap: 6px; }
.mdf-due-pill { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 20px; }
.mdf-ticket-project-chip { font-size: 11px; background: #f0fdf4; color: #166534; padding: 2px 8px; border-radius: 20px; margin-left: auto; font-weight: 600; border: 1px solid #bbf7d0; }

/* Badge utilities (shared) */
.badge { font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px; }

/* ── Search bar ─────────────────────────────────────────────────────────── */
.mdf-search-wrap {
  position: relative;
  display: flex;
  align-items: center;
  margin-bottom: 12px;
}
.mdf-search-icon {
  position: absolute;
  left: 14px;
  font-size: 14px;
  pointer-events: none;
  line-height: 1;
}
.mdf-search-input {
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
  font-family: inherit;
}
.mdf-search-input:focus {
  border-color: #059669;
  box-shadow: 0 0 0 3px rgba(5,150,105,.12);
}
.mdf-search-input::placeholder { color: #94a3b8; }
.mdf-search-clear {
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
.mdf-search-clear:hover { color: #64748b; background: #f1f5f9; }

/* ── Filter bar ─────────────────────────────────────────────────────────── */
.mdf-filter-bar {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 12px;
  flex-wrap: wrap;
}
.mdf-filter-select {
  appearance: none;
  -webkit-appearance: none;
  padding: 6px 28px 6px 12px;
  border: 1.5px solid #e2e8f0;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
  color: #475569;
  background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%2394a3b8' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E") no-repeat right 10px center;
  cursor: pointer;
  outline: none;
  transition: border-color .15s, box-shadow .15s;
  font-family: inherit;
}
.mdf-filter-select:focus { border-color: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,.12); }
.mdf-filter-select:hover { border-color: #059669; }
.mdf-filter-clear-btn {
  padding: 6px 12px;
  border-radius: 8px;
  border: 1.5px solid #fca5a5;
  background: #fff;
  color: #dc2626;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  font-family: inherit;
  transition: background .15s;
}
.mdf-filter-clear-btn:hover { background: #fef2f2; }

/* ── Pagination ─────────────────────────────────────────────────────────── */
.mdf-pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
  margin-top: 24px;
  padding: 16px 0 8px;
  border-top: 1.5px solid #f1f5f9;
}
.mdf-pagination-info { font-size: 12px; color: #94a3b8; font-weight: 500; }
.mdf-pagination-controls { display: flex; align-items: center; gap: 4px; }
.mdf-page-btn {
  min-width: 34px;
  height: 34px;
  padding: 0 8px;
  border-radius: 8px;
  border: 1.5px solid #e2e8f0;
  background: #fff;
  color: #475569;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: background .15s, border-color .15s, color .15s;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: inherit;
}
.mdf-page-btn:hover:not(:disabled) { background: #f0fdf4; border-color: #059669; color: #059669; }
.mdf-page-btn:disabled { opacity: 0.4; cursor: default; }
.mdf-page-btn--active { background: #059669; border-color: #059669; color: #fff; }
.mdf-page-btn--active:hover:not(:disabled) { background: #047857; }
.mdf-page-ellipsis { font-size: 13px; color: #94a3b8; padding: 0 4px; line-height: 34px; }
</style>
