<template>
  <div class="ticket-page">

    <!-- Header -->
    <header class="ticket-header">
      <div class="ticket-header-inner">
        <div class="ticket-header-logo">
          <div class="app-logo-icon" style="font-size:20px;">🐛</div>
          <div>
            <div style="font-weight:700;color:#fff;font-size:15px;">QA Bug Tracker</div>
            <div style="font-size:11px;color:#a5b4fc;">Ticket View</div>
          </div>
        </div>
        <div v-if="bug" class="ticket-header-meta">
          <span :class="['badge', priorityBadgeClass(bug.priority)]">{{ bug.priority }}</span>
          <span :class="['badge', statusBadgeClass(bug.status)]">{{ bug.status }}</span>
        </div>
      </div>
    </header>

    <!-- Loading -->
    <div v-if="loading" class="ticket-loading">
      <div class="ticket-loading-spinner"></div>
      <p>Loading ticket…</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="ticket-error">
      <div style="font-size:40px;margin-bottom:12px;">⚠️</div>
      <h2>Ticket not found</h2>
      <p>{{ error }}</p>
    </div>

    <!-- Content -->
    <div v-else-if="bug" class="ticket-layout">

      <!-- Left Panel: Main content -->
      <div class="ticket-main">

        <!-- Bug title -->
        <div class="ticket-title-block">
          <span class="bug-seq" style="font-size:14px;flex-shrink:0;">#{{ bug.sequence }}</span>
          <h1 class="ticket-title">{{ bug.title }}</h1>
        </div>

        <!-- Description -->
        <div v-if="bug.description" class="ticket-section">
          <div class="ticket-section-label">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            Description
          </div>
          <div class="ticket-description" v-html="bug.description"></div>
        </div>

        <!-- Activity Thread -->
        <div class="ticket-section">
          <div class="ticket-section-label">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            Activity
            <span v-if="activityLog.length" style="font-weight:400;color:#94a3b8;font-size:12px;">({{ activityLog.length }})</span>
          </div>

          <div class="ticket-activity-list" ref="activityListEl">
            <div v-if="!activityLog.length" class="ticket-activity-empty">
              No activity yet. Be the first to comment.
            </div>
            <div v-for="(entry, i) in activityLog" :key="i" class="ticket-activity-item" :class="'ticket-activity-' + entry.type">
              <!-- Icon -->
              <div class="ticket-activity-icon">
                <svg v-if="entry.type === 'comment'" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <svg v-else-if="entry.type === 'ticket_sent'" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                <svg v-else-if="entry.type === 'resolved'" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                <svg v-else width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              </div>
              <!-- Content -->
              <div class="ticket-activity-body">
                <div class="ticket-activity-header">
                  <span class="ticket-activity-author">{{ entry.user_name }}</span>
                  <span class="ticket-activity-time">{{ formatTime(entry.timestamp) }}</span>
                </div>
                <div v-if="entry.type === 'comment'" class="ticket-activity-comment">{{ entry.content }}</div>
                <div v-else class="ticket-activity-event">{{ entry.content }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Compose area (locked after resolved) -->
        <div class="ticket-compose" :class="{ 'ticket-compose--locked': isResolved }">
          <div v-if="isResolved" class="ticket-resolved-notice">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            This ticket has been resolved. No further comments can be added.
          </div>
          <template v-else>
            <textarea
              v-model="newComment"
              class="ticket-compose-textarea"
              placeholder="Write a comment… (Ctrl+Enter to post)"
              rows="3"
              @keydown.ctrl.enter.prevent="postComment"
            ></textarea>
            <div class="ticket-compose-footer">
              <div class="ticket-author-input-wrap">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <input v-model="authorName" class="ticket-author-input" placeholder="Your name" />
              </div>
              <div style="display:flex;gap:8px;">
                <button class="btn-ticket-resolve" @click="markResolved" :disabled="posting">
                  <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                  Mark as resolved
                </button>
                <button class="btn-ticket-comment" :disabled="!newComment.trim() || posting" @click="postComment">
                  <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                  {{ posting ? 'Posting…' : 'Post comment' }}
                </button>
              </div>
            </div>
          </template>
        </div>

      </div>

      <!-- Right Panel: Sidebar -->
      <aside class="ticket-sidebar">

        <!-- Status -->
        <div class="ticket-sidebar-card">
          <div class="ticket-sidebar-label">Status</div>
          <select
            v-if="!isResolved"
            v-model="currentStatus"
            class="ticket-status-select"
            :class="'ticket-status--' + currentStatus.toLowerCase().replace(/\s+/g, '-')"
            @change="updateStatus"
          >
            <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
          </select>
          <span v-else class="badge badge-completed" style="font-size:13px;padding:6px 14px;">Completed</span>
        </div>

        <!-- Assigned Developer -->
        <div class="ticket-sidebar-card">
          <div class="ticket-sidebar-label">Assigned Developer</div>
          <div v-if="bug.assigned_developer" class="ticket-sidebar-person">
            <div class="ticket-sidebar-avatar">{{ bug.assigned_developer.name?.[0]?.toUpperCase() }}</div>
            <div>
              <div class="ticket-sidebar-person-name">{{ bug.assigned_developer.name }}</div>
              <div class="ticket-sidebar-person-email">{{ bug.assigned_developer.email }}</div>
            </div>
          </div>
          <div v-else class="ticket-sidebar-empty">Not assigned</div>
        </div>

        <!-- Bug Metadata -->
        <div class="ticket-sidebar-card">
          <div class="ticket-sidebar-label">Details</div>
          <div class="ticket-meta-list">
            <div class="ticket-meta-row">
              <span class="ticket-meta-key">Priority</span>
              <span :class="['badge', priorityBadgeClass(bug.priority)]">{{ bug.priority }}</span>
            </div>
            <div class="ticket-meta-row">
              <span class="ticket-meta-key">Scenario</span>
              <span :class="['badge', scenarioBadgeClass(bug.scenario_type)]">{{ bug.scenario_type }}</span>
            </div>
            <div v-if="bug.project" class="ticket-meta-row">
              <span class="ticket-meta-key">Project</span>
              <span style="font-size:13px;color:#334155;font-weight:500;display:flex;align-items:center;gap:6px;">
                <span style="width:8px;height:8px;border-radius:50%;display:inline-block;" :style="{ background: bug.project.color }"></span>
                {{ bug.project.name }}
              </span>
            </div>
            <div class="ticket-meta-row">
              <span class="ticket-meta-key">Reported</span>
              <span style="font-size:13px;color:#334155;">{{ formatDate(bug.created_at) }}</span>
            </div>
            <div v-if="bug.ticket_sent_at" class="ticket-meta-row">
              <span class="ticket-meta-key">Sent</span>
              <span style="font-size:13px;color:#334155;">{{ formatDate(bug.ticket_sent_at) }}</span>
            </div>
          </div>
        </div>

        <!-- Activity Log counts -->
        <div class="ticket-sidebar-card">
          <div class="ticket-sidebar-label">Activity Log</div>
          <div class="ticket-meta-list">
            <div class="ticket-meta-row">
              <span class="ticket-meta-key">Comments</span>
              <span style="font-size:13px;color:#334155;font-weight:600;">{{ commentCount }}</span>
            </div>
            <div class="ticket-meta-row">
              <span class="ticket-meta-key">Status changes</span>
              <span style="font-size:13px;color:#334155;font-weight:600;">{{ statusChangeCount }}</span>
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

const route   = useRoute()
const config  = useRuntimeConfig()
const apiBase = config.public.apiBase

const bug            = ref(null)
const loading        = ref(true)
const error          = ref(null)
const newComment     = ref('')
const authorName     = ref('')
const posting        = ref(false)
const currentStatus  = ref('Pending')
const activityListEl = ref(null)

const statuses = ['Pending', 'Out of Scope', 'Ongoing', 'Completed']

const activityLog = computed(() => bug.value?.activity_log ?? [])
const isResolved  = computed(() => bug.value?.status === 'Completed')
const commentCount = computed(() => activityLog.value.filter(e => e.type === 'comment').length)
const statusChangeCount = computed(() => activityLog.value.filter(e => e.type === 'status_change').length)

const priorityBadgeClass = (p) => ({ Critical: 'badge-critical', High: 'badge-high', Medium: 'badge-medium', Low: 'badge-low' }[p] || '')
const statusBadgeClass   = (s) => ({ Pending: 'badge-pending', 'Out of Scope': 'badge-outofscope', Ongoing: 'badge-ongoing', Completed: 'badge-completed' }[s] || '')
const scenarioBadgeClass = (t) => ({ UI: 'badge-ui', Functionality: 'badge-functionality', 'UI & Functionality': 'badge-uifunctionality' }[t] || '')

const formatTime = (iso) => {
  if (!iso) return ''
  const d = new Date(iso)
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) +
    ' · ' + d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
}
const formatDate = (iso) => {
  if (!iso) return ''
  return new Date(iso).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
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
    bug.value = await $fetch(`${apiBase}/bugs/${route.params.id}/ticket`)
    currentStatus.value = bug.value.status
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
    bug.value = await $fetch(`${apiBase}/bugs/${bug.value.id}/comments`, {
      method: 'POST',
      body: { message, author: authorName.value || 'Developer' },
    })
    currentStatus.value = bug.value.status
    scrollToBottom()
  } catch (e) {
    console.error('Failed to post comment', e)
    newComment.value = message
  } finally {
    posting.value = false
  }
}

