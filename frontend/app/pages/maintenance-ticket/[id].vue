<template>
  <div class="mt-page">

    <!-- Header -->
    <header class="mt-header">
      <div class="mt-header-inner">
        <div style="display:flex;align-items:center;gap:16px;">
          <NuxtLink :to="backLink" class="mt-back-link">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            {{ backLabel }}
          </NuxtLink>
          <div class="mt-header-logo">
            <div class="app-logo-icon" style="font-size:20px;">🔧</div>
            <div>
              <div style="font-weight:700;color:#fff;font-size:15px;">QA Maintenance Tracker</div>
              <div style="font-size:11px;color:#a7f3d0;">Ticket View</div>
            </div>
          </div>
        </div>
        <div v-if="ticket" class="mt-header-meta">
          <span :class="['badge', statusBadgeClass(ticket.status)]">{{ ticket.status }}</span>
          <span v-if="ticket.dev_status" :class="['badge', devStatusBadgeClass(ticket.dev_status)]">{{ ticket.dev_status }}</span>
        </div>
      </div>
    </header>

    <!-- Loading -->
    <div v-if="loading" class="mt-loading">
      <div class="mt-loading-spinner"></div>
      <p>Loading ticket…</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="mt-error">
      <div style="font-size:40px;margin-bottom:12px;">⚠️</div>
      <h2>Ticket not found</h2>
      <p>{{ error }}</p>
    </div>

    <!-- Content -->
    <div v-else-if="ticket" class="mt-layout">

      <!-- Left Panel -->
      <div class="mt-main">

        <!-- Title -->
        <div class="mt-title-block">
          <span class="mt-ticket-num">{{ ticket.ticket_number }}</span>
          <div>
            <h1 class="mt-title">{{ ticket.client }}</h1>
            <div class="mt-subtitle">{{ ticket.request }}</div>
          </div>
        </div>

        <!-- Request -->
        <div class="mt-section">
          <div class="mt-section-label">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            Request
          </div>
          <div class="mt-description">{{ ticket.request }}</div>
        </div>

        <!-- Notes -->
        <div v-if="ticket.notes" class="mt-section">
          <div class="mt-section-label">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Notes
          </div>
          <div class="mt-description">{{ ticket.notes }}</div>
        </div>

        <!-- Comments -->
        <div class="mt-section">
          <div class="mt-section-label" style="justify-content:space-between;">
            <span style="display:flex;align-items:center;gap:7px;">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              Comments
            </span>
            <span v-if="ticket.comments?.length" class="ticket-count-pill">{{ ticket.comments.length }}</span>
          </div>
          <div class="mt-activity-list" ref="activityListEl">
            <div v-if="!ticket.comments || !ticket.comments.length" class="mt-activity-empty">
              No comments yet. Be the first to comment.
            </div>
            <div v-for="(msg, i) in ticket.comments" :key="i" class="mt-activity-item mt-activity-comment">
              <div class="mt-activity-icon">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              </div>
              <div class="mt-activity-body">
                <div class="mt-activity-header">
                  <span class="mt-activity-author">{{ msg.author || 'Anonymous' }}</span>
                  <span class="mt-activity-time">{{ formatTime(msg.timestamp) }}{{ msg.edited ? ' · edited' : '' }}</span>
                </div>
                <div class="mt-activity-bubble">{{ msg.message }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Attachments -->
        <div v-if="ticket.attachments && ticket.attachments.length" class="mt-section">
          <div class="mt-section-label">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
            Attachments
            <span class="ticket-count-pill">{{ ticket.attachments.length }}</span>
          </div>
          <div class="mt-attachments-grid">
            <template v-for="(url, idx) in ticket.attachments" :key="idx">
              <a v-if="isPdf(url)" :href="storageBase + url" target="_blank" class="mt-attachment-pdf">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                {{ urlFilename(url) }}
              </a>
              <a v-else :href="storageBase + url" target="_blank" class="mt-attachment-img">
                <img :src="storageBase + url" :alt="`Attachment ${idx+1}`" />
                <div class="mt-img-overlay">
                  <svg width="16" height="16" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                </div>
              </a>
            </template>
          </div>
        </div>

        <!-- Compose -->
        <div class="mt-compose">
          <textarea
            v-model="newComment"
            class="mt-compose-textarea"
            placeholder="Write a comment… (Ctrl+Enter to post)"
            rows="3"
            @keydown.ctrl.enter.prevent="postComment"
          ></textarea>
          <div class="mt-compose-footer">
            <div class="mt-author-input-wrap">
              <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              <input v-model="authorName" class="mt-author-input" placeholder="Your name" />
            </div>
            <button class="btn-mt-comment" :disabled="!newComment.trim() || posting" @click="postComment">
              <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
              {{ posting ? 'Posting…' : 'Post comment' }}
            </button>
          </div>
        </div>

        <!-- Activity Log -->
        <div class="mt-section mt-activity-log-section">
          <div class="mt-section-label">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Activity Log
            <span class="ticket-count-pill" style="margin-left:4px;">{{ activityTimeline.length }}</span>
          </div>

          <div v-if="activityTimeline.length === 0" class="mt-activity-empty">No activity yet.</div>

          <div v-else class="mt-timeline">
            <div
              v-for="(entry, i) in activityTimeline"
              :key="i"
              class="mt-timeline-item"
              :class="'mt-tl-' + entry.type"
            >
              <!-- Line -->
              <div class="mt-tl-line-col">
                <div class="mt-tl-dot">
                  <!-- Created -->
                  <svg v-if="entry.type === 'created'" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                  <!-- Comment -->
                  <svg v-else-if="entry.type === 'comment'" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                  <!-- Status change -->
                  <svg v-else-if="entry.type === 'status'" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                  <!-- Dev status -->
                  <svg v-else width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                </div>
                <div v-if="i < activityTimeline.length - 1" class="mt-tl-connector"></div>
              </div>
              <!-- Content -->
              <div class="mt-tl-content">
                <div class="mt-tl-top">
                  <span class="mt-tl-label">{{ entry.label }}</span>
                  <span class="mt-tl-time">{{ formatTime(entry.time) }}</span>
                </div>
                <div v-if="entry.body" class="mt-tl-body">{{ entry.body }}</div>
                <div v-if="entry.from || entry.to" class="mt-tl-change">
                  <span v-if="entry.from" class="mt-tl-from">{{ entry.from }}</span>
                  <svg v-if="entry.from && entry.to" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                  <span v-if="entry.to" class="mt-tl-to">{{ entry.to }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Right Sidebar -->
      <aside class="mt-sidebar">

        <!-- Dev Status -->
        <div class="mt-sidebar-card">
          <div class="mt-sidebar-label">Dev Status</div>
          <select
            v-model="currentDevStatus"
            :class="['mt-dev-status-select', 'mt-dev-status--' + currentDevStatus.toLowerCase().replace(/\s+/g, '-')]"
            @change="updateDevStatus"
          >
            <option value="Not Started">Not Started</option>
            <option value="In Progress">In Progress</option>
            <option value="Ready for QA">Ready for QA ✅</option>
            <option value="Blocked">Blocked ⚠️</option>
          </select>
        </div>

        <!-- Status -->
        <div class="mt-sidebar-card">
          <div class="mt-sidebar-label">Status</div>
          <select
            v-model="currentStatus"
            :class="['mt-status-select', 'mt-status--' + currentStatus.toLowerCase().replace(/\s+/g, '-')]"
            @change="updateStatus"
          >
            <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
          </select>
        </div>

        <!-- Assigned People -->
        <div class="mt-sidebar-card">
          <div class="mt-sidebar-label">Assigned Developers</div>
          <div v-if="ticket.assigned_devs?.length">
            <div style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Developers</div>
            <div v-for="email in ticket.assigned_devs" :key="email" class="mt-dev-block">
              <span class="mt-person-avatar">{{ email[0].toUpperCase() }}</span>
              <div style="flex:1;min-width:0;">
                <div class="mt-person-name">{{ email.split('@')[0] }}</div>
                <div class="mt-person-email">{{ email }}</div>
              </div>
              <a :href="folderUrl(email)" target="_blank" class="mt-folder-icon-btn" title="View Folder">
                📁
              </a>
            </div>
          </div>
          <div v-if="ticket.assigned_qa?.length" :style="ticket.assigned_devs?.length ? 'margin-top:14px;' : ''">
            <div style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">QA</div>
            <div v-for="email in ticket.assigned_qa" :key="email" class="mt-person-row">
              <span class="mt-person-avatar" style="background:#7c3aed;">{{ email[0].toUpperCase() }}</span>
              <div style="flex:1;min-width:0;">
                <div class="mt-person-name">{{ email.split('@')[0] }}</div>
                <div class="mt-person-email">{{ email }}</div>
              </div>
            </div>
          </div>
          <div v-if="!ticket.assigned_devs?.length && !ticket.assigned_qa?.length" class="mt-sidebar-empty">Not assigned</div>
        </div>

        <!-- Details -->
        <div class="mt-sidebar-card">
          <div class="mt-sidebar-label">Details</div>
          <div class="mt-meta-list">
            <div v-if="ticket.project" class="mt-meta-row">
              <span class="mt-meta-key">Project</span>
              <span class="mt-meta-val" style="font-weight:600;">{{ ticket.project.name }}</span>
            </div>
            <div class="mt-meta-row">
              <span class="mt-meta-key">Client</span>
              <span class="mt-meta-val">{{ ticket.client }}</span>
            </div>
            <div class="mt-meta-row">
              <span class="mt-meta-key">Sent Through</span>
              <span class="mt-meta-val">{{ ticket.sent_thru || '—' }}</span>
            </div>
            <div class="mt-meta-row">
              <span class="mt-meta-key">Reported</span>
              <span class="mt-meta-val">{{ formatDate(ticket.date_received) }}</span>
            </div>
            <div class="mt-meta-row">
              <span class="mt-meta-key">Sent</span>
              <span class="mt-meta-val">{{ formatDate(ticket.created_at) }}</span>
            </div>
            <div class="mt-meta-row">
              <span class="mt-meta-key">Due Date</span>
              <span class="mt-meta-val" :class="{ 'mt-overdue': isTargetOverdue }">{{ formatDate(ticket.target_date) || '—' }}</span>
            </div>
            <div v-if="ticket.completion_date" class="mt-meta-row">
              <span class="mt-meta-key">Completion</span>
              <span class="mt-meta-val">{{ formatDate(ticket.completion_date) }}</span>
            </div>
          </div>
        </div>

        <!-- Activity counts -->
        <div class="mt-sidebar-card">
          <div class="mt-sidebar-label">Activity Log</div>
          <div class="mt-meta-list">
            <div class="mt-meta-row">
              <span class="mt-meta-key">Total events</span>
              <span class="mt-meta-val" style="font-weight:700;color:#059669;">{{ activityTimeline.length }}</span>
            </div>
            <div class="mt-meta-row">
              <span class="mt-meta-key">Comments</span>
              <span class="mt-meta-val" style="font-weight:600;">{{ ticket.comments?.length ?? 0 }}</span>
            </div>
            <div v-if="ticket.completion_date" class="mt-meta-row">
              <span class="mt-meta-key">Completed on</span>
              <span class="mt-meta-val" style="color:#16a34a;font-weight:600;">{{ formatDate(ticket.completion_date) }}</span>
            </div>
            <div class="mt-meta-row">
              <span class="mt-meta-key">Last event</span>
              <span class="mt-meta-val" style="font-size:11px;">{{ activityTimeline.length ? formatTime(activityTimeline[activityTimeline.length - 1].time) : '—' }}</span>
            </div>
          </div>
        </div>

      </aside>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted } from 'vue'
import { useRoute } from 'vue-router'

const route       = useRoute()
const config      = useRuntimeConfig()
const apiBase     = config.public.apiBase
const storageBase = config.public.apiBase.replace('/api', '')

const backLink = computed(() => {
  if (route.query.from === 'folder' && route.query.email) {
    return `/maintenance-dev-folder?email=${encodeURIComponent(route.query.email)}`
  }
  if (route.query.from === 'dashboard') {
    return '/'
  }
  if (ticket.value?.project?.id) {
    return `/maintenance?project=${ticket.value.project.id}`
  }
  return '/maintenance'
})

const backLabel = computed(() => {
  if (route.query.from === 'folder' && route.query.email) {
    return `Back to ${route.query.email.split('@')[0]}'s Folder`
  }
  if (route.query.from === 'dashboard') {
    return 'Back to Dashboard'
  }
  if (ticket.value?.project?.name) {
    return `Back to ${ticket.value.project.name}`
  }
  return 'Back to Maintenance'
})

const ticket         = ref(null)
const loading        = ref(true)
const error          = ref(null)
const newComment     = ref('')
const authorName     = ref('')
const posting        = ref(false)
const currentStatus    = ref('Pending')
const currentDevStatus = ref('Not Started')
const activityListEl   = ref(null)

const statuses = ['Pending', 'In Progress', 'On Hold', 'Completed', 'Cancelled']

const isTargetOverdue = computed(() => {
  if (!ticket.value?.target_date) return false
  return new Date(ticket.value.target_date) < new Date(new Date().toDateString())
})

const activityTimeline = computed(() => {
  if (!ticket.value) return []
  const entries = []

  // Ticket created
  entries.push({
    type: 'created',
    label: 'Ticket created',
    body: ticket.value.request,
    time: ticket.value.created_at,
  })

  // Completion event
  if (ticket.value.status === 'Completed' && ticket.value.completion_date) {
    entries.push({
      type: 'status',
      label: 'Marked as Completed',
      to: 'Completed',
      time: ticket.value.completion_date,
    })
  }

  // Comments
  for (const c of (ticket.value.comments || [])) {
    entries.push({
      type: 'comment',
      label: `${c.author || 'Anonymous'} commented`,
      body: c.message,
      time: c.timestamp,
    })
  }

  // Sort ascending by time
  return entries.sort((a, b) => new Date(a.time) - new Date(b.time))
})

const statusBadgeClass = (s) => ({
  'Pending':     'badge-pending',
  'In Progress': 'badge-ongoing',
  'On Hold':     'badge-outofscope',
  'Completed':   'badge-completed',
  'Cancelled':   'badge-outofscope',
}[s] || '')

const devStatusBadgeClass = (s) => ({
  'Not Started':  'badge-pending',
  'In Progress':  'badge-ongoing',
  'Ready for QA': 'badge-completed',
  'Blocked':      'badge-critical',
}[s] || '')

const formatTime = (iso) => {
  if (!iso) return ''
  const d = new Date(iso)
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) +
    ' · ' + d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
}

