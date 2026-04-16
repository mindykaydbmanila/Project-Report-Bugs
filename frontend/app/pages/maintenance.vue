<template>
  <div class="app-layout">

    <!-- ── Header ── -->
    <header class="app-header">
      <div class="app-header-inner">
        <div style="display:flex;align-items:center;gap:16px;">
          <NuxtLink to="/" class="btn btn-ghost btn-sm" style="color:rgba(255,255,255,0.8);gap:6px;padding:6px 10px;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            Bug Tracker
          </NuxtLink>
          <div class="app-logo">
            <div class="app-logo-icon" style="background:linear-gradient(135deg,#34d399,#10b981);">🔧</div>
            <div>
              <div>Maintenance Tracker</div>
              <span class="app-logo-subtitle">Client Maintenance Management</span>
            </div>
          </div>
        </div>
        <div style="display:flex;align-items:center;gap:10px;">
          <!-- Notification Bell -->
          <div class="notif-bell-wrap" ref="maintNotifDropdownRef" @click.stop="toggleMaintNotifDropdown">
            <button class="notif-bell-btn" :class="{ 'notif-bell-btn--active': maintNotifDropdownOpen }">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
              </svg>
              <span v-if="maintNotifUnreadCount > 0" class="notif-badge">{{ maintNotifUnreadCount > 99 ? '99+' : maintNotifUnreadCount }}</span>
            </button>

            <!-- Dropdown -->
            <transition name="notif-drop">
              <div v-if="maintNotifDropdownOpen" class="notif-dropdown" @click.stop>
                <div class="notif-dropdown-header">
                  <span class="notif-dropdown-title">Notifications</span>
                  <button v-if="maintNotifUnreadCount > 0" class="notif-read-all-btn" @click="markAllMaintNotifRead">Mark all read</button>
                </div>
                <div class="notif-list">
                  <div v-if="!maintNotifications.length" class="notif-empty">No notifications yet</div>
                  <div
                    v-for="n in maintNotifications"
                    :key="n.id"
                    class="notif-item"
                    :class="{ 'notif-item--unread': !n.read_at }"
                    @click="openMaintNotif(n)"
                  >
                    <div class="notif-item-icon" :class="maintNotifIconClass(n.type)">{{ maintNotifIcon(n.type) }}</div>
                    <div class="notif-item-body">
                      <div class="notif-item-title">{{ n.title }}</div>
                      <div class="notif-item-msg">{{ n.message }}</div>
                      <div class="notif-item-time">{{ timeAgo(n.created_at) }}</div>
                    </div>
                    <div class="notif-item-right">
                      <div v-if="!n.read_at" class="notif-item-dot"></div>
                      <button class="notif-item-dismiss" title="Remove" @click.stop="dismissMaintNotif(n)">
                        <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </transition>
          </div>

          <!-- Auth -->
          <template v-if="currentUser">
            <div class="auth-user" @click.stop="profileDropdownOpen = !profileDropdownOpen" ref="profileDropdownRef">
              <img v-if="currentUser.avatar" :src="currentUser.avatar" class="auth-avatar" :alt="currentUser.name" />
              <span v-else class="auth-avatar auth-avatar-initials">{{ currentUser.name?.[0]?.toUpperCase() }}</span>
              <span class="auth-name">{{ currentUser.name }}</span>
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="opacity:.6"><polyline points="6 9 12 15 18 9"/></svg>
              <div v-if="profileDropdownOpen" class="profile-dropdown">
                <div class="profile-dropdown-info">
                  <img v-if="currentUser.avatar" :src="currentUser.avatar" class="profile-dropdown-avatar" :alt="currentUser.name" />
                  <span v-else class="profile-dropdown-avatar profile-dropdown-initials">{{ currentUser.name?.[0]?.toUpperCase() }}</span>
                  <div>
                    <div class="profile-dropdown-name">{{ currentUser.name }}</div>
                    <div class="profile-dropdown-email">{{ currentUser.email }}</div>
                  </div>
                </div>
                <div class="profile-dropdown-divider"></div>
                <button class="profile-dropdown-item profile-dropdown-signout" @click.stop="logout">
                  <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
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

    <!-- ── Body ── -->
    <div class="app-body">

      <!-- ── Sidebar ── -->
      <aside class="sidebar">
        <!-- ACTIVE section -->
        <div class="sidebar-section-label">Maintenance Projects</div>
        <nav class="sidebar-nav">
          <button class="sidebar-item" :class="{ active: !selectedProject && !dashboardOpen && !inactiveOpen }" @click="selectProject(null); dashboardOpen = false; inactiveOpen = false">
            <span class="sidebar-item-icon">🏠</span>
            <span class="sidebar-item-name">All Projects</span>
            <span class="sidebar-item-count">{{ activeProjects.length }}</span>
          </button>
        </nav>
        <div v-if="activeProjects.length" class="sidebar-section-label" style="margin-top:10px;">Projects</div>
        <nav v-if="activeProjects.length" class="sidebar-nav">
          <button
            v-for="p in activeProjects" :key="p.id"
            class="sidebar-item" :class="{ active: selectedProject?.id === p.id && !dashboardOpen }"
            @click="selectProject(p); dashboardOpen = false"
          >
            <span class="sidebar-color-dot" :style="{ background: p.color }"></span>
            <span class="sidebar-item-name">{{ p.name }}</span>
            <span class="sidebar-item-count">{{ p.tickets_count ?? 0 }}</span>
          </button>
        </nav>


        <div class="sidebar-divider" style="margin:8px 0 4px;"></div>
        <div class="sidebar-section-label" style="margin-top:4px;">Tools</div>
        <nav class="sidebar-nav">
          <button class="sidebar-item" :class="{ active: dashboardOpen }" @click="openDashboard">
            <span class="sidebar-item-icon">📊</span>
            <span class="sidebar-item-name">Dashboard</span>
          </button>
        </nav>
        <div class="sidebar-footer">
          <button class="btn-new-proj" @click="openProjectModal(null)">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            New Project
          </button>
        </div>
      </aside>

      <!-- ── Main ── -->
      <main class="main-content">

        <!-- ══ Dashboard View ══ -->
        <div v-if="dashboardOpen">
          <div class="view-header" style="margin-bottom:24px;">
            <div>
              <h1 class="view-title">Dashboard</h1>
              <p class="view-subtitle">Overall maintenance metrics across all projects</p>
            </div>
          </div>

          <!-- Stat cards -->
          <div class="mdash-stat-grid">
            <div class="mdash-stat-card">
              <div class="mdash-stat-top">Total tickets</div>
              <div class="mdash-stat-value">{{ dashStats.total }}</div>
              <div class="mdash-stat-sub">across {{ projects.length }} project{{ projects.length !== 1 ? 's' : '' }}</div>
            </div>
            <div class="mdash-stat-card mdash-pending">
              <div class="mdash-stat-top">Pending</div>
              <div class="mdash-stat-value">{{ dashStats.pending }}</div>
              <div class="mdash-stat-sub">awaiting start</div>
            </div>
            <div class="mdash-stat-card mdash-ongoing">
              <div class="mdash-stat-top">Ongoing</div>
              <div class="mdash-stat-value">{{ dashStats.ongoing }}</div>
              <div class="mdash-stat-sub">in progress</div>
            </div>
            <div class="mdash-stat-card mdash-done">
              <div class="mdash-stat-top">Completed</div>
              <div class="mdash-stat-value">{{ dashStats.completed }}</div>
              <div class="mdash-stat-sub">all time</div>
            </div>
            <div class="mdash-stat-card mdash-overdue">
              <div class="mdash-stat-top">Overdue</div>
              <div class="mdash-stat-value">{{ dashStats.overdue }}</div>
              <div class="mdash-stat-sub">needs attention</div>
            </div>
            <div class="mdash-stat-card">
              <div class="mdash-stat-top">Avg ticket age</div>
              <div class="mdash-stat-value">{{ dashStats.avgAge }}<span style="font-size:18px;font-weight:600;">d</span></div>
              <div class="mdash-stat-sub">since received</div>
            </div>
          </div>

          <!-- Middle row -->
          <div class="mdash-mid-row">

            <!-- Tickets by client -->
            <div class="mdash-panel">
              <div class="mdash-panel-header">
                <span class="mdash-panel-title">Tickets by client</span>
                <span class="mdash-panel-meta">{{ dashByClient.length }} clients</span>
              </div>
              <div v-if="dashByClient.length === 0" class="mdash-empty">No tickets yet</div>
              <div v-else class="mdash-client-rows">
                <div v-for="c in dashByClient" :key="c.name" class="mdash-client-row">
                  <span class="mdash-client-name">{{ c.name }}</span>
                  <div class="mdash-client-bar-track">
                    <div class="mdash-client-bar" :style="{ width: (dashStats.total > 0 ? c.count / dashStats.total * 100 : 0) + '%', background: c.color }"></div>
                  </div>
                  <span class="mdash-client-count">{{ c.count }}</span>
                </div>
              </div>
            </div>

            <!-- Status breakdown donut -->
            <div class="mdash-panel mdash-panel-center">
              <div class="mdash-panel-title" style="margin-bottom:12px;">Status breakdown</div>
              <svg viewBox="0 0 120 120" class="mdash-donut" v-if="dashStats.total > 0">
                <!-- background ring -->
                <circle cx="60" cy="60" r="46" fill="none" stroke="#f1f5f9" stroke-width="18"/>
                <!-- Completed arc -->
                <circle cx="60" cy="60" r="46" fill="none" stroke="#22c55e" stroke-width="18"
                  :stroke-dasharray="`${dashStatusArc.completed} ${289 - dashStatusArc.completed}`"
                  :stroke-dashoffset="289 * 0.25"
                  transform="rotate(-90 60 60)"/>
                <!-- Ongoing arc -->
                <circle cx="60" cy="60" r="46" fill="none" stroke="#3b82f6" stroke-width="18"
                  :stroke-dasharray="`${dashStatusArc.ongoing} ${289 - dashStatusArc.ongoing}`"
                  :stroke-dashoffset="289 * 0.25 - dashStatusArc.completed"
                  transform="rotate(-90 60 60)"/>
                <!-- Pending arc -->
                <circle cx="60" cy="60" r="46" fill="none" stroke="#f59e0b" stroke-width="18"
                  :stroke-dasharray="`${dashStatusArc.pending} ${289 - dashStatusArc.pending}`"
                  :stroke-dashoffset="289 * 0.25 - dashStatusArc.completed - dashStatusArc.ongoing"
                  transform="rotate(-90 60 60)"/>
                <!-- Overdue arc -->
                <circle cx="60" cy="60" r="46" fill="none" stroke="#ef4444" stroke-width="18"
                  :stroke-dasharray="`${dashStatusArc.overdue} ${289 - dashStatusArc.overdue}`"
                  :stroke-dashoffset="289 * 0.25 - dashStatusArc.completed - dashStatusArc.ongoing - dashStatusArc.pending"
                  transform="rotate(-90 60 60)"/>
              </svg>
              <div v-else class="mdash-empty">No tickets yet</div>
              <div class="mdash-donut-legend">
                <div class="mdash-dl-row"><span class="mdash-dl-dot" style="background:#22c55e"></span>Completed — {{ dashStats.completed }}</div>
                <div class="mdash-dl-row"><span class="mdash-dl-dot" style="background:#3b82f6"></span>Ongoing — {{ dashStats.ongoing }}</div>
                <div class="mdash-dl-row"><span class="mdash-dl-dot" style="background:#f59e0b"></span>Pending — {{ dashStats.pending }}</div>
                <div class="mdash-dl-row"><span class="mdash-dl-dot" style="background:#ef4444"></span>Overdue — {{ dashStats.overdue }}</div>
              </div>
            </div>

            <!-- Dev workload -->
            <div class="mdash-panel">
              <div class="mdash-panel-header">
                <span class="mdash-panel-title">Dev workload</span>
                <span class="mdash-panel-meta">open tickets</span>
              </div>
              <div v-if="dashDevWorkload.length === 0" class="mdash-empty">No devs assigned</div>
              <div v-else class="mdash-dev-rows">
                <div v-for="d in dashDevWorkload" :key="d.email" class="mdash-dev-row">
                  <div class="mdash-dev-avatar" :style="{ background: d.color }">{{ d.initials }}</div>
                  <span class="mdash-dev-name">{{ d.name }}</span>
                  <span class="mdash-dev-tickets">{{ d.count }} ticket{{ d.count !== 1 ? 's' : '' }}</span>
                  <div class="mdash-dev-bar-track">
                    <div class="mdash-dev-bar" :style="{ width: (dashDevWorkload[0]?.count > 0 ? d.count / dashDevWorkload[0].count * 100 : 0) + '%', background: d.color }"></div>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <!-- Bottom row -->
          <div class="mdash-bot-row">

            <!-- Overdue & at-risk -->
            <div class="mdash-panel">
              <div class="mdash-panel-header">
                <span class="mdash-panel-title">Overdue &amp; at-risk</span>
                <span v-if="dashAtRisk.length" class="mdash-badge-red">{{ dashAtRisk.filter(t => isOverdue(t)).length }} overdue</span>
              </div>
              <div v-if="dashAtRisk.length === 0" class="mdash-empty">No at-risk tickets</div>
              <div v-else class="mdash-risk-rows">
                <div v-for="t in dashAtRisk" :key="t.id" class="mdash-risk-row">
                  <span class="mdash-risk-badge" :style="riskBadgeStyle(t)">{{ riskLabel(t) }}</span>
                  <div class="mdash-risk-info">
                    <div class="mdash-risk-title">{{ t.request }}</div>
                    <div class="mdash-risk-meta">{{ t.client }} · {{ firstDev(t) }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tickets due this week -->
            <div class="mdash-panel">
              <div class="mdash-panel-title" style="margin-bottom:14px;">Tickets due this week</div>
              <div v-if="dashDueThisWeek.length === 0" class="mdash-empty">No tickets due this week</div>
              <div v-else class="mdash-due-rows">
                <div v-for="t in dashDueThisWeek" :key="t.id" class="mdash-due-row">
                  <div class="mdash-due-info">
                    <div class="mdash-due-title">{{ t.request }}</div>
                    <div class="mdash-due-meta">{{ t.client }} · {{ t.target_date ? formatDate(t.target_date) : '—' }}</div>
                  </div>
                  <span class="mdash-status-badge" :style="statusBadgeStyle(t.status)">{{ t.status }}</span>
                </div>
              </div>
            </div>

            <!-- Recent activity -->
            <div class="mdash-panel">
              <div class="mdash-panel-title" style="margin-bottom:14px;">Recent activity</div>
              <div v-if="dashRecentActivity.length === 0" class="mdash-empty">No recent activity</div>
              <div v-else class="mdash-activity-rows">
                <div v-for="a in dashRecentActivity" :key="a.id" class="mdash-activity-row">
                  <span class="mdash-activity-dot" :style="{ background: a.color }"></span>
                  <div>
                    <div class="mdash-activity-text">{{ a.text }}</div>
                    <div class="mdash-activity-time">{{ a.timeLabel }}</div>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <!-- Channel breakdown -->
          <div class="mdash-panel" style="margin-top:16px;">
            <div class="mdash-panel-header" style="margin-bottom:14px;">
              <span class="mdash-panel-title">Tickets by channel (sent thru)</span>
              <span class="mdash-panel-meta">how requests arrive</span>
            </div>
            <div v-if="dashByChannel.length === 0" class="mdash-empty">No data</div>
            <div v-else class="mdash-channel-grid">
              <div v-for="c in dashByChannel" :key="c.name" class="mdash-channel-row">
                <span class="mdash-channel-name">{{ c.name }}</span>
                <div class="mdash-channel-bar-track">
                  <div class="mdash-channel-bar" :style="{ width: (dashStats.total > 0 ? c.count / dashStats.total * 100 : 0) + '%', background: c.color }"></div>
                </div>
                <span class="mdash-channel-count">{{ c.count }}</span>
              </div>
            </div>
          </div>

        </div>

        <!-- ══ Inactive Projects View ══ -->
        <div v-else-if="inactiveOpen">
          <div class="view-header">
            <div>
              <h1 class="view-title">Inactive Projects</h1>
              <p class="view-subtitle">{{ filteredInactiveProjects.length }} project{{ filteredInactiveProjects.length !== 1 ? 's' : '' }}</p>
            </div>
          </div>

          <!-- Search bar -->
          <div class="filters-bar" style="margin-bottom:20px;">
            <div class="search-input-wrap">
              <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
              <input v-model="projectSearch" class="form-control" placeholder="Search inactive projects..." />
            </div>
            <button v-if="projectSearch" class="btn btn-ghost btn-sm" @click="projectSearch = ''">
              <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
              Clear
            </button>
          </div>

          <div v-if="filteredInactiveProjects.length > 0">
            <div style="font-size:11px;font-weight:700;letter-spacing:.8px;color:var(--gray-400);text-transform:uppercase;margin-bottom:10px;">Inactive</div>
            <div class="projects-grid">
              <div v-for="p in filteredInactiveProjects" :key="p.id" class="project-card" style="opacity:.8;" @click="selectProject(p)">
                <div class="project-card-stripe" :style="{ background: '#d1d5db' }"></div>
                <div class="project-card-body">
                  <div class="project-card-icon" style="background:#f3f4f6;color:#9ca3af;">🔧</div>
                  <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;">
                    <h3 class="project-card-name" style="margin:0;color:var(--gray-500);">{{ p.name }}</h3>
                    <span style="font-size:10px;font-weight:600;padding:2px 8px;border-radius:10px;letter-spacing:.4px;background:#fee2e2;color:#dc2626;">INACTIVE</span>
                  </div>
                  <p v-if="p.description" class="project-card-desc">{{ p.description }}</p>
                  <div style="display:flex;align-items:center;gap:6px;margin:4px 0 0;font-size:11px;color:var(--gray-400);">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <span v-if="p.contract_start || p.contract_end">{{ p.contract_start ? p.contract_start.slice(0, 10) : '?' }} → {{ p.contract_end ? p.contract_end.slice(0, 10) : '?' }}</span>
                    <span v-else style="font-style:italic;">No contract set</span>
                  </div>
                  <div class="project-card-stats">
                    <span class="pstat pstat-total">{{ p.tickets_count ?? 0 }} tickets</span>
                    <span v-if="p.pending_count" class="pstat pstat-pending">{{ p.pending_count }} pending</span>
                    <span v-if="p.in_progress_count" class="pstat pstat-ongoing">{{ p.in_progress_count }} in progress</span>
                    <span v-if="p.completed_count" class="pstat pstat-done">{{ p.completed_count }} done</span>
                  </div>
                </div>
                <div class="project-card-actions" @click.stop>
                  <div class="proj-menu-wrap">
                    <button class="btn btn-icon proj-menu-btn" @click="openProjectMenuId = openProjectMenuId === p.id ? null : p.id" title="Project actions">
                      <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                    </button>
                    <div v-if="openProjectMenuId === p.id" class="proj-menu-dropdown">
                      <button class="proj-menu-item" @click="openProjectModal(p); openProjectMenuId = null">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Edit
                      </button>
                      <button class="proj-menu-item proj-menu-item-delete" @click="confirmDeleteProject(p); openProjectMenuId = null">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                        Delete
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="empty-state" style="margin-top:40px;">
            <div class="empty-state-icon">🔍</div>
            <div class="empty-state-title">No projects match your search</div>
            <div class="empty-state-text">Try a different name or clear the search</div>
            <button class="btn btn-ghost btn-sm" style="margin-top:16px;" @click="projectSearch = ''">Clear search</button>
          </div>
        </div>

        <!-- ══ All Projects View ══ -->
        <div v-else-if="!selectedProject">

          <!-- Page hero -->
          <div class="allproj-hero">
            <div class="allproj-hero-left">
              <div class="allproj-hero-icon">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
              </div>
              <div>
                <h1 class="allproj-hero-title">Maintenance Projects</h1>
                <p class="allproj-hero-sub">{{ activeProjects.length }} active · {{ inactiveProjects.length }} inactive</p>
              </div>
            </div>
            <button class="btn-allproj-new" @click="openProjectModal(null)">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
              New Project
            </button>
          </div>

          <!-- Toolbar -->
          <div class="allproj-toolbar">
            <div class="search-input-wrap" style="flex:1;max-width:340px;">
              <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
              <input v-model="projectSearch" class="form-control" placeholder="Search projects..." />
            </div>
            <div class="allproj-filter-pills">
              <button class="allproj-pill" :class="{ active: projectStatusFilter === '' }" @click="projectStatusFilter = ''">All</button>
              <button class="allproj-pill" :class="{ active: projectStatusFilter === 'active' }" @click="projectStatusFilter = 'active'">Active</button>
              <button class="allproj-pill" :class="{ active: projectStatusFilter === 'inactive' }" @click="projectStatusFilter = 'inactive'">Inactive</button>
            </div>
            <button v-if="projectSearch" class="btn btn-ghost btn-sm" @click="projectSearch = ''">
              <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
              Clear
            </button>
          </div>

          <div v-if="projects.length > 0">
            <!-- Active projects -->
            <div v-if="filteredActiveProjects.length && projectStatusFilter !== 'inactive'" style="margin-bottom:28px;">
              <div class="allproj-section-label">
                <span class="allproj-section-dot" style="background:#10b981;"></span>
                Active
                <span class="allproj-section-count">{{ filteredActiveProjects.length }}</span>
              </div>
              <div class="projects-grid">
                <div v-for="p in filteredActiveProjects" :key="p.id" class="project-card2" :style="{ '--pc-color': p.color || '#10b981' }" @click="selectProject(p)">
                  <div class="pc2-accent"></div>
                  <div class="pc2-body">
                    <div class="pc2-top-row">
                      <div class="pc2-icon" :style="{ background: (p.color||'#10b981')+'18', color: p.color||'#10b981' }">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                      </div>
                      <div class="pc2-name-block">
                        <h3 class="pc2-name">{{ p.name }}</h3>
                        <div style="display:flex;align-items:center;gap:4px;">
                          <div class="pc2-date">
                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <span v-if="p.contract_start || p.contract_end">{{ p.contract_start ? p.contract_start.slice(0,10) : '?' }} → {{ p.contract_end ? p.contract_end.slice(0,10) : '?' }}</span>
                            <span v-else style="font-style:italic;">No contract set</span>
                          </div>
                          <div class="pc2-actions" @click.stop>
                            <div class="proj-menu-wrap">
                              <button class="btn btn-icon proj-menu-btn" @click="openProjectMenuId = openProjectMenuId === p.id ? null : p.id" title="Project actions">
                                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                              </button>
                              <div v-if="openProjectMenuId === p.id" class="proj-menu-dropdown">
                                <button class="proj-menu-item" @click="openProjectModal(p); openProjectMenuId = null">
                                  <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                  Edit
                                </button>
                                <button class="proj-menu-item proj-menu-item-delete" @click="confirmDeleteProject(p); openProjectMenuId = null">
                                  <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                  Delete
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <span class="pc2-badge pc2-badge-active">ACTIVE</span>
                    </div>
                    <p v-if="p.description" class="pc2-desc">{{ p.description }}</p>
                    <div class="pc2-stats-row">
                      <div class="pc2-stat">
                        <div class="pc2-stat-val">{{ p.tickets_count ?? 0 }}</div>
                        <div class="pc2-stat-label">Total</div>
                      </div>
                      <div class="pc2-stat">
                        <div class="pc2-stat-val" style="color:#f59e0b;">{{ p.pending_count ?? 0 }}</div>
                        <div class="pc2-stat-label">Pending</div>
                      </div>
                      <div class="pc2-stat">
                        <div class="pc2-stat-val" style="color:#3b82f6;">{{ p.in_progress_count ?? 0 }}</div>
                        <div class="pc2-stat-label">In Progress</div>
                      </div>
                      <div class="pc2-stat">
                        <div class="pc2-stat-val" style="color:#10b981;">{{ p.completed_count ?? 0 }}</div>
                        <div class="pc2-stat-label">Done</div>
                      </div>
                    </div>
                    <div class="pc2-progress-row">
                      <div class="pc2-progress-track">
                        <div class="pc2-progress-bar" :style="{ width: (p.tickets_count > 0 ? Math.round((p.completed_count??0)/p.tickets_count*100) : 0) + '%', background: p.color||'#10b981' }"></div>
                      </div>
                      <span class="pc2-progress-label">{{ p.tickets_count > 0 ? Math.round((p.completed_count??0)/p.tickets_count*100) : 0 }}% done</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- Inactive projects -->
            <div v-if="filteredInactiveProjects.length && projectStatusFilter !== 'active'">
              <div class="allproj-section-label">
                <span class="allproj-section-dot" style="background:#9ca3af;"></span>
                Inactive
                <span class="allproj-section-count">{{ filteredInactiveProjects.length }}</span>
              </div>
              <div class="projects-grid">
                <div v-for="p in filteredInactiveProjects" :key="p.id" class="project-card2 pc2-inactive" :style="{ '--pc-color': '#9ca3af' }" @click="selectProject(p)">
                  <div class="pc2-accent"></div>
                  <div class="pc2-body">
                    <div class="pc2-top-row">
                      <div class="pc2-icon" style="background:#f3f4f6;color:#9ca3af;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                      </div>
                      <div class="pc2-name-block">
                        <h3 class="pc2-name" style="color:var(--gray-500);">{{ p.name }}</h3>
                        <div style="display:flex;align-items:center;gap:4px;">
                          <div class="pc2-date">
                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <span v-if="p.contract_start || p.contract_end">{{ p.contract_start ? p.contract_start.slice(0,10) : '?' }} → {{ p.contract_end ? p.contract_end.slice(0,10) : '?' }}</span>
                            <span v-else style="font-style:italic;">No contract set</span>
                          </div>
                          <div class="pc2-actions" @click.stop>
                            <div class="proj-menu-wrap">
                              <button class="btn btn-icon proj-menu-btn" @click="openProjectMenuId = openProjectMenuId === p.id ? null : p.id" title="Project actions">
                                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                              </button>
                              <div v-if="openProjectMenuId === p.id" class="proj-menu-dropdown">
                                <button class="proj-menu-item" @click="openProjectModal(p); openProjectMenuId = null">
                                  <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                  Edit
                                </button>
                                <button class="proj-menu-item proj-menu-item-delete" @click="confirmDeleteProject(p); openProjectMenuId = null">
                                  <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                  Delete
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <span class="pc2-badge pc2-badge-inactive">INACTIVE</span>
                    </div>
                    <p v-if="p.description" class="pc2-desc">{{ p.description }}</p>
                    <div class="pc2-stats-row">
                      <div class="pc2-stat">
                        <div class="pc2-stat-val">{{ p.tickets_count ?? 0 }}</div>
                        <div class="pc2-stat-label">Total</div>
                      </div>
                      <div class="pc2-stat">
                        <div class="pc2-stat-val" style="color:#f59e0b;">{{ p.pending_count ?? 0 }}</div>
                        <div class="pc2-stat-label">Pending</div>
                      </div>
                      <div class="pc2-stat">
                        <div class="pc2-stat-val" style="color:#3b82f6;">{{ p.in_progress_count ?? 0 }}</div>
                        <div class="pc2-stat-label">In Progress</div>
                      </div>
                      <div class="pc2-stat">
                        <div class="pc2-stat-val" style="color:#10b981;">{{ p.completed_count ?? 0 }}</div>
                        <div class="pc2-stat-label">Done</div>
                      </div>
                    </div>
                    <div class="pc2-progress-row">
                      <div class="pc2-progress-track">
                        <div class="pc2-progress-bar" :style="{ width: (p.tickets_count > 0 ? Math.round((p.completed_count??0)/p.tickets_count*100) : 0) + '%', background: '#9ca3af' }"></div>
                      </div>
                      <span class="pc2-progress-label">{{ p.tickets_count > 0 ? Math.round((p.completed_count??0)/p.tickets_count*100) : 0 }}% done</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-else-if="projects.length > 0 && filteredAllProjects.length === 0" class="empty-state" style="margin-top:60px;">
            <div class="empty-state-icon">🔍</div>
            <div class="empty-state-title">No projects match your search</div>
            <div class="empty-state-text">Try a different name or clear the filters</div>
            <button class="btn btn-ghost btn-sm" style="margin-top:16px;" @click="projectSearch = ''; projectStatusFilter = ''">Clear filters</button>
          </div>
          <div v-else class="empty-state" style="margin-top:60px;">
            <div class="empty-state-icon">🔧</div>
            <div class="empty-state-title">No maintenance projects yet</div>
            <div class="empty-state-text">Create your first project to start tracking maintenance tickets</div>
            <button class="btn btn-primary" style="margin-top:20px;background:linear-gradient(135deg,#059669,#047857);" @click="openProjectModal(null)">+ Create Project</button>
          </div>
        </div>

        <!-- ══ Project Detail / Ticket Table ══ -->
        <div v-else-if="selectedProject">

          <!-- Project Banner -->
          <div class="proj-banner" :style="{ '--proj-color': selectedProject.color || '#10b981' }">
            <div class="proj-banner-accent"></div>
            <div class="proj-banner-body">
              <div class="proj-banner-left">
                <div class="proj-banner-icon" :style="{ background: (selectedProject.color || '#10b981') + '22', color: selectedProject.color || '#10b981' }">
                  <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                </div>
                <div>
                  <div class="proj-banner-title-row">
                    <h1 class="proj-banner-name">{{ selectedProject.name }}</h1>
                    <span class="proj-banner-status-badge" :class="selectedProject.is_active !== false ? 'badge-active' : 'badge-inactive'">
                      {{ selectedProject.is_active !== false ? 'Active' : 'Inactive' }}
                    </span>
                  </div>
                  <div class="proj-banner-meta">
                    <span class="proj-banner-meta-item">
                      <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                      {{ tickets.length }} ticket{{ tickets.length !== 1 ? 's' : '' }}
                    </span>
                    <span v-if="overdueCount" class="proj-banner-meta-item proj-banner-meta-overdue">
                      <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                      {{ overdueCount }} overdue
                    </span>
                    <span v-if="selectedProject.contract_start || selectedProject.contract_end" class="proj-banner-meta-item">
                      <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                      {{ selectedProject.contract_start ? selectedProject.contract_start.slice(0,10) : '?' }} → {{ selectedProject.contract_end ? selectedProject.contract_end.slice(0,10) : '?' }}
                    </span>
                  </div>
                </div>
              </div>
              <div class="proj-banner-right">
                <span v-if="selectedProject.is_active === false" class="proj-banner-inactive-notice">Project Inactive — tickets cannot be added</span>
                <button v-else class="btn-new-ticket" @click="openNewTicketModal">
                  <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                  New Ticket
                </button>
              </div>
            </div>
          </div>

          <!-- Stat cards -->
          <div class="proj-stat-grid">
            <!-- Total -->
            <div class="proj-stat-card proj-stat-total">
              <div class="proj-stat-card-top">
                <span class="proj-stat-label">Total</span>
                <div class="proj-stat-icon proj-stat-icon-total">
                  <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                </div>
              </div>
              <div class="proj-stat-value">{{ tickets.length }}</div>
              <div class="proj-stat-pct-row">
                <div class="proj-stat-pct-bar-track">
                  <div class="proj-stat-pct-bar" :style="{ width: completionPct + '%' }"></div>
                </div>
                <span class="proj-stat-pct-label">{{ completionPct }}% done</span>
              </div>
            </div>
            <!-- Pending -->
            <div class="proj-stat-card proj-stat-pending">
              <div class="proj-stat-card-top">
                <span class="proj-stat-label">Pending</span>
                <div class="proj-stat-icon proj-stat-icon-pending">
                  <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
              </div>
              <div class="proj-stat-value">{{ tickets.filter(t => t.status === 'Pending').length }}</div>
              <div class="proj-stat-sub">awaiting start</div>
            </div>
            <!-- In Progress -->
            <div class="proj-stat-card proj-stat-ongoing">
              <div class="proj-stat-card-top">
                <span class="proj-stat-label">In Progress</span>
                <div class="proj-stat-icon proj-stat-icon-ongoing">
                  <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                </div>
              </div>
              <div class="proj-stat-value">{{ tickets.filter(t => t.status === 'In Progress').length }}</div>
              <div class="proj-stat-sub">active work</div>
            </div>
            <!-- On Hold -->
            <div class="proj-stat-card proj-stat-hold">
              <div class="proj-stat-card-top">
                <span class="proj-stat-label">On Hold</span>
                <div class="proj-stat-icon proj-stat-icon-hold">
                  <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
                </div>
              </div>
              <div class="proj-stat-value">{{ tickets.filter(t => t.status === 'On Hold').length }}</div>
              <div class="proj-stat-sub">paused</div>
            </div>
            <!-- Completed -->
            <div class="proj-stat-card proj-stat-done">
              <div class="proj-stat-card-top">
                <span class="proj-stat-label">Completed</span>
                <div class="proj-stat-icon proj-stat-icon-done">
                  <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
              </div>
              <div class="proj-stat-value">{{ tickets.filter(t => t.status === 'Completed').length }}</div>
              <div class="proj-stat-sub">finished</div>
            </div>
            <!-- Overdue -->
            <div class="proj-stat-card proj-stat-overdue">
              <div class="proj-stat-card-top">
                <span class="proj-stat-label">Overdue</span>
                <div class="proj-stat-icon proj-stat-icon-overdue">
                  <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </div>
              </div>
              <div class="proj-stat-value">{{ overdueCount }}</div>
              <div class="proj-stat-sub">needs attention</div>
            </div>
          </div>

          <!-- Filters -->
          <div class="proj-filters-bar">
            <div class="search-input-wrap">
              <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
              <input v-model="ticketSearch" class="form-control" placeholder="Search tickets..." />
            </div>
            <select v-model="ticketStatusFilter" class="form-control" style="max-width:160px;">
              <option value="">All Statuses</option>
              <option>Pending</option>
              <option>In Progress</option>
              <option>On Hold</option>
              <option>Completed</option>
              <option>Cancelled</option>
            </select>
            <button v-if="ticketSearch || ticketStatusFilter" class="btn btn-ghost btn-sm" @click="ticketSearch = ''; ticketStatusFilter = ''">
              <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
              Clear
            </button>
          </div>

          <!-- Ticket Table -->
          <div class="table-wrap">
            <table class="maint-pipeline-table">
              <thead>
                <tr>
                  <th style="width:90px;">Ticket #</th>
                  <th style="width:120px;">Client</th>
                  <th>Request</th>
                  <th style="width:90px;">Sent Thru</th>
                  <th style="width:100px;">Target</th>
                  <th style="width:70px;">Age</th>
                  <th style="width:130px;">Dev(s)</th>
                  <th style="width:130px;">QA</th>
                  <th style="width:120px;">Dev Status</th>
                  <th style="width:110px;">Status</th>
                  <th style="width:150px;">Comment</th>
                  <th style="width:130px;">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="filteredTickets.length === 0">
                  <td colspan="12" style="text-align:center;padding:40px;color:var(--gray-400);">
                    {{ tickets.length ? 'No tickets match your filters.' : 'No tickets yet. Create one to get started.' }}
                  </td>
                </tr>
                <tr v-for="t in filteredTickets" :key="t.id"
                    class="clickable-row"
                    :class="{ 'tt-overdue-row': isOverdue(t), 'maint-row-completed': t.status === 'Completed' }"
                    @click="navigateTo('/maintenance-ticket/' + t.id)">
                  <!-- Ticket # -->
                  <td>
                    <div style="display:flex;align-items:center;gap:5px;">
                      <span v-if="isOverdue(t)" class="tt-overdue-dot" title="Overdue"></span>
                      <span class="bug-seq" style="font-size:11px;background:#ecfdf5;color:#059669;">{{ t.ticket_number }}</span>
                    </div>
                  </td>
                  <!-- Client -->
                  <td>
                    <span style="font-weight:600;color:var(--gray-800);font-size:13px;">{{ t.client }}</span>
                  </td>
                  <!-- Request -->
                  <td>
                    <span class="maint-truncate" :title="t.request">{{ t.request }}</span>
                  </td>
                  <!-- Sent Thru -->
                  <td>
                    <span style="display:inline-flex;padding:2px 8px;border-radius:99px;font-size:11px;font-weight:600;background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;">{{ t.sent_thru }}</span>
                  </td>
                  <!-- Target -->
                  <td>
                    <span style="font-size:12px;" :style="{ color: isOverdue(t) ? '#ef4444' : 'var(--gray-600)', fontWeight: isOverdue(t) ? '600' : '400' }">
                      {{ formatDate(t.target_date) || '—' }}
                    </span>
                  </td>
                  <!-- Age -->
                  <td>
                    <span :class="['age-badge', ageBadgeClass(t)]">{{ ticketAgeDays(t) }}d</span>
                  </td>
                  <!-- Dev(s) -->
                  <td>
                    <div style="display:flex;gap:3px;flex-wrap:wrap;">
                      <div v-for="email in (t.assigned_devs || [])" :key="email" :title="email"
                        style="display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:50%;background:#dbeafe;color:#1d4ed8;font-size:10px;font-weight:700;flex-shrink:0;">
                        {{ emailInitials(email) }}
                      </div>
                      <span v-if="!t.assigned_devs?.length" style="font-size:11px;color:var(--gray-400);">—</span>
                    </div>
                  </td>
                  <!-- QA -->
                  <td>
                    <div style="display:flex;gap:3px;flex-wrap:wrap;">
                      <div v-for="email in (t.assigned_qa || [])" :key="email" :title="email"
                        style="display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:50%;background:#f3e8ff;color:#7c3aed;font-size:10px;font-weight:700;flex-shrink:0;">
                        {{ emailInitials(email) }}
                      </div>
                      <span v-if="!t.assigned_qa?.length" style="font-size:11px;color:var(--gray-400);">—</span>
                    </div>
                  </td>
                  <!-- Dev Status -->
                  <td @click.stop>
                    <select
                      :value="t.dev_status || 'Not Started'"
                      :class="['tt-dev-status-badge', 'inline-status-select', 'tt-dev-status--' + (t.dev_status || 'Not Started').toLowerCase().replace(/\s+/g, '-')]"
                      @change="updateTicketDevStatus(t, $event.target.value)"
                    >
                      <option value="Not Started">Not Started</option>
                      <option value="In Progress">In Progress</option>
                      <option value="Ready for QA">Ready for QA</option>
                      <option value="Blocked">Blocked</option>
                    </select>
                  </td>
                  <!-- Status -->
                  <td @click.stop>
                    <select
                      :value="t.status"
                      :class="['badge', 'inline-status-select', maintStatusBadgeClass(t.status)]"
                      @change="updateTicketStatus(t, $event.target.value)"
                    >
                      <option value="Pending">Pending</option>
                      <option value="In Progress">In Progress</option>
                      <option value="On Hold">On Hold</option>
                      <option value="Completed">Completed</option>
                      <option value="Cancelled">Cancelled</option>
                    </select>
                  </td>
                  <!-- Comment -->
                  <td class="dev-comment-cell" @click.stop="navigateTo('/maintenance-ticket/' + t.id)">
                    <div class="dev-thread-trigger">
                      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                      <span v-if="t.comments && t.comments.length" class="dev-thread-count">{{ t.comments.length }}</span>
                      <span v-if="t.comments && t.comments.length" class="dev-thread-preview">{{ t.comments[t.comments.length - 1].message }}</span>
                      <span v-else class="dev-comment-empty">Add comment…</span>
                    </div>
                  </td>
                  <!-- Actions -->
                  <td @click.stop>
                    <div class="maint-actions">
                      <!-- View -->
                      <button class="btn btn-icon action-btn-view" title="View ticket" @click.stop="navigateTo('/maintenance-ticket/' + t.id)">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                      </button>
                      <!-- Edit -->
                      <button class="btn btn-icon action-btn-edit" title="Edit ticket" @click="openTicketModal(t, 'edit')">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                      </button>
                      <!-- Notify -->
                      <button
                        class="btn btn-icon"
                        :class="t.notification_sent_at ? 'action-btn-notify-sent' : 'action-btn-notify'"
                        :title="t.notification_sent_at ? 'Notification sent — click to re-send' : 'Send notification'"
                        :disabled="notifyingId === t.id"
                        @click="sendNotification(t)"
                      >
                        <svg v-if="notifyingId === t.id" class="spin-icon" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                        <svg v-else width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12.3 19.79 19.79 0 0 1 1.6 3.68 2 2 0 0 1 3.59 1.5h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.1a16 16 0 0 0 6 6l1.27-.88a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                      </button>
                      <!-- Delete -->
                      <button class="btn btn-icon action-btn-delete" title="Delete ticket" @click="confirmDeleteTicket(t)">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </main>
    </div>

    <!-- ══ New Ticket Modal ══ -->
    <Transition name="fade">
      <div v-if="showNewTicketModal" class="modal-overlay" @click.self="showNewTicketModal = false">
        <div class="modal" style="max-width:640px;">
          <div class="modal-header">
            <h3 class="modal-title">New Maintenance Ticket</h3>
            <button class="btn btn-ghost btn-icon" @click="showNewTicketModal = false">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
          </div>
          <form @submit.prevent="submitNewTicket">
            <div class="modal-body" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
              <!-- Client -->
              <div class="form-group" style="grid-column:1/2;">
                <label class="form-label">Client <span style="color:#ef4444;">*</span></label>
                <input v-model="newTicketForm.client" class="form-control" required placeholder="Client name" />
              </div>
              <!-- Sent Thru -->
              <div class="form-group" style="grid-column:2/3;position:relative;">
                <label class="form-label">Sent Through</label>
                <input v-model="newTicketForm.sent_thru" class="form-control" placeholder="e.g. Email, Viber…" autocomplete="off" @focus="showSentThruNew = true" @blur="hideSentThruNew" />
                <div v-if="showSentThruNew" class="sent-thru-dropdown">
                  <div v-for="opt in sentThruOptions" :key="opt" class="sent-thru-option" @mousedown.prevent="newTicketForm.sent_thru = opt; showSentThruNew = false">{{ opt }}</div>
                </div>
              </div>
              <!-- Request -->
              <div class="form-group" style="grid-column:1/3;">
                <label class="form-label">Request <span style="color:#ef4444;">*</span></label>
                <textarea v-model="newTicketForm.request" class="form-control" rows="3" required placeholder="Describe the maintenance request..."></textarea>
              </div>
              <!-- Date Received -->
              <div class="form-group">
                <label class="form-label">Date Received</label>
                <input v-model="newTicketForm.date_received" type="date" class="form-control" />
              </div>
              <!-- Target Date -->
              <div class="form-group">
                <label class="form-label">Target Date</label>
                <input v-model="newTicketForm.target_date" type="date" class="form-control" />
              </div>
              <!-- Status -->
              <div class="form-group">
                <label class="form-label">Status</label>
                <select v-model="newTicketForm.status" class="form-control">
                  <option>Pending</option>
                  <option>In Progress</option>
                  <option>On Hold</option>
                  <option>Completed</option>
                  <option>Cancelled</option>
                </select>
              </div>
              <!-- Completion Date -->
              <div class="form-group">
                <label class="form-label">Completion Date</label>
                <input v-model="newTicketForm.completion_date" type="date" class="form-control" />
              </div>
              <!-- Assigned Devs -->
              <div class="form-group" style="grid-column:1/3;">
                <label class="form-label">Assigned Dev(s) <span style="font-size:11px;color:var(--gray-400);font-weight:400;">Press Enter or comma to add email</span></label>
                <div class="email-tag-input" @click="$refs.devInput.focus()">
                  <span v-for="(email, i) in newTicketForm.assigned_devs" :key="i" class="maint-email-tag-pill dev-pill">
                    {{ email }}
                    <button type="button" @click.stop="newTicketForm.assigned_devs.splice(i, 1)">×</button>
                  </span>
                  <input ref="devInput" v-model="devInputVal" class="email-tag-inner-input" placeholder="dev@example.com" @keydown.enter.prevent="addEmail('dev')" @keydown="checkComma($event, 'dev')" @blur="addEmail('dev')" />
                </div>
              </div>
              <!-- Assigned QA -->
              <div class="form-group" style="grid-column:1/3;">
                <label class="form-label">Assigned QA <span style="font-size:11px;color:var(--gray-400);font-weight:400;">Press Enter or comma to add email</span></label>
                <div class="email-tag-input" @click="$refs.qaInput.focus()">
                  <span v-for="(email, i) in newTicketForm.assigned_qa" :key="i" class="maint-email-tag-pill qa-pill">
                    {{ email }}
                    <button type="button" @click.stop="newTicketForm.assigned_qa.splice(i, 1)">×</button>
                  </span>
                  <input ref="qaInput" v-model="qaInputVal" class="email-tag-inner-input" placeholder="qa@example.com" @keydown.enter.prevent="addEmail('qa')" @keydown="checkComma($event, 'qa')" @blur="addEmail('qa')" />
                </div>
              </div>
              <!-- Notes -->
              <div class="form-group" style="grid-column:1/3;">
                <label class="form-label">Notes</label>
                <textarea v-model="newTicketForm.notes" class="form-control" rows="2" placeholder="Additional notes..."></textarea>
              </div>
              <!-- Attachments -->
              <div class="form-group" style="grid-column:1/3;">
                <label class="form-label">Attachments <span style="font-size:11px;color:var(--gray-400);font-weight:400;">Images or PDF, max 20 MB each</span></label>
                <div class="attach-drop-zone" :class="{ 'attach-drop-zone--active': newDragOver }" @dragover.prevent="newDragOver = true" @dragleave="newDragOver = false" @drop.prevent="onDropNew">
                  <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--gray-300);"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                  <span style="font-size:13px;color:var(--gray-400);">Drag & drop files here or <label style="color:var(--primary);cursor:pointer;text-decoration:underline;">browse<input type="file" accept="image/*,.pdf" multiple style="display:none;" @change="onPickNew" /></label></span>
                </div>
                <div v-if="newAttachFiles.length" class="attach-preview-grid">
                  <div v-for="(f, i) in newAttachFiles" :key="i" class="attach-preview-item">
                    <img v-if="f.type.startsWith('image/')" :src="f.preview" class="attach-thumb" />
                    <div v-else class="attach-pdf-icon">
                      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M9 15h6M9 11h6"/></svg>
                    </div>
                    <div class="attach-file-name">{{ f.name }}</div>
                    <button type="button" class="attach-remove-btn" @click="removeNewFile(i)">×</button>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <div style="font-size:12px;color:var(--gray-400);">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:4px;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12.3 19.79 19.79 0 0 1 1.6 3.68 2 2 0 0 1 3.59 1.5h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.1a16 16 0 0 0 6 6l1.27-.88a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                Notifications auto-send if emails are assigned
              </div>
              <div style="display:flex;gap:8px;">
                <button type="button" class="btn btn-secondary" @click="showNewTicketModal = false">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg,#059669,#047857);" :disabled="submitting">
                  {{ submitting ? 'Saving…' : 'Create Ticket' }}
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <!-- ══ View/Edit Ticket Modal ══ -->
    <Transition name="fade">
      <div v-if="showTicketModal" class="modal-overlay" @click.self="showTicketModal = false">
        <div class="modal" style="max-width:680px;">
          <div class="modal-header">
            <div style="display:flex;align-items:center;gap:10px;">
              <span class="bug-seq" style="font-size:11px;background:#ecfdf5;color:#059669;">{{ activeTicket?.ticket_number }}</span>
              <h3 class="modal-title">{{ ticketModalMode === 'view' ? 'Ticket Details' : 'Edit Ticket' }}</h3>
            </div>
            <div style="display:flex;align-items:center;gap:6px;">
              <!-- Mode toggle -->
              <button v-if="ticketModalMode === 'view'" class="btn btn-ghost btn-sm" style="gap:5px;" @click="ticketModalMode = 'edit'; initEditForm()">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit
              </button>
              <button v-if="ticketModalMode === 'edit'" class="btn btn-ghost btn-sm" style="gap:5px;" @click="ticketModalMode = 'view'">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                View
              </button>
              <button class="btn btn-ghost btn-icon" @click="showTicketModal = false">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
              </button>
            </div>
          </div>

          <!-- VIEW MODE -->
          <div v-if="ticketModalMode === 'view' && activeTicket" class="modal-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
              <div>
                <div class="maint-view-field">
                  <div class="maint-view-label">Client</div>
                  <div class="maint-view-value">{{ activeTicket.client }}</div>
                </div>
                <div class="maint-view-field">
                  <div class="maint-view-label">Sent Through</div>
                  <div class="maint-view-value">
                    <span :class="['badge', sentThruBadgeClass(activeTicket.sent_thru)]">{{ activeTicket.sent_thru }}</span>
                  </div>
                </div>
                <div class="maint-view-field">
                  <div class="maint-view-label">Status</div>
                  <div class="maint-view-value">
                    <select
                      :value="activeTicket.status"
                      :class="['badge', 'inline-status-select', maintStatusBadgeClass(activeTicket.status)]"
                      @change="updateTicketStatus(activeTicket, $event.target.value)"
                    >
                      <option value="Pending">Pending</option>
                      <option value="In Progress">In Progress</option>
                      <option value="On Hold">On Hold</option>
                      <option value="Completed">Completed</option>
                      <option value="Cancelled">Cancelled</option>
                    </select>
                  </div>
                </div>
                <div class="maint-view-field">
                  <div class="maint-view-label">Dev Status</div>
                  <div class="maint-view-value">
                    <select
                      :value="activeTicket.dev_status || 'Not Started'"
                      :class="['tt-dev-status-badge', 'inline-status-select', 'tt-dev-status--' + (activeTicket.dev_status || 'Not Started').toLowerCase().replace(/\s+/g, '-')]"
                      @change="updateTicketDevStatus(activeTicket, $event.target.value)"
                    >
                      <option value="Not Started">Not Started</option>
                      <option value="In Progress">In Progress</option>
                      <option value="Ready for QA">Ready for QA</option>
                      <option value="Blocked">Blocked</option>
                    </select>
                  </div>
                </div>
                <div class="maint-view-field">
                  <div class="maint-view-label">Ticket Age</div>
                  <div class="maint-view-value" style="display:flex;align-items:center;gap:8px;">
                    <span class="maint-age-badge" :style="ageStyle(activeTicket)">{{ ticketAgeDays(activeTicket) }} days</span>
                    <span v-if="isOverdue(activeTicket)" class="maint-overdue-flag">Overdue</span>
                  </div>
                </div>
              </div>
              <div>
                <div class="maint-view-field">
                  <div class="maint-view-label">Date Received</div>
                  <div class="maint-view-value">{{ formatDate(activeTicket.date_received) || '—' }}</div>
                </div>
                <div class="maint-view-field">
                  <div class="maint-view-label">Target Date</div>
                  <div class="maint-view-value" :style="{ color: isOverdue(activeTicket) ? '#ef4444' : 'inherit', fontWeight: isOverdue(activeTicket) ? '600' : '400' }">
                    {{ formatDate(activeTicket.target_date) || '—' }}
                  </div>
                </div>
                <div class="maint-view-field">
                  <div class="maint-view-label">Completion Date</div>
                  <div class="maint-view-value">{{ formatDate(activeTicket.completion_date) || '—' }}</div>
                </div>
                <div class="maint-view-field">
                  <div class="maint-view-label">Notification</div>
                  <div class="maint-view-value" style="font-size:12px;">
                    <span v-if="activeTicket.notification_sent_at" style="color:#059669;">
                      ✓ Sent {{ formatDate(activeTicket.notification_sent_at) }}
                    </span>
                    <span v-else style="color:var(--gray-400);">Not sent</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="maint-view-field" style="margin-top:4px;">
              <div class="maint-view-label">Request</div>
              <div class="maint-view-value" style="white-space:pre-wrap;line-height:1.7;">{{ activeTicket.request }}</div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:4px;">
              <div class="maint-view-field">
                <div class="maint-view-label">Assigned Dev(s)</div>
                <div class="maint-view-value">
                  <div v-if="activeTicket.assigned_devs?.length" class="maint-email-pills-list">
                    <span v-for="e in activeTicket.assigned_devs" :key="e" class="maint-email-tag-pill dev-pill" style="font-size:11px;">{{ e }}</span>
                  </div>
                  <span v-else style="color:var(--gray-400);">—</span>
                </div>
              </div>
              <div class="maint-view-field">
                <div class="maint-view-label">Assigned QA</div>
                <div class="maint-view-value">
                  <div v-if="activeTicket.assigned_qa?.length" class="maint-email-pills-list">
                    <span v-for="e in activeTicket.assigned_qa" :key="e" class="maint-email-tag-pill qa-pill" style="font-size:11px;">{{ e }}</span>
                  </div>
                  <span v-else style="color:var(--gray-400);">—</span>
                </div>
              </div>
            </div>
            <div v-if="activeTicket.notes" class="maint-view-field" style="margin-top:4px;">
              <div class="maint-view-label">Notes</div>
              <div class="maint-view-value" style="white-space:pre-wrap;line-height:1.7;color:var(--gray-600);">{{ activeTicket.notes }}</div>
            </div>
            <!-- Comments in view modal -->
            <div class="maint-view-field" style="margin-top:8px;">
              <div class="maint-view-label" style="display:flex;align-items:center;justify-content:space-between;">
                <span style="display:flex;align-items:center;gap:6px;">
                  <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                  Comments
                </span>
                <span v-if="activeTicket.comments?.length" class="bug-view-count-pill">{{ activeTicket.comments.length }}</span>
              </div>
              <div v-if="activeTicket.comments && activeTicket.comments.length" class="thread-msg-list" style="margin-top:8px;gap:8px;">
                <div v-for="(msg, i) in activeTicket.comments" :key="i" class="thread-msg-item">
                  <div class="thread-msg-author-row">
                    <span class="thread-msg-avatar">{{ (msg.author || '?')[0].toUpperCase() }}</span>
                    <span class="thread-msg-author-name">{{ msg.author || 'Anonymous' }}</span>
                    <span class="thread-msg-time-inline">{{ formatThreadTime(msg.timestamp) }}</span>
                  </div>
                  <div class="thread-msg-bubble">{{ msg.message }}</div>
                </div>
              </div>
              <div v-else class="bug-view-empty" style="margin-top:6px;">No comments yet.</div>
              <!-- Inline compose -->
              <div style="margin-top:12px;display:flex;flex-direction:column;gap:8px;">
                <textarea
                  v-model="mtNewMessage"
                  class="form-control"
                  placeholder="Write a comment… (Ctrl+Enter to post)"
                  rows="2"
                  style="font-size:13px;resize:vertical;"
                  @keydown.ctrl.enter.prevent="addMtComment"
                ></textarea>
                <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;">
                  <div style="display:flex;align-items:center;gap:6px;border:1px solid #e2e8f0;border-radius:7px;padding:5px 10px;flex:1;min-width:120px;max-width:200px;">
                    <svg width="12" height="12" fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <input v-model="mtNewAuthor" class="email-tag-inner-input" placeholder="Your name" style="font-size:12px;" />
                  </div>
                  <button
                    class="btn btn-primary btn-sm"
                    style="background:linear-gradient(135deg,#059669,#047857);display:flex;align-items:center;gap:5px;"
                    :disabled="!mtNewMessage.trim() || !mtNewAuthor.trim() || mtThreadSending"
                    @click="addMtComment"
                  >
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    {{ mtThreadSending ? 'Posting…' : 'Post comment' }}
                  </button>
                </div>
              </div>
            </div>
            <div v-if="activeTicket.attachments?.length" class="maint-view-field" style="margin-top:8px;">
              <div class="maint-view-label">Attachments</div>
              <div class="attach-preview-grid" style="margin-top:6px;">
                <a v-for="url in activeTicket.attachments" :key="url" :href="apiBase + url" target="_blank" rel="noopener" class="attach-preview-item" style="text-decoration:none;">
                  <img v-if="isImageUrl(url)" :src="apiBase + url" class="attach-thumb" />
                  <div v-else class="attach-pdf-icon">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M9 15h6M9 11h6"/></svg>
                  </div>
                  <div class="attach-file-name">{{ url.split('/').pop() }}</div>
                </a>
              </div>
            </div>
          </div>

          <!-- EDIT MODE -->
          <form v-if="ticketModalMode === 'edit'" @submit.prevent="submitEditTicket">
            <div class="modal-body" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
              <div class="form-group" style="grid-column:1/2;">
                <label class="form-label">Client <span style="color:#ef4444;">*</span></label>
                <input v-model="editTicketForm.client" class="form-control" required placeholder="Client name" />
              </div>
              <div class="form-group" style="grid-column:2/3;position:relative;">
                <label class="form-label">Sent Through</label>
                <input v-model="editTicketForm.sent_thru" class="form-control" placeholder="e.g. Email, Viber…" autocomplete="off" @focus="showSentThruEdit = true" @blur="hideSentThruEdit" />
                <div v-if="showSentThruEdit" class="sent-thru-dropdown">
                  <div v-for="opt in sentThruOptions" :key="opt" class="sent-thru-option" @mousedown.prevent="editTicketForm.sent_thru = opt; showSentThruEdit = false">{{ opt }}</div>
                </div>
              </div>
              <div class="form-group" style="grid-column:1/3;">
                <label class="form-label">Request <span style="color:#ef4444;">*</span></label>
                <textarea v-model="editTicketForm.request" class="form-control" rows="3" required placeholder="Describe the maintenance request..."></textarea>
              </div>
              <div class="form-group">
                <label class="form-label">Date Received</label>
                <input v-model="editTicketForm.date_received" type="date" class="form-control" />
              </div>
              <div class="form-group">
                <label class="form-label">Target Date</label>
                <input v-model="editTicketForm.target_date" type="date" class="form-control" />
              </div>
              <div class="form-group">
                <label class="form-label">Status</label>
                <select v-model="editTicketForm.status" class="form-control">
                  <option>Pending</option>
                  <option>In Progress</option>
                  <option>On Hold</option>
                  <option>Completed</option>
                  <option>Cancelled</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Dev Status</label>
                <select v-model="editTicketForm.dev_status" :class="['form-control', 'tt-dev-status-badge', 'tt-dev-status--' + (editTicketForm.dev_status || 'Not Started').toLowerCase().replace(/\s+/g, '-')]">
                  <option value="Not Started">Not Started</option>
                  <option value="In Progress">In Progress</option>
                  <option value="Ready for QA">Ready for QA</option>
                  <option value="Blocked">Blocked</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Completion Date</label>
                <input v-model="editTicketForm.completion_date" type="date" class="form-control" />
              </div>
              <div class="form-group" style="grid-column:1/3;">
                <label class="form-label">Assigned Dev(s)</label>
                <div class="email-tag-input" @click="$refs.editDevInput.focus()">
                  <span v-for="(email, i) in editTicketForm.assigned_devs" :key="i" class="maint-email-tag-pill dev-pill">
                    {{ email }}
                    <button type="button" @click.stop="editTicketForm.assigned_devs.splice(i, 1)">×</button>
                  </span>
                  <input ref="editDevInput" v-model="editDevInputVal" class="email-tag-inner-input" placeholder="dev@example.com" @keydown.enter.prevent="addEmail('editDev')" @keydown="checkComma($event, 'editDev')" @blur="addEmail('editDev')" />
                </div>
              </div>
              <div class="form-group" style="grid-column:1/3;">
                <label class="form-label">Assigned QA</label>
                <div class="email-tag-input" @click="$refs.editQaInput.focus()">
                  <span v-for="(email, i) in editTicketForm.assigned_qa" :key="i" class="maint-email-tag-pill qa-pill">
                    {{ email }}
                    <button type="button" @click.stop="editTicketForm.assigned_qa.splice(i, 1)">×</button>
                  </span>
                  <input ref="editQaInput" v-model="editQaInputVal" class="email-tag-inner-input" placeholder="qa@example.com" @keydown.enter.prevent="addEmail('editQa')" @keydown="checkComma($event, 'editQa')" @blur="addEmail('editQa')" />
                </div>
              </div>
              <div class="form-group" style="grid-column:1/3;">
                <label class="form-label">Notes</label>
                <textarea v-model="editTicketForm.notes" class="form-control" rows="2" placeholder="Additional notes..."></textarea>
              </div>
              <!-- Attachments -->
              <div class="form-group" style="grid-column:1/3;">
                <label class="form-label">Attachments <span style="font-size:11px;color:var(--gray-400);font-weight:400;">Images or PDF, max 20 MB each</span></label>
                <!-- Existing -->
                <div v-if="editExistingAttachments.length" class="attach-preview-grid" style="margin-bottom:8px;">
                  <div v-for="(url, i) in editExistingAttachments" :key="url" class="attach-preview-item">
                    <a :href="apiBase + url" target="_blank" rel="noopener">
                      <img v-if="isImageUrl(url)" :src="apiBase + url" class="attach-thumb" />
                      <div v-else class="attach-pdf-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M9 15h6M9 11h6"/></svg>
                      </div>
                    </a>
                    <div class="attach-file-name">{{ url.split('/').pop() }}</div>
                    <button type="button" class="attach-remove-btn" @click="editExistingAttachments.splice(i, 1)">×</button>
                  </div>
                </div>
                <!-- New files -->
                <div class="attach-drop-zone" :class="{ 'attach-drop-zone--active': editDragOver }" @dragover.prevent="editDragOver = true" @dragleave="editDragOver = false" @drop.prevent="onDropEdit">
                  <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--gray-300);"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                  <span style="font-size:13px;color:var(--gray-400);">Drag & drop or <label style="color:var(--primary);cursor:pointer;text-decoration:underline;">browse<input type="file" accept="image/*,.pdf" multiple style="display:none;" @change="onPickEdit" /></label></span>
                </div>
                <div v-if="editNewFiles.length" class="attach-preview-grid" style="margin-top:8px;">
                  <div v-for="(f, i) in editNewFiles" :key="i" class="attach-preview-item">
                    <img v-if="f.type.startsWith('image/')" :src="f.preview" class="attach-thumb" />
                    <div v-else class="attach-pdf-icon">
                      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M9 15h6M9 11h6"/></svg>
                    </div>
                    <div class="attach-file-name">{{ f.name }}</div>
                    <button type="button" class="attach-remove-btn" @click="removeEditFile(i)">×</button>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="ticketModalMode = 'view'">Cancel</button>
              <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg,#059669,#047857);" :disabled="submitting">
                {{ submitting ? 'Saving…' : 'Save Changes' }}
              </button>
            </div>
          </form>

          <!-- VIEW footer -->
          <div v-if="ticketModalMode === 'view'" class="modal-footer">
            <button class="btn btn-secondary" @click="showTicketModal = false">Close</button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- ══ Project Modal ══ -->
    <Transition name="fade">
      <div v-if="showProjectModal" class="modal-overlay" @click.self="showProjectModal = false">
        <div class="modal" style="max-width:440px;">
          <div class="modal-header">
            <h3 class="modal-title">{{ editingProject ? 'Edit Project' : 'New Maintenance Project' }}</h3>
            <button class="btn btn-ghost btn-icon" @click="showProjectModal = false">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
          </div>
          <form @submit.prevent="submitProject">
            <div class="modal-body" style="display:flex;flex-direction:column;gap:14px;">
              <div class="form-group">
                <label class="form-label">Project Name <span style="color:#ef4444;">*</span></label>
                <input v-model="projectForm.name" class="form-control" required placeholder="e.g. Client Portal Maintenance" />
              </div>
              <div class="form-group">
                <label class="form-label">Description</label>
                <textarea v-model="projectForm.description" class="form-control" rows="2" placeholder="Optional description..."></textarea>
              </div>
              <div class="form-group">
                <label class="form-label">Color</label>
                <div style="display:flex;align-items:center;gap:10px;">
                  <input v-model="projectForm.color" type="color" style="width:40px;height:36px;border:1px solid var(--gray-200);border-radius:8px;padding:2px;cursor:pointer;" />
                  <input v-model="projectForm.color" class="form-control" placeholder="#10b981" style="flex:1;" />
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">Status</label>
                <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;background:var(--gray-50);border:1px solid var(--gray-200);border-radius:8px;">
                  <span style="font-size:13px;color:var(--gray-700);flex:1;">
                    {{ projectForm.is_active ? 'Active — new tickets can be added' : 'Inactive — no new tickets allowed' }}
                  </span>
                  <button type="button" @click="projectForm.is_active = !projectForm.is_active"
                    :style="{ display:'flex', alignItems:'center', width:'44px', height:'24px', borderRadius:'12px', border:'none', cursor:'pointer', padding:'2px', transition:'background .2s', background: projectForm.is_active ? '#059669' : '#d1d5db', justifyContent: projectForm.is_active ? 'flex-end' : 'flex-start' }">
                    <span style="width:20px;height:20px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.2);"></span>
                  </button>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">Contract Duration</label>
                <div style="display:flex;gap:10px;align-items:center;">
                  <div style="flex:1;">
                    <label style="font-size:11px;color:var(--gray-500);margin-bottom:4px;display:block;">Start Date</label>
                    <input v-model="projectForm.contract_start" type="date" class="form-control" />
                  </div>
                  <span style="color:var(--gray-400);margin-top:18px;">→</span>
                  <div style="flex:1;">
                    <label style="font-size:11px;color:var(--gray-500);margin-bottom:4px;display:block;">End Date</label>
                    <input v-model="projectForm.contract_end" type="date" class="form-control" :min="projectForm.contract_start" />
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="showProjectModal = false">Cancel</button>
              <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg,#059669,#047857);" :disabled="submitting">
                {{ submitting ? 'Saving…' : (editingProject ? 'Save Changes' : 'Create Project') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <!-- ══ Delete Ticket Confirm ══ -->
    <Transition name="fade">
      <div v-if="showDeleteTicketModal" class="modal-overlay" @click.self="showDeleteTicketModal = false">
        <div class="modal" style="max-width:400px;">
          <div class="modal-header">
            <h3 class="modal-title" style="color:var(--danger);">Delete Ticket</h3>
            <button class="btn btn-ghost btn-icon" @click="showDeleteTicketModal = false">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
          </div>
          <div class="modal-body">
            <p style="color:var(--gray-700);">Are you sure you want to delete ticket <strong>{{ deletingTicket?.ticket_number }}</strong>? This action cannot be undone.</p>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="showDeleteTicketModal = false">Cancel</button>
            <button class="btn btn-danger" :disabled="submitting" @click="deleteTicket">
              {{ submitting ? 'Deleting…' : 'Delete Ticket' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- ══ Delete Project Confirm ══ -->
    <Transition name="fade">
      <div v-if="showDeleteProjectModal" class="modal-overlay" @click.self="showDeleteProjectModal = false">
        <div class="modal" style="max-width:400px;">
          <div class="modal-header">
            <h3 class="modal-title" style="color:var(--danger);">Delete Project</h3>
            <button class="btn btn-ghost btn-icon" @click="showDeleteProjectModal = false">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
          </div>
          <div class="modal-body">
            <p style="color:var(--gray-700);">Delete <strong>{{ deletingProject?.name }}</strong>? All tickets in this project will be permanently removed.</p>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="showDeleteProjectModal = false">Cancel</button>
            <button class="btn btn-danger" :disabled="submitting" @click="deleteProject">
              {{ submitting ? 'Deleting…' : 'Delete Project' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- ══ Comment Thread Modal ══ -->
    <Transition name="fade">
      <div v-if="showMtThreadModal" class="modal-overlay" @click.self="showMtThreadModal = false">
        <div class="modal thread-modal">
          <div class="modal-header">
            <div style="display:flex;align-items:center;gap:10px;min-width:0;">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              <h3 class="modal-title" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Comments — {{ mtThreadTicket?.ticket_number }}</h3>
            </div>
            <button class="btn btn-ghost btn-icon" @click="showMtThreadModal = false">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
          </div>
          <div class="modal-body thread-modal-body">
            <div class="thread-msg-list" ref="mtThreadListEl" @click="mtOpenMenuIdx = null">
              <div v-if="mtThreadTicket?.comments && mtThreadTicket.comments.length">
                <div v-for="(msg, i) in mtThreadTicket.comments" :key="i" class="thread-msg-item">
                  <!-- Edit mode -->
                  <div v-if="mtEditingMsgIndex === i" class="thread-msg-editing">
                    <textarea
                      v-model="mtEditingMsgValue"
                      class="thread-textarea"
                      rows="3"
                      @keydown.ctrl.enter.prevent="saveMtEditMsg(i)"
                      @keydown.esc="cancelMtEditMsg"
                    ></textarea>
                    <div class="thread-edit-actions">
                      <span style="font-size:11px;color:var(--gray-400);">Ctrl+Enter to save</span>
                      <div style="display:flex;gap:6px;">
                        <button class="btn btn-ghost btn-sm" @click="cancelMtEditMsg">Cancel</button>
                        <button class="btn btn-primary btn-sm" :disabled="!mtEditingMsgValue.trim()" @click="saveMtEditMsg(i)">Save</button>
                      </div>
                    </div>
                  </div>
                  <!-- View mode -->
                  <template v-else>
                    <div class="thread-msg-author-row">
                      <span class="thread-msg-avatar">{{ (msg.author || '?')[0].toUpperCase() }}</span>
                      <span class="thread-msg-author-name">{{ msg.author || 'Anonymous' }}</span>
                      <span class="thread-msg-time-inline">{{ formatThreadTime(msg.timestamp) }}{{ msg.edited ? ' · edited' : '' }}</span>
                      <div class="thread-msg-menu-wrap" style="margin-left:auto;">
                        <button class="thread-ellipsis-btn" @click.stop="mtOpenMenuIdx = (mtOpenMenuIdx === i ? null : i)">⋯</button>
                        <div v-if="mtOpenMenuIdx === i" class="thread-dropdown" @click.stop>
                          <button class="thread-dropdown-item" @click="startMtEditMsg(i, msg.message); mtOpenMenuIdx = null">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            Edit
                          </button>
                          <button class="thread-dropdown-item thread-dropdown-delete" @click="mtDeletingMsgIdx = i; mtOpenMenuIdx = null">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                            Delete
                          </button>
                        </div>
                      </div>
                    </div>
                    <div class="thread-msg-bubble">{{ msg.message }}</div>
                    <div v-if="mtDeletingMsgIdx === i" class="thread-delete-confirm">
                      <span>Delete this comment?</span>
                      <div style="display:flex;gap:6px;">
                        <button class="btn btn-ghost btn-sm" @click="mtDeletingMsgIdx = null">Cancel</button>
                        <button class="btn btn-sm" style="background:var(--danger);color:#fff;border-color:var(--danger);" @click="deleteMtMsg(i); mtDeletingMsgIdx = null">Delete</button>
                      </div>
                    </div>
                  </template>
                </div>
              </div>
              <div v-else class="thread-empty">
                <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--gray-300);margin-bottom:8px;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <div>No comments yet. Be the first to add one.</div>
              </div>
            </div>
          </div>
          <div class="thread-input-area">
            <input
              v-model="mtNewAuthor"
              class="thread-author-input"
              placeholder="Your name…"
              maxlength="80"
            />
            <textarea
              v-model="mtNewMessage"
              class="thread-textarea"
              placeholder="Write a comment… (Ctrl+Enter to send)"
              rows="3"
              @keydown.ctrl.enter.prevent="addMtComment"
            ></textarea>
            <div class="thread-input-footer">
              <span style="font-size:11px;color:var(--gray-400);">Ctrl + Enter to send</span>
              <button class="btn btn-primary" :disabled="!mtNewMessage.trim() || !mtNewAuthor.trim() || mtThreadSending" @click="addMtComment">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                {{ mtThreadSending ? 'Sending…' : 'Send' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- ══ Toast ══ -->
    <Transition name="toast-slide">
      <div v-if="toast.show" :class="['toast-notification', `toast-${toast.type}`]">
        <svg v-if="toast.type === 'success'" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        <svg v-else width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        {{ toast.message }}
      </div>
    </Transition>

  </div>
</template>

<script setup>
import { ref, computed, reactive, nextTick, onMounted, onUnmounted } from 'vue'

const config  = useRuntimeConfig()
const apiBase = config.public.apiBase.replace('/api', '')
const route   = useRoute()

// ── Auth ─────────────────────────────────────────────────────────────────────
const authToken           = ref(null)
const currentUser         = ref(null)
const profileDropdownOpen = ref(false)
const profileDropdownRef  = ref(null)
const openProjectMenuId   = ref(null)

const apiFetch = (url, options = {}) => {
  if (authToken.value) {
    options.headers = { Authorization: `Bearer ${authToken.value}`, ...(options.headers || {}) }
  }
  return $fetch(url, options)
}

const login  = () => { window.location.href = `${apiBase}/api/auth/google` }
const logout = async () => {
  try { await apiFetch(`${config.public.apiBase}/auth/logout`, { method: 'POST' }) } catch {}
  authToken.value = null
  localStorage.removeItem('auth_token')
  currentUser.value = null
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

// ── State ─────────────────────────────────────────────────────────────────────
const dashboardOpen         = ref(false)
const inactiveOpen          = ref(false)
const allTickets            = ref([])
const projects              = ref([])
const tickets               = ref([])
const selectedProject       = ref(null)

const ticketSearch          = ref('')
const ticketStatusFilter    = ref('')

const showNewTicketModal     = ref(false)
const showTicketModal        = ref(false)
const showProjectModal       = ref(false)
const showDeleteTicketModal  = ref(false)
const showDeleteProjectModal = ref(false)

const activeTicket    = ref(null)
const ticketModalMode = ref('view') // 'view' | 'edit'
const editingProject  = ref(null)
const deletingTicket  = ref(null)
const deletingProject = ref(null)
const notifyingId     = ref(null)
const submitting      = ref(false)

// ── Notifications ─────────────────────────────────────────────────────────────
const maintNotifications     = ref([])
const maintNotifDropdownOpen = ref(false)
const maintNotifDropdownRef  = ref(null)
let   maintNotifPollTimer    = null

const toast = reactive({ show: false, message: '', type: 'success' })

// ── Comment thread state ──────────────────────────────────────────────────────
const showMtThreadModal  = ref(false)
const mtThreadTicketId   = ref(null)
const mtNewMessage       = ref('')
const mtNewAuthor        = ref('')
const mtThreadSending    = ref(false)
const mtThreadListEl     = ref(null)
const mtEditingMsgIndex  = ref(null)
const mtEditingMsgValue  = ref('')
const mtOpenMenuIdx      = ref(null)
const mtDeletingMsgIdx   = ref(null)

const mtThreadTicket = computed(() => tickets.value.find(t => t.id === mtThreadTicketId.value) || null)
let toastTimer = null

const sentThruOptions = ['Email', 'Slack', 'Phone', 'Teams', 'Viber', 'In-person', 'Other']

const showSentThruNew  = ref(false)
const showSentThruEdit = ref(false)
const hideSentThruNew  = () => setTimeout(() => { showSentThruNew.value  = false }, 120)
const hideSentThruEdit = () => setTimeout(() => { showSentThruEdit.value = false }, 120)

// ── Attachment state ──────────────────────────────────────────────────────────
const newAttachFiles         = ref([])   // File objects with .preview for new ticket
const editExistingAttachments = ref([])  // URLs of existing attachments to keep
const editNewFiles           = ref([])   // New File objects added in edit modal
const newDragOver            = ref(false)
const editDragOver           = ref(false)

const addFilesWithPreview = (fileList, targetRef) => {
  Array.from(fileList).forEach(file => {
    const item = Object.assign(file, { preview: file.type.startsWith('image/') ? URL.createObjectURL(file) : '' })
    targetRef.value.push(item)
  })
}
const onPickNew   = (e) => addFilesWithPreview(e.target.files, newAttachFiles)
const onDropNew   = (e) => { newDragOver.value = false; addFilesWithPreview(e.dataTransfer.files, newAttachFiles) }
const onPickEdit  = (e) => addFilesWithPreview(e.target.files, editNewFiles)
const onDropEdit  = (e) => { editDragOver.value = false; addFilesWithPreview(e.dataTransfer.files, editNewFiles) }
const removeNewFile  = (i) => { URL.revokeObjectURL(newAttachFiles.value[i]?.preview); newAttachFiles.value.splice(i, 1) }
const removeEditFile = (i) => { URL.revokeObjectURL(editNewFiles.value[i]?.preview); editNewFiles.value.splice(i, 1) }

const revokeAttachPreviews = (files) => files.forEach(f => { if (f.preview) URL.revokeObjectURL(f.preview) })
const isImageUrl = (url) => /\.(jpe?g|png|gif|webp)$/i.test(url)

// ── Tag email inputs ──────────────────────────────────────────────────────────
const devInputVal      = ref('')
const qaInputVal       = ref('')
const editDevInputVal  = ref('')
const editQaInputVal   = ref('')

const newTicketForm = ref({
  client: '', request: '', sent_thru: 'Email',
  date_received: '', target_date: '', completion_date: '',
  assigned_devs: [], assigned_qa: [],
  status: 'Pending', notes: ''
})

const editTicketForm = ref({
  client: '', request: '', sent_thru: 'Email',
  date_received: '', target_date: '', completion_date: '',
  assigned_devs: [], assigned_qa: [],
  status: 'Pending', dev_status: 'Not Started', notes: ''
})

const projectForm = ref({ name: '', description: '', color: '#10b981', is_active: true, contract_start: '', contract_end: '' })

// ── Computed ──────────────────────────────────────────────────────────────────
const filteredTickets = computed(() => tickets.value.filter(t => {
  if (ticketSearch.value) {
    const q = ticketSearch.value.toLowerCase()
    if (!t.client.toLowerCase().includes(q) &&
        !t.request.toLowerCase().includes(q) &&
        !t.ticket_number.toLowerCase().includes(q) &&
        !(t.notes || '').toLowerCase().includes(q)) return false
  }
  if (ticketStatusFilter.value && t.status !== ticketStatusFilter.value) return false
  return true
}))

const overdueCount = computed(() => tickets.value.filter(t => isOverdue(t)).length)

// ── Stat completion % ─────────────────────────────────────────────────────────
const completionPct = computed(() => {
  if (!tickets.value.length) return 0
  return Math.round(tickets.value.filter(t => t.status === 'Completed').length / tickets.value.length * 100)
})

const activeProjects   = computed(() => projects.value.filter(p => p.is_active !== false))
const inactiveProjects = computed(() => projects.value.filter(p => p.is_active === false))

// ── Project search/filter ─────────────────────────────────────────────────────
const projectSearch      = ref('')
const projectStatusFilter = ref('') // 'active' | 'inactive' | ''

const filteredAllProjects = computed(() => {
  const q = projectSearch.value.trim().toLowerCase()
  return projects.value.filter(p => {
    const matchSearch = !q ||
      p.name.toLowerCase().includes(q) ||
      (p.description || '').toLowerCase().includes(q)
    const isActive = p.is_active !== false
    const matchStatus =
      projectStatusFilter.value === 'active'   ? isActive :
      projectStatusFilter.value === 'inactive' ? !isActive : true
    return matchSearch && matchStatus
  })
})

const filteredActiveProjects = computed(() =>
  filteredAllProjects.value.filter(p => p.is_active !== false)
)

const filteredInactiveProjects = computed(() => {
  const q = projectSearch.value.trim().toLowerCase()
  return inactiveProjects.value.filter(p =>
    !q || p.name.toLowerCase().includes(q) || (p.description || '').toLowerCase().includes(q)
  )
})

// ── Dashboard computeds ───────────────────────────────────────────────────────
const CHANNEL_COLORS = { Email:'#3b82f6', Slack:'#8b5cf6', Phone:'#10b981', Viber:'#06b6d4', Teams:'#f59e0b', 'In-person':'#f97316', Other:'#94a3b8' }
const DEV_PALETTE    = ['#6366f1','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#14b8a6','#f97316']

const dashStats = computed(() => {
  const ts = allTickets.value
  const total     = ts.length
  const pending   = ts.filter(t => t.status === 'Pending').length
  const ongoing   = ts.filter(t => t.status === 'In Progress').length
  const completed = ts.filter(t => t.status === 'Completed').length
  const overdue   = ts.filter(t => isOverdue(t)).length
  const ages      = ts.map(t => ticketAgeDays(t))
  const avgAge    = ages.length ? Math.round(ages.reduce((a, b) => a + b, 0) / ages.length) : 0
  return { total, pending, ongoing, completed, overdue, avgAge }
})

const dashStatusArc = computed(() => {
  const t = dashStats.value.total || 1
  const circ = 289
  return {
    completed: Math.round(dashStats.value.completed / t * circ),
    ongoing:   Math.round(dashStats.value.ongoing   / t * circ),
    pending:   Math.round(dashStats.value.pending   / t * circ),
    overdue:   Math.round(dashStats.value.overdue   / t * circ),
  }
})

const dashByClient = computed(() => {
  const map = {}
  allTickets.value.forEach(t => {
    if (!t.client) return
    map[t.client] = (map[t.client] || 0) + 1
  })
  const colors = ['#3b82f6','#10b981','#8b5cf6','#f59e0b','#ef4444','#ec4899','#f97316','#06b6d4']
  return Object.entries(map)
    .sort((a, b) => b[1] - a[1])
    .map(([name, count], i) => ({ name, count, color: colors[i % colors.length] }))
})

const dashDevWorkload = computed(() => {
  const map = {}
  allTickets.value
    .filter(t => t.status !== 'Completed' && t.status !== 'Cancelled')
    .forEach(t => {
      (t.assigned_devs || []).forEach(email => {
        if (!map[email]) map[email] = 0
        map[email]++
      })
    })
  return Object.entries(map)
    .sort((a, b) => b[1] - a[1])
    .map(([email, count], i) => {
      const name = email.split('@')[0]
      return {
        email, count,
        name,
        initials: name.slice(0, 2).toUpperCase(),
        color: DEV_PALETTE[i % DEV_PALETTE.length],
      }
    })
})

const dashAtRisk = computed(() => {
  const now = new Date()
  const in2  = new Date(now); in2.setDate(in2.getDate() + 2)
  return allTickets.value
    .filter(t => {
      if (!t.target_date || t.status === 'Completed' || t.status === 'Cancelled') return false
      const d = parseLocalDate(t.target_date); d.setHours(23, 59, 59, 999)
      return d <= in2
    })
    .sort((a, b) => parseLocalDate(a.target_date) - parseLocalDate(b.target_date))
    .slice(0, 5)
})

const dashDueThisWeek = computed(() => {
  const now = new Date()
  const in7  = new Date(now); in7.setDate(in7.getDate() + 7)
  return allTickets.value
    .filter(t => {
      if (!t.target_date || t.status === 'Completed' || t.status === 'Cancelled') return false
      const d = parseLocalDate(t.target_date)
      return d >= now && d <= in7
    })
    .sort((a, b) => parseLocalDate(a.target_date) - parseLocalDate(b.target_date))
    .slice(0, 6)
})

const dashRecentActivity = computed(() => {
  const ts = [...allTickets.value].sort((a, b) => new Date(b.updated_at) - new Date(a.updated_at)).slice(0, 6)
  const now = new Date()
  return ts.map(t => {
    const d    = new Date(t.updated_at)
    const diff = Math.floor((now - d) / 60000)
    let timeLabel
    if (diff < 60) timeLabel = `${diff}m ago`
    else if (diff < 1440) timeLabel = `Today, ${d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`
    else timeLabel = `${d.toLocaleDateString([], { month: 'short', day: 'numeric' })}, ${d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`

    let text, color
    if (t.status === 'Completed')    { text = `${t.ticket_number} marked Completed — ${t.client}`;  color = '#22c55e' }
    else if (t.status === 'In Progress') { text = `${t.ticket_number} is In Progress — ${t.client}`; color = '#3b82f6' }
    else if (isOverdue(t))           { text = `${t.ticket_number} became overdue — ${t.client}`;    color = '#ef4444' }
    else                             { text = `${t.ticket_number} updated — ${t.client}`;            color = '#94a3b8' }
    return { id: t.id, text, timeLabel, color }
  })
})

const dashByChannel = computed(() => {
  const map = {}
  allTickets.value.forEach(t => {
    const ch = t.sent_thru || 'Other'
    map[ch] = (map[ch] || 0) + 1
  })
  return Object.entries(map)
    .sort((a, b) => b[1] - a[1])
    .map(([name, count]) => ({ name, count, color: CHANNEL_COLORS[name] || '#94a3b8' }))
})

const riskLabel = (t) => {
  if (isOverdue(t)) {
    const d = Math.floor((new Date() - parseLocalDate(t.target_date)) / 86400000)
    return `${d}d overdue`
  }
  const diff = Math.ceil((parseLocalDate(t.target_date) - new Date()) / 86400000)
  return diff === 0 ? 'due today' : `due in ${diff}d`
}

const riskBadgeStyle = (t) => {
  const overdue = isOverdue(t)
  const bg = overdue ? '#fef2f2' : '#fffbeb'
  const color = overdue ? '#b91c1c' : '#92400e'
  return { background: bg, color, fontWeight: '700', fontSize: '11px', padding: '2px 8px', borderRadius: '99px', whiteSpace: 'nowrap' }
}

const statusBadgeStyle = (status) => {
  const map = { 'Completed': ['#f0fdf4','#166534'], 'In Progress': ['#eff6ff','#1d4ed8'], 'Pending': ['#fffbeb','#92400e'], 'On Hold': ['#f8fafc','#475569'], 'Cancelled': ['#f8fafc','#64748b'] }
  const [bg, color] = map[status] || ['#f8fafc','#64748b']
  return { background: bg, color, fontWeight: '700', fontSize: '11px', padding: '2px 10px', borderRadius: '99px' }
}

const firstDev = (t) => (t.assigned_devs || [])[0]?.split('@')[0] || '—'

// ── Helpers ───────────────────────────────────────────────────────────────────
const showToast = (message, type = 'success') => {
  if (toastTimer) clearTimeout(toastTimer)
  toast.message = message; toast.type = type; toast.show = true
  toastTimer = setTimeout(() => { toast.show = false }, 3500)
}

const formatDate = (d) => {
  if (!d) return ''
  const date = typeof d === 'string' ? d.slice(0, 10) : d
  if (!date) return ''
  const [y, m, day] = date.split('-')
  if (!y || !m || !day) return ''
  return new Date(+y, +m - 1, +day).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const ticketAgeDays = (t) => {
  const start = t.date_received ? new Date(t.date_received) : new Date(t.created_at)
  const end   = t.completion_date ? new Date(t.completion_date) : new Date()
  return Math.max(0, Math.floor((end - start) / 86400000))
}

const parseLocalDate = (str) => {
  const [y, m, d] = str.split('-').map(Number)
  return new Date(y, m - 1, d)
}

const isOverdue = (t) => {
  if (!t.target_date || t.completion_date) return false
  const target = parseLocalDate(t.target_date)
  target.setHours(23, 59, 59, 999)
  return new Date() > target
}

const agePercent = (t) => {
  if (!t.target_date || !t.date_received) return 0
  const start   = new Date(t.date_received)
  const target  = new Date(t.target_date)
  const now     = new Date()
  const total   = target - start
  if (total <= 0) return 100
  return Math.min(100, Math.max(0, Math.round(((now - start) / total) * 100)))
}

const ageColor = (t) => {
  if (t.completion_date) return '#22c55e'
  if (isOverdue(t))      return '#ef4444'
  const pct = agePercent(t)
  if (pct < 50)  return '#22c55e'
  if (pct < 80)  return '#f59e0b'
  return '#ef4444'
}

const ageBadgeClass = (t) => {
  if (t.completion_date) return 'age-badge--resolved'
  if (isOverdue(t))      return 'age-badge--old'
  const days = ticketAgeDays(t)
  if (days <= 3)  return 'age-badge--fresh'
  if (days <= 10) return 'age-badge--aging'
  return 'age-badge--old'
}

const ageStyle = (t) => {
  const color = ageColor(t)
  return {
    background: color + '1a',
    color,
    fontWeight: '700',
    fontSize: '11px',
    padding: '2px 8px',
    borderRadius: '99px',
    whiteSpace: 'nowrap',
  }
}

const ageBarStyle = (t) => {
  const pct   = agePercent(t)
  const color = ageColor(t)
  return { width: pct + '%', background: color }
}

const emailInitials = (email) => {
  const local = email.split('@')[0]
  const parts = local.split(/[._-]/)
  if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase()
  return local.slice(0, 2).toUpperCase()
}

const updateTicketStatus = async (ticket, newStatus) => {
  try {
    await apiFetch(
      `${config.public.apiBase}/maintenance-tickets/${ticket.id}/status`,
      { method: 'PATCH', body: { status: newStatus } }
    )
    ticket.status = newStatus
    if (activeTicket.value?.id === ticket.id) activeTicket.value.status = newStatus
    await fetchTickets()
    await fetchProjects()
  } catch (e) {
    console.error('Failed to update status', e)
  }
}

const updateTicketDevStatus = async (ticket, newDevStatus) => {
  try {
    await apiFetch(
      `${config.public.apiBase}/maintenance-tickets/${ticket.id}/dev-status`,
      { method: 'PATCH', body: { dev_status: newDevStatus } }
    )
    ticket.dev_status = newDevStatus
    if (activeTicket.value?.id === ticket.id) activeTicket.value.dev_status = newDevStatus
    await fetchTickets()
  } catch (e) {
    console.error('Failed to update dev status', e)
  }
}

const maintStatusBadgeClass = (status) => ({
  'Pending':     'badge-pending',
  'In Progress': 'badge-ongoing',
  'On Hold':     'badge-hold',
  'Completed':   'badge-completed',
  'Cancelled':   'badge-out-of-scope',
}[status] || '')

const sentThruBadgeClass = (v) => ({
  'Email':     'badge-sent-email',
  'Slack':     'badge-sent-slack',
  'Phone':     'badge-sent-phone',
  'Teams':     'badge-sent-teams',
  'In-person': 'badge-sent-inperson',
  'Other':     'badge-sent-other',
}[v] || '')

// ── Email tag input ───────────────────────────────────────────────────────────
const isValidEmail = (e) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e.trim())

const addEmail = (type) => {
  const valMap = { dev: devInputVal, qa: qaInputVal, editDev: editDevInputVal, editQa: editQaInputVal }
  const listMap = {
    dev:     newTicketForm.value.assigned_devs,
    qa:      newTicketForm.value.assigned_qa,
    editDev: editTicketForm.value.assigned_devs,
    editQa:  editTicketForm.value.assigned_qa,
  }
  const val = valMap[type].value.trim().replace(/,$/, '')
  if (val && isValidEmail(val) && !listMap[type].includes(val)) {
    listMap[type].push(val)
  }
  valMap[type].value = ''
}

const checkComma = (e, type) => {
  if (e.key === ',') {
    e.preventDefault()
    addEmail(type)
  }
}

// ── API: Projects ─────────────────────────────────────────────────────────────
const fetchProjects = async () => {
  try {
    const data = await apiFetch(`${config.public.apiBase}/maintenance/projects`)
    projects.value = data
    if (selectedProject.value) {
      const updated = data.find(p => p.id === selectedProject.value.id)
      if (updated) selectedProject.value = updated
    }
  } catch (e) { console.error('Failed to fetch maintenance projects', e) }
}

const selectProject = (project) => {
  selectedProject.value = project
  inactiveOpen.value = false
  dashboardOpen.value = false
  tickets.value = []
  ticketSearch.value = ''
  ticketStatusFilter.value = ''
  if (project) fetchTickets()
}

const openProjectModal = (project) => {
  editingProject.value = project
  projectForm.value = project
    ? { name: project.name, description: project.description || '', color: project.color || '#10b981', is_active: project.is_active !== false, contract_start: project.contract_start ? project.contract_start.slice(0, 10) : '', contract_end: project.contract_end ? project.contract_end.slice(0, 10) : '' }
    : { name: '', description: '', color: '#10b981', is_active: true, contract_start: '', contract_end: '' }
  showProjectModal.value = true
}

const submitProject = async () => {
  submitting.value = true
  try {
    if (editingProject.value) {
      await apiFetch(`${config.public.apiBase}/maintenance/projects/${editingProject.value.id}`, { method: 'PUT', body: { ...projectForm.value } })
      showToast('Project updated')
    } else {
      await apiFetch(`${config.public.apiBase}/maintenance/projects`, { method: 'POST', body: { ...projectForm.value } })
      showToast('Project created')
    }
    await fetchProjects()
    showProjectModal.value = false
  } catch (e) { console.error(e); showToast('Failed to save project', 'error') }
  finally { submitting.value = false }
}

const confirmDeleteProject = (p) => { deletingProject.value = p; showDeleteProjectModal.value = true }

const deleteProject = async () => {
  submitting.value = true
  try {
    await apiFetch(`${config.public.apiBase}/maintenance/projects/${deletingProject.value.id}`, { method: 'DELETE' })
    if (selectedProject.value?.id === deletingProject.value.id) selectProject(null)
    await fetchProjects()
    showDeleteProjectModal.value = false
    deletingProject.value = null
    showToast('Project deleted')
  } catch (e) { console.error(e); showToast('Failed to delete project', 'error') }
  finally { submitting.value = false }
}

// ── API: Tickets ──────────────────────────────────────────────────────────────
const fetchTickets = async () => {
  if (!selectedProject.value) return
  try {
    const data = await apiFetch(`${config.public.apiBase}/maintenance/projects/${selectedProject.value.id}/tickets`)
    tickets.value = data
  } catch (e) { console.error('Failed to fetch tickets', e) }
}

const fetchAllTickets = async () => {
  try {
    const results = await Promise.all(
      projects.value.map(p =>
        apiFetch(`${config.public.apiBase}/maintenance/projects/${p.id}/tickets`)
          .catch(() => [])
      )
    )
    allTickets.value = results.flat()
  } catch (e) { console.error('Failed to fetch all tickets', e) }
}

const openInactiveView = () => {
  inactiveOpen.value = true
  dashboardOpen.value = false
  selectedProject.value = null
  tickets.value = []
}

const openDashboard = async () => {
  dashboardOpen.value = true
  inactiveOpen.value = false
  selectedProject.value = null
  tickets.value = []
  if (projects.value.length) await fetchAllTickets()
}

const openNewTicketModal = () => {
  if (selectedProject.value?.is_active === false) {
    showToast('This project is inactive. New tickets cannot be added.', 'error')
    return
  }
  newTicketForm.value = {
    client: '', request: '', sent_thru: 'Email',
    date_received: today(), target_date: '', completion_date: '',
    assigned_devs: [], assigned_qa: [],
    status: 'Pending', notes: ''
  }
  devInputVal.value = ''
  qaInputVal.value = ''
  revokeAttachPreviews(newAttachFiles.value)
  newAttachFiles.value = []
  showNewTicketModal.value = true
}

const today = () => {
  const d = new Date()
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
}

const submitNewTicket = async () => {
  addEmail('dev'); addEmail('qa')
  submitting.value = true
  try {
    const f = newTicketForm.value
    const fd = new FormData()
    fd.append('client',          f.client)
    fd.append('request',         f.request)
    fd.append('sent_thru',       f.sent_thru || 'Email')
    fd.append('status',          f.status)
    if (f.date_received)   fd.append('date_received',   f.date_received)
    if (f.target_date)     fd.append('target_date',     f.target_date)
    if (f.completion_date) fd.append('completion_date', f.completion_date)
    if (f.notes)           fd.append('notes',           f.notes)
    f.assigned_devs.forEach((e, i) => fd.append(`assigned_devs[${i}]`, e))
    f.assigned_qa.forEach((e, i)   => fd.append(`assigned_qa[${i}]`,   e))
    newAttachFiles.value.forEach(file => fd.append('attachments[]', file))

    await apiFetch(`${config.public.apiBase}/maintenance/projects/${selectedProject.value.id}/tickets`, {
      method: 'POST', body: fd
    })
    await fetchTickets()
    await fetchProjects()
    revokeAttachPreviews(newAttachFiles.value)
    newAttachFiles.value = []
    showNewTicketModal.value = false
    const hasEmails = (f.assigned_devs?.length || 0) + (f.assigned_qa?.length || 0) > 0
    showToast(hasEmails ? 'Ticket created & notification sent' : 'Ticket created')
  } catch (e) { console.error(e); showToast('Failed to create ticket', 'error') }
  finally { submitting.value = false }
}

const openTicketModal = (ticket, mode) => {
  activeTicket.value = ticket
  ticketModalMode.value = mode
  mtThreadTicketId.value = ticket.id
  if (!mtNewAuthor.value && currentUser.value?.name) mtNewAuthor.value = currentUser.value.name
  if (mode === 'edit') initEditForm()
  showTicketModal.value = true
}

const initEditForm = () => {
  const t = activeTicket.value
  if (!t) return
  editTicketForm.value = {
    client:          t.client,
    request:         t.request,
    sent_thru:       t.sent_thru || 'Email',
    date_received:   t.date_received ? t.date_received.slice(0, 10) : '',
    target_date:     t.target_date   ? t.target_date.slice(0, 10)   : '',
    completion_date: t.completion_date ? t.completion_date.slice(0, 10) : '',
    assigned_devs:   [...(t.assigned_devs || [])],
    assigned_qa:     [...(t.assigned_qa   || [])],
    status:          t.status,
    dev_status:      t.dev_status || 'Not Started',
    notes:           t.notes || '',
  }
  editExistingAttachments.value = [...(t.attachments || [])]
  revokeAttachPreviews(editNewFiles.value)
  editNewFiles.value = []
  editDevInputVal.value = ''
  editQaInputVal.value  = ''
}

const submitEditTicket = async () => {
  addEmail('editDev'); addEmail('editQa')
  submitting.value = true
  try {
    const f = editTicketForm.value
    const fd = new FormData()
    fd.append('client',          f.client)
    fd.append('request',         f.request)
    fd.append('sent_thru',       f.sent_thru || 'Email')
    fd.append('status',          f.status)
    fd.append('dev_status',      f.dev_status || 'Not Started')
    if (f.date_received)   fd.append('date_received',   f.date_received)
    if (f.target_date)     fd.append('target_date',     f.target_date)
    if (f.completion_date) fd.append('completion_date', f.completion_date)
    if (f.notes)           fd.append('notes',           f.notes)
    f.assigned_devs.forEach((e, i) => fd.append(`assigned_devs[${i}]`, e))
    f.assigned_qa.forEach((e, i)   => fd.append(`assigned_qa[${i}]`,   e))
    editExistingAttachments.value.forEach((url, i) => fd.append(`existing_attachments[${i}]`, url))
    editNewFiles.value.forEach(file => fd.append('new_attachments[]', file))
    // Laravel needs _method for PUT with FormData
    fd.append('_method', 'PUT')

    const updated = await apiFetch(
      `${config.public.apiBase}/maintenance/projects/${selectedProject.value.id}/tickets/${activeTicket.value.id}`,
      { method: 'POST', body: fd }
    )
    await fetchTickets()
    await fetchProjects()
    revokeAttachPreviews(editNewFiles.value)
    editNewFiles.value = []
    activeTicket.value = tickets.value.find(t => t.id === activeTicket.value.id) || updated
    ticketModalMode.value = 'view'
    showToast('Ticket updated')
  } catch (e) { console.error(e); showToast('Failed to update ticket', 'error') }
  finally { submitting.value = false }
}

const confirmDeleteTicket = (t) => { deletingTicket.value = t; showDeleteTicketModal.value = true }

const deleteTicket = async () => {
  submitting.value = true
  try {
    await apiFetch(
      `${config.public.apiBase}/maintenance/projects/${selectedProject.value.id}/tickets/${deletingTicket.value.id}`,
      { method: 'DELETE' }
    )
    await fetchTickets()
    await fetchProjects()
    showDeleteTicketModal.value = false
    deletingTicket.value = null
    showToast('Ticket deleted')
  } catch (e) { console.error(e); showToast('Failed to delete ticket', 'error') }
  finally { submitting.value = false }
}

const sendNotification = async (ticket) => {
  notifyingId.value = ticket.id
  try {
    const res = await apiFetch(
      `${config.public.apiBase}/maintenance/projects/${selectedProject.value.id}/tickets/${ticket.id}/notify`,
      { method: 'POST' }
    )
    // Update ticket in list
    const idx = tickets.value.findIndex(t => t.id === ticket.id)
    if (idx !== -1) tickets.value[idx] = { ...tickets.value[idx], notification_sent_at: res.sent_at }
    showToast(`Notification sent to ${res.recipients.join(', ')}`)
  } catch (e) {
    const msg = e?.data?.error || 'Failed to send notification'
    showToast(msg, 'error')
  }
  finally { notifyingId.value = null }
}

// ── Comment thread functions ──────────────────────────────────────────────────
const formatThreadTime = (iso) => {
  if (!iso) return ''
  const d = new Date(iso)
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) +
    ' · ' + d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
}

const openMtThread = (ticket) => {
  mtThreadTicketId.value = ticket.id
  mtNewMessage.value = ''
  if (!mtNewAuthor.value && currentUser.value?.name) {
    mtNewAuthor.value = currentUser.value.name
  }
  mtEditingMsgIndex.value = null
  mtOpenMenuIdx.value = null
  showMtThreadModal.value = true
  nextTick(() => {
    if (mtThreadListEl.value) mtThreadListEl.value.scrollTop = mtThreadListEl.value.scrollHeight
  })
}

const addMtComment = async () => {
  if (!mtNewMessage.value.trim() || !mtNewAuthor.value.trim() || mtThreadSending.value || !mtThreadTicket.value) return
  mtThreadSending.value = true
  const message = mtNewMessage.value.trim()
  const author  = mtNewAuthor.value.trim()
  mtNewMessage.value = ''
  try {
    const updated = [
      ...(mtThreadTicket.value.comments || []),
      { message, author, timestamp: new Date().toISOString() }
    ]
    await saveMtComments(updated)
    nextTick(() => {
      if (mtThreadListEl.value) mtThreadListEl.value.scrollTop = mtThreadListEl.value.scrollHeight
    })
  } catch (e) {
    console.error('Failed to add comment', e)
    mtNewMessage.value = message
  } finally { mtThreadSending.value = false }
}

const startMtEditMsg  = (idx, text) => { mtEditingMsgIndex.value = idx; mtEditingMsgValue.value = text }
const cancelMtEditMsg = () => { mtEditingMsgIndex.value = null; mtEditingMsgValue.value = '' }

const saveMtEditMsg = async (idx) => {
  if (!mtEditingMsgValue.value.trim() || !mtThreadTicket.value) return
  const updated = mtThreadTicket.value.comments.map((m, i) =>
    i === idx ? { ...m, message: mtEditingMsgValue.value.trim(), edited: true } : m
  )
  cancelMtEditMsg()
  await saveMtComments(updated)
}

const deleteMtMsg = async (idx) => {
  if (!mtThreadTicket.value) return
  const updated = mtThreadTicket.value.comments.filter((_, i) => i !== idx)
  await saveMtComments(updated)
}

const saveMtComments = async (comments) => {
  if (!mtThreadTicket.value || !selectedProject.value) return
  const fd = new FormData()
  fd.append('_method', 'PUT')
  fd.append('comments_json', JSON.stringify(comments))
  await apiFetch(
    `${config.public.apiBase}/maintenance/projects/${selectedProject.value.id}/tickets/${mtThreadTicket.value.id}`,
    { method: 'POST', body: fd }
  )
  await fetchTickets()
}

// ── Notification helpers ──────────────────────────────────────────────────────
const maintNotifUnreadCount = computed(() => maintNotifications.value.filter(n => !n.read_at).length)

const fetchMaintNotifications = async () => {
  try {
    const all = await apiFetch(`${config.public.apiBase}/notifications`)
    maintNotifications.value = all.filter(n => n.type?.startsWith('mt_'))
  } catch { /* silent */ }
}

