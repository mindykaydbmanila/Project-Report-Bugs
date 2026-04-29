<template>
  <div class="sp-page">

    <!-- ── Header ── -->
    <header class="sp-header">
      <div class="sp-header-inner">
        <div class="sp-logo">
          <NuxtLink to="/shared-folders" class="sp-back-btn" title="Back to Shared Projects">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
          </NuxtLink>
          <div class="sp-logo-icon" :style="project ? { background: (project.color || '#6366f1') + '22' } : {}">
            <span v-if="project" :style="{ color: project.color || '#6366f1' }">📁</span>
            <span v-else>🐛</span>
          </div>
          <div>
            <div class="sp-logo-name">{{ project?.name || 'Loading…' }}</div>
            <div class="sp-logo-sub">Bug Tracker — Shared Access</div>
          </div>
        </div>

        <div class="sp-header-right">
          <template v-if="currentUser">
            <div class="sp-user" ref="userMenuRef" @click.stop="userMenuOpen = !userMenuOpen">
              <img v-if="currentUser.avatar" :src="currentUser.avatar" class="sp-avatar" :alt="currentUser.name" />
              <span v-else class="sp-avatar sp-avatar-initials">{{ currentUser.name?.[0]?.toUpperCase() }}</span>
              <span class="sp-user-name">{{ currentUser.name }}</span>
              <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="opacity:.5"><polyline points="6 9 12 15 18 9"/></svg>
              <div v-if="userMenuOpen" class="sp-user-dropdown">
                <div class="sp-dropdown-info">
                  <img v-if="currentUser.avatar" :src="currentUser.avatar" class="sp-dropdown-avatar" :alt="currentUser.name" />
                  <span v-else class="sp-dropdown-avatar sp-avatar-initials" style="font-size:16px;">{{ currentUser.name?.[0]?.toUpperCase() }}</span>
                  <div>
                    <div style="font-weight:600;color:#1e293b;font-size:14px;">{{ currentUser.name }}</div>
                    <div style="font-size:12px;color:#64748b;">{{ currentUser.email }}</div>
                  </div>
                </div>
                <div style="border-top:1px solid #f1f5f9;margin:8px 0;"></div>
                <button class="sp-dropdown-signout" @click.stop="logout">
                  <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                  Sign out
                </button>
              </div>
            </div>
          </template>
          <button v-else class="sp-signin-btn" @click="login">
            <svg width="15" height="15" viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.97 2.31-8.16 2.31-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg>
            Sign in with Google
          </button>
        </div>
      </div>
    </header>

    <!-- ── Auth gate ── -->
    <div v-if="!authToken && !loading" class="sp-gate">
      <div class="sp-gate-card">
        <div class="sp-gate-icon">🔗</div>
        <h2 class="sp-gate-title">Sign in to view this project</h2>
        <p class="sp-gate-sub">Sign in with the Google account that was invited to access this project.</p>
        <button class="sp-signin-btn sp-gate-btn" @click="login">
          <svg width="18" height="18" viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.97 2.31-8.16 2.31-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg>
          Sign in with Google
        </button>
      </div>
    </div>

    <!-- ── Loading ── -->
    <div v-else-if="loading" class="sp-loading">
      <div class="sp-spinner"></div>
      <p>Loading project…</p>
    </div>

    <!-- ── No access ── -->
    <div v-else-if="!project" class="sp-gate">
      <div class="sp-gate-card">
        <div class="sp-gate-icon">🔒</div>
        <h2 class="sp-gate-title">Project not found</h2>
        <p class="sp-gate-sub">This project doesn't exist or hasn't been shared with your account.</p>
        <NuxtLink to="/shared-folders" class="sp-gate-btn-link">View Shared Projects</NuxtLink>
      </div>
    </div>

    <!-- ── Main layout ── -->
    <div v-else class="sp-body">

      <!-- ── Sidebar ── -->
      <aside class="sp-sidebar">
        <div class="sp-sidebar-label">Shared with Me</div>
        <nav class="sp-sidebar-nav">
          <NuxtLink
            v-for="p in sharedProjects"
            :key="p.id"
            :to="`/project/${p.id}`"
            class="sp-nav-item"
            :class="{ 'sp-nav-item--active': p.id === projectId }"
          >
            <span class="sp-nav-dot" :style="{ background: p.color || '#6366f1' }"></span>
            <span class="sp-nav-name">{{ p.name }}</span>
            <span class="sp-nav-count">{{ p.bugs_count }}</span>
          </NuxtLink>
        </nav>
        <div class="sp-sidebar-divider"></div>
        <NuxtLink to="/shared-folders" class="sp-sidebar-back-link">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
          All Shared Projects
        </NuxtLink>
      </aside>

      <!-- ── Main content ── -->
      <main class="sp-main">

        <!-- Project header -->
        <div class="sp-project-head">
          <div class="sp-project-title-row">
            <span class="sp-project-dot" :style="{ background: project.color || '#6366f1' }"></span>
            <h1 class="sp-project-title">{{ project.name }}</h1>
            <span :class="['sp-perm-badge', project.my_permission === 'editor' ? 'sp-perm-edit' : 'sp-perm-view']">
              {{ project.my_permission === 'editor' ? 'Editor' : 'Viewer' }}
            </span>
          </div>
          <div v-if="project.description" class="sp-project-desc">{{ project.description }}</div>
          <div class="sp-project-meta">
            <span class="sp-meta-shared">
              Shared by <strong>{{ project.invited_by_name }}</strong>
            </span>
          </div>
        </div>

        <!-- Summary chips -->
        <div class="sp-summary">
          <div class="sp-summary-chip sp-chip-total">
            <div class="sp-chip-value">{{ bugs.length }}</div>
            <div class="sp-chip-label">Total Bugs</div>
          </div>
          <div class="sp-summary-chip sp-chip-pending">
            <div class="sp-chip-value">{{ bugCounts.pending }}</div>
            <div class="sp-chip-label">Pending</div>
          </div>
          <div class="sp-summary-chip sp-chip-ongoing">
            <div class="sp-chip-value">{{ bugCounts.ongoing }}</div>
            <div class="sp-chip-label">Ongoing</div>
          </div>
          <div class="sp-summary-chip sp-chip-done">
            <div class="sp-chip-value">{{ bugCounts.completed }}</div>
            <div class="sp-chip-label">Completed</div>
          </div>
          <div class="sp-summary-chip sp-chip-critical">
            <div class="sp-chip-value">{{ bugCounts.critical }}</div>
            <div class="sp-chip-label">Critical</div>
          </div>
        </div>

        <!-- Bug table card -->
        <div class="sp-card">
          <div class="sp-card-header">
            <div class="sp-card-title-row">
              <h2 class="sp-card-title">Bug Reports</h2>
              <span class="sp-result-chip">{{ filteredBugs.length }} result{{ filteredBugs.length !== 1 ? 's' : '' }}</span>
            </div>
            <div class="sp-filters">
              <div class="sp-search-wrap">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input v-model="filterSearch" class="sp-search-input" placeholder="Search bugs…" />
              </div>
              <select v-model="filterStatus" class="sp-select">
                <option value="">All Statuses</option>
                <option value="Pending">Pending</option>
                <option value="Ongoing">Ongoing</option>
                <option value="Completed">Completed</option>
                <option value="Out of Scope">Out of Scope</option>
              </select>
              <select v-model="filterPriority" class="sp-select">
                <option value="">All Priorities</option>
                <option value="Critical">Critical</option>
                <option value="High">High</option>
                <option value="Medium">Medium</option>
                <option value="Low">Low</option>
              </select>
              <button v-if="filterSearch || filterStatus || filterPriority" class="sp-clear-btn" @click="clearFilters">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
                Clear
              </button>
            </div>
          </div>

          <!-- Empty state -->
          <div v-if="!bugs.length" class="sp-empty">
            <div class="sp-empty-icon">🐛</div>
            <div class="sp-empty-title">No bugs reported yet</div>
            <div class="sp-empty-sub">This project has no bug reports.</div>
          </div>

          <!-- No results -->
          <div v-else-if="!filteredBugs.length" class="sp-empty">
            <div class="sp-empty-icon">🔍</div>
            <div class="sp-empty-title">No results</div>
            <div class="sp-empty-sub">Try adjusting your filters.</div>
            <button class="sp-clear-btn" style="margin:0 auto;" @click="clearFilters">Clear filters</button>
          </div>

          <!-- Table -->
          <div v-else class="sp-table-wrap">
            <table class="sp-table">
              <thead>
                <tr>
                  <th style="width:56px;">#</th>
                  <th>Title</th>
                  <th style="width:100px;">Priority</th>
                  <th style="width:150px;">Scenario</th>
                  <th style="width:160px;">Status</th>
                  <th style="width:150px;">Dev Status</th>
                  <th style="width:120px;">Assigned</th>
                  <th style="width:90px;text-align:center;">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="bug in filteredBugs"
                  :key="bug.id"
                  :class="{ 'sp-row-completed': bug.status === 'Completed' }"
                >
                  <td><span class="sp-bug-seq">#{{ bug.sequence }}</span></td>
                  <td>
                    <div class="sp-bug-title" @click="viewBug(bug)">{{ bug.title }}</div>
                    <div v-if="bug.description" class="sp-bug-desc">{{ stripHtml(bug.description) }}</div>
                  </td>
                  <td><span :class="['sp-badge', priorityClass(bug.priority)]">{{ bug.priority }}</span></td>
                  <td><span :class="['sp-badge', scenarioClass(bug.scenario_type)]">{{ bug.scenario_type }}</span></td>
                  <td>
                    <select
                      v-if="isEditor"
                      :value="bug.status"
                      :class="['sp-status-select', 'sp-status--' + bug.status.toLowerCase().replace(/\s+/g, '-')]"
                      @change="updateStatus(bug, $event.target.value)"
                    >
                      <option value="Pending">Pending</option>
                      <option value="Ongoing">Ongoing</option>
                      <option value="Completed">Completed</option>
                      <option value="Out of Scope">Out of Scope</option>
                    </select>
                    <span v-else :class="['sp-badge', statusClass(bug.status)]">{{ bug.status }}</span>
                  </td>
                  <td>
                    <span :class="['sp-dev-status', 'sp-dev--' + (bug.dev_status || 'Not Started').toLowerCase().replace(/\s+/g, '-')]">
                      {{ bug.dev_status || 'Not Started' }}
                    </span>
                  </td>
                  <td>
                    <div v-if="bug.assigned_developers?.length" class="sp-assigned">
                      <span
                        v-for="(dev, i) in bug.assigned_developers.slice(0, 2)" :key="i"
                        class="sp-dev-avatar" :title="dev.name"
                      >{{ dev.name?.[0]?.toUpperCase() }}</span>
                      <span v-if="bug.assigned_developers.length > 2" class="sp-dev-more">+{{ bug.assigned_developers.length - 2 }}</span>
                    </div>
                    <span v-else class="sp-unassigned">—</span>
                  </td>
                  <td style="text-align:center;">
                    <button class="sp-btn-view" @click="viewBug(bug)" title="View details">
                      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </main>
    </div>

    <!-- ── Bug View Modal ── -->
    <Transition name="sp-fade">
      <div v-if="viewingBug" class="sp-modal-overlay" @click.self="viewingBug = null">
        <div class="sp-modal sp-modal-wide">
          <div class="sp-modal-head">
            <div style="display:flex;align-items:center;gap:10px;flex:1;min-width:0;">
              <span class="sp-bug-seq" style="flex-shrink:0;">#{{ viewingBug.sequence }}</span>
              <h3 class="sp-modal-title">{{ viewingBug.title }}</h3>
            </div>
            <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
              <span :class="['sp-badge', priorityClass(viewingBug.priority)]">{{ viewingBug.priority }}</span>
              <span :class="['sp-badge', statusClass(viewingBug.status)]">{{ viewingBug.status }}</span>
              <button class="sp-modal-close" @click="viewingBug = null">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
              </button>
            </div>
          </div>

          <div class="sp-modal-body">
            <div class="sp-modal-two-col">
              <!-- Left: details -->
              <div class="sp-modal-main">
                <!-- Subtitles -->
                <div v-if="viewingBug.subtitles?.length" class="sp-detail-section">
                  <div class="sp-detail-label">Subtitles</div>
                  <div class="sp-subtitle-list">
                    <div v-for="(sub, i) in viewingBug.subtitles" :key="i" class="sp-subtitle-item">
                      <span class="sp-subtitle-num">{{ i + 1 }}</span>
                      <div>
                        <div v-if="typeof sub === 'string' ? sub : sub.text">{{ typeof sub === 'string' ? sub : sub.text }}</div>
                        <a v-if="sub.link" :href="sub.link" target="_blank" class="sp-link">{{ sub.link }}</a>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Description -->
                <div class="sp-detail-section">
                  <div class="sp-detail-label">Description</div>
                  <div v-if="viewingBug.description" class="sp-description" v-html="sanitizeHtml(viewingBug.description)"></div>
                  <div v-else class="sp-empty-text">No description provided.</div>
                </div>

                <!-- Screenshots -->
                <div v-if="(viewingBug.frontend_images?.length || viewingBug.cms_images?.length)" class="sp-detail-section">
                  <div class="sp-detail-label">Screenshots</div>
                  <div class="sp-images-row">
                    <div v-if="viewingBug.frontend_images?.length">
                      <div class="sp-images-sub">Front-End</div>
                      <div class="sp-images-grid">
                        <a v-for="(img, i) in viewingBug.frontend_images" :key="i" :href="apiBase + img" target="_blank" class="sp-img-thumb">
                          <img :src="apiBase + img" :alt="`FE ${i+1}`" />
                        </a>
                      </div>
                    </div>
                    <div v-if="viewingBug.cms_images?.length">
                      <div class="sp-images-sub">CMS</div>
                      <div class="sp-images-grid">
                        <a v-for="(img, i) in viewingBug.cms_images" :key="i" :href="apiBase + img" target="_blank" class="sp-img-thumb">
                          <img :src="apiBase + img" :alt="`CMS ${i+1}`" />
                        </a>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Comments -->
                <div v-if="viewingBug.dev_comments?.length" class="sp-detail-section">
                  <div class="sp-detail-label">Comments ({{ viewingBug.dev_comments.length }})</div>
                  <div class="sp-comments">
                    <div v-for="(c, i) in viewingBug.dev_comments" :key="i" class="sp-comment">
                      <div class="sp-comment-author">{{ c.author || 'Unknown' }}</div>
                      <div class="sp-comment-text">{{ c.message }}</div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Right: metadata -->
              <aside class="sp-modal-side">
                <div class="sp-meta-card">
                  <div class="sp-meta-label">Status</div>
                  <span :class="['sp-badge', statusClass(viewingBug.status)]">{{ viewingBug.status }}</span>
                </div>
                <div class="sp-meta-card">
                  <div class="sp-meta-label">Priority</div>
                  <span :class="['sp-badge', priorityClass(viewingBug.priority)]">{{ viewingBug.priority }}</span>
                </div>
                <div class="sp-meta-card">
                  <div class="sp-meta-label">Scenario</div>
                  <span :class="['sp-badge', scenarioClass(viewingBug.scenario_type)]">{{ viewingBug.scenario_type }}</span>
                </div>
                <div class="sp-meta-card">
                  <div class="sp-meta-label">Dev Status</div>
                  <span :class="['sp-dev-status', 'sp-dev--' + (viewingBug.dev_status || 'Not Started').toLowerCase().replace(/\s+/g, '-')]">
                    {{ viewingBug.dev_status || 'Not Started' }}
                  </span>
                </div>
                <div v-if="viewingBug.assigned_developers?.length" class="sp-meta-card">
                  <div class="sp-meta-label">Assigned Dev{{ viewingBug.assigned_developers.length > 1 ? 's' : '' }}</div>
                  <div style="display:flex;flex-direction:column;gap:6px;">
                    <div v-for="dev in viewingBug.assigned_developers" :key="dev.email" class="sp-dev-row">
                      <span class="sp-dev-avatar">{{ dev.name?.[0]?.toUpperCase() }}</span>
                      <div>
                        <div style="font-size:13px;font-weight:600;color:#1e293b;">{{ dev.name }}</div>
                        <div style="font-size:11px;color:#94a3b8;">{{ dev.email }}</div>
                      </div>
                    </div>
                  </div>
                </div>
                <div v-if="viewingBug.date_to_accomplish" class="sp-meta-card">
                  <div class="sp-meta-label">Due Date</div>
                  <span style="font-size:13px;color:#1e293b;">{{ formatDate(viewingBug.date_to_accomplish) }}</span>
                </div>
              </aside>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- ── Toast ── -->
    <Transition name="sp-toast">
      <div v-if="toast.show" class="sp-toast" :class="'sp-toast-' + toast.type">
        <svg v-if="toast.type === 'success'" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        <svg v-else width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        {{ toast.message }}
      </div>
    </Transition>

  </div>
