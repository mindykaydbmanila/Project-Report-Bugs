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

        <!-- Subtitles -->
        <div v-if="bug.subtitles && bug.subtitles.length" class="ticket-section">
          <div class="ticket-section-label">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            Subtitles
          </div>
          <div class="ticket-subtitle-list">
            <div v-for="(sub, idx) in bug.subtitles" :key="idx" class="ticket-subtitle-card">
              <div class="ticket-subtitle-index">{{ idx + 1 }}</div>
              <div class="ticket-subtitle-content">
                <div v-if="(typeof sub === 'string' ? sub : sub.text)" class="ticket-subtitle-text">{{ typeof sub === 'string' ? sub : sub.text }}</div>
                <a v-if="sub.link" :href="sub.link" target="_blank" rel="noopener" class="ticket-subtitle-link">
                  <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                  {{ sub.link }}
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Description -->
        <div class="ticket-section">
          <div class="ticket-section-label">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            Description
          </div>
          <div v-if="bug.description" class="ticket-description" v-html="bug.description"></div>
          <div v-else class="ticket-empty-text">No description provided.</div>
        </div>

        <!-- Dev Comments -->
        <div class="ticket-section">
          <div class="ticket-section-label" style="justify-content:space-between;">
            <span style="display:flex;align-items:center;gap:7px;">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              Comments
            </span>
            <span v-if="bug.dev_comments?.length" class="ticket-count-pill">{{ bug.dev_comments.length }}</span>
          </div>
          <div v-if="bug.dev_comments && bug.dev_comments.length" class="ticket-dev-comments">
            <div v-for="(msg, i) in bug.dev_comments" :key="i" class="ticket-dev-comment-item">
              <div class="ticket-dev-comment-header">
                <span class="ticket-dev-comment-avatar">{{ (msg.author || '?')[0].toUpperCase() }}</span>
                <span class="ticket-dev-comment-author">{{ msg.author || 'Anonymous' }}</span>
                <span class="ticket-dev-comment-time">{{ formatTime(msg.timestamp) }}{{ msg.edited ? ' · edited' : '' }}</span>
              </div>
              <div class="ticket-dev-comment-bubble">{{ msg.message }}</div>
            </div>
          </div>
          <div v-else class="ticket-empty-text">No comments yet.</div>
        </div>

        <!-- Screenshots -->
        <div class="ticket-screenshots-row">
          <div class="ticket-section">
            <div class="ticket-section-label">
              <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
              Screenshots — <span style="color:#0891b2;font-weight:700;">Front-End</span>
              <span v-if="bug.frontend_images?.length" class="ticket-count-pill">{{ bug.frontend_images.length }}</span>
            </div>
            <div v-if="bug.frontend_images && bug.frontend_images.length" class="ticket-img-grid">
              <a v-for="(img, idx) in bug.frontend_images" :key="idx" :href="storageBase + img" target="_blank" class="ticket-img-item">
                <img :src="storageBase + img" :alt="`FE ${idx+1}`" />
                <div class="ticket-img-overlay">
                  <svg width="16" height="16" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                </div>
              </a>
            </div>
            <div v-else class="ticket-empty-text">No images attached.</div>
          </div>

          <div class="ticket-section">
            <div class="ticket-section-label">
              <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
              Screenshots — <span style="color:#7c3aed;font-weight:700;">CMS</span>
              <span v-if="bug.cms_images?.length" class="ticket-count-pill">{{ bug.cms_images.length }}</span>
            </div>
            <div v-if="bug.cms_images && bug.cms_images.length" class="ticket-img-grid">
              <a v-for="(img, idx) in bug.cms_images" :key="idx" :href="storageBase + img" target="_blank" class="ticket-img-item">
                <img :src="storageBase + img" :alt="`CMS ${idx+1}`" />
                <div class="ticket-img-overlay">
                  <svg width="16" height="16" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                </div>
              </a>
            </div>
            <div v-else class="ticket-empty-text">No images attached.</div>
          </div>
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
                <svg v-else-if="entry.type === 'dev_status_change'" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
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

        <!-- Dev Status -->
        <div class="ticket-sidebar-card">
          <div class="ticket-sidebar-label">Dev Status</div>
          <select
            v-model="currentDevStatus"
            :class="['ticket-dev-status-select', 'ticket-dev-status--' + currentDevStatus.toLowerCase().replace(/\s+/g, '-')]"
            @change="updateDevStatus"
          >
            <option value="Not Started">Not Started</option>
            <option value="In Progress">In Progress</option>
            <option value="Ready for QA">Ready for QA ✅</option>
            <option value="Blocked">Blocked ⚠️</option>
          </select>
          <div v-if="currentDevStatus === 'Ready for QA'" class="ticket-dev-status-notice ticket-dev-status-notice--ready">
            🔔 QA team has been notified to verify this bug.
          </div>
          <div v-else-if="currentDevStatus === 'Blocked'" class="ticket-dev-status-notice ticket-dev-status-notice--blocked">
            ⚠️ This bug is blocked — QA team has been alerted.
          </div>
        </div>

        <!-- Status -->
        <div class="ticket-sidebar-card">
          <div class="ticket-sidebar-label">QA Status</div>
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
          <!-- My Folder link -->
          <button
            v-if="bug.assigned_developer"
            class="my-folder-btn"
            style="margin-top:12px;width:100%;"
            @click="openMyFolder"
          >
            📁 View My Ticket Folder
          </button>
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
            <div class="ticket-meta-row ticket-meta-row--date" style="flex-direction:column;align-items:flex-start;gap:6px;">
              <span class="ticket-meta-key">Due Date</span>
              <div class="date-accomplish-wrap">
                <input
                  v-if="editingDate"
                  type="date"
                  class="date-accomplish-input"
                  v-model="dateInput"
                  @change="saveDateToAccomplish"
                  @blur="editingDate = false"
                  autofocus
                />
                <template v-else>
                  <template v-if="bug.date_to_accomplish">
                    <div style="display:flex;flex-direction:column;gap:3px;">
                      <div style="display:flex;align-items:center;gap:6px;">
                        <span
                          class="date-accomplish-value"
                          :class="{ 'date-accomplish-overdue': isOverdue }"
                        >{{ formatDate(bug.date_to_accomplish) }}</span>
                        <button class="date-accomplish-btn" @click="startEditDate" title="Change date">
                          <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                      </div>
                      <span v-if="dueDaysLabel" class="due-days-pill" :style="{ color: dueDaysLabel.color, borderColor: dueDaysLabel.color + '40', background: dueDaysLabel.color + '12' }">{{ dueDaysLabel.text }}</span>
                    </div>
                  </template>
                  <button v-else class="date-set-btn" @click="startEditDate">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    Set due date
                  </button>
                </template>
              </div>
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