const formatDate = (val) => {
  if (!val) return '—'
  return new Date(val).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
}

const isPdf       = (url) => /\.pdf$/i.test(url)
const urlFilename = (url) => url.split('/').pop()

const folderUrl = (email) => {
  const base = typeof window !== 'undefined' ? window.location.origin : 'http://localhost:3000'
  return `${base}/maintenance-dev-folder?email=${encodeURIComponent(email)}`
}

const scrollToBottom = () => {
  nextTick(() => {
    if (activityListEl.value) activityListEl.value.scrollTop = activityListEl.value.scrollHeight
  })
}

const loadTicket = async () => {
  loading.value = true
  error.value   = null
  try {
    ticket.value = await $fetch(`${apiBase}/maintenance-tickets/${route.params.id}`)
    currentStatus.value    = ticket.value.status    || 'Pending'
    currentDevStatus.value = ticket.value.dev_status || 'Not Started'
    scrollToBottom()
  } catch (e) {
    error.value = 'This ticket does not exist or could not be loaded.'
  } finally {
    loading.value = false
  }
}

const postComment = async () => {
  if (!newComment.value.trim() || posting.value) return
  posting.value = true
  const message = newComment.value.trim()
  newComment.value = ''
  try {
    ticket.value = await $fetch(`${apiBase}/maintenance-tickets/${ticket.value.id}/comments`, {
      method: 'POST',
      body: { message, author: authorName.value || 'Anonymous' },
    })
    currentStatus.value    = ticket.value.status    || 'Pending'
    currentDevStatus.value = ticket.value.dev_status || 'Not Started'
    scrollToBottom()
  } catch (e) {
    console.error('Failed to post comment', e)
    newComment.value = message
  } finally {
    posting.value = false
  }
}