const toggleMaintNotifDropdown = () => {
  maintNotifDropdownOpen.value = !maintNotifDropdownOpen.value
}

const openMaintNotif = async (n) => {
  if (!n.read_at) {
    try { await apiFetch(`${config.public.apiBase}/notifications/${n.id}/read`, { method: 'PATCH' }) } catch {}
    n.read_at = new Date().toISOString()
  }
  maintNotifDropdownOpen.value = false
  if (n.data?.project_id && n.data?.ticket_id) {
    const project = projects.value.find(p => p.id === n.data.project_id)
    if (project) {
      await selectProject(project)
      await nextTick()
      const ticket = tickets.value.find(t => t.id === n.data.ticket_id)
      if (ticket) openTicketModal(ticket, 'view')
    }
  }
}

const dismissMaintNotif = async (n) => {
  try { await apiFetch(`${config.public.apiBase}/notifications/${n.id}`, { method: 'DELETE' }) } catch {}
  maintNotifications.value = maintNotifications.value.filter(x => x.id !== n.id)
}

const markAllMaintNotifRead = async () => {
  const unread = maintNotifications.value.filter(n => !n.read_at)
  await Promise.all(unread.map(n =>
    apiFetch(`${config.public.apiBase}/notifications/${n.id}/read`, { method: 'PATCH' }).catch(() => {})
  ))
  maintNotifications.value.forEach(n => { if (!n.read_at) n.read_at = new Date().toISOString() })
}