const route       = useRoute()
const config      = useRuntimeConfig()
const apiBase     = config.public.apiBase          // used for API calls  e.g. /api/bugs/1/ticket
const storageBase = config.public.apiBase.replace('/api', '') // used for image src  e.g. /storage/bug-images/...

const bug            = ref(null)
const loading        = ref(true)
const error          = ref(null)
const newComment     = ref('')
const authorName     = ref('')
const posting        = ref(false)
const currentStatus    = ref('Pending')
const currentDevStatus = ref('Not Started')
const editingDate      = ref(false)
const dateInput        = ref('')
const activityListEl   = ref(null)

const statuses    = ['Pending', 'Out of Scope', 'Ongoing', 'Completed']
const devStatuses = ['Not Started', 'In Progress', 'Ready for QA', 'Blocked']

const activityLog = computed(() => bug.value?.activity_log ?? [])
const isResolved  = computed(() => bug.value?.status === 'Completed')
const isOverdue   = computed(() => {
  if (!bug.value?.date_to_accomplish) return false
  return new Date(bug.value.date_to_accomplish) < new Date(new Date().toDateString())
})
const dueDaysLabel = computed(() => {
  if (!bug.value?.date_to_accomplish) return null
  const today = new Date(new Date().toDateString())
  const due   = new Date(bug.value.date_to_accomplish)
  const diff  = Math.round((due - today) / 86400000)
  if (diff < 0)   return { text: diff === -1 ? '1 day overdue' : `${Math.abs(diff)} days overdue`, color: '#dc2626' }
  if (diff === 0) return { text: 'Due today',         color: '#dc2626' }
  if (diff <= 2)  return { text: `${diff} day${diff === 1 ? '' : 's'} left`, color: '#dc2626' }
  if (diff <= 7)  return { text: `${diff} days left`, color: '#d97706' }
  return { text: `${diff} days left`, color: '#16a34a' }
})
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
    currentStatus.value    = bug.value.status
    currentDevStatus.value = bug.value.dev_status || 'Not Started'
    dateInput.value        = bug.value.date_to_accomplish ?? ''
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