</template>

<script setup>
const route    = useRoute()
const config   = useRuntimeConfig()
const apiBase  = config.public.apiBase

const projectId   = computed(() => {
  const n = Number(route.params.id)
  return isNaN(n) ? null : n
})
const authToken   = ref(null)
const currentUser = ref(null)

const loading        = ref(true)
const sharedProjects = ref([])
const bugs           = ref([])
const viewingBug     = ref(null)
const userMenuOpen   = ref(false)
const userMenuRef    = ref(null)

const filterSearch   = ref('')
const filterStatus   = ref('')
const filterPriority = ref('')

const toast = reactive({ show: false, message: '', type: 'success' })
let toastTimer = null

const showToast = (message, type = 'success') => {
  if (toastTimer) clearTimeout(toastTimer)
  toast.message = message; toast.type = type; toast.show = true
  toastTimer = setTimeout(() => { toast.show = false }, 3000)
}

const project  = computed(() => sharedProjects.value.find(p => p.id === projectId.value) || null)
const isEditor = computed(() => project.value?.my_permission === 'editor')

const bugCounts = computed(() => ({
  pending:   bugs.value.filter(b => b.status === 'Pending').length,
  ongoing:   bugs.value.filter(b => b.status === 'Ongoing').length,
  completed: bugs.value.filter(b => b.status === 'Completed').length,
  critical:  bugs.value.filter(b => b.priority === 'Critical').length,
}))