const updateStatus = async () => {
  try {
    bug.value = await $fetch(`${apiBase}/bugs/${bug.value.id}/status`, {
      method: 'PATCH',
      body: { status: currentStatus.value, author: authorName.value || 'Developer' },
    })
    currentStatus.value = bug.value.status
    scrollToBottom()
  } catch (e) {
    console.error('Failed to update status', e)
    currentStatus.value = bug.value?.status ?? 'Pending'
  }
}

const markResolved = async () => {
  if (posting.value) return
  posting.value = true
  try {
    bug.value = await $fetch(`${apiBase}/bugs/${bug.value.id}/resolve`, {
      method: 'POST',
      body: { author: authorName.value || 'Developer' },
    })
    currentStatus.value = bug.value.status
    scrollToBottom()
  } catch (e) {
    console.error('Failed to resolve ticket', e)
  } finally {
    posting.value = false
  }
}

onMounted(loadTicket)
</script>

<style scoped>
.ticket-page { min-height: 100vh; background: #f1f5f9; font-family: Inter, Arial, sans-serif; }

/* Header */
.ticket-header { background: #4f46e5; padding: 0; }
.ticket-header-inner { max-width: 1100px; margin: 0 auto; padding: 16px 24px; display: flex; align-items: center; justify-content: space-between; }
.ticket-header-logo { display: flex; align-items: center; gap: 10px; }
.ticket-header-meta { display: flex; gap: 8px; }

/* Loading / Error */
.ticket-loading { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 80px; gap: 16px; color: #64748b; }
.ticket-loading-spinner { width: 36px; height: 36px; border: 3px solid #e2e8f0; border-top-color: #4f46e5; border-radius: 50%; animation: spin .7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.ticket-error { text-align: center; padding: 80px; color: #64748b; }
.ticket-error h2 { margin-bottom: 8px; color: #1e293b; }

/* Layout */
.ticket-layout { max-width: 1100px; margin: 0 auto; padding: 28px 24px; display: grid; grid-template-columns: 1fr 300px; gap: 24px; align-items: start; }
@media (max-width: 768px) { .ticket-layout { grid-template-columns: 1fr; } }

/* Main */
.ticket-main { display: flex; flex-direction: column; gap: 20px; }
.ticket-title-block { display: flex; align-items: flex-start; gap: 10px; background: #fff; padding: 20px 24px; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
.ticket-title { font-size: 22px; font-weight: 700; color: #1e293b; margin: 0; line-height: 1.3; }
.ticket-section { background: #fff; border-radius: 12px; padding: 20px 24px; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
.ticket-section-label { display: flex; align-items: center; gap: 7px; font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .6px; margin-bottom: 16px; }
.ticket-description { font-size: 14px; color: #334155; line-height: 1.7; }
.ticket-description ul, .ticket-description ol { padding-left: 20px; }

/* Activity */
.ticket-activity-list { display: flex; flex-direction: column; gap: 0; max-height: 400px; overflow-y: auto; }
.ticket-activity-empty { color: #94a3b8; font-size: 13px; text-align: center; padding: 24px; }
.ticket-activity-item { display: flex; gap: 12px; padding: 12px 0; border-bottom: 1px solid #f1f5f9; }
.ticket-activity-item:last-child { border-bottom: none; }
.ticket-activity-icon { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 2px; }
.ticket-activity-comment .ticket-activity-icon { background: #eff6ff; color: #1d4ed8; }
.ticket-activity-ticket_sent .ticket-activity-icon { background: #f0f9ff; color: #0284c7; }
.ticket-activity-resolved .ticket-activity-icon { background: #f0fdf4; color: #16a34a; }
.ticket-activity-status_change .ticket-activity-icon { background: #fef9c3; color: #854d0e; }
.ticket-activity-body { flex: 1; min-width: 0; }
.ticket-activity-header { display: flex; align-items: center; gap: 8px; margin-bottom: 4px; }
.ticket-activity-author { font-size: 13px; font-weight: 600; color: #1e293b; }
.ticket-activity-time { font-size: 11px; color: #94a3b8; }
.ticket-activity-comment { background: #f8fafc; border-radius: 8px; padding: 10px 14px; font-size: 13px; color: #334155; line-height: 1.6; }
.ticket-activity-event { font-size: 13px; color: #64748b; font-style: italic; }

/* Compose */
.ticket-compose { background: #fff; border-radius: 12px; padding: 20px 24px; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
.ticket-compose--locked { background: #f8fafc; }
.ticket-resolved-notice { display: flex; align-items: center; gap: 8px; color: #16a34a; font-size: 13px; font-weight: 500; padding: 8px 0; }
.ticket-compose-textarea { width: 100%; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; font-size: 14px; resize: vertical; font-family: inherit; outline: none; color: #334155; }
.ticket-compose-textarea:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px #eef2ff; }
.ticket-compose-footer { display: flex; align-items: center; justify-content: space-between; margin-top: 12px; gap: 10px; flex-wrap: wrap; }
.ticket-author-input-wrap { display: flex; align-items: center; gap: 7px; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 12px; flex: 1; min-width: 140px; max-width: 220px; }
.ticket-author-input { border: none; outline: none; font-size: 13px; color: #334155; width: 100%; background: transparent; }
.btn-ticket-comment { display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; border-radius: 8px; background: #4f46e5; color: #fff; border: none; font-size: 13px; font-weight: 600; cursor: pointer; transition: background .15s; }
.btn-ticket-comment:hover:not(:disabled) { background: #4338ca; }
.btn-ticket-comment:disabled { opacity: .5; cursor: not-allowed; }
.btn-ticket-resolve { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 8px; background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; font-size: 13px; font-weight: 600; cursor: pointer; transition: all .15s; }
.btn-ticket-resolve:hover:not(:disabled) { background: #dcfce7; }
.btn-ticket-resolve:disabled { opacity: .5; cursor: not-allowed; }

/* Sidebar */
.ticket-sidebar { display: flex; flex-direction: column; gap: 16px; }
.ticket-sidebar-card { background: #fff; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
.ticket-sidebar-label { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .6px; margin-bottom: 12px; }
.ticket-sidebar-person { display: flex; align-items: center; gap: 10px; }
.ticket-sidebar-avatar { width: 36px; height: 36px; border-radius: 50%; background: #4f46e5; color: #fff; font-size: 14px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ticket-sidebar-person-name { font-size: 14px; font-weight: 600; color: #1e293b; }
.ticket-sidebar-person-email { font-size: 12px; color: #94a3b8; margin-top: 2px; }
.ticket-sidebar-empty { font-size: 13px; color: #94a3b8; font-style: italic; }
.ticket-meta-list { display: flex; flex-direction: column; gap: 0; }
.ticket-meta-row { display: flex; align-items: center; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f1f5f9; }
.ticket-meta-row:last-child { border-bottom: none; }
.ticket-meta-key { font-size: 12px; color: #94a3b8; font-weight: 500; }

/* Status select */
.ticket-status-select { width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 13px; font-weight: 600; cursor: pointer; outline: none; }
.ticket-status--pending { background: #fefce8; color: #854d0e; border-color: #fde68a; }
.ticket-status--out-of-scope { background: #f1f5f9; color: #475569; border-color: #e2e8f0; }
.ticket-status--ongoing { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
.ticket-status--completed { background: #f0fdf4; color: #166534; border-color: #bbf7d0; }

/* Shared badge styles (copied for scoped) */
.bug-seq { font-size: 12px; font-weight: 700; color: #94a3b8; background: #f1f5f9; padding: 2px 8px; border-radius: 6px; }
.badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 99px; font-size: 11px; font-weight: 700; border: 1px solid transparent; }
.badge-critical { background: #fef2f2; color: #dc2626; border-color: #fecaca; }
.badge-high     { background: #fff7ed; color: #ea580c; border-color: #fed7aa; }
.badge-medium   { background: #fefce8; color: #ca8a04; border-color: #fde68a; }
.badge-low      { background: #f0fdf4; color: #16a34a; border-color: #bbf7d0; }
.badge-pending      { background: #fefce8; color: #854d0e; border-color: #fde68a; }
.badge-outofscope   { background: #f1f5f9; color: #475569; border-color: #e2e8f0; }
.badge-ongoing      { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
.badge-completed    { background: #f0fdf4; color: #166534; border-color: #bbf7d0; }
.badge-ui               { background: #f0f9ff; color: #0284c7; border-color: #bae6fd; }
.badge-functionality    { background: #fdf4ff; color: #9333ea; border-color: #e9d5ff; }
.badge-uifunctionality  { background: #fff1f2; color: #e11d48; border-color: #fecdd3; }
</style>