const maintNotifIcon = (type) => ({
  mt_ticket_created:   '🔧',
  mt_ticket_completed: '✅',
  mt_status_changed:   '🔄',
  mt_ticket_assigned:  '👤',
  mt_ticket_overdue:   '⏰',
}[type] ?? '🔔')

const maintNotifIconClass = (type) => ({
  mt_ticket_created:   'notif-icon--created',
  mt_ticket_completed: 'notif-icon--done',
  mt_status_changed:   'notif-icon--qa',
  mt_ticket_assigned:  'notif-icon--assigned',
  mt_ticket_overdue:   'notif-icon--blocked',
}[type] ?? '')

const timeAgo = (dateStr) => {
  if (!dateStr) return ''
  const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000)
  if (diff < 60)   return 'just now'
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`
  if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`
  return `${Math.floor(diff / 86400)}d ago`
}

// ── Lifecycle ─────────────────────────────────────────────────────────────────
const profileClickHandler = (e) => {
  if (profileDropdownRef.value && !profileDropdownRef.value.contains(e.target)) {
    profileDropdownOpen.value = false
  }
  if (maintNotifDropdownRef.value && !maintNotifDropdownRef.value.contains(e.target)) {
    maintNotifDropdownOpen.value = false
  }
  if (openProjectMenuId.value !== null) {
    openProjectMenuId.value = null
  }
}