const filteredBugs = computed(() => {
  let list = bugs.value
  if (filterSearch.value.trim()) {
    const q = filterSearch.value.toLowerCase()
    list = list.filter(b => b.title.toLowerCase().includes(q) || stripHtml(b.description || '').toLowerCase().includes(q))
  }
  if (filterStatus.value)   list = list.filter(b => b.status === filterStatus.value)
  if (filterPriority.value) list = list.filter(b => b.priority === filterPriority.value)
  return list
})

const clearFilters = () => { filterSearch.value = ''; filterStatus.value = ''; filterPriority.value = '' }

const apiFetch = (url, options = {}) => {
  if (authToken.value) options.headers = { Authorization: `Bearer ${authToken.value}`, ...(options.headers || {}) }
  return $fetch(url, options)
}

const authRedirectBase = apiBase.replace(/\/api$/, '')
const login  = () => { window.location.href = `${authRedirectBase}/api/auth/google` }
const logout = async () => {
  try { await apiFetch(`${apiBase}/auth/logout`, { method: 'POST' }) } catch {}
  authToken.value = null; localStorage.removeItem('auth_token'); currentUser.value = null
  navigateTo('/login')
}

const fetchCurrentUser = async () => {
  try {
    const user = await apiFetch(`${apiBase}/auth/me`)
    currentUser.value = (user && user.id) ? user : null
    if (!currentUser.value) { authToken.value = null; localStorage.removeItem('auth_token') }
  } catch {
    authToken.value = null; localStorage.removeItem('auth_token'); currentUser.value = null
  }
}