const updateDevStatus = async () => {
  try {
    ticket.value = await $fetch(`${apiBase}/maintenance-tickets/${ticket.value.id}/dev-status`, {
      method: 'PATCH',
      body: { dev_status: currentDevStatus.value },
    })
    currentDevStatus.value = ticket.value.dev_status || 'Not Started'
  } catch (e) {
    console.error('Failed to update dev status', e)
    currentDevStatus.value = ticket.value?.dev_status || 'Not Started'
  }
}

const updateStatus = async () => {
  try {
    ticket.value = await $fetch(`${apiBase}/maintenance-tickets/${ticket.value.id}/status`, {
      method: 'PATCH',
      body: { status: currentStatus.value },
    })
    currentStatus.value = ticket.value.status || 'Pending'
  } catch (e) {
    console.error('Failed to update status', e)
    currentStatus.value = ticket.value?.status || 'Pending'
  }
}

onMounted(loadTicket)
</script>

<style scoped>
.mt-page { min-height: 100vh; background: #f1f5f9; font-family: Inter, Arial, sans-serif; }

/* Header */
.mt-header { background: linear-gradient(135deg, #064e3b 0%, #065f46 60%, #047857 100%); padding: 0; }
.mt-header-inner { max-width: 1100px; margin: 0 auto; padding: 16px 24px; display: flex; align-items: center; justify-content: space-between; }
.mt-header-logo { display: flex; align-items: center; gap: 10px; }
.mt-header-meta { display: flex; gap: 8px; }
.mt-back-link { display: inline-flex; align-items: center; gap: 5px; color: rgba(255,255,255,0.8); font-size: 13px; font-weight: 500; text-decoration: none; padding: 6px 10px; border-radius: 7px; transition: background .15s, color .15s; }
.mt-back-link:hover { background: rgba(255,255,255,0.12); color: #fff; }

/* Loading / Error */
.mt-loading { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 80px; gap: 16px; color: #64748b; }
.mt-loading-spinner { width: 36px; height: 36px; border: 3px solid #e2e8f0; border-top-color: #059669; border-radius: 50%; animation: spin .7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.mt-error { text-align: center; padding: 80px; color: #64748b; }
.mt-error h2 { margin-bottom: 8px; color: #1e293b; }

/* Layout */
.mt-layout { max-width: 1100px; margin: 0 auto; padding: 28px 24px; display: grid; grid-template-columns: 1fr 300px; gap: 24px; align-items: start; }
@media (max-width: 768px) { .mt-layout { grid-template-columns: 1fr; } }

/* Main */
.mt-main { display: flex; flex-direction: column; gap: 20px; }
.mt-title-block { display: flex; align-items: flex-start; gap: 14px; background: #fff; padding: 20px 24px; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
.mt-ticket-num { font-size: 13px; font-weight: 700; color: #059669; background: #d1fae5; padding: 4px 10px; border-radius: 6px; white-space: nowrap; flex-shrink: 0; margin-top: 4px; }
.mt-title { font-size: 22px; font-weight: 700; color: #1e293b; margin: 0 0 4px; line-height: 1.3; }
.mt-subtitle { font-size: 14px; color: #64748b; line-height: 1.5; }
.mt-section { background: #fff; border-radius: 12px; padding: 20px 24px; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
.mt-section-label { display: flex; align-items: center; gap: 7px; font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .6px; margin-bottom: 16px; }
.mt-description { font-size: 14px; color: #334155; line-height: 1.7; white-space: pre-wrap; }
.mt-overdue { color: #dc2626 !important; font-weight: 600; }

/* Activity / Comments */
.mt-activity-list { display: flex; flex-direction: column; gap: 0; max-height: 400px; overflow-y: auto; }
.mt-activity-empty { color: #94a3b8; font-size: 13px; text-align: center; padding: 24px; }
.mt-activity-item { display: flex; gap: 12px; padding: 12px 0; border-bottom: 1px solid #f1f5f9; }
.mt-activity-item:last-child { border-bottom: none; }
.mt-activity-icon { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 2px; }
.mt-activity-comment .mt-activity-icon { background: #ecfdf5; color: #059669; }
.mt-activity-body { flex: 1; min-width: 0; }
.mt-activity-header { display: flex; align-items: center; gap: 8px; margin-bottom: 4px; }
.mt-activity-author { font-size: 13px; font-weight: 600; color: #1e293b; }
.mt-activity-time { font-size: 11px; color: #94a3b8; }
.mt-activity-bubble { background: #f8fafc; border-radius: 8px; padding: 10px 14px; font-size: 13px; color: #334155; line-height: 1.6; }

/* Attachments */
.mt-attachments-grid { display: flex; flex-wrap: wrap; gap: 10px; }
.mt-attachment-img { position: relative; width: 90px; height: 90px; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; display: block; }
.mt-attachment-img img { width: 100%; height: 100%; object-fit: cover; }
.mt-img-overlay { position: absolute; inset: 0; background: rgba(0,0,0,.4); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity .15s; }
.mt-attachment-img:hover .mt-img-overlay { opacity: 1; }
.mt-attachment-pdf { display: flex; align-items: center; gap: 8px; padding: 8px 12px; background: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px; color: #c2410c; font-size: 12px; font-weight: 600; text-decoration: none; }
.mt-attachment-pdf:hover { background: #ffedd5; }

/* Compose */
.mt-compose { background: #fff; border-radius: 12px; padding: 20px 24px; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
.mt-compose-textarea { width: 100%; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; font-size: 14px; color: #334155; resize: vertical; font-family: inherit; outline: none; box-sizing: border-box; }
.mt-compose-textarea:focus { border-color: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,.1); }
.mt-compose-footer { display: flex; align-items: center; justify-content: space-between; margin-top: 12px; gap: 10px; flex-wrap: wrap; }
.mt-author-input-wrap { display: flex; align-items: center; gap: 7px; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 12px; flex: 1; min-width: 140px; max-width: 220px; color: #94a3b8; }
.mt-author-input { border: none; outline: none; font-size: 13px; color: #334155; width: 100%; background: transparent; font-family: inherit; }
.btn-mt-comment { display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; background: linear-gradient(135deg, #059669, #047857); color: #fff; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: opacity .15s; }
.btn-mt-comment:hover:not(:disabled) { opacity: .85; }
.btn-mt-comment:disabled { opacity: .5; cursor: not-allowed; }

/* Sidebar */
.mt-sidebar { display: flex; flex-direction: column; gap: 16px; }
.mt-sidebar-card { background: #fff; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
.mt-sidebar-label { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .6px; margin-bottom: 12px; }
.mt-sidebar-empty { color: #94a3b8; font-size: 13px; font-style: italic; }
.mt-meta-list { display: flex; flex-direction: column; gap: 0; }
.mt-meta-row { display: flex; justify-content: space-between; align-items: center; padding: 7px 0; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
.mt-meta-row:last-child { border-bottom: none; }
.mt-meta-key { color: #64748b; font-size: 12px; }
.mt-meta-val { color: #1e293b; text-align: right; max-width: 55%; word-break: break-word; }

/* Person rows */
.mt-person-row { display: flex; align-items: center; gap: 10px; padding: 6px 0; }
.mt-person-avatar { width: 30px; height: 30px; border-radius: 50%; background: #059669; color: #fff; font-size: 12px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.mt-person-name { font-size: 13px; font-weight: 600; color: #1e293b; }
.mt-person-email { font-size: 11px; color: #64748b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.mt-dev-block { display: flex; align-items: center; gap: 10px; padding: 7px 0; border-bottom: 1px solid #f1f5f9; }
.mt-dev-block:last-child { border-bottom: none; }
.mt-folder-btn { display: flex; align-items: center; justify-content: center; gap: 6px; width: 100%; margin-top: 10px; padding: 8px 14px; background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; cursor: pointer; transition: background .15s; }
.mt-folder-btn:hover { background: #d1fae5; }
.mt-folder-icon-btn { display: flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 7px; background: #ecfdf5; border: 1px solid #a7f3d0; font-size: 15px; text-decoration: none; flex-shrink: 0; transition: background .15s; }
.mt-folder-icon-btn:hover { background: #d1fae5; }

/* Dev Status select */
.mt-dev-status-select { width: 100%; padding: 8px 12px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 13px; font-weight: 600; outline: none; cursor: pointer; }
.mt-dev-status--not-started { background: #f8fafc; color: #64748b; border-color: #cbd5e1; }
.mt-dev-status--in-progress  { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
.mt-dev-status--ready-for-qa { background: #f0fdf4; color: #16a34a; border-color: #bbf7d0; }
.mt-dev-status--blocked       { background: #fff1f2; color: #e11d48; border-color: #fecdd3; }

/* Status select */
.mt-status-select { width: 100%; padding: 8px 12px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 13px; font-weight: 600; outline: none; cursor: pointer; }
.mt-status--pending      { background: #fefce8; color: #854d0e; border-color: #fde68a; }
.mt-status--in-progress  { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
.mt-status--on-hold      { background: #fff7ed; color: #c2410c; border-color: #fed7aa; }
.mt-status--completed    { background: #f0fdf4; color: #16a34a; border-color: #bbf7d0; }
.mt-status--cancelled    { background: #f8fafc; color: #64748b; border-color: #cbd5e1; }

/* Count pill */
.ticket-count-pill { display: inline-flex; align-items: center; justify-content: center; min-width: 20px; height: 20px; padding: 0 6px; background: #e2e8f0; color: #475569; font-size: 11px; font-weight: 700; border-radius: 99px; }

/* ── Activity Log Timeline ─────────────────────────────────────── */
.mt-activity-log-section { padding-bottom: 8px; }
.mt-timeline { display: flex; flex-direction: column; }
.mt-timeline-item { display: flex; gap: 14px; }

/* Left column: dot + connector */
.mt-tl-line-col { display: flex; flex-direction: column; align-items: center; flex-shrink: 0; width: 28px; }
.mt-tl-dot {
  width: 28px; height: 28px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; border: 2px solid transparent;
}
.mt-tl-connector { flex: 1; width: 2px; background: #f1f5f9; min-height: 16px; margin: 3px 0; }

/* Dot colors by type */
.mt-tl-created   .mt-tl-dot { background: #ecfdf5; color: #059669; border-color: #a7f3d0; }
.mt-tl-comment   .mt-tl-dot { background: #eff6ff; color: #2563eb; border-color: #bfdbfe; }
.mt-tl-status    .mt-tl-dot { background: #f0fdf4; color: #16a34a; border-color: #bbf7d0; }
.mt-tl-dev       .mt-tl-dot { background: #faf5ff; color: #7c3aed; border-color: #ddd6fe; }

/* Right content */
.mt-tl-content { flex: 1; padding-bottom: 20px; padding-top: 4px; min-width: 0; }
.mt-timeline-item:last-child .mt-tl-content { padding-bottom: 0; }
.mt-tl-top { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 5px; flex-wrap: wrap; }
.mt-tl-label { font-size: 13px; font-weight: 600; color: #1e293b; }
.mt-tl-time  { font-size: 11px; color: #94a3b8; white-space: nowrap; }
.mt-tl-body  { font-size: 13px; color: #475569; background: #f8fafc; border-radius: 8px; padding: 8px 12px; line-height: 1.6; border: 1px solid #f1f5f9; }
.mt-tl-change { display: inline-flex; align-items: center; gap: 6px; margin-top: 4px; }
.mt-tl-from  { font-size: 11.5px; font-weight: 600; background: #fee2e2; color: #b91c1c; padding: 2px 8px; border-radius: 99px; }
.mt-tl-to    { font-size: 11.5px; font-weight: 600; background: #dcfce7; color: #15803d; padding: 2px 8px; border-radius: 99px; }
</style>