onMounted(async () => {
  const stored = localStorage.getItem('auth_token')
  if (stored) authToken.value = stored
  if (authToken.value) await fetchCurrentUser()

  document.addEventListener('click', profileClickHandler)

  await fetchProjects()

  // Auto-select project when coming back from a ticket view
  const projectId = route.query.project
  if (projectId) {
    const match = projects.value.find(p => String(p.id) === String(projectId))
    if (match) selectProject(match)
  }

  // Start notification polling
  fetchMaintNotifications()
  maintNotifPollTimer = setInterval(fetchMaintNotifications, 15000)
})

onUnmounted(() => {
  document.removeEventListener('click', profileClickHandler)
  clearInterval(maintNotifPollTimer)
})
</script>

<style scoped>
/* ── Maintenance-specific styles ── */
.maint-pipeline-table {
  width: 100%;
  border-collapse: collapse;
}
.maint-pipeline-table th {
  padding: 10px 14px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .5px;
  color: var(--gray-500);
  background: var(--gray-50);
  border-bottom: 1px solid var(--gray-200);
  white-space: nowrap;
  text-align: left;
}
.maint-pipeline-table td {
  padding: 10px 14px;
  font-size: 13px;
  border-bottom: 1px solid var(--gray-100);
  vertical-align: middle;
}