const fetchSharedProjects = async () => {
  try { sharedProjects.value = await apiFetch(`${apiBase}/my-shared-projects`) } catch { sharedProjects.value = [] }
}

const fetchBugs = async () => {
  try { bugs.value = await apiFetch(`${apiBase}/bugs`, { params: { project_id: projectId.value } }) } catch { bugs.value = [] }
}

const updateStatus = async (bug, status) => {
  try {
    await apiFetch(`${apiBase}/bugs/${bug.id}/status`, { method: 'PATCH', body: { status } })
    bug.status = status
    if (viewingBug.value?.id === bug.id) viewingBug.value.status = status
    showToast('Status updated')
  } catch { showToast('Failed to update status', 'error') }
}

const viewBug = (bug) => { viewingBug.value = bug }

const stripHtml = (html) => {
  if (!html) return ''
  return html.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 120)
}

const sanitizeHtml = (html) => {
  if (!html) return ''
  return html
    .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
    .replace(/\s+on\w+\s*=\s*(?:"[^"]*"|'[^']*'|[^\s>]+)/gi, '')
    .replace(/(?:href|src|action)\s*=\s*["']\s*javascript:[^"']*/gi, '')
}

const formatDate = (d) => {
  if (!d) return ''
  return new Date(d + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const priorityClass  = (p) => ({ Critical: 'sp-badge-critical', High: 'sp-badge-high', Medium: 'sp-badge-medium', Low: 'sp-badge-low' }[p] || '')
const statusClass    = (s) => ({ Pending: 'sp-badge-pending', Ongoing: 'sp-badge-ongoing', Completed: 'sp-badge-completed', 'Out of Scope': 'sp-badge-oos' }[s] || '')
const scenarioClass  = (s) => ({ UI: 'sp-badge-ui', Functionality: 'sp-badge-func', 'UI & Functionality': 'sp-badge-both' }[s] || '')

watch(projectId, async (newId) => {
  if (newId === null || !authToken.value) return
  bugs.value = []
  await fetchBugs()
})

onMounted(async () => {
  const urlParams = new URLSearchParams(window.location.search)
  const token = urlParams.get('token')
  if (token) {
    authToken.value = token
    localStorage.setItem('auth_token', token)
    window.history.replaceState({}, '', `/project/${projectId.value}`)
  } else {
    const stored = localStorage.getItem('auth_token')
    if (stored) authToken.value = stored
  }

  if (authToken.value) await fetchCurrentUser()

  if (currentUser.value) {
    await Promise.all([fetchSharedProjects(), fetchBugs()])
  }

  loading.value = false

  const onOutsideClick = (e) => {
    if (userMenuRef.value && !userMenuRef.value.contains(e.target)) userMenuOpen.value = false
  }
  document.addEventListener('click', onOutsideClick)
  onUnmounted(() => document.removeEventListener('click', onOutsideClick))
})
</script>

<style scoped>
.sp-page { min-height: 100vh; background: #f8fafc; font-family: 'Inter', system-ui, sans-serif; display: flex; flex-direction: column; }

/* Header */
.sp-header { background: #fff; border-bottom: 1px solid #e2e8f0; position: sticky; top: 0; z-index: 50; }
.sp-header-inner { max-width: 1400px; margin: 0 auto; padding: 0 24px; height: 60px; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
.sp-logo { display: flex; align-items: center; gap: 10px; }
.sp-back-btn { display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; color: #64748b; text-decoration: none; transition: background .15s, color .15s; }
.sp-back-btn:hover { background: #f1f5f9; color: #1e293b; }
.sp-logo-icon { width: 36px; height: 36px; border-radius: 10px; font-size: 18px; display: flex; align-items: center; justify-content: center; background: #e0e7ff; flex-shrink: 0; }
.sp-logo-name { font-size: 15px; font-weight: 700; color: #1e293b; line-height: 1.2; }
.sp-logo-sub { font-size: 11px; color: #94a3b8; }
.sp-header-right { display: flex; align-items: center; gap: 10px; }

/* User */
.sp-user { position: relative; display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 6px 10px; border-radius: 8px; transition: background .15s; }
.sp-user:hover { background: #f1f5f9; }
.sp-avatar { width: 28px; height: 28px; border-radius: 50%; object-fit: cover; }
.sp-avatar-initials { background: #6366f1; color: #fff; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; }
.sp-user-name { font-size: 13px; font-weight: 600; color: #1e293b; }
.sp-user-dropdown { position: absolute; top: calc(100% + 6px); right: 0; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,.1); padding: 10px; min-width: 200px; z-index: 100; }
.sp-dropdown-info { display: flex; align-items: center; gap: 10px; padding: 4px 2px 8px; }
.sp-dropdown-avatar { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; }
.sp-dropdown-signout { display: flex; align-items: center; gap: 8px; width: 100%; padding: 8px 10px; border: none; background: none; cursor: pointer; font-size: 13px; color: #ef4444; border-radius: 6px; transition: background .15s; }
.sp-dropdown-signout:hover { background: #fef2f2; }
.sp-signin-btn { display: flex; align-items: center; gap: 8px; padding: 8px 16px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; font-weight: 600; color: #1e293b; cursor: pointer; transition: box-shadow .15s; }
.sp-signin-btn:hover { box-shadow: 0 2px 8px rgba(0,0,0,.1); }

/* Gate / loading */
.sp-gate { display: flex; align-items: center; justify-content: center; flex: 1; min-height: calc(100vh - 60px); padding: 40px 24px; }
.sp-gate-card { background: #fff; border-radius: 16px; padding: 48px 40px; text-align: center; box-shadow: 0 1px 4px rgba(0,0,0,.08); max-width: 400px; width: 100%; }
.sp-gate-icon { font-size: 48px; margin-bottom: 16px; }
.sp-gate-title { font-size: 20px; font-weight: 700; color: #1e293b; margin: 0 0 8px; }
.sp-gate-sub { font-size: 14px; color: #64748b; margin: 0 0 28px; line-height: 1.6; }
.sp-gate-btn { margin: 0 auto; }
.sp-gate-btn-link { display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; background: #6366f1; color: #fff; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none; transition: background .15s; }
.sp-gate-btn-link:hover { background: #4f46e5; }
.sp-loading { display: flex; flex-direction: column; align-items: center; justify-content: center; flex: 1; min-height: calc(100vh - 60px); gap: 16px; color: #64748b; font-size: 14px; }
.sp-spinner { width: 36px; height: 36px; border: 3px solid #e2e8f0; border-top-color: #6366f1; border-radius: 50%; animation: sp-spin .7s linear infinite; }
@keyframes sp-spin { to { transform: rotate(360deg); } }

/* Body layout */
.sp-body { display: flex; flex: 1; min-height: calc(100vh - 60px); }

/* Sidebar */
.sp-sidebar { width: 240px; flex-shrink: 0; background: #fff; border-right: 1px solid #e2e8f0; padding: 20px 0; display: flex; flex-direction: column; }
.sp-sidebar-label { font-size: 10px; font-weight: 700; letter-spacing: .8px; color: #94a3b8; text-transform: uppercase; padding: 0 16px; margin-bottom: 6px; }
.sp-sidebar-nav { display: flex; flex-direction: column; gap: 1px; padding: 0 8px; }
.sp-nav-item { display: flex; align-items: center; gap: 8px; padding: 8px 8px; border-radius: 8px; text-decoration: none; color: #475569; font-size: 13px; font-weight: 500; transition: background .15s, color .15s; }
.sp-nav-item:hover { background: #f1f5f9; color: #1e293b; }
.sp-nav-item--active { background: #e0e7ff; color: #4338ca; font-weight: 600; }
.sp-nav-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.sp-nav-name { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.sp-nav-count { font-size: 11px; font-weight: 600; color: #94a3b8; flex-shrink: 0; }
.sp-sidebar-divider { border: none; border-top: 1px solid #f1f5f9; margin: 12px 16px; }
.sp-sidebar-back-link { display: flex; align-items: center; gap: 8px; padding: 8px 16px; font-size: 12px; color: #64748b; text-decoration: none; transition: color .15s; }
.sp-sidebar-back-link:hover { color: #1e293b; }

/* Main */
.sp-main { flex: 1; padding: 28px 32px; min-width: 0; overflow-y: auto; }

/* Project header */
.sp-project-head { margin-bottom: 24px; }
.sp-project-title-row { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.sp-project-dot { width: 12px; height: 12px; border-radius: 50%; flex-shrink: 0; }
.sp-project-title { font-size: 22px; font-weight: 800; color: #1e293b; margin: 0; }
.sp-project-desc { font-size: 14px; color: #64748b; margin: 8px 0 0; }
.sp-project-meta { margin-top: 6px; font-size: 12px; color: #94a3b8; }
.sp-meta-shared strong { color: #475569; }
.sp-perm-badge { font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 99px; letter-spacing: .3px; flex-shrink: 0; }
.sp-perm-edit { background: #ede9fe; color: #7c3aed; }
.sp-perm-view { background: #e0f2fe; color: #0369a1; }

/* Summary chips */
.sp-summary { display: flex; gap: 12px; margin-bottom: 24px; flex-wrap: wrap; }
.sp-summary-chip { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px 20px; min-width: 90px; text-align: center; }
.sp-chip-value { font-size: 22px; font-weight: 800; color: #1e293b; }
.sp-chip-label { font-size: 11px; color: #94a3b8; margin-top: 2px; font-weight: 500; }
.sp-chip-total   .sp-chip-value { color: #1e293b; }
.sp-chip-pending .sp-chip-value { color: #d97706; }
.sp-chip-ongoing .sp-chip-value { color: #1d4ed8; }
.sp-chip-done    .sp-chip-value { color: #16a34a; }
.sp-chip-critical .sp-chip-value { color: #dc2626; }

/* Card */
.sp-card { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; }
.sp-card-header { padding: 16px 20px; border-bottom: 1px solid #f1f5f9; }
.sp-card-title-row { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
.sp-card-title { font-size: 15px; font-weight: 700; color: #1e293b; margin: 0; }
.sp-result-chip { font-size: 11px; font-weight: 700; background: #f1f5f9; color: #64748b; padding: 2px 8px; border-radius: 99px; }
.sp-filters { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
.sp-search-wrap { position: relative; display: flex; align-items: center; }
.sp-search-wrap svg { position: absolute; left: 10px; color: #94a3b8; pointer-events: none; }
.sp-search-input { padding: 7px 12px 7px 32px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; color: #1e293b; background: #fff; outline: none; transition: border-color .15s, box-shadow .15s; width: 200px; }
.sp-search-input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.12); }
.sp-select { padding: 7px 10px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; color: #1e293b; background: #fff; outline: none; cursor: pointer; transition: border-color .15s; }
.sp-select:focus { border-color: #6366f1; }
.sp-clear-btn { display: flex; align-items: center; gap: 5px; padding: 7px 12px; background: #f1f5f9; border: none; border-radius: 8px; font-size: 12px; font-weight: 600; color: #64748b; cursor: pointer; transition: background .15s; }
.sp-clear-btn:hover { background: #e2e8f0; }

/* Empty */
.sp-empty { text-align: center; padding: 60px 24px; }
.sp-empty-icon { font-size: 40px; margin-bottom: 12px; }
.sp-empty-title { font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 6px; }
.sp-empty-sub { font-size: 13px; color: #64748b; margin-bottom: 16px; }

/* Table */
.sp-table-wrap { overflow-x: auto; }
.sp-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.sp-table th { padding: 10px 14px; text-align: left; font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .4px; border-bottom: 1px solid #f1f5f9; background: #fafafa; }
.sp-table td { padding: 12px 14px; border-bottom: 1px solid #f8fafc; vertical-align: middle; }
.sp-table tr:last-child td { border-bottom: none; }
.sp-table tr:hover td { background: #f8fafc; }
.sp-row-completed td { opacity: .6; }
.sp-bug-seq { font-size: 11px; font-weight: 700; color: #94a3b8; background: #f1f5f9; padding: 2px 6px; border-radius: 5px; }
.sp-bug-title { font-weight: 600; color: #1e293b; cursor: pointer; transition: color .15s; }
.sp-bug-title:hover { color: #6366f1; }
.sp-bug-desc { font-size: 12px; color: #94a3b8; margin-top: 2px; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* Badges */
.sp-badge { font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px; letter-spacing: .2px; display: inline-block; }
.sp-badge-critical { background: #fee2e2; color: #dc2626; }
.sp-badge-high     { background: #fff7ed; color: #c2410c; }
.sp-badge-medium   { background: #fef9c3; color: #a16207; }
.sp-badge-low      { background: #f0fdf4; color: #16a34a; }
.sp-badge-pending  { background: #fef3c7; color: #d97706; }
.sp-badge-ongoing  { background: #dbeafe; color: #1d4ed8; }
.sp-badge-completed { background: #dcfce7; color: #16a34a; }
.sp-badge-oos      { background: #f1f5f9; color: #64748b; }
.sp-badge-ui       { background: #e0e7ff; color: #4338ca; }
.sp-badge-func     { background: #f0fdf4; color: #15803d; }
.sp-badge-both     { background: #fdf2f8; color: #a21caf; }

/* Status select */
.sp-status-select { padding: 5px 8px; border-radius: 6px; font-size: 12px; font-weight: 600; border: 1.5px solid transparent; outline: none; cursor: pointer; }
.sp-status--pending    { background: #fef3c7; color: #d97706; border-color: #fde68a; }
.sp-status--ongoing    { background: #dbeafe; color: #1d4ed8; border-color: #bfdbfe; }
.sp-status--completed  { background: #dcfce7; color: #16a34a; border-color: #bbf7d0; }
.sp-status--out-of-scope { background: #f1f5f9; color: #64748b; border-color: #e2e8f0; }

/* Dev status */
.sp-dev-status { font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 6px; display: inline-block; }
.sp-dev--not-started  { background: #f1f5f9; color: #64748b; }
.sp-dev--in-progress  { background: #dbeafe; color: #1d4ed8; }
.sp-dev--ready-for-qa { background: #dcfce7; color: #16a34a; }
.sp-dev--blocked      { background: #fee2e2; color: #dc2626; }

/* Assigned */
.sp-assigned { display: flex; align-items: center; gap: 2px; }
.sp-dev-avatar { width: 24px; height: 24px; border-radius: 50%; background: #6366f1; color: #fff; font-size: 10px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; border: 2px solid #fff; }
.sp-dev-more { font-size: 11px; color: #94a3b8; margin-left: 4px; }
.sp-unassigned { color: #cbd5e1; font-size: 13px; }
.sp-dev-row { display: flex; align-items: center; gap: 8px; }

/* Action buttons */
.sp-btn-view { background: none; border: 1px solid #e2e8f0; border-radius: 6px; padding: 5px; cursor: pointer; color: #64748b; display: inline-flex; align-items: center; transition: background .15s, color .15s; }
.sp-btn-view:hover { background: #f1f5f9; color: #1e293b; }

/* Modal */
.sp-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.45); display: flex; align-items: center; justify-content: center; z-index: 200; padding: 16px; }
.sp-modal { background: #fff; border-radius: 14px; width: 100%; max-width: 540px; box-shadow: 0 20px 60px rgba(0,0,0,.15); display: flex; flex-direction: column; max-height: 90vh; }
.sp-modal-wide { max-width: 860px; }
.sp-modal-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 20px 24px 16px; border-bottom: 1px solid #f1f5f9; flex-shrink: 0; }
.sp-modal-title { font-size: 16px; font-weight: 700; color: #1e293b; margin: 0; line-height: 1.3; }
.sp-modal-close { background: none; border: none; cursor: pointer; color: #94a3b8; padding: 4px; border-radius: 6px; display: flex; align-items: center; flex-shrink: 0; }
.sp-modal-close:hover { background: #f1f5f9; color: #475569; }
.sp-modal-body { padding: 20px 24px; overflow-y: auto; flex: 1; }

.sp-modal-two-col { display: flex; gap: 20px; }
.sp-modal-main { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 16px; }
.sp-modal-side { width: 200px; flex-shrink: 0; display: flex; flex-direction: column; gap: 12px; }

.sp-detail-section { display: flex; flex-direction: column; gap: 8px; }
.sp-detail-label { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .4px; }
.sp-description { font-size: 13px; color: #374151; line-height: 1.7; }
.sp-empty-text { font-size: 13px; color: #94a3b8; font-style: italic; }

.sp-meta-card { display: flex; flex-direction: column; gap: 5px; padding: 12px; background: #f8fafc; border-radius: 8px; }
.sp-meta-label { font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .4px; }

.sp-subtitle-list { display: flex; flex-direction: column; gap: 6px; }
.sp-subtitle-item { display: flex; gap: 10px; font-size: 13px; color: #374151; }
.sp-subtitle-num { width: 20px; height: 20px; border-radius: 50%; background: #e0e7ff; color: #4338ca; font-size: 10px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px; }
.sp-link { font-size: 12px; color: #6366f1; text-decoration: none; word-break: break-all; }
.sp-link:hover { text-decoration: underline; }

.sp-images-row { display: flex; flex-direction: column; gap: 12px; }
.sp-images-sub { font-size: 11px; font-weight: 600; color: #64748b; margin-bottom: 6px; }
.sp-images-grid { display: flex; flex-wrap: wrap; gap: 8px; }
.sp-img-thumb { display: block; width: 80px; height: 60px; border-radius: 6px; overflow: hidden; border: 1px solid #e2e8f0; }
.sp-img-thumb img { width: 100%; height: 100%; object-fit: cover; }

.sp-comments { display: flex; flex-direction: column; gap: 8px; }
.sp-comment { background: #f8fafc; border-radius: 8px; padding: 10px 12px; }
.sp-comment-author { font-size: 11px; font-weight: 700; color: #64748b; margin-bottom: 3px; }
.sp-comment-text { font-size: 13px; color: #374151; }

/* Toast */
.sp-toast { position: fixed; bottom: 24px; right: 24px; display: flex; align-items: center; gap: 8px; padding: 12px 18px; border-radius: 10px; font-size: 13px; font-weight: 600; box-shadow: 0 4px 16px rgba(0,0,0,.12); z-index: 300; }
.sp-toast-success { background: #1e293b; color: #fff; }
.sp-toast-error   { background: #ef4444; color: #fff; }

/* Transitions */
.sp-fade-enter-active, .sp-fade-leave-active { transition: opacity .2s; }
.sp-fade-enter-from, .sp-fade-leave-to { opacity: 0; }
.sp-toast-enter-active, .sp-toast-leave-active { transition: opacity .25s, transform .25s; }
.sp-toast-enter-from, .sp-toast-leave-to { opacity: 0; transform: translateY(8px); }

@media (max-width: 768px) {
  .sp-sidebar { display: none; }
  .sp-main { padding: 16px; }
  .sp-modal-two-col { flex-direction: column; }
  .sp-modal-side { width: 100%; }
}
</style>