const updateDevStatus = async () => {
  try {
    bug.value = await $fetch(`${apiBase}/bugs/${bug.value.id}/dev-status`, {
      method: 'PATCH',
      body: { dev_status: currentDevStatus.value, author: authorName.value || 'Developer' },
    })
    currentDevStatus.value = bug.value.dev_status || 'Not Started'
    scrollToBottom()
  } catch (e) {
    console.error('Failed to update dev status', e)
    currentDevStatus.value = bug.value?.dev_status || 'Not Started'
  }
}

const startEditDate = () => {
  dateInput.value = bug.value.date_to_accomplish ?? ''
  editingDate.value = true
}

const saveDateToAccomplish = async () => {
  editingDate.value = false
  try {
    bug.value = await $fetch(`${apiBase}/bugs/${bug.value.id}/date-to-accomplish`, {
      method: 'PATCH',
      body: { date_to_accomplish: dateInput.value || null, author: authorName.value || 'Developer' },
    })
    dateInput.value = bug.value.date_to_accomplish ?? ''
    scrollToBottom()
  } catch (e) {
    console.error('Failed to update date to accomplish', e)
    dateInput.value = bug.value?.date_to_accomplish ?? ''
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

async function openMyFolder() {
  if (!bug.value?.assigned_developer) return
  const dev = bug.value.assigned_developer
  try {
    const body = {
      developer_email: dev.email,
      developer_name:  dev.name,
      visibility:      'private',
    }
    if (bug.value.project_id) body.project_id = bug.value.project_id

    const data = await $fetch(`${apiBase}/dev-folders`, {
      method: 'POST',
      body,
    })
    window.open(data.url, '_blank')
  } catch (e) {
    const msg = e?.data?.message || e?.message || JSON.stringify(e?.data) || 'Unknown error'
    alert(`Could not open folder.\n\nError: ${msg}\n\nMake sure you ran: php artisan migrate`)
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
.my-folder-btn { display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 13px; font-weight: 600; color: #4f46e5; background: #eef2ff; border: 1.5px solid #c7d2fe; border-radius: 8px; padding: 8px 14px; cursor: pointer; transition: background .15s; }
.my-folder-btn:hover { background: #e0e7ff; }
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

/* Dev Status select */
.ticket-dev-status-select { width: 100%; padding: 8px 12px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 13px; font-weight: 600; cursor: pointer; outline: none; appearance: none; font-family: inherit; }
.ticket-dev-status-select:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px #eef2ff; }
.ticket-dev-status--not-started  { background: #f8fafc; color: #64748b; border-color: #e2e8f0; }
.ticket-dev-status--in-progress  { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
.ticket-dev-status--ready-for-qa { background: #f0fdf4; color: #15803d; border-color: #86efac; animation: ticket-dev-glow 1.8s ease-in-out infinite; }
.ticket-dev-status--blocked      { background: #fef2f2; color: #dc2626; border-color: #fecaca; animation: ticket-dev-glow-red 1.8s ease-in-out infinite; }
@keyframes ticket-dev-glow { 0%,100% { box-shadow: 0 0 0 0 rgba(21,128,61,.3); } 50% { box-shadow: 0 0 0 5px rgba(21,128,61,0); } }
@keyframes ticket-dev-glow-red { 0%,100% { box-shadow: 0 0 0 0 rgba(220,38,38,.3); } 50% { box-shadow: 0 0 0 5px rgba(220,38,38,0); } }
.ticket-dev-status-notice { margin-top: 8px; padding: 8px 12px; border-radius: 8px; font-size: 12px; font-weight: 500; line-height: 1.5; }
.ticket-dev-status-notice--ready   { background: #f0fdf4; color: #15803d; border: 1px solid #86efac; }
.ticket-dev-status-notice--blocked { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
.ticket-activity-dev_status_change .ticket-activity-icon { background: #f5f3ff; color: #7c3aed; }
.ticket-activity-date_to_accomplish_change .ticket-activity-icon { background: #fff7ed; color: #c2410c; }

/* Date to accomplish */
.ticket-meta-row--date { align-items: flex-start !important; }
.date-accomplish-wrap { display: flex; align-items: center; gap: 6px; }
.date-accomplish-value { font-size: 13px; color: #334155; }
.date-accomplish-overdue { color: #dc2626; font-weight: 600; }
.date-accomplish-btn { background: none; border: none; cursor: pointer; color: #94a3b8; padding: 2px; display: flex; align-items: center; border-radius: 4px; }
.date-accomplish-btn:hover { color: #4f46e5; background: #f1f5f9; }
.date-accomplish-input { font-size: 13px; border: 1px solid #c7d2fe; border-radius: 6px; padding: 3px 8px; color: #1e293b; outline: none; }
.date-set-btn { display: inline-flex; align-items: center; gap: 5px; padding: 5px 10px; border-radius: 6px; border: 1px dashed #c7d2fe; background: #f8faff; color: #6366f1; font-size: 12px; font-weight: 500; cursor: pointer; transition: all .15s; }
.date-set-btn:hover { background: #eef2ff; border-color: #818cf8; color: #4f46e5; }
.due-days-pill { display: inline-block; font-size: 11px; font-weight: 600; padding: 2px 7px; border-radius: 20px; border: 1px solid; width: fit-content; }

/* Subtitles */
.ticket-subtitle-list { display: flex; flex-direction: column; gap: 8px; }
.ticket-subtitle-card { display: flex; align-items: flex-start; gap: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 14px; }
.ticket-subtitle-index { width: 24px; height: 24px; border-radius: 50%; background: #4f46e5; color: #fff; font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ticket-subtitle-content { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
.ticket-subtitle-text { font-size: 13.5px; color: #334155; font-weight: 500; }
.ticket-subtitle-link { display: inline-flex; align-items: center; gap: 5px; font-size: 12px; color: #4f46e5; text-decoration: none; word-break: break-all; }
.ticket-subtitle-link:hover { text-decoration: underline; }

/* Dev Comments */
.ticket-dev-comments { display: flex; flex-direction: column; gap: 10px; }
.ticket-dev-comment-item { display: flex; flex-direction: column; gap: 5px; }
.ticket-dev-comment-header { display: flex; align-items: center; gap: 8px; }
.ticket-dev-comment-avatar { width: 24px; height: 24px; border-radius: 50%; background: #4f46e5; color: #fff; font-size: 10px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ticket-dev-comment-author { font-size: 13px; font-weight: 600; color: #1e293b; }
.ticket-dev-comment-time { font-size: 11px; color: #94a3b8; }
.ticket-dev-comment-bubble { background: #f8fafc; border-radius: 3px 10px 10px 10px; padding: 10px 14px; font-size: 13px; color: #334155; line-height: 1.6; margin-left: 32px; }

/* Screenshots */
.ticket-screenshots-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
@media (max-width: 640px) { .ticket-screenshots-row { grid-template-columns: 1fr; } }
.ticket-img-grid { display: flex; flex-wrap: wrap; gap: 8px; }
.ticket-img-item { position: relative; width: 100px; height: 72px; border-radius: 8px; overflow: hidden; cursor: pointer; border: 1px solid #e2e8f0; }
.ticket-img-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
.ticket-img-overlay { position: absolute; inset: 0; background: rgba(0,0,0,.45); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity .15s; }
.ticket-img-item:hover .ticket-img-overlay { opacity: 1; }

/* Misc */
.ticket-count-pill { background: #e0e7ff; color: #4f46e5; font-size: 11px; font-weight: 700; border-radius: 99px; padding: 2px 8px; }
.ticket-empty-text { font-size: 13px; color: #94a3b8; font-style: italic; }

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