.maint-truncate {
  display: block;
  max-width: 180px;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
  font-size: 13px;
  color: var(--gray-700);
}

/* Age badge + bar */
.maint-age-wrap {
  display: flex;
  flex-direction: column;
  gap: 3px;
  align-items: flex-start;
}
.maint-age-bar-bg {
  width: 64px;
  height: 4px;
  background: var(--gray-200);
  border-radius: 99px;
  overflow: hidden;
}
.maint-age-bar-fill {
  height: 100%;
  border-radius: 99px;
  transition: width 0.3s ease;
}
.maint-overdue-flag {
  font-size: 10px;
  font-weight: 700;
  color: #ef4444;
  background: #fef2f2;
  padding: 1px 6px;
  border-radius: 99px;
  white-space: nowrap;
}

/* Overdue row tint */
.maint-row-overdue td {
  background: #fff8f8 !important;
}

/* Completed row gray-out */
.maint-row-completed td {
  background: #f8fafc !important;
  opacity: 0.55;
}

/* Email tags in table */
.maint-email-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 3px;
}
.maint-email-tag {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 26px;
  height: 26px;
  border-radius: 50%;
  font-size: 10px;
  font-weight: 700;
  cursor: default;
}
.dev-tag {
  background: #ede9fe;
  color: #6d28d9;
  border: 1px solid #c4b5fd;
}
.qa-tag {
  background: #ecfdf5;
  color: #059669;
  border: 1px solid #6ee7b7;
}

/* Email tag pills (in forms) */
.email-tag-input {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  min-height: 40px;
  padding: 6px 10px;
  border: 1px solid var(--gray-200);
  border-radius: var(--radius);
  background: var(--white);
  cursor: text;
  align-items: center;
  transition: border-color 0.2s;
}
.email-tag-input:focus-within {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(99,102,241,.12);
}
.email-tag-inner-input {
  border: none;
  outline: none;
  background: transparent;
  font-size: 13px;
  min-width: 140px;
  flex: 1;
  color: var(--gray-800);
}
.maint-email-tag-pill {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  font-weight: 500;
  padding: 2px 8px 2px 10px;
  border-radius: 99px;
  white-space: nowrap;
}
.maint-email-tag-pill button {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 14px;
  line-height: 1;
  padding: 0;
  color: inherit;
  opacity: 0.6;
  margin-left: 2px;
}
.maint-email-tag-pill button:hover { opacity: 1; }
.dev-pill { background: #ede9fe; color: #6d28d9; border: 1px solid #c4b5fd; }
.qa-pill  { background: #ecfdf5; color: #059669; border: 1px solid #6ee7b7; }
.maint-email-pills-list { display: flex; flex-wrap: wrap; gap: 4px; }

/* Ellipsis project menu */
.proj-menu-wrap {
  position: relative;
}
.proj-menu-btn {
  background: transparent;
  border: 1px solid transparent;
  color: var(--gray-400);
}
.proj-menu-btn:hover {
  background: var(--gray-100);
  border-color: var(--gray-200);
  color: var(--gray-600);
}
.proj-menu-dropdown {
  position: absolute;
  top: calc(100% + 4px);
  right: 0;
  z-index: 200;
  background: #fff;
  border: 1px solid var(--gray-200);
  border-radius: 8px;
  box-shadow: 0 4px 16px rgba(0,0,0,.1);
  min-width: 120px;
  padding: 4px;
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.proj-menu-item {
  display: flex;
  align-items: center;
  gap: 8px;
  width: 100%;
  padding: 7px 10px;
  border: none;
  background: none;
  border-radius: 6px;
  font-size: 13px;
  color: var(--gray-700);
  cursor: pointer;
  text-align: left;
  white-space: nowrap;
}
.proj-menu-item:hover {
  background: var(--gray-100);
}
.proj-menu-item-delete {
  color: #dc2626;
}
.proj-menu-item-delete:hover {
  background: #fef2f2;
}

/* Actions column */
.maint-actions {
  display: flex;
  gap: 4px;
  align-items: center;
}

/* Notify button states */
.action-btn-notify {
  background: #f0fdf4;
  border: 1px solid #d1fae5;
  color: #059669;
}
.action-btn-notify:hover { background: #dcfce7; }
.action-btn-notify-sent {
  background: #059669;
  border: 1px solid #047857;
  color: white;
}
.action-btn-notify-sent:hover { background: #047857; }
.action-btn-view {
  background: var(--primary-light);
  border: 1px solid #c7d2fe;
  color: var(--primary);
}
.action-btn-view:hover { background: #e0e7ff; }

/* View modal fields */
.maint-view-field { margin-bottom: 12px; }
.maint-view-label {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .5px;
  color: var(--gray-400);
  margin-bottom: 4px;
}
.maint-view-value { font-size: 14px; color: var(--gray-800); }

/* Sent-thru custom dropdown */
.sent-thru-dropdown {
  position: absolute;
  top: calc(100% + 2px);
  left: 0;
  right: 0;
  background: var(--white);
  border: 1px solid var(--gray-200);
  border-radius: var(--radius);
  box-shadow: var(--shadow-md);
  z-index: 999;
  overflow: hidden;
}
.sent-thru-option {
  padding: 8px 12px;
  font-size: 13px;
  color: var(--gray-700);
  cursor: pointer;
}
.sent-thru-option:hover {
  background: var(--primary-light);
  color: var(--primary);
}

/* Sent-thru badges */
.badge-sent-email   { background:#eff6ff; color:#1d4ed8; }
.badge-sent-slack   { background:#fef3c7; color:#92400e; }
.badge-sent-phone   { background:#f0fdf4; color:#166534; }
.badge-sent-teams   { background:#f5f3ff; color:#6d28d9; }
.badge-sent-inperson{ background:#fdf2f8; color:#9d174d; }
.badge-sent-other   { background:#f8fafc; color:#475569; }

/* Status badge extras */
.badge-hold         { background:#fff7ed; color:#c2410c; }
.badge-out-of-scope { background:#f8fafc; color:#475569; }

/* Inline status select — looks like a badge */
.inline-status-select {
  border: none;
  outline: none;
  cursor: pointer;
  appearance: none;
  -webkit-appearance: none;
  font-family: inherit;
  padding: 2px 8px;
  border-radius: 99px;
  font-weight: 600;
}

/* ── Attachment upload ── */
.attach-drop-zone {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 20px 16px;
  border: 2px dashed var(--gray-200);
  border-radius: var(--radius);
  background: var(--gray-50);
  cursor: default;
  transition: border-color 0.2s, background 0.2s;
  text-align: center;
}
.attach-drop-zone--active {
  border-color: var(--primary);
  background: var(--primary-light);
}
.attach-preview-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 10px;
}
.attach-preview-item {
  position: relative;
  width: 80px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}
.attach-thumb {
  width: 80px;
  height: 64px;
  object-fit: cover;
  border-radius: 6px;
  border: 1px solid var(--gray-200);
}
.attach-pdf-icon {
  width: 80px;
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #dc2626;
}
.attach-file-name {
  font-size: 10px;
  color: var(--gray-500);
  word-break: break-all;
  text-align: center;
  max-width: 80px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.attach-remove-btn {
  position: absolute;
  top: -6px;
  right: -6px;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: var(--danger);
  color: #fff;
  border: none;
  font-size: 13px;
  line-height: 1;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
}
.attach-remove-btn:hover { background: #b91c1c; }

/* Spin animation for notify loading */
.spin-icon {
  animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Maintenance Dashboard ─────────────────────────────────── */
.mdash-stat-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 14px;
  margin-bottom: 20px;
}
/* Project detail stat summary */
/* ── Project Banner ───────────────────────────────────────────────── */
.proj-banner {
  position: relative;
  background: white;
  border-radius: 14px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 2px 8px rgba(0,0,0,.05);
  overflow: hidden;
  margin-bottom: 16px;
}
.proj-banner-accent {
  position: absolute;
  left: 0; top: 0; bottom: 0;
  width: 5px;
  background: var(--proj-color, #10b981);
}
.proj-banner-body {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 18px 22px 18px 28px;
  gap: 16px;
}
.proj-banner-left { display: flex; align-items: center; gap: 16px; }
.proj-banner-icon {
  width: 44px; height: 44px;
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.proj-banner-title-row { display: flex; align-items: center; gap: 10px; margin-bottom: 6px; }
.proj-banner-name { font-size: 20px; font-weight: 800; color: #0f172a; letter-spacing: -.3px; margin: 0; }
.proj-banner-status-badge {
  font-size: 10px; font-weight: 700; padding: 2px 9px;
  border-radius: 99px; letter-spacing: .4px; text-transform: uppercase;
}
.badge-active   { background: #d1fae5; color: #059669; }
.badge-inactive { background: #fee2e2; color: #dc2626; }
.proj-banner-meta { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
.proj-banner-meta-item {
  display: inline-flex; align-items: center; gap: 4px;
  font-size: 12px; color: #64748b; font-weight: 500;
}
.proj-banner-meta-overdue { color: #ef4444; font-weight: 600; }
.proj-banner-right { flex-shrink: 0; }
.proj-banner-inactive-notice {
  font-size: 12px; color: #dc2626; background: #fee2e2;
  padding: 7px 14px; border-radius: 8px; font-weight: 600;
}
.btn-new-ticket {
  display: inline-flex; align-items: center; gap: 7px;
  background: linear-gradient(135deg, #059669, #047857);
  color: white; font-size: 13px; font-weight: 700;
  padding: 9px 18px; border-radius: 9px; border: none;
  cursor: pointer; box-shadow: 0 2px 8px rgba(5,150,105,.3);
  transition: opacity .15s, transform .15s;
}
.btn-new-ticket:hover { opacity: .9; transform: translateY(-1px); }

/* ── Stat Cards ────────────────────────────────────────────────────── */
.proj-stat-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 12px;
  margin-bottom: 16px;
}
.proj-stat-card {
  border-radius: 12px;
  padding: 14px 16px;
  border: 1px solid transparent;
}
.proj-stat-card-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
.proj-stat-label { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; }
.proj-stat-value { font-size: 28px; font-weight: 800; line-height: 1; margin-bottom: 4px; }
.proj-stat-sub   { font-size: 10.5px; font-weight: 500; opacity: .65; }
.proj-stat-icon  { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }

/* Total */
.proj-stat-total { background: #f8fafc; border-color: #e2e8f0; }
.proj-stat-total .proj-stat-label { color: #475569; }
.proj-stat-total .proj-stat-value { color: #0f172a; }
.proj-stat-icon-total { background: #e2e8f0; color: #475569; }

/* Pending */
.proj-stat-pending { background: #fffbeb; border-color: #fde68a; }
.proj-stat-pending .proj-stat-label { color: #b45309; }
.proj-stat-pending .proj-stat-value { color: #92400e; }
.proj-stat-pending .proj-stat-sub   { color: #b45309; }
.proj-stat-icon-pending { background: #fde68a; color: #b45309; }

/* In Progress */
.proj-stat-ongoing { background: #eff6ff; border-color: #bfdbfe; }
.proj-stat-ongoing .proj-stat-label { color: #1d4ed8; }
.proj-stat-ongoing .proj-stat-value { color: #1e3a8a; }
.proj-stat-ongoing .proj-stat-sub   { color: #1d4ed8; }
.proj-stat-icon-ongoing { background: #bfdbfe; color: #1d4ed8; }

/* On Hold */
.proj-stat-hold { background: #faf5ff; border-color: #ddd6fe; }
.proj-stat-hold .proj-stat-label { color: #6d28d9; }
.proj-stat-hold .proj-stat-value { color: #4c1d95; }
.proj-stat-hold .proj-stat-sub   { color: #6d28d9; }
.proj-stat-icon-hold { background: #ddd6fe; color: #6d28d9; }

/* Completed */
.proj-stat-done { background: #f0fdf4; border-color: #bbf7d0; }
.proj-stat-done .proj-stat-label { color: #15803d; }
.proj-stat-done .proj-stat-value { color: #14532d; }
.proj-stat-done .proj-stat-sub   { color: #15803d; }
.proj-stat-icon-done { background: #bbf7d0; color: #15803d; }

/* Overdue */
.proj-stat-overdue { background: #fff1f2; border-color: #fecdd3; }
.proj-stat-overdue .proj-stat-label { color: #be123c; }
.proj-stat-overdue .proj-stat-value { color: #9f1239; }
.proj-stat-overdue .proj-stat-sub   { color: #be123c; }
.proj-stat-icon-overdue { background: #fecdd3; color: #be123c; }

/* Completion % in Total card */
.proj-stat-pct-row { display: flex; align-items: center; gap: 7px; margin-top: 6px; }
.proj-stat-pct-bar-track { flex: 1; height: 5px; background: #cbd5e1; border-radius: 99px; overflow: hidden; }
.proj-stat-pct-bar { height: 100%; background: #22c55e; border-radius: 99px; transition: width .4s ease; }
.proj-stat-pct-label { font-size: 10px; font-weight: 700; color: #15803d; white-space: nowrap; }

/* ── Project Filters Bar ──────────────────────────────────────────── */
.proj-filters-bar {
  display: flex; gap: 10px; flex-wrap: wrap; align-items: center;
  background: white; border: 1px solid #e2e8f0; border-radius: 10px;
  padding: 10px 14px; margin-bottom: 16px;
  box-shadow: 0 1px 3px rgba(0,0,0,.04);
}
.proj-filters-bar .form-control { background: #f8fafc; border-color: #e2e8f0; }



.mdash-stat-card {
  background: white;
  border-radius: 12px;
  padding: 18px 20px;
  box-shadow: 0 1px 4px rgba(0,0,0,.07);
  border: 1px solid #f1f5f9;
  border-top: 3px solid #cbd5e1;
}
.mdash-pending  { border-top-color: #f59e0b; }
.mdash-ongoing  { border-top-color: #3b82f6; }
.mdash-done     { border-top-color: #22c55e; }
.mdash-overdue  { border-top-color: #ef4444; }
.mdash-stat-top   { font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 6px; }
.mdash-stat-value { font-size: 32px; font-weight: 800; color: #0f172a; line-height: 1; margin-bottom: 4px; }
.mdash-stat-sub   { font-size: 11.5px; color: #94a3b8; }

.mdash-mid-row {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 16px;
  margin-bottom: 16px;
}
.mdash-bot-row {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 16px;
}
.mdash-panel {
  background: white;
  border-radius: 12px;
  padding: 20px 22px;
  box-shadow: 0 1px 4px rgba(0,0,0,.07);
  border: 1px solid #f1f5f9;
}
.mdash-panel-center { display: flex; flex-direction: column; align-items: center; }
.mdash-panel-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; }
.mdash-panel-title  { font-size: 13px; font-weight: 700; color: #1e293b; }
.mdash-panel-meta   { font-size: 11px; color: #94a3b8; font-weight: 500; }
.mdash-badge-red    { background: #fef2f2; color: #b91c1c; font-size: 11px; font-weight: 700; padding: 2px 10px; border-radius: 99px; }
.mdash-empty        { color: #cbd5e1; font-size: 13px; text-align: center; padding: 20px 0; }

/* Client rows */
.mdash-client-rows { display: flex; flex-direction: column; gap: 10px; }
.mdash-client-row  { display: flex; align-items: center; gap: 10px; }
.mdash-client-name { font-size: 12.5px; color: #334155; width: 100px; flex-shrink: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.mdash-client-bar-track { flex: 1; height: 7px; background: #f1f5f9; border-radius: 99px; overflow: hidden; }
.mdash-client-bar  { height: 100%; border-radius: 99px; }
.mdash-client-count { font-size: 12px; font-weight: 700; color: #64748b; width: 20px; text-align: right; }

/* Status donut */
.mdash-donut { width: 120px; height: 120px; margin: 4px 0; }
.mdash-donut-legend { display: flex; flex-direction: column; gap: 5px; margin-top: 12px; width: 100%; }
.mdash-dl-row { display: flex; align-items: center; gap: 7px; font-size: 12px; color: #475569; }
.mdash-dl-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }

/* Dev workload */
.mdash-dev-rows  { display: flex; flex-direction: column; gap: 12px; }
.mdash-dev-row   { display: flex; align-items: center; gap: 10px; }
.mdash-dev-avatar { width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: white; flex-shrink: 0; }
.mdash-dev-name   { font-size: 12.5px; color: #334155; flex: 1; font-weight: 500; }
.mdash-dev-tickets { font-size: 11.5px; color: #94a3b8; white-space: nowrap; margin-right: 6px; }
.mdash-dev-bar-track { width: 60px; height: 5px; background: #f1f5f9; border-radius: 99px; overflow: hidden; }
.mdash-dev-bar { height: 100%; border-radius: 99px; }

/* Overdue & at-risk */
.mdash-risk-rows { display: flex; flex-direction: column; gap: 12px; }
.mdash-risk-row  { display: flex; align-items: flex-start; gap: 10px; }
.mdash-risk-badge { font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 99px; white-space: nowrap; margin-top: 2px; }
.mdash-risk-info {}
.mdash-risk-title { font-size: 13px; color: #1e293b; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px; }
.mdash-risk-meta  { font-size: 11.5px; color: #94a3b8; margin-top: 2px; }

/* Due this week */
.mdash-due-rows { display: flex; flex-direction: column; gap: 12px; }
.mdash-due-row  { display: flex; align-items: center; gap: 10px; justify-content: space-between; }
.mdash-due-info {}
.mdash-due-title { font-size: 13px; color: #1e293b; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px; }
.mdash-due-meta  { font-size: 11.5px; color: #94a3b8; margin-top: 2px; }
.mdash-status-badge { font-size: 11px; font-weight: 700; padding: 2px 10px; border-radius: 99px; white-space: nowrap; }

/* Recent activity */
.mdash-activity-rows { display: flex; flex-direction: column; gap: 12px; }
.mdash-activity-row  { display: flex; align-items: flex-start; gap: 10px; }
.mdash-activity-dot  { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; margin-top: 4px; }
.mdash-activity-text { font-size: 12.5px; color: #334155; }
.mdash-activity-time { font-size: 11px; color: #94a3b8; margin-top: 2px; }

/* Channel breakdown */
.mdash-channel-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px 24px; }
.mdash-channel-row  { display: flex; align-items: center; gap: 8px; }
.mdash-channel-name { font-size: 12.5px; color: #334155; width: 70px; flex-shrink: 0; }
.mdash-channel-bar-track { flex: 1; height: 6px; background: #f1f5f9; border-radius: 99px; overflow: hidden; }
.mdash-channel-bar { height: 100%; border-radius: 99px; }
.mdash-channel-count { font-size: 12px; font-weight: 700; color: #64748b; }

@media (max-width: 1100px) {
  .mdash-stat-grid { grid-template-columns: repeat(3, 1fr); }
  .mdash-mid-row, .mdash-bot-row { grid-template-columns: 1fr; }
  .mdash-channel-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
