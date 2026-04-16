<template>
  <!-- Bug Squish Celebration -->
  <Teleport to="body">
    <Transition name="squish-fade">
      <div v-if="showSquishAnim" class="squish-overlay">
        <div class="squish-scene">
          <div class="squish-foot">🦶</div>
          <div class="squish-bug">🐛</div>
          <div class="squish-splat">💥</div>
          <div class="squish-stars">
            <span>⭐</span><span>✨</span><span>⭐</span><span>✨</span><span>⭐</span>
          </div>
          <div class="squish-label">Bug Squashed! <span class="squish-check">✓</span></div>
        </div>
      </div>
    </Transition>
  </Teleport>

  <div class="app-layout">

    <!-- ── Header ── -->
    <header class="app-header">
      <div class="app-header-inner">
        <div class="app-logo">
          <div class="app-logo-icon">🐛</div>
          <div>
            <div>QA Bug Tracker</div>
            <span class="app-logo-subtitle">Quality Assurance Dashboard</span>
          </div>
        </div>
        <div style="display:flex;align-items:center;gap:10px;">
          <!-- Notification Bell -->
          <div class="notif-bell-wrap" ref="notifDropdownRef" @click.stop="toggleNotifDropdown">
            <button class="notif-bell-btn" :class="{ 'notif-bell-btn--active': notifDropdownOpen }">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
              </svg>
              <span v-if="notifUnreadCount > 0" class="notif-badge">{{ notifUnreadCount > 99 ? '99+' : notifUnreadCount }}</span>
            </button>

            <!-- Dropdown -->
            <transition name="notif-drop">
              <div v-if="notifDropdownOpen" class="notif-dropdown" @click.stop>
                <div class="notif-dropdown-header">
                  <span class="notif-dropdown-title">Notifications</span>
                  <button v-if="notifUnreadCount > 0" class="notif-read-all-btn" @click="markAllNotifRead">Mark all read</button>
                </div>
                <div class="notif-list">
                  <div v-if="!notifications.length" class="notif-empty">No notifications yet</div>
                  <div
                    v-for="n in notifications"
                    :key="n.id"
                    class="notif-item"
                    :class="{ 'notif-item--unread': !n.read_at }"
                    @click="openNotif(n)"
                  >
                    <div class="notif-item-icon" :class="notifIconClass(n.type)">{{ notifIcon(n.type) }}</div>
                    <div class="notif-item-body">
                      <div class="notif-item-title">{{ n.title }}</div>
                      <div class="notif-item-msg">{{ n.message }}</div>
                      <div class="notif-item-time">{{ timeAgo(n.created_at) }}</div>
                    </div>
                    <div class="notif-item-right">
                      <div v-if="!n.read_at" class="notif-item-dot"></div>
                      <button class="notif-item-dismiss" title="Remove" @click.stop="dismissNotif(n)">
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
        </div>
      </div>
    </header>

    <!-- ── Body ── -->
    <div class="app-body">

      <!-- ── Sidebar ── -->
      <aside class="sidebar">
        <div class="sidebar-section-label">DB Projects</div>
        <nav class="sidebar-nav">
          <button class="sidebar-item" :class="{ active: !selectedProject && !ticketTrackerOpen && !dashboardOpen && !devFoldersViewOpen && !inactiveOpen }" @click="selectProject(null); ticketTrackerOpen = false; dashboardOpen = false; devFoldersViewOpen = false; inactiveOpen = false; projectStatusTab = 'all'">
            <span class="sidebar-item-icon">🏠</span>
            <span class="sidebar-item-name">All Projects</span>
            <span class="sidebar-item-count">{{ activeProjects.length }}</span>
          </button>
        </nav>
        <div v-if="activeProjects.length" class="sidebar-section-label" style="margin-top:10px;">Projects</div>
        <nav v-if="activeProjects.length" class="sidebar-nav">
          <button
            v-for="p in activeProjects" :key="p.id"
            class="sidebar-item" :class="{ active: selectedProject?.id === p.id && !ticketTrackerOpen && !dashboardOpen && !devFoldersViewOpen }"
            @click="selectProject(p); ticketTrackerOpen = false; dashboardOpen = false; devFoldersViewOpen = false; inactiveOpen = false"
          >
            <span class="sidebar-color-dot" :style="{ background: p.color }"></span>
            <span class="sidebar-item-name">{{ p.name }}</span>
            <span class="sidebar-item-count">{{ p.bugs_count }}</span>
          </button>
        </nav>
        <div class="sidebar-divider" style="margin:8px 0 4px;"></div>
        <div class="sidebar-section-label" style="margin-top:4px;">Tools</div>
        <nav class="sidebar-nav">
          <button class="sidebar-item" :class="{ active: dashboardOpen }" @click="openDashboard()">
            <span class="sidebar-item-icon">📊</span>
            <span class="sidebar-item-name">Dashboard</span>
          </button>
          <button class="sidebar-item" :class="{ active: ticketTrackerOpen }" @click="openTicketTracker(); dashboardOpen = false">
            <span class="sidebar-item-icon">🎫</span>
            <span class="sidebar-item-name">Ticket Tracker</span>
            <span v-if="allTickets.length" class="sidebar-item-count">{{ allTickets.length }}</span>
          </button>
          <button class="sidebar-item" :class="{ active: devFoldersViewOpen }" @click="openDevFoldersView">
            <span class="sidebar-item-icon">📁</span>
            <span class="sidebar-item-name">Dev Folders</span>
            <span v-if="devFolders.length" class="sidebar-item-count">{{ devFolders.length }}</span>
          </button>
          <NuxtLink to="/maintenance" class="sidebar-item" style="text-decoration:none;color:inherit;">
            <span class="sidebar-item-icon">🔧</span>
            <span class="sidebar-item-name">Maintenance</span>
          </NuxtLink>
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

        <!-- ══ All Projects view ══ -->
        <div v-if="!selectedProject && !ticketTrackerOpen && !dashboardOpen && !devFoldersViewOpen">
          <div class="view-header">
            <div>
              <h1 class="view-title">All Projects</h1>
              <p class="view-subtitle">{{ activeProjects.length }} active{{ inactiveProjects.length ? `, ${inactiveProjects.length} inactive` : '' }}</p>
            </div>
            <button class="btn btn-primary" @click="openProjectModal(null)">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
              New Project
            </button>
          </div>

          <div class="filters-bar" style="margin-bottom:20px;">
            <div class="search-input-wrap">
              <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
              <input v-model="projectSearch" class="form-control" placeholder="Search projects..." />
            </div>
            <div class="project-status-tabs">
              <button :class="['project-status-tab', { active: projectStatusTab === 'all' }]" @click="projectStatusTab = 'all'">All</button>
              <button :class="['project-status-tab', { active: projectStatusTab === 'active' }]" @click="projectStatusTab = 'active'">Active</button>
              <button :class="['project-status-tab', { active: projectStatusTab === 'inactive' }]" @click="projectStatusTab = 'inactive'">Inactive</button>
            </div>
            <button v-if="projectSearch" class="btn btn-ghost btn-sm" @click="projectSearch = ''; projectFilter = ''">
              <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
              Clear
            </button>
          </div>

          <div v-if="projects.length > 0">
            <!-- Active projects -->
            <div v-if="filteredActiveProjects.length && projectStatusTab !== 'inactive'">
              <div style="font-size:11px;font-weight:700;letter-spacing:.8px;color:var(--gray-400);text-transform:uppercase;margin-bottom:10px;">Active</div>
              <div class="projects-grid" style="margin-bottom:24px;">
                <div v-for="p in filteredActiveProjects" :key="p.id" class="project-card" @click="selectProject(p)">
                  <div class="project-card-stripe" :style="{ background: p.color }"></div>
                  <div class="project-card-body">
                    <div class="project-card-icon" :style="{ background: p.color + '22', color: p.color }">📁</div>
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;">
                      <h3 class="project-card-name" style="margin:0;">{{ p.name }}</h3>
                      <span style="font-size:10px;font-weight:600;padding:2px 8px;border-radius:10px;letter-spacing:.4px;background:#e0e7ff;color:#4338ca;">ACTIVE</span>
                    </div>
                    <p v-if="p.description" class="project-card-desc">{{ p.description }}</p>
                    <div class="project-card-stats">
                      <span class="pstat pstat-total">{{ p.bugs_count }} bugs</span>
                      <span v-if="p.pending_count" class="pstat pstat-pending">{{ p.pending_count }} pending</span>
                      <span v-if="p.ongoing_count" class="pstat pstat-ongoing">{{ p.ongoing_count }} ongoing</span>
                      <span v-if="p.completed_count" class="pstat pstat-done">{{ p.completed_count }} done</span>
                      <span v-if="p.critical_count" class="pstat pstat-critical">{{ p.critical_count }} critical</span>
                    </div>
                  </div>
                  <div class="project-card-actions" @click.stop>
                    <button class="btn btn-icon action-btn-share" title="Share project" @click="currentUser ? openShareModal(p) : login()">
                      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                    </button>
                    <button v-if="canEditProject(p)" class="btn btn-icon action-btn-edit" @click="openProjectModal(p)">
                      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button v-if="canEditProject(p)" class="btn btn-icon action-btn-delete" @click="confirmDeleteProject(p)">
                      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Inactive projects -->
            <div v-if="filteredInactiveProjects.length && projectStatusTab !== 'active'">
              <div style="font-size:11px;font-weight:700;letter-spacing:.8px;color:var(--gray-400);text-transform:uppercase;margin-bottom:10px;">Inactive</div>
              <div class="projects-grid">
                <div v-for="p in filteredInactiveProjects" :key="p.id" class="project-card" style="opacity:.75;" @click="selectProject(p)">
                  <div class="project-card-stripe" :style="{ background: '#d1d5db' }"></div>
                  <div class="project-card-body">
                    <div class="project-card-icon" style="background:#f3f4f6;color:#9ca3af;">📁</div>
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;">
                      <h3 class="project-card-name" style="margin:0;color:var(--gray-500);">{{ p.name }}</h3>
                      <span style="font-size:10px;font-weight:600;padding:2px 8px;border-radius:10px;letter-spacing:.4px;background:#fee2e2;color:#dc2626;">INACTIVE</span>
                    </div>
                    <p v-if="p.description" class="project-card-desc">{{ p.description }}</p>
                    <div class="project-card-stats">
                      <span class="pstat pstat-total">{{ p.bugs_count }} bugs</span>
                      <span v-if="p.pending_count" class="pstat pstat-pending">{{ p.pending_count }} pending</span>
                      <span v-if="p.completed_count" class="pstat pstat-done">{{ p.completed_count }} done</span>
                    </div>
                  </div>
                  <div class="project-card-actions" @click.stop>
                    <button v-if="canEditProject(p)" class="btn btn-icon action-btn-edit" @click="openProjectModal(p)">
                      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button v-if="canEditProject(p)" class="btn btn-icon action-btn-delete" @click="confirmDeleteProject(p)">
                      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-else class="empty-state" style="margin-top:60px;">
            <div class="empty-state-icon">📁</div>
            <div class="empty-state-title">No projects yet</div>
            <div class="empty-state-text">Create your first project to start tracking bugs</div>
            <button class="btn btn-primary" style="margin-top:20px;" @click="openProjectModal(null)">+ Create Project</button>
          </div>
        </div>


        <!-- ══ Dashboard view ══ -->
        <div v-else-if="dashboardOpen" :key="dashboardKey">
          <div class="view-header" style="margin-bottom:24px;">
            <div>
              <h1 class="view-title">Dashboard</h1>
              <p class="view-subtitle">Overall QA metrics across all projects</p>
            </div>
          </div>

          <!-- Stat cards -->
          <div class="dash-grid" style="margin-bottom:20px;">
            <div class="dash-card dash-card-projects">
              <div class="dash-card-icon">📁</div>
              <div class="dash-card-body">
                <div class="dash-card-value">{{ overallStats.totalProjects }}</div>
                <div class="dash-card-label">Total Projects</div>
              </div>
            </div>
            <div class="dash-card dash-card-bugs">
              <div class="dash-card-icon">🐛</div>
              <div class="dash-card-body">
                <div class="dash-card-value">{{ overallStats.total }}</div>
                <div class="dash-card-label">Total Bugs</div>
              </div>
            </div>
            <div class="dash-card dash-card-critical">
              <div class="dash-card-icon">🔥</div>
              <div class="dash-card-body">
                <div class="dash-card-value">{{ overallStats.critical }}</div>
                <div class="dash-card-label">Critical</div>
              </div>
            </div>
            <div class="dash-card dash-card-pending">
              <div class="dash-card-icon">⏳</div>
              <div class="dash-card-body">
                <div class="dash-card-value">{{ overallStats.pending }}</div>
                <div class="dash-card-label">Pending</div>
              </div>
            </div>
            <div class="dash-card dash-card-ongoing">
              <div class="dash-card-icon">⚡</div>
              <div class="dash-card-body">
                <div class="dash-card-value">{{ overallStats.ongoing }}</div>
                <div class="dash-card-label">Ongoing</div>
              </div>
            </div>
            <div class="dash-card dash-card-done">
              <div class="dash-card-icon">✅</div>
              <div class="dash-card-body">
                <div class="dash-card-value">{{ overallStats.completed }}</div>
                <div class="dash-card-label">Completed</div>
              </div>
            </div>
          </div>

          <!-- Resolution bar + Per-project breakdown -->
          <div class="dash-two-col">

            <!-- Left: resolution rate -->
            <div class="dash-panel">
              <div class="dash-panel-title">Overall Resolution Rate</div>
              <div class="dash-rate-circle-wrap">
                <svg viewBox="0 0 120 120" class="dash-donut">
                  <circle cx="60" cy="60" r="50" fill="none" stroke="#f1f5f9" stroke-width="14"/>
                  <circle cx="60" cy="60" r="50" fill="none" stroke="#22c55e" stroke-width="14"
                    stroke-dasharray="314"
                    :stroke-dashoffset="314 - (314 * overallStats.rate / 100)"
                    stroke-linecap="round"
                    transform="rotate(-90 60 60)"
                    style="transition:stroke-dashoffset .5s ease;"
                  />
                  <text x="60" y="56" text-anchor="middle" font-size="22" font-weight="800" fill="#1e293b">{{ overallStats.rate }}%</text>
                  <text x="60" y="72" text-anchor="middle" font-size="10" fill="#94a3b8">resolved</text>
                </svg>
              </div>
              <div class="dash-progress-track" style="margin-top:16px;">
                <div class="dash-progress-fill dash-fill-done"    :style="{ width: (overallStats.total > 0 ? overallStats.completed / overallStats.total * 100 : 0) + '%' }"></div>
                <div class="dash-progress-fill dash-fill-ongoing" :style="{ width: (overallStats.total > 0 ? overallStats.ongoing   / overallStats.total * 100 : 0) + '%' }"></div>
                <div class="dash-progress-fill dash-fill-pending" :style="{ width: (overallStats.total > 0 ? overallStats.pending   / overallStats.total * 100 : 0) + '%' }"></div>
              </div>
              <div class="dash-legend" style="margin-top:12px;">
                <span class="dash-legend-dot" style="background:#22c55e;"></span><span>Completed ({{ overallStats.completed }})</span>
                <span class="dash-legend-dot" style="background:#3b82f6;"></span><span>Ongoing ({{ overallStats.ongoing }})</span>
                <span class="dash-legend-dot" style="background:#f59e0b;"></span><span>Pending ({{ overallStats.pending }})</span>
                <span class="dash-legend-dot" style="background:#ef4444;"></span><span>Critical ({{ overallStats.critical }})</span>
              </div>
            </div>

            <!-- Right: per-project breakdown -->
            <div class="dash-panel">
              <div class="dash-panel-title">Bugs by Project</div>
              <div v-if="projects.length === 0" class="dash-empty">No projects yet</div>
              <div v-else class="dash-project-rows">
                <div v-for="p in projects" :key="p.id" class="dash-proj-row">
                  <div class="dash-proj-row-header">
                    <span class="dash-proj-dot" :style="{ background: p.color }"></span>
                    <span class="dash-proj-name">{{ p.name }}</span>
                    <span class="dash-proj-total">{{ p.bugs_count }}</span>
                  </div>
                  <div class="dash-proj-bar-track">
                    <div class="dash-proj-bar-fill" :style="{ width: overallStats.total > 0 ? (p.bugs_count / overallStats.total * 100) + '%' : '0%', background: p.color }"></div>
                  </div>
                  <div class="dash-proj-pills">
                    <span v-if="p.critical_count" class="pstat pstat-critical">{{ p.critical_count }} critical</span>
                    <span v-if="p.pending_count"  class="pstat pstat-pending">{{ p.pending_count }} pending</span>
                    <span v-if="p.ongoing_count"  class="pstat pstat-ongoing">{{ p.ongoing_count }} ongoing</span>
                    <span v-if="p.completed_count" class="pstat pstat-done">{{ p.completed_count }} done</span>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- ══ Ticket Tracker view ══ -->
        <div v-else-if="ticketTrackerOpen" class="ticket-tracker-view">

          <!-- ── Inline Ticket Detail ── -->
          <div v-if="ttDetailBug" class="tt-detail-wrap">
            <!-- Back button -->
            <div class="tt-detail-back-bar">
              <button class="btn btn-ghost btn-sm" @click="ttDetailBug = null">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                Back to Ticket Tracker
              </button>
              <div style="display:flex;gap:8px;margin-left:auto;">
                <span :class="['badge', priorityBadgeClass(ttDetailBug.priority)]">{{ ttDetailBug.priority }}</span>
                <span :class="['badge', statusBadgeClass(ttDetailBug.status)]">{{ ttDetailBug.status }}</span>
              </div>
            </div>

            <div class="tt-detail-layout">
              <!-- Main panel -->
              <div class="tt-detail-main">
                <!-- Title -->
                <div class="tt-detail-title-block">
                  <span class="bug-seq" style="font-size:14px;flex-shrink:0;">#{{ ttDetailBug.sequence }}</span>
                  <h2 class="tt-detail-title">{{ ttDetailBug.title }}</h2>
                </div>

                <!-- Subtitles -->
                <div v-if="ttDetailBug.subtitles && ttDetailBug.subtitles.length" class="tt-detail-section">
                  <div class="tt-detail-section-label">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                    Subtitles
                  </div>
                  <div class="bug-view-subtitle-list">
                    <div v-for="(sub, idx) in ttDetailBug.subtitles" :key="idx" class="bug-view-subtitle-card">
                      <div class="bug-view-subtitle-index">{{ idx + 1 }}</div>
                      <div class="bug-view-subtitle-content">
                        <div v-if="(typeof sub === 'string' ? sub : sub.text)" class="bug-view-subtitle-text">{{ typeof sub === 'string' ? sub : sub.text }}</div>
                        <a v-if="sub.link" :href="sub.link" target="_blank" rel="noopener" class="bug-view-subtitle-link">
                          <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                          {{ sub.link }}
                        </a>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Description -->
                <div class="tt-detail-section">
                  <div class="tt-detail-section-label">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    Description
                  </div>
                  <div v-if="ttDetailBug.description" class="ticket-description" v-html="ttDetailBug.description"></div>
                  <div v-else class="bug-view-empty">No description provided.</div>
                </div>

                <!-- Dev Comments -->
                <div class="tt-detail-section">
                  <div class="tt-detail-section-label" style="justify-content:space-between;">
                    <span style="display:flex;align-items:center;gap:6px;">
                      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                      Comments
                    </span>
                    <span v-if="ttDetailBug.dev_comments?.length" class="bug-view-count-pill">{{ ttDetailBug.dev_comments.length }}</span>
                  </div>
                  <div v-if="ttDetailBug.dev_comments && ttDetailBug.dev_comments.length" class="thread-msg-list" style="gap:8px;">
                    <div v-for="(msg, i) in ttDetailBug.dev_comments" :key="i" class="thread-msg-item">
                      <div class="thread-msg-author-row">
                        <span class="thread-msg-avatar">{{ (msg.author || '?')[0].toUpperCase() }}</span>
                        <span class="thread-msg-author-name">{{ msg.author || 'Anonymous' }}</span>
                        <span class="thread-msg-time-inline">{{ formatThreadTime(msg.timestamp) }}</span>
                      </div>
                      <div class="thread-msg-bubble">{{ msg.message }}</div>
                    </div>
                  </div>
                  <div v-else class="bug-view-empty">No comments yet.</div>
                </div>

                <!-- Screenshots -->
                <div class="bug-view-screenshots-row">
                  <div class="tt-detail-section bug-view-screenshot-col">
                    <div class="tt-detail-section-label">
                      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                      <span>Screenshots — <span class="label-fe">Front-End</span></span>
                      <span v-if="ttDetailBug.frontend_images?.length" class="bug-view-count-pill">{{ ttDetailBug.frontend_images.length }}</span>
                    </div>
                    <div v-if="ttDetailBug.frontend_images && ttDetailBug.frontend_images.length" class="bug-view-images">
                      <a v-for="(img, idx) in ttDetailBug.frontend_images" :key="idx" :href="apiBase + img" target="_blank" class="bug-view-img-item">
                        <img :src="apiBase + img" :alt="`FE ${idx+1}`" />
                        <div class="bug-view-img-overlay">
                          <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                          <span>Open</span>
                        </div>
                      </a>
                    </div>
                    <div v-else class="bug-view-empty">No images attached.</div>
                  </div>
                  <div class="tt-detail-section bug-view-screenshot-col">
                    <div class="tt-detail-section-label">
                      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                      <span>Screenshots — <span class="label-cms">CMS</span></span>
                      <span v-if="ttDetailBug.cms_images?.length" class="bug-view-count-pill">{{ ttDetailBug.cms_images.length }}</span>
                    </div>
                    <div v-if="ttDetailBug.cms_images && ttDetailBug.cms_images.length" class="bug-view-images">
                      <a v-for="(img, idx) in ttDetailBug.cms_images" :key="idx" :href="apiBase + img" target="_blank" class="bug-view-img-item">
                        <img :src="apiBase + img" :alt="`CMS ${idx+1}`" />
                        <div class="bug-view-img-overlay">
                          <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                          <span>Open</span>
                        </div>
                      </a>
                    </div>
                    <div v-else class="bug-view-empty">No images attached.</div>
                  </div>
                </div>

                <!-- Activity -->
                <div class="tt-detail-section">
                  <div class="tt-detail-section-label">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    Activity
                    <span style="font-weight:400;color:var(--gray-400);font-size:12px;">({{ (ttDetailBug.activity_log || []).length }})</span>
                  </div>
                  <div class="ticket-activity-list" ref="ttActivityListEl">
                    <div v-if="!(ttDetailBug.activity_log || []).length" class="ticket-activity-empty">No activity yet.</div>
                    <div
                      v-for="(entry, i) in (ttDetailBug.activity_log || [])" :key="i"
                      class="ticket-activity-item"
                      :class="'ticket-activity-' + entry.type"
                    >
                      <div class="ticket-activity-icon">
                        <svg v-if="entry.type === 'comment'" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        <svg v-else-if="entry.type === 'ticket_sent'" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        <svg v-else-if="entry.type === 'resolved'" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        <svg v-else-if="entry.type === 'attachment'" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                        <svg v-else width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                      </div>
                      <div class="ticket-activity-body">
                        <div class="ticket-activity-header">
                          <span class="ticket-activity-author">{{ entry.user_name }}</span>
                          <span class="ticket-activity-time">{{ formatThreadTime(entry.timestamp) }}</span>
                        </div>
                        <div v-if="entry.type === 'comment'" class="ticket-activity-comment">{{ entry.content }}</div>
                        <div v-else class="ticket-activity-event">{{ entry.content }}</div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Compose -->
                <div class="tt-detail-section" :class="{ 'tt-detail-compose--locked': ttDetailBug.status === 'Completed' }">
                  <div v-if="ttDetailBug.status === 'Completed'" style="display:flex;align-items:center;gap:8px;color:#16a34a;font-size:13px;font-weight:500;padding:4px 0;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    This ticket has been resolved.
                  </div>
                  <template v-else>
                    <textarea
                      v-model="ttNewComment"
                      class="ticket-compose-textarea"
                      placeholder="Write a comment… (Ctrl+Enter to post)"
                      rows="3"
                      @keydown.ctrl.enter.prevent="ttPostComment"
                    ></textarea>
                    <div class="ticket-compose-footer">
                      <div class="ticket-author-input-wrap">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <input v-model="ttAuthorName" class="ticket-author-input" placeholder="Your name" />
                      </div>
                      <div style="display:flex;gap:8px;">
                        <button class="btn-ticket-resolve" @click="ttMarkResolved" :disabled="ttPosting">
                          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                          Mark as resolved
                        </button>
                        <button class="btn-ticket-comment" :disabled="!ttNewComment.trim() || ttPosting" @click="ttPostComment">
                          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                          {{ ttPosting ? 'Posting…' : 'Post comment' }}
                        </button>
                      </div>
                    </div>
                  </template>
                </div>
              </div>

              <!-- Sidebar -->
              <aside class="tt-detail-sidebar">
                <!-- Dev Status -->
                <div class="ticket-sidebar-card">
                  <div class="ticket-sidebar-label">Dev Status</div>
                  <select
                    v-model="ttCurrentDevStatus"
                    :class="['dev-status-select', 'dev-status--' + ttCurrentDevStatus.toLowerCase().replace(/\s+/g, '-')]"
                    @change="ttUpdateDevStatus"
                  >
                    <option value="Not Started">Not Started</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Ready for QA">Ready for QA ✅</option>
                    <option value="Blocked">Blocked ⚠️</option>
                  </select>
                  <div v-if="ttCurrentDevStatus === 'Ready for QA'" class="ticket-dev-status-notice ticket-dev-status-notice--ready" style="margin-top:8px;">
                    🔔 QA team notified to verify.
                  </div>
                  <div v-else-if="ttCurrentDevStatus === 'Blocked'" class="ticket-dev-status-notice ticket-dev-status-notice--blocked" style="margin-top:8px;">
                    ⚠️ Blocked — needs attention.
                  </div>
                </div>

                <!-- QA Status -->
                <div class="ticket-sidebar-card">
                  <div class="ticket-sidebar-label">QA Status</div>
                  <select
                    v-if="ttDetailBug.status !== 'Completed'"
                    v-model="ttCurrentStatus"
                    class="ticket-status-select"
                    :class="'ticket-status--' + ttCurrentStatus.toLowerCase().replace(/\s+/g, '-')"
                    @change="ttUpdateStatus"
                  >
                    <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
                  </select>
                  <span v-else class="badge badge-completed" style="font-size:13px;padding:6px 14px;">Completed</span>
                </div>

                <!-- Assigned Developers -->
                <div class="ticket-sidebar-card">
                  <div class="ticket-sidebar-label">Assigned Developer{{ (ttDetailBug.assigned_developers || []).length > 1 ? 's' : '' }}</div>
                  <div v-if="(ttDetailBug.assigned_developers || []).length" style="display:flex;flex-direction:column;gap:10px;">
                    <div v-for="dev in ttDetailBug.assigned_developers" :key="dev.email" class="ticket-sidebar-person">
                      <div class="ticket-sidebar-avatar">{{ dev.name?.[0]?.toUpperCase() }}</div>
                      <div>
                        <div class="ticket-sidebar-person-name">{{ dev.name }}</div>
                        <div class="ticket-sidebar-person-email">{{ dev.email }}</div>
                      </div>
                    </div>
                  </div>
                  <div v-else class="ticket-sidebar-empty">Not assigned</div>
                </div>

                <!-- Details -->
                <div class="ticket-sidebar-card">
                  <div class="ticket-sidebar-label">Details</div>
                  <div class="ticket-meta-list">
                    <div class="ticket-meta-row">
                      <span class="ticket-meta-key">Priority</span>
                      <span :class="['badge', priorityBadgeClass(ttDetailBug.priority)]">{{ ttDetailBug.priority }}</span>
                    </div>
                    <div class="ticket-meta-row">
                      <span class="ticket-meta-key">Scenario</span>
                      <span :class="['badge', scenarioBadgeClass(ttDetailBug.scenario_type)]">{{ ttDetailBug.scenario_type }}</span>
                    </div>
                    <div v-if="ttDetailBug.project" class="ticket-meta-row">
                      <span class="ticket-meta-key">Project</span>
                      <span style="font-size:13px;color:var(--gray-800);font-weight:500;display:flex;align-items:center;gap:6px;">
                        <span style="width:8px;height:8px;border-radius:50%;display:inline-block;" :style="{ background: ttDetailBug.project?.color || '#94a3b8' }"></span>
                        {{ ttDetailBug.project?.name }}
                      </span>
                    </div>
                    <div class="ticket-meta-row">
                      <span class="ticket-meta-key">Reported</span>
                      <span style="font-size:13px;color:var(--gray-800);">{{ formatBugDate(ttDetailBug.created_at) }}</span>
                    </div>
                    <div v-if="ttDetailBug.ticket_sent_at" class="ticket-meta-row">
                      <span class="ticket-meta-key">Sent</span>
                      <span style="font-size:13px;color:var(--gray-800);">{{ formatBugDate(ttDetailBug.ticket_sent_at) }}</span>
                    </div>
                    <div class="ticket-meta-row" style="align-items:flex-start;">
                      <span class="ticket-meta-key">Due Date</span>
                      <div v-if="ttDetailBug.date_to_accomplish" style="display:flex;flex-direction:column;gap:3px;">
                        <span :style="{ fontSize: '13px', color: isAccomplishOverdue(ttDetailBug) ? '#dc2626' : 'var(--gray-800)', fontWeight: isAccomplishOverdue(ttDetailBug) ? '600' : 'normal' }">{{ formatBugDate(ttDetailBug.date_to_accomplish) }}</span>
                        <span v-if="dueDaysLabel(ttDetailBug)" class="due-days-pill-sm" :style="{ color: dueDaysLabel(ttDetailBug).color, borderColor: dueDaysLabel(ttDetailBug).color + '40', background: dueDaysLabel(ttDetailBug).color + '12' }">{{ dueDaysLabel(ttDetailBug).text }}</span>
                      </div>
                      <span v-else style="font-size:13px;color:var(--gray-400);">—</span>
                    </div>
                    <div v-if="ttDetailBug.resolved_by" class="ticket-meta-row">
                      <span class="ticket-meta-key">Resolved By</span>
                      <span style="font-size:13px;color:var(--gray-800);">{{ ttDetailBug.resolved_by }}</span>
                    </div>
                  </div>
                </div>

                <!-- Activity counts -->
                <div class="ticket-sidebar-card">
                  <div class="ticket-sidebar-label">Activity Log</div>
                  <div class="ticket-meta-list">
                    <div class="ticket-meta-row">
                      <span class="ticket-meta-key">Comments</span>
                      <span style="font-size:13px;color:var(--gray-800);font-weight:600;">{{ (ttDetailBug.activity_log || []).filter(e => e.type === 'comment').length }}</span>
                    </div>
                    <div class="ticket-meta-row">
                      <span class="ticket-meta-key">Status changes</span>
                      <span style="font-size:13px;color:var(--gray-800);font-weight:600;">{{ (ttDetailBug.activity_log || []).filter(e => e.type === 'status_change').length }}</span>
                    </div>
                  </div>
                </div>
              </aside>
            </div>
          </div>

          <!-- ── Tracker List ── -->
          <template v-else>

          <!-- Header -->
          <div class="view-header" style="margin-bottom:0;">
            <div>
              <h1 class="view-title">Ticket Tracker</h1>
              <p class="view-subtitle">Monitor and manage bug tickets across all projects</p>
            </div>
            <button class="btn btn-ghost btn-sm" @click="fetchAllTickets(); fetchAllMaintenanceTickets()" title="Refresh">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
              Refresh
            </button>
          </div>

          <!-- Global search bar -->
          <div class="tt-global-search-wrap">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input v-model="ttGlobalSearch" class="tt-global-search" placeholder="Search by title, assignee, or ticket #…" />
            <button v-if="ttGlobalSearch" class="tt-search-clear" @click="ttGlobalSearch = ''" title="Clear">
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
          </div>

          <!-- Tab bar -->
          <div class="tt-tab-bar">
            <button :class="['tt-tab', { 'tt-tab--active': ticketTab === 'overview' }]" @click="ticketTab = 'overview'">
              Overview
            </button>
            <button :class="['tt-tab', { 'tt-tab--active': ticketTab === 'all' }]" @click="ticketTab = 'all'">
              All Tickets
              <span class="tt-tab-chip">{{ allTickets.length }}</span>
            </button>
            <button :class="['tt-tab', { 'tt-tab--active': ticketTab === 'maintenance' }]" @click="ticketTab = 'maintenance'">
              Maintenance
              <span v-if="allMaintenanceTickets.length" class="tt-tab-chip" style="background:#d1fae5;color:#065f46;">{{ allMaintenanceTickets.length }}</span>
            </button>
          </div>

          <!-- ── Overview Tab ── -->
          <div v-if="ticketTab === 'overview'" style="display:flex;flex-direction:column;gap:16px;">
            <!-- Stat cards -->
            <div class="tt-stat-grid">
              <div class="tt-stat-card">
                <div class="tt-stat-icon" style="background:#eff6ff;color:#1d4ed8;">🎫</div>
                <div class="tt-stat-label">Total Tickets</div>
                <div class="tt-stat-value">{{ allTickets.length }}</div>
              </div>
              <div class="tt-stat-card">
                <div class="tt-stat-icon" style="background:#f0fdf4;color:#16a34a;">✅</div>
                <div class="tt-stat-label">Resolved</div>
                <div class="tt-stat-value" style="color:#16a34a;">{{ resolvedTickets.length }}</div>
              </div>
              <div class="tt-stat-card">
                <div class="tt-stat-icon" style="background:#fefce8;color:#854d0e;">⚡</div>
                <div class="tt-stat-label">In Progress</div>
                <div class="tt-stat-value" style="color:#854d0e;">{{ inProgressTickets.length }}</div>
              </div>
              <div class="tt-stat-card">
                <div class="tt-stat-icon" style="background:#fef2f2;color:#dc2626;">⏳</div>
                <div class="tt-stat-label">Needs Action</div>
                <div class="tt-stat-value" style="color:#dc2626;">{{ pendingTickets.length }}</div>
              </div>
              <div class="tt-stat-card">
                <div class="tt-stat-icon" style="background:#fff1f2;color:#b91c1c;">🔴</div>
                <div class="tt-stat-label">Overdue</div>
                <div class="tt-stat-value" style="color:#b91c1c;">{{ overdueTickets.length }}</div>
              </div>
              <div class="tt-stat-card">
                <div class="tt-stat-icon" style="background:#f5f3ff;color:#6d28d9;">🕐</div>
                <div class="tt-stat-label">Avg Age</div>
                <div class="tt-stat-value" style="color:#6d28d9;font-size:22px;">{{ avgAge }}</div>
              </div>
            </div>

            <div class="tt-overview-grid">
              <!-- Ticket Pipeline Table -->
              <div class="card" style="grid-column:1/-1;">
                <div class="card-header">
                  <h3 class="card-title">Ticket Pipeline</h3>
                </div>
                <div class="card-body" style="padding:0;">
                  <table v-if="pipelineTickets.length">
                    <thead>
                      <tr>
                        <th style="width:56px;">#</th>
                        <th>Title</th>
                        <th style="width:120px;">Project</th>
                        <th style="width:90px;">Priority</th>
                        <th style="width:130px;">Assigned Dev</th>
                        <th style="width:120px;">Dev Status</th>
                        <th style="width:110px;">QA Status</th>
                        <th style="width:70px;">Age</th>
                        <th style="width:110px;">SLA</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="t in pipelineTickets" :key="t.id" class="clickable-row" :class="{ 'tt-overdue-row': isOverdue(t) }" @click="openTTDetail(t)">
                        <td>
                          <div style="display:flex;align-items:center;gap:5px;">
                            <span v-if="isOverdue(t)" class="tt-overdue-dot" title="Overdue"></span>
                            <span class="bug-seq">#{{ t.sequence }}</span>
                          </div>
                        </td>
                        <td>
                          <div class="bug-title-main bug-title-link" style="max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ t.title }}</div>
                        </td>
                        <td>
                          <span v-if="t.project" style="display:flex;align-items:center;gap:6px;font-size:12px;">
                            <span style="width:7px;height:7px;border-radius:50%;flex-shrink:0;" :style="{ background: t.project?.color || '#94a3b8' }"></span>
                            {{ t.project?.name }}
                          </span>
                        </td>
                        <td><span :class="['badge', priorityBadgeClass(t.priority)]">{{ t.priority }}</span></td>
                        <td>
                          <span v-if="t.assigned_developer" style="display:flex;align-items:center;gap:6px;font-size:12px;">
                            <span class="assign-avatar-xs">{{ t.assigned_developer.name?.[0]?.toUpperCase() }}</span>
                            {{ t.assigned_developer.name.split(' ')[0] }}
                          </span>
                          <span v-else style="color:var(--gray-300);font-size:12px;">Unassigned</span>
                        </td>
                        <td><span :class="['tt-dev-status-badge', 'tt-dev-status--' + (t.dev_status || 'Not Started').toLowerCase().replace(/\s+/g,'-')]">{{ t.dev_status || 'Not Started' }}</span></td>
                        <td><span :class="['badge', statusBadgeClass(t.status)]">{{ t.status }}</span></td>
                        <td><span :class="['age-badge', ageBadgeClass(t)]">{{ ageLabel(t) }}</span></td>
                        <td>
                          <div class="sla-bar-wrap" :title="`${slaProgress(t)}% of SLA used`">
                            <div class="sla-bar" :style="{ width: slaProgress(t) + '%', background: slaBarColor(slaProgress(t)) }"></div>
                          </div>
                          <div style="font-size:10px;color:var(--gray-400);margin-top:2px;text-align:center;">{{ slaProgress(t) }}%</div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  <div v-else class="empty-state" style="padding:40px;">
                    <div class="empty-state-icon">🎫</div>
                    <div class="empty-state-title">No tickets yet</div>
                    <div class="empty-state-text">Assign developers to bugs to start tracking tickets</div>
                  </div>
                </div>
              </div>

              <!-- Maintenance Pipeline Table -->
              <div class="card" style="grid-column:1/-1;">
                <div class="card-header">
                  <div style="display:flex;align-items:center;gap:8px;">
                    <span style="font-size:16px;">🔧</span>
                    <h3 class="card-title">Maintenance Pipeline</h3>
                  </div>
                  <span class="result-chip" style="background:#ecfdf5;color:#065f46;">{{ allMaintenanceTickets.filter(t => t.status !== 'Completed').length }} ticket{{ allMaintenanceTickets.filter(t => t.status !== 'Completed').length !== 1 ? 's' : '' }}</span>
                </div>
                <div class="card-body" style="padding:0;">
                  <table v-if="allMaintenanceTickets.filter(t => t.status !== 'Completed').length">
                    <thead>
                      <tr>
                        <th style="width:80px;">Ticket #</th>
                        <th style="width:130px;">Project</th>
                        <th style="width:120px;">Client</th>
                        <th>Request</th>
                        <th style="width:90px;">Sent Thru</th>
                        <th style="width:100px;">Target</th>
                        <th style="width:130px;">Dev(s)</th>
                        <th style="width:120px;">Dev Status</th>
                        <th style="width:110px;">Status</th>
                        <th style="width:65px;">Age</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="t in allMaintenanceTickets.filter(t => t.status !== 'Completed')" :key="'mto-' + t.id"
                          class="clickable-row"
                          :class="{ 'tt-overdue-row': mtIsOverdue(t) }"
                          @click="navigateTo(`/maintenance-ticket/${t.id}?from=dashboard`)">
                        <td>
                          <span class="bug-seq" style="font-size:11px;background:#ecfdf5;color:#059669;">{{ t.ticket_number }}</span>
                        </td>
                        <td>
                          <span v-if="t._project" style="display:flex;align-items:center;gap:6px;font-size:12px;">
                            <span style="width:7px;height:7px;border-radius:50%;flex-shrink:0;" :style="{ background: t._project.color || '#10b981' }"></span>
                            {{ t._project.name }}
                          </span>
                        </td>
                        <td><span style="font-weight:600;font-size:13px;color:var(--gray-800);">{{ t.client }}</span></td>
                        <td>
                          <div style="max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:13px;" :title="t.request">{{ t.request }}</div>
                        </td>
                        <td><span style="display:inline-flex;padding:2px 8px;border-radius:99px;font-size:11px;font-weight:600;background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;">{{ t.sent_thru }}</span></td>
                        <td>
                          <span style="font-size:12px;" :style="{ color: mtIsOverdue(t) ? '#ef4444' : 'var(--gray-600)', fontWeight: mtIsOverdue(t) ? '600' : '400' }">
                            {{ t.target_date ? t.target_date.slice(0, 10) : '—' }}
                          </span>
                        </td>
                        <td>
                          <div style="display:flex;gap:3px;flex-wrap:wrap;">
                            <span v-for="email in (t.assigned_devs || [])" :key="email" :title="email"
                              style="display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:50%;background:#dbeafe;color:#1d4ed8;font-size:10px;font-weight:700;flex-shrink:0;">
                              {{ mtEmailInitials(email) }}
                            </span>
                            <span v-if="!t.assigned_devs?.length" style="font-size:11px;color:var(--gray-400);">—</span>
                          </div>
                        </td>
                        <td><span :class="['tt-dev-status-badge', 'tt-dev-status--' + (t.dev_status || 'Not Started').toLowerCase().replace(/\s+/g, '-')]">{{ t.dev_status || 'Not Started' }}</span></td>
                        <td><span :class="['badge', mtStatusBadgeClass(t.status)]">{{ t.status }}</span></td>
                        <td><span :class="['age-badge', mtAgeBadgeClass(t)]">{{ mtAgeDays(t) }}d</span></td>
                      </tr>
                    </tbody>
                  </table>
                  <div v-else class="empty-state" style="padding:32px;">
                    <div class="empty-state-icon">🔧</div>
                    <div class="empty-state-title">No maintenance tickets</div>
                    <div class="empty-state-text">Maintenance tickets will appear here once created</div>
                  </div>
                </div>
              </div>

              <!-- Per-project breakdown -->
              <div class="card">
                <div class="card-header"><h3 class="card-title">By Project</h3></div>
                <div class="card-body" style="padding:12px 20px;">
                  <div v-if="!projectBreakdown.length" class="empty-state" style="padding:20px;"><div class="empty-state-title">No data</div></div>
                  <div v-for="row in projectBreakdown" :key="row.name" class="tt-breakdown-row">
                    <div style="display:flex;align-items:center;gap:8px;min-width:0;">
                      <span style="width:8px;height:8px;border-radius:50%;flex-shrink:0;" :style="{ background: row.color }"></span>
                      <span style="font-size:13px;font-weight:500;color:var(--gray-700);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ row.name }}</span>
                    </div>
                    <div class="tt-bar-wrap">
                      <div class="tt-bar" :style="{ width: row.pct + '%', background: row.color }"></div>
                    </div>
                    <span style="font-size:12px;font-weight:600;color:var(--gray-600);min-width:20px;text-align:right;">{{ row.count }}</span>
                  </div>
                </div>
              </div>

              <!-- Dev Workload -->
              <div class="card">
                <div class="card-header"><h3 class="card-title">Dev Workload</h3><span class="card-title" style="font-size:12px;font-weight:400;color:var(--gray-400);">Open tickets per developer</span></div>
                <div class="card-body" style="padding:12px 20px;">
                  <div v-if="!devWorkload.length" class="empty-state" style="padding:20px;"><div class="empty-state-title">No assignments yet</div></div>
                  <div v-for="dev in devWorkload" :key="dev.email || dev.name" class="tt-breakdown-row">
                    <div style="display:flex;align-items:center;gap:8px;min-width:0;flex-shrink:0;width:160px;">
                      <span class="assign-avatar-xs" style="flex-shrink:0;">{{ dev.name?.[0]?.toUpperCase() }}</span>
                      <span style="font-size:13px;font-weight:500;color:var(--gray-700);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ dev.name.split(' ')[0] }}</span>
                    </div>
                    <div class="tt-bar-wrap">
                      <div class="tt-bar" :style="{ width: dev.pct + '%', background: '#4f46e5' }"></div>
                    </div>
                    <span style="font-size:12px;font-weight:600;color:var(--gray-600);min-width:20px;text-align:right;">{{ dev.count }}</span>
                    <a
                      v-if="dev.folderToken"
                      :href="'/dev-folder/' + dev.folderToken"
                      target="_blank"
                      title="View dev folder"
                      style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:6px;background:#eef2ff;color:#6366f1;text-decoration:none;font-size:11px;font-weight:600;flex-shrink:0;white-space:nowrap;border:1px solid #e0e7ff;"
                    >📁 Folder</a>
                    <span v-else style="width:66px;flex-shrink:0;"></span>
                  </div>
                </div>
              </div>

              <!-- Recent Activity -->
              <div class="card" style="grid-column:1/-1;">
                <div class="card-header">
                  <h3 class="card-title">Recent Activity</h3>
                  <button v-if="recentActivity.length" class="btn btn-ghost btn-sm" @click="clearActivityLog">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    Clear
                  </button>
                </div>
                <div class="card-body" style="padding:0 20px;">
                  <div v-if="!recentActivity.length" class="empty-state" style="padding:32px;"><div class="empty-state-title">No activity yet</div></div>
                  <div v-for="(entry, i) in recentActivity" :key="i" class="tt-activity-row">
                    <div class="tt-activity-dot" :class="'tt-activity-dot--' + entry.type" :title="entry.type"></div>
                    <div style="flex:1;min-width:0;">
                      <span style="font-size:13px;font-weight:600;color:var(--gray-800);">{{ entry.user_name }}</span>
                      <span style="font-size:13px;color:var(--gray-500);margin-left:6px;">{{ entry.content }}</span>
                      <span style="font-size:11px;color:var(--gray-400);margin-left:8px;">· #{{ entry.bugSequence }} {{ entry.bugTitle }}</span>
                    </div>
                    <span style="font-size:11px;color:var(--gray-400);white-space:nowrap;margin-left:12px;">{{ formatBugDate(entry.timestamp) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- ── All Tickets Tab ── -->
          <div v-else-if="ticketTab === 'all'">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">All Tickets</h3>
                <span class="result-chip">{{ filteredAllTickets.length }} result{{ filteredAllTickets.length !== 1 ? 's' : '' }}</span>
              </div>
              <div class="card-body">
                <!-- Filter pills -->
                <div class="tt-pill-bar">
                  <button :class="['tt-pill', { 'tt-pill--active': ttFilterPill === 'all' }]" @click="ttFilterPill = 'all'">All <span class="tt-pill-count">{{ allTickets.length }}</span></button>
                  <button :class="['tt-pill', { 'tt-pill--active': ttFilterPill === 'pending' }]" @click="ttFilterPill = 'pending'">Pending <span class="tt-pill-count">{{ allTickets.filter(t => t.status === 'Pending').length }}</span></button>
                  <button :class="['tt-pill', { 'tt-pill--active': ttFilterPill === 'in-progress' }]" @click="ttFilterPill = 'in-progress'">In Progress <span class="tt-pill-count">{{ inProgressTickets.length }}</span></button>
                  <button :class="['tt-pill', { 'tt-pill--active': ttFilterPill === 'resolved' }]" @click="ttFilterPill = 'resolved'">Resolved <span class="tt-pill-count tt-pill-count--green">{{ resolvedTickets.length }}</span></button>
                  <button :class="['tt-pill', { 'tt-pill--active': ttFilterPill === 'overdue' }]" @click="ttFilterPill = 'overdue'">Overdue <span v-if="overdueTickets.length" class="tt-pill-count tt-pill-count--red">{{ overdueTickets.length }}</span></button>
                  <button :class="['tt-pill', { 'tt-pill--active': ttFilterPill === 'sent' }]" @click="ttFilterPill = 'sent'">Sent <span class="tt-pill-count tt-pill-count--blue">{{ sentTickets.length }}</span></button>
                </div>
                <!-- Dropdown filters -->
                <div class="filters-bar">
                  <select v-model="ttFilters.project_id" class="form-control" style="width:155px;">
                    <option value="">All Projects</option>
                    <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                  </select>
                  <select v-model="ttPriorityFilter" class="form-control" style="width:140px;">
                    <option value="">All Priorities</option>
                    <option v-for="p in priorities" :key="p" :value="p">{{ p }}</option>
                  </select>
                  <select v-model="ttAssigneeFilter" class="form-control" style="width:155px;">
                    <option value="">All Assignees</option>
                    <option v-for="m in teamMembers" :key="m.id" :value="m.name.toLowerCase()">{{ m.name }}</option>
                  </select>
                  <button v-if="ttFilters.project_id || ttPriorityFilter || ttAssigneeFilter || ttFilterPill !== 'all'" class="btn btn-ghost btn-sm" @click="ttFilters.project_id=''; ttPriorityFilter=''; ttAssigneeFilter=''; ttFilterPill='all'">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    Clear
                  </button>
                </div>
                <div class="table-wrap">
                  <table v-if="filteredAllTickets.length">
                    <thead>
                      <tr>
                        <th style="width:50px;">#</th>
                        <th>Title</th>
                        <th style="width:115px;">Project</th>
                        <th style="width:90px;">Priority</th>
                        <th style="width:115px;">QA Status</th>
                        <th style="width:140px;">Assigned Dev</th>
                        <th style="width:115px;">Dev Status</th>
                        <th style="width:65px;">Age</th>
                        <th style="width:105px;">SLA</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="t in filteredAllTickets" :key="t.id" class="clickable-row" :class="{ 'tt-overdue-row': isOverdue(t) }" @click="openTTDetail(t)">
                        <td>
                          <div style="display:flex;align-items:center;gap:5px;">
                            <span v-if="isOverdue(t)" class="tt-overdue-dot" title="Overdue"></span>
                            <span class="bug-seq">#{{ t.sequence }}</span>
                          </div>
                        </td>
                        <td>
                          <div class="bug-title-main bug-title-link">{{ t.title }}</div>
                          <div v-if="t.description" class="bug-title-desc">{{ stripHtml(t.description) }}</div>
                        </td>
                        <td>
                          <span v-if="t.project" style="display:flex;align-items:center;gap:6px;font-size:12px;">
                            <span style="width:7px;height:7px;border-radius:50%;flex-shrink:0;" :style="{ background: t.project?.color || '#94a3b8' }"></span>
                            {{ t.project?.name }}
                          </span>
                        </td>
                        <td><span :class="['badge', priorityBadgeClass(t.priority)]">{{ t.priority }}</span></td>
                        <td><span :class="['badge', statusBadgeClass(t.status)]">{{ t.status }}</span></td>
                        <td>
                          <span v-if="t.assigned_developer" style="display:flex;align-items:center;gap:6px;font-size:12px;">
                            <span class="assign-avatar-xs">{{ t.assigned_developer.name?.[0]?.toUpperCase() }}</span>
                            {{ t.assigned_developer.name }}
                          </span>
                          <span v-else style="color:var(--gray-300);font-size:12px;">Unassigned</span>
                        </td>
                        <td><span :class="['tt-dev-status-badge', 'tt-dev-status--' + (t.dev_status || 'Not Started').toLowerCase().replace(/\s+/g,'-')]">{{ t.dev_status || 'Not Started' }}</span></td>
                        <td><span :class="['age-badge', ageBadgeClass(t)]">{{ ageLabel(t) }}</span></td>
                        <td>
                          <div class="sla-bar-wrap" :title="`${slaProgress(t)}% of SLA used`">
                            <div class="sla-bar" :style="{ width: slaProgress(t) + '%', background: slaBarColor(slaProgress(t)) }"></div>
                          </div>
                          <div style="font-size:10px;color:var(--gray-400);margin-top:2px;text-align:center;">{{ slaProgress(t) }}%</div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  <div v-else class="empty-state"><div class="empty-state-icon">🔍</div><div class="empty-state-title">No tickets found</div></div>
                </div>
              </div>
            </div>
          </div>

          <!-- ── Sent Tab ── -->
          <div v-else-if="ticketTab === 'sent'">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Sent Tickets</h3>
                <span class="result-chip">{{ sentTickets.length }}</span>
              </div>
              <div class="card-body" style="padding:0;">
                <table v-if="sentTickets.length">
                  <thead>
                    <tr>
                      <th style="width:56px;">#</th>
                      <th>Title</th>
                      <th style="width:130px;">Project</th>
                      <th style="width:100px;">Priority</th>
                      <th style="width:160px;">Sent To</th>
                      <th style="width:140px;">Sent At</th>
                      <th style="width:130px;">Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="t in sentTickets" :key="t.id" class="clickable-row" @click="openTTDetail(t)">
                      <td><span class="bug-seq">#{{ t.sequence }}</span></td>
                      <td><div class="bug-title-main bug-title-link">{{ t.title }}</div></td>
                      <td>
                        <span v-if="t.project" style="display:flex;align-items:center;gap:6px;font-size:12px;">
                          <span style="width:7px;height:7px;border-radius:50%;flex-shrink:0;" :style="{ background: t.project?.color || '#94a3b8' }"></span>
                          {{ t.project?.name }}
                        </span>
                      </td>
                      <td><span :class="['badge', priorityBadgeClass(t.priority)]">{{ t.priority }}</span></td>
                      <td>
                        <span v-if="t.assigned_developer" style="display:flex;align-items:center;gap:6px;font-size:12px;">
                          <span class="assign-avatar-xs">{{ t.assigned_developer.name?.[0]?.toUpperCase() }}</span>
                          {{ t.assigned_developer.name }}
                        </span>
                      </td>
                      <td style="font-size:12px;color:var(--gray-500);">{{ formatBugDate(t.ticket_sent_at) }}</td>
                      <td><span :class="['badge', statusBadgeClass(t.status)]">{{ t.status }}</span></td>
                    </tr>
                  </tbody>
                </table>
                <div v-else class="empty-state" style="padding:40px;">
                  <div class="empty-state-icon">📤</div>
                  <div class="empty-state-title">No tickets sent yet</div>
                  <div class="empty-state-text">Assign a developer and click "Send Ticket" in the bug table</div>
                </div>
              </div>
            </div>
          </div>

          <!-- ── Resolved Tab ── -->
          <div v-else-if="ticketTab === 'resolved'">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Resolved Tickets</h3>
                <span class="result-chip">{{ resolvedTickets.length }}</span>
              </div>
              <div class="card-body" style="padding:0;">
                <table v-if="resolvedTickets.length">
                  <thead>
                    <tr>
                      <th style="width:56px;">#</th>
                      <th>Title</th>
                      <th style="width:130px;">Project</th>
                      <th style="width:100px;">Priority</th>
                      <th style="width:160px;">Resolved By</th>
                      <th style="width:130px;">Date Resolved</th>
                      <th style="width:120px;">Time Taken</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="t in resolvedTickets" :key="t.id" class="clickable-row" @click="openTTDetail(t)">
                      <td><span class="bug-seq">#{{ t.sequence }}</span></td>
                      <td><div class="bug-title-main bug-title-link">{{ t.title }}</div></td>
                      <td>
                        <span v-if="t.project" style="display:flex;align-items:center;gap:6px;font-size:12px;">
                          <span style="width:7px;height:7px;border-radius:50%;flex-shrink:0;" :style="{ background: t.project?.color || '#94a3b8' }"></span>
                          {{ t.project?.name }}
                        </span>
                      </td>
                      <td><span :class="['badge', priorityBadgeClass(t.priority)]">{{ t.priority }}</span></td>
                      <td>
                        <span style="font-size:12px;">{{ resolvedBy(t) || '—' }}</span>
                      </td>
                      <td style="font-size:12px;color:var(--gray-500);">{{ formatBugDate(resolvedAt(t)) }}</td>
                      <td style="font-size:12px;color:#16a34a;font-weight:600;">{{ timeTaken(t) }}</td>
                    </tr>
                  </tbody>
                </table>
                <div v-else class="empty-state" style="padding:40px;">
                  <div class="empty-state-icon">✅</div>
                  <div class="empty-state-title">No resolved tickets yet</div>
                  <div class="empty-state-text">Resolved tickets will appear here with completion details</div>
                </div>
              </div>
            </div>
          </div>

          <!-- ── Overdue Tab ── -->
          <div v-else-if="ticketTab === 'overdue'">
            <div v-if="!overdueTickets.length" class="empty-state" style="padding:60px;">
              <div class="empty-state-icon">🎉</div>
              <div class="empty-state-title">No overdue tickets!</div>
              <div class="empty-state-text">All tickets are within their SLA response windows</div>
            </div>
            <div v-else class="card">
              <div class="card-header">
                <div style="display:flex;align-items:center;gap:8px;">
                  <span style="width:8px;height:8px;border-radius:50%;background:#ef4444;animation:qa-pulse-red 1.5s ease-in-out infinite;"></span>
                  <h3 class="card-title">Overdue Tickets</h3>
                </div>
                <span class="result-chip" style="background:#fee2e2;color:#b91c1c;">{{ overdueTickets.length }}</span>
              </div>
              <div class="card-body" style="padding:0;">
                <table>
                  <thead>
                    <tr>
                      <th style="width:50px;">#</th>
                      <th>Title</th>
                      <th style="width:115px;">Project</th>
                      <th style="width:90px;">Priority</th>
                      <th style="width:65px;">Age</th>
                      <th style="width:110px;">SLA Used</th>
                      <th style="width:140px;">Assigned Dev</th>
                      <th style="width:115px;">QA Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="t in overdueTickets" :key="t.id" class="clickable-row tt-overdue-row" @click="openTTDetail(t)">
                      <td>
                        <div style="display:flex;align-items:center;gap:5px;">
                          <span class="tt-overdue-dot"></span>
                          <span class="bug-seq">#{{ t.sequence }}</span>
                        </div>
                      </td>
                      <td><div class="bug-title-main bug-title-link">{{ t.title }}</div></td>
                      <td>
                        <span v-if="t.project" style="display:flex;align-items:center;gap:6px;font-size:12px;">
                          <span style="width:7px;height:7px;border-radius:50%;flex-shrink:0;" :style="{ background: t.project?.color || '#94a3b8' }"></span>
                          {{ t.project?.name }}
                        </span>
                      </td>
                      <td><span :class="['badge', priorityBadgeClass(t.priority)]">{{ t.priority }}</span></td>
                      <td><span :class="['age-badge', ageBadgeClass(t)]">{{ ageLabel(t) }}</span></td>
                      <td>
                        <div class="sla-bar-wrap" :title="`${slaProgress(t)}% of SLA used`">
                          <div class="sla-bar" :style="{ width: '100%', background: '#ef4444' }"></div>
                        </div>
                        <div style="font-size:10px;color:#ef4444;margin-top:2px;text-align:center;font-weight:600;">{{ slaProgress(t) }}%</div>
                      </td>
                      <td>
                        <span v-if="t.assigned_developer" style="display:flex;align-items:center;gap:6px;font-size:12px;">
                          <span class="assign-avatar-xs">{{ t.assigned_developer.name?.[0]?.toUpperCase() }}</span>
                          {{ t.assigned_developer.name }}
                        </span>
                        <span v-else style="color:var(--gray-300);font-size:12px;">Unassigned</span>
                      </td>
                      <td><span :class="['badge', statusBadgeClass(t.status)]">{{ t.status }}</span></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- ── Pending Tab ── -->
          <div v-else-if="ticketTab === 'pending'">
            <div v-if="!pendingTickets.length" class="empty-state" style="padding:60px;">
              <div class="empty-state-icon">🎉</div>
              <div class="empty-state-title">All caught up!</div>
              <div class="empty-state-text">No tickets need action right now</div>
            </div>
            <template v-else>
              <!-- Group: Assign Dev First -->
              <div v-if="needsAssignment.length" class="card" style="margin-bottom:20px;">
                <div class="card-header">
                  <div style="display:flex;align-items:center;gap:8px;">
                    <span style="width:8px;height:8px;border-radius:50%;background:#ef4444;"></span>
                    <h3 class="card-title">Assign Developer First</h3>
                  </div>
                  <span class="result-chip">{{ needsAssignment.length }}</span>
                </div>
                <div class="card-body" style="padding:0;">
                  <table>
                    <thead>
                      <tr>
                        <th style="width:56px;">#</th>
                        <th>Title</th>
                        <th style="width:130px;">Project</th>
                        <th style="width:100px;">Priority</th>
                        <th style="width:130px;">Status</th>
                        <th style="width:160px;">Action Needed</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="t in needsAssignment" :key="t.id" class="clickable-row" @click="openTTDetail(t)">
                        <td><span class="bug-seq">#{{ t.sequence }}</span></td>
                        <td><div class="bug-title-main bug-title-link">{{ t.title }}</div></td>
                        <td>
                          <span v-if="t.project" style="display:flex;align-items:center;gap:6px;font-size:12px;">
                            <span style="width:7px;height:7px;border-radius:50%;flex-shrink:0;" :style="{ background: t.project?.color || '#94a3b8' }"></span>
                            {{ t.project?.name }}
                          </span>
                        </td>
                        <td><span :class="['badge', priorityBadgeClass(t.priority)]">{{ t.priority }}</span></td>
                        <td><span :class="['badge', statusBadgeClass(t.status)]">{{ t.status }}</span></td>
                        <td><span class="tt-action-chip tt-action-chip--red">Assign dev first</span></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Group: Send Ticket -->
              <div v-if="needsSend.length" class="card">
                <div class="card-header">
                  <div style="display:flex;align-items:center;gap:8px;">
                    <span style="width:8px;height:8px;border-radius:50%;background:#f59e0b;"></span>
                    <h3 class="card-title">Ready to Send</h3>
                  </div>
                  <span class="result-chip">{{ needsSend.length }}</span>
                </div>
                <div class="card-body" style="padding:0;">
                  <table>
                    <thead>
                      <tr>
                        <th style="width:56px;">#</th>
                        <th>Title</th>
                        <th style="width:130px;">Project</th>
                        <th style="width:100px;">Priority</th>
                        <th style="width:150px;">Assigned Dev</th>
                        <th style="width:160px;">Action Needed</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="t in needsSend" :key="t.id" class="clickable-row" @click="openTTDetail(t)">
                        <td><span class="bug-seq">#{{ t.sequence }}</span></td>
                        <td><div class="bug-title-main bug-title-link">{{ t.title }}</div></td>
                        <td>
                          <span v-if="t.project" style="display:flex;align-items:center;gap:6px;font-size:12px;">
                            <span style="width:7px;height:7px;border-radius:50%;flex-shrink:0;" :style="{ background: t.project?.color || '#94a3b8' }"></span>
                            {{ t.project?.name }}
                          </span>
                        </td>
                        <td><span :class="['badge', priorityBadgeClass(t.priority)]">{{ t.priority }}</span></td>
                        <td>
                          <span v-if="t.assigned_developer" style="display:flex;align-items:center;gap:6px;font-size:12px;">
                            <span class="assign-avatar-xs">{{ t.assigned_developer.name?.[0]?.toUpperCase() }}</span>
                            {{ t.assigned_developer.name.split(' ')[0] }}
                          </span>
                        </td>
                        <td><span class="tt-action-chip tt-action-chip--yellow">Send ticket</span></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </template>
          </div>

          <!-- ── Maintenance Tab ── -->
          <div v-else-if="ticketTab === 'maintenance'">
            <div class="card">
              <div class="card-header">
                <div style="display:flex;align-items:center;gap:8px;">
                  <span class="sidebar-item-icon" style="font-size:16px;">🔧</span>
                  <h3 class="card-title">Maintenance Tickets</h3>
                </div>
                <span class="result-chip">{{ filteredMtTickets.length }} ticket{{ filteredMtTickets.length !== 1 ? 's' : '' }}</span>
              </div>
              <div class="card-body">
                <!-- Status pills -->
                <div class="tt-pill-bar">
                  <button :class="['tt-pill', { 'tt-pill--active': mtStatusPill === 'all' }]" @click="mtStatusPill = 'all'">All <span class="tt-pill-count">{{ allMaintenanceTickets.length }}</span></button>
                  <button :class="['tt-pill', { 'tt-pill--active': mtStatusPill === 'pending' }]" @click="mtStatusPill = 'pending'">Pending <span class="tt-pill-count">{{ allMaintenanceTickets.filter(t => t.status === 'Pending').length }}</span></button>
                  <button :class="['tt-pill', { 'tt-pill--active': mtStatusPill === 'in-progress' }]" @click="mtStatusPill = 'in-progress'">In Progress <span class="tt-pill-count">{{ allMaintenanceTickets.filter(t => t.status === 'In Progress').length }}</span></button>
                  <button :class="['tt-pill', { 'tt-pill--active': mtStatusPill === 'on-hold' }]" @click="mtStatusPill = 'on-hold'">On Hold <span class="tt-pill-count">{{ allMaintenanceTickets.filter(t => t.status === 'On Hold').length }}</span></button>
                  <button :class="['tt-pill', { 'tt-pill--active': mtStatusPill === 'completed' }]" @click="mtStatusPill = 'completed'">Completed <span class="tt-pill-count tt-pill-count--green">{{ allMaintenanceTickets.filter(t => t.status === 'Completed').length }}</span></button>
                  <button :class="['tt-pill', { 'tt-pill--active': mtStatusPill === 'overdue' }]" @click="mtStatusPill = 'overdue'">Overdue <span v-if="allMaintenanceTickets.filter(mtIsOverdue).length" class="tt-pill-count tt-pill-count--red">{{ allMaintenanceTickets.filter(mtIsOverdue).length }}</span></button>
                </div>
                <!-- Search bar -->
                <div class="filters-bar" style="margin-bottom:0;">
                  <div class="search-input-wrap">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    <input v-model="mtSearch" class="form-control" placeholder="Search by title, assignee, or ticket #..." />
                  </div>
                  <button v-if="mtSearch || mtStatusPill !== 'all'" class="btn btn-ghost btn-sm" @click="mtSearch = ''; mtStatusPill = 'all'">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    Clear
                  </button>
                </div>
              </div>
              <div style="padding:0 0 0 0;">
                <table v-if="filteredMtTickets.length">
                  <thead>
                    <tr>
                      <th style="width:80px;">Ticket #</th>
                      <th style="width:120px;">Project</th>
                      <th style="width:120px;">Client</th>
                      <th>Request</th>
                      <th style="width:90px;">Sent Thru</th>
                      <th style="width:100px;">Target</th>
                      <th style="width:130px;">Dev(s)</th>
                      <th style="width:120px;">Dev Status</th>
                      <th style="width:110px;">Status</th>
                      <th style="width:65px;">Age</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="t in filteredMtTickets" :key="'mt-' + t.id"
                        class="clickable-row"
                        :class="{ 'tt-overdue-row': mtIsOverdue(t) }"
                        @click="navigateTo(`/maintenance-ticket/${t.id}?from=dashboard`)">
                      <td>
                        <span class="bug-seq" style="font-size:11px;background:#ecfdf5;color:#059669;">{{ t.ticket_number }}</span>
                      </td>
                      <td>
                        <span v-if="t._project" style="display:flex;align-items:center;gap:6px;font-size:12px;">
                          <span style="width:7px;height:7px;border-radius:50%;flex-shrink:0;" :style="{ background: t._project.color || '#10b981' }"></span>
                          {{ t._project.name }}
                        </span>
                      </td>
                      <td><span style="font-weight:600;font-size:13px;color:var(--gray-800);">{{ t.client }}</span></td>
                      <td>
                        <div style="max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:13px;" :title="t.request">{{ t.request }}</div>
                      </td>
                      <td><span style="display:inline-flex;padding:2px 8px;border-radius:99px;font-size:11px;font-weight:600;background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;">{{ t.sent_thru }}</span></td>
                      <td>
                        <span style="font-size:12px;" :style="{ color: mtIsOverdue(t) ? '#ef4444' : 'var(--gray-600)', fontWeight: mtIsOverdue(t) ? '600' : '400' }">
                          {{ t.target_date ? t.target_date.slice(0, 10) : '—' }}
                        </span>
                      </td>
                      <td>
                        <div style="display:flex;gap:3px;flex-wrap:wrap;">
                          <span v-for="email in (t.assigned_devs || [])" :key="email" :title="email"
                            style="display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:50%;background:#dbeafe;color:#1d4ed8;font-size:10px;font-weight:700;flex-shrink:0;">
                            {{ mtEmailInitials(email) }}
                          </span>
                          <span v-if="!t.assigned_devs?.length" style="font-size:11px;color:var(--gray-400);">—</span>
                        </div>
                      </td>
                      <td><span :class="['tt-dev-status-badge', 'tt-dev-status--' + (t.dev_status || 'Not Started').toLowerCase().replace(/\s+/g, '-')]">{{ t.dev_status || 'Not Started' }}</span></td>
                      <td><span :class="['badge', mtStatusBadgeClass(t.status)]">{{ t.status }}</span></td>
                      <td><span :class="['age-badge', mtAgeBadgeClass(t)]">{{ mtAgeDays(t) }}d</span></td>
                    </tr>
                  </tbody>
                </table>
                <div v-else class="empty-state" style="padding:40px;">
                  <div class="empty-state-icon">🔧</div>
                  <div class="empty-state-title">{{ allMaintenanceTickets.length ? 'No tickets match your filters' : 'No maintenance tickets' }}</div>
                  <div class="empty-state-text">{{ allMaintenanceTickets.length ? 'Try a different search or clear the filters' : 'Maintenance tickets will appear here once created' }}</div>
                  <button v-if="mtSearch || mtStatusPill !== 'all'" class="btn btn-ghost btn-sm" style="margin-top:12px;" @click="mtSearch = ''; mtStatusPill = 'all'">Clear filters</button>
                </div>
              </div>
            </div>
          </div>

          </template><!-- end v-else tracker list -->

        </div>

        <!-- ══ Dev Folders view ══ -->
        <div v-else-if="devFoldersViewOpen">
          <div class="view-header">
            <div style="display:flex;align-items:center;gap:12px;">
              <div class="df-page-icon">📁</div>
              <div>
                <h1 class="view-title" style="margin:0;">Dev Folders</h1>
                <p class="view-subtitle" style="margin:2px 0 0;">{{ devFolders.length }} folder{{ devFolders.length !== 1 ? 's' : '' }} · Shared portals for each developer</p>
              </div>
            </div>
            <button class="btn btn-ghost btn-sm" @click="fetchDevFolders" title="Refresh">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-4.5"/></svg>
              Refresh
            </button>
          </div>

          <!-- ── Dev Folders Summary ── -->
          <div v-if="devFoldersSummary" class="dfs-wrap">

            <!-- Left: overall stats -->
            <div class="dfs-overall">
              <div class="dfs-section-label"><span>📅</span> {{ devFoldersSummary.date }}</div>

              <div class="dfs-stat-grid">
                <div class="dfs-stat-card" style="--sc:#4f46e5;--sc-bg:#eef2ff;--sc-bd:#c7d2fe;">
                  <div class="dfs-stat-num">{{ devFoldersSummary.overall.total_active }}</div>
                  <div class="dfs-stat-lbl">Active Fixes</div>
                </div>
                <div class="dfs-stat-card" style="--sc:#d97706;--sc-bg:#fffbeb;--sc-bd:#fde68a;">
                  <div class="dfs-stat-num">{{ devFoldersSummary.overall.pending }}</div>
                  <div class="dfs-stat-lbl">Pending</div>
                </div>
                <div class="dfs-stat-card" style="--sc:#2563eb;--sc-bg:#eff6ff;--sc-bd:#bfdbfe;">
                  <div class="dfs-stat-num">{{ devFoldersSummary.overall.ongoing_fixing }}</div>
                  <div class="dfs-stat-lbl">Fixing</div>
                </div>
                <div class="dfs-stat-card" style="--sc:#7c3aed;--sc-bg:#f5f3ff;--sc-bd:#ddd6fe;">
                  <div class="dfs-stat-num">{{ devFoldersSummary.overall.ongoing_qa }}</div>
                  <div class="dfs-stat-lbl">In QA</div>
                </div>
                <div class="dfs-stat-card" style="--sc:#dc2626;--sc-bg:#fef2f2;--sc-bd:#fecaca;">
                  <div class="dfs-stat-num">{{ devFoldersSummary.overall.sent_back }}</div>
                  <div class="dfs-stat-lbl">Blocked</div>
                </div>
                <div class="dfs-stat-card" style="--sc:#15803d;--sc-bg:#f0fdf4;--sc-bd:#bbf7d0;">
                  <div class="dfs-stat-num">{{ devFoldersSummary.overall.total_completed }}</div>
                  <div class="dfs-stat-lbl">Completed</div>
                </div>
              </div>

              <div class="dfs-section-label" style="margin-top:18px;"><span>🔥</span> By Priority</div>
              <div class="dfs-priority-row">
                <div class="dfs-priority-item dfs-p--critical">
                  <span class="dfs-p-dot"></span>
                  <span class="dfs-p-val">{{ devFoldersSummary.overall.critical }}</span>
                  <span class="dfs-p-label">Critical</span>
                </div>
                <div class="dfs-priority-item dfs-p--high">
                  <span class="dfs-p-dot"></span>
                  <span class="dfs-p-val">{{ devFoldersSummary.overall.high }}</span>
                  <span class="dfs-p-label">High</span>
                </div>
                <div class="dfs-priority-item dfs-p--medium">
                  <span class="dfs-p-dot"></span>
                  <span class="dfs-p-val">{{ devFoldersSummary.overall.medium }}</span>
                  <span class="dfs-p-label">Medium</span>
                </div>
                <div class="dfs-priority-item dfs-p--low">
                  <span class="dfs-p-dot"></span>
                  <span class="dfs-p-val">{{ devFoldersSummary.overall.low }}</span>
                  <span class="dfs-p-label">Low</span>
                </div>
              </div>

              <!-- Resolution Rate (compact) -->
              <template v-if="dfsSummaryProgress">
                <div class="dfs-section-label" style="margin-top:18px;"><span>📊</span> Resolution Rate</div>
                <div class="dfs-progress-compact">
                  <div class="dfs-progress-track">
                    <div v-if="dfsSummaryProgress.done"    class="dfs-pb dfs-pb--done"    :style="{ width: dfsSummaryProgress.done    + '%' }"></div>
                    <div v-if="dfsSummaryProgress.active"  class="dfs-pb dfs-pb--active"  :style="{ width: dfsSummaryProgress.active  + '%' }"></div>
                    <div v-if="dfsSummaryProgress.pend"    class="dfs-pb dfs-pb--pend"    :style="{ width: dfsSummaryProgress.pend    + '%' }"></div>
                    <div v-if="dfsSummaryProgress.blocked" class="dfs-pb dfs-pb--blocked" :style="{ width: dfsSummaryProgress.blocked + '%' }"></div>
                  </div>
                  <div class="dfs-progress-compact-row">
                    <span class="dfs-progress-compact-pct">{{ dfsSummaryProgress.pct }}% resolved</span>
                    <div class="dfs-progress-legend">
                      <span class="dfs-pl dfs-pl--done"><i></i>Done</span>
                      <span class="dfs-pl dfs-pl--active"><i></i>Active</span>
                      <span class="dfs-pl dfs-pl--pend"><i></i>Pending</span>
                      <span class="dfs-pl dfs-pl--blocked"><i></i>Blocked</span>
                    </div>
                  </div>
                </div>
              </template>
            </div>

            <!-- Right: per-developer summary -->
            <div class="dfs-devs">
              <div class="dfs-section-label"><span>👥</span> Developers' Summary</div>
              <div class="dfs-devs-list">
                <!-- Bug Tracker devs -->
                <template v-if="devFoldersSummary.developers.length">
                  <div class="dfs-sub-label">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
                    Bug Tracker
                  </div>
                  <div v-for="dev in devFoldersSummary.developers" :key="dev.email" class="dfs-dev-row dfs-dev-row--clickable" @click="openFolderDetailByEmail(dev.email)">
                    <div class="dfs-dev-avatar" :style="{ background: dev.avatar_color || '#4338ca' }">{{ folderInitials(dev.name) }}</div>
                    <div class="dfs-dev-info">
                      <div class="dfs-dev-name">{{ dev.name }}</div>
                      <div class="dfs-dev-email">{{ dev.email }}</div>
                    </div>
                    <div class="dfs-dev-badges">
                      <span class="dfs-dev-badge dfs-dev-badge--pending"><strong>{{ dev.pending }}</strong><span>Pending</span></span>
                      <span class="dfs-dev-badge dfs-dev-badge--active"><strong>{{ dev.active }}</strong><span>Active</span></span>
                      <span class="dfs-dev-badge dfs-dev-badge--done"><strong>{{ dev.completed }}</strong><span>Done</span></span>
                    </div>
                  </div>
                </template>
                <!-- Maintenance devs -->
                <template v-if="maintenanceDevs.length">
                  <div class="dfs-sub-label" :style="devFoldersSummary.developers.length ? 'margin-top:14px;' : ''">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                    Maintenance
                  </div>
                  <div v-for="(dev, idx) in maintenanceDevs" :key="'m-' + dev.email" class="dfs-dev-row dfs-dev-row--clickable" @click="openMaintenanceDevDetail(dev, idx)">
                    <div class="dfs-dev-avatar" :style="{ background: getMaintDevColor(dev.email, idx) }">{{ folderInitials(dev.email.split('@')[0]) }}</div>
                    <div class="dfs-dev-info">
                      <div class="dfs-dev-name">{{ dev.email.split('@')[0] }}</div>
                      <div class="dfs-dev-email">{{ dev.email }}</div>
                    </div>
                    <div class="dfs-dev-badges">
                      <span class="dfs-dev-badge dfs-dev-badge--pending"><strong>{{ dev.pending }}</strong><span>Pending</span></span>
                      <span class="dfs-dev-badge dfs-dev-badge--active"><strong>{{ dev.in_progress }}</strong><span>Active</span></span>
                      <span class="dfs-dev-badge dfs-dev-badge--done"><strong>{{ dev.completed }}</strong><span>Done</span></span>
                    </div>
                  </div>
                </template>
              </div>
            </div>

          </div>

          <div v-if="devFoldersLoading" style="text-align:center;padding:60px;color:var(--gray-400);">
            <div style="width:28px;height:28px;border:3px solid #e2e8f0;border-top-color:#6366f1;border-radius:50%;animation:spin .7s linear infinite;margin:0 auto 12px;"></div>
            Loading folders…
          </div>

          <template v-else>
            <!-- ── Bug Tracker Folders ── -->
            <div class="df-section-label">
              <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
              Bug Tracker
            </div>

          <div v-if="devFolders.length" class="df-grid">
            <div v-for="(folder, idx) in devFolders" :key="folder.token" class="df-card df-card--clickable" :style="`--i:${idx}`" @click="openFolderDetail(folder)">
              <div class="df-card-hero" :style="{ background: folder.avatar_color || '#4f46e5' }">
                <button
                  :class="['df-vis-badge', folder.visibility === 'public' ? 'df-vis--pub' : 'df-vis--priv']"
                  :title="folder.visibility === 'public' ? 'Click to make private' : 'Click to make public'"
                  @click.stop="toggleFolderVisibility(folder)"
                >
                  <svg v-if="folder.visibility === 'public'" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                  <svg v-else width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                  {{ folder.visibility === 'public' ? 'Public' : 'Private' }}
                </button>
              </div>
              <div class="df-card-body">
                <div class="df-avatar" :style="{ background: folder.avatar_color || '#4f46e5' }">{{ folderInitials(folder.developer_name) }}</div>
                <div class="df-card-name">{{ folder.developer_name }}</div>
                <div class="df-card-email">{{ folder.developer_email }}</div>
                <div class="df-card-footer">
                  <button class="df-action-btn df-action-copy" @click.stop="copyFolderLink(folder)" title="Copy link">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                    Copy link
                  </button>
                  <a :href="'/dev-folder/' + folder.token" target="_blank" rel="noopener" class="df-action-btn df-action-open" @click.stop>
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    Open
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- ── Maintenance Folders ── -->
          <div class="df-section-label" style="margin-top:28px;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
            Maintenance
          </div>

          <div v-if="maintenanceDevs.length" class="df-grid">
            <div
              v-for="(dev, idx) in maintenanceDevs"
              :key="dev.email"
              class="df-card df-card--clickable"
              :style="`--i:${idx}`"
              @click="openMaintenanceDevDetail(dev, idx)"
            >
              <div class="df-card-hero" :style="{ background: getMaintDevColor(dev.email, idx) }">
                <button
                  :class="['df-vis-badge', getMaintDevVisibility(dev.email) === 'public' ? 'df-vis--pub' : 'df-vis--priv']"
                  :title="getMaintDevVisibility(dev.email) === 'public' ? 'Click to make private' : 'Click to make public'"
                  @click.stop="toggleMaintDevVisibility(dev.email)"
                >
                  <svg v-if="getMaintDevVisibility(dev.email) === 'public'" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                  <svg v-else width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                  {{ getMaintDevVisibility(dev.email) === 'public' ? 'Public' : 'Private' }}
                </button>
              </div>
              <div class="df-card-body">
                <div class="df-avatar" :style="{ background: getMaintDevColor(dev.email, idx) }">{{ folderInitials(dev.email.split('@')[0]) }}</div>
                <div class="df-card-name">{{ dev.email.split('@')[0] }}</div>
                <div class="df-card-email">{{ dev.email }}</div>
                <div style="margin:3px 0 6px;display:flex;gap:4px;flex-wrap:wrap;">
                  <span v-if="dev.roles.includes('dev')" style="font-size:10px;padding:1px 6px;border-radius:8px;background:#ede9fe;color:#6d28d9;font-weight:600;">Dev</span>
                  <span v-if="dev.roles.includes('qa')" style="font-size:10px;padding:1px 6px;border-radius:8px;background:#e0f2fe;color:#0369a1;font-weight:600;">QA</span>
                </div>
                <div class="df-card-footer">
                  <button class="df-action-btn df-action-copy" @click.stop="copyMaintenanceFolderLink(dev.email)" title="Copy link">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                    Copy link
                  </button>
                  <a :href="maintenanceFolderUrl(dev.email)" target="_blank" rel="noopener" class="df-action-btn df-action-open" @click.stop>
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    Open
                  </a>
                </div>
              </div>
            </div>
          </div>
          <div v-else-if="!devFoldersLoading" style="font-size:13px;color:var(--gray-400);padding:12px 0;">No maintenance developers found.</div>

          <!-- ── Maintenance Dev Detail Modal ── -->
          <Teleport to="body">
            <Transition name="modal-fade">
              <div v-if="maintDevDetail" class="df-detail-overlay" @click.self="closeMaintDevDetail">
                <div class="df-detail-modal">
                  <div class="df-detail-header" :style="{ background: `linear-gradient(135deg, ${maintDevDetail.color} 0%, ${maintDevDetail.color}cc 100%)` }">
                    <div style="display:flex;align-items:center;gap:14px;">
                      <div class="df-detail-avatar" :style="{ background: 'rgba(255,255,255,.22)', borderColor: 'rgba(255,255,255,.45)' }">{{ folderInitials(maintDevDetail.dev.email.split('@')[0]) }}</div>
                      <div>
                        <div class="df-detail-name">{{ maintDevDetail.dev.email.split('@')[0] }}</div>
                        <div class="df-detail-email">{{ maintDevDetail.dev.email }}</div>
                        <button
                          :class="['df-vis-badge', getMaintDevVisibility(maintDevDetail.dev.email) === 'public' ? 'df-vis--pub' : 'df-vis--priv']"
                          style="margin-top:5px;"
                          :title="getMaintDevVisibility(maintDevDetail.dev.email) === 'public' ? 'Click to make private' : 'Click to make public'"
                          @click.stop="toggleMaintDevVisibility(maintDevDetail.dev.email)"
                        >
                          <svg v-if="getMaintDevVisibility(maintDevDetail.dev.email) === 'public'" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                          <svg v-else width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                          {{ getMaintDevVisibility(maintDevDetail.dev.email) === 'public' ? 'Public' : 'Private' }}
                        </button>
                      </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;">
                      <!-- Color picker -->
                      <div class="df-color-picker-wrap" @click.stop>
                        <button class="df-detail-close" title="Change color" @click.stop="maintColorPickerOpen = !maintColorPickerOpen">
                          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/></svg>
                        </button>
                        <div v-if="maintColorPickerOpen" class="df-color-swatches">
                          <button
                            v-for="c in MAINT_DEV_COLORS" :key="c"
                            class="df-color-swatch"
                            :class="{ 'df-color-swatch--active': c === maintDevDetail.color }"
                            :style="{ background: c }"
                            @click.stop="setMaintDevColor(maintDevDetail.dev.email, c)"
                          ></button>
                        </div>
                      </div>
                      <button class="df-detail-close" @click="closeMaintDevDetail">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
                      </button>
                    </div>
                  </div>

                  <div v-if="maintDevDetailLoading" style="text-align:center;padding:36px;color:var(--gray-400);">
                    <div style="width:26px;height:26px;border:3px solid #e2e8f0;border-top-color:#059669;border-radius:50%;animation:spin .7s linear infinite;margin:0 auto 10px;"></div>
                    Loading tickets…
                  </div>

                  <div v-else class="df-detail-body">
                    <div class="df-detail-stats">
                      <div class="df-detail-stat" style="--sc:#d97706;--sc-bg:#fffbeb;--sc-bd:#fde68a;">
                        <div class="df-detail-stat-num">{{ maintDevDetailStats.pending }}</div>
                        <div class="df-detail-stat-lbl">Pending</div>
                      </div>
                      <div class="df-detail-stat" style="--sc:#2563eb;--sc-bg:#eff6ff;--sc-bd:#bfdbfe;">
                        <div class="df-detail-stat-num">{{ maintDevDetailStats.in_progress }}</div>
                        <div class="df-detail-stat-lbl">In Progress</div>
                      </div>
                      <div class="df-detail-stat" style="--sc:#7c3aed;--sc-bg:#f5f3ff;--sc-bd:#ddd6fe;">
                        <div class="df-detail-stat-num">{{ maintDevDetailStats.on_hold }}</div>
                        <div class="df-detail-stat-lbl">On Hold</div>
                      </div>
                      <div class="df-detail-stat" style="--sc:#15803d;--sc-bg:#f0fdf4;--sc-bd:#bbf7d0;">
                        <div class="df-detail-stat-num">{{ maintDevDetailStats.completed }}</div>
                        <div class="df-detail-stat-lbl">Completed</div>
                      </div>
                    </div>

                    <div v-if="maintDevDetailTickets.length" class="df-detail-bugs">
                      <div class="df-detail-bugs-title">Assigned Tickets</div>
                      <div v-for="t in maintDevDetailTickets" :key="t.id" class="df-detail-bug-row">
                        <span class="df-detail-bug-seq">{{ t.ticket_number }}</span>
                        <span class="df-detail-bug-title">{{ t.client }} — {{ t.request }}</span>
                        <span :class="['badge', maintStatusBadgeClass(t.status)]">{{ t.status }}</span>
                      </div>
                    </div>
                    <div v-else style="text-align:center;padding:28px 0;color:var(--gray-400);font-size:13px;">
                      No tickets assigned yet
                    </div>
                  </div>

                  <div class="df-detail-footer">
                    <a :href="maintenanceFolderUrl(maintDevDetail.dev.email)" target="_blank" rel="noopener" class="btn btn-sm" style="background:#059669;color:#fff;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
                      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                      Open Full Folder
                    </a>
                  </div>
                </div>
              </div>
            </Transition>
          </Teleport>

          <!-- ── Dev Folder Detail Modal ── -->
          <Teleport to="body">
            <Transition name="modal-fade">
              <div v-if="devFolderDetail" class="df-detail-overlay" @click.self="closeFolderDetail">
                <div class="df-detail-modal">
                  <!-- Modal header -->
                  <div class="df-detail-header" :style="{ background: `linear-gradient(135deg, ${devFolderDetail.avatar_color || '#4f46e5'} 0%, ${devFolderDetail.avatar_color || '#4f46e5'}cc 100%)` }">
                    <div style="display:flex;align-items:center;gap:14px;">
                      <div class="df-detail-avatar" :style="{ background: 'rgba(255,255,255,.22)', borderColor: 'rgba(255,255,255,.45)' }">{{ folderInitials(devFolderDetail.developer_name) }}</div>
                      <div>
                        <div class="df-detail-name">{{ devFolderDetail.developer_name }}</div>
                        <div class="df-detail-email">{{ devFolderDetail.developer_email }}</div>
                      </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;">
                      <!-- Color picker -->
                      <div class="df-color-picker-wrap" @click.stop>
                        <button class="df-detail-close" title="Change color" @click.stop="colorPickerOpen = !colorPickerOpen">
                          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/></svg>
                        </button>
                        <div v-if="colorPickerOpen" class="df-color-swatches">
                          <button
                            v-for="c in AVATAR_COLORS" :key="c"
                            class="df-color-swatch"
                            :class="{ 'df-color-swatch--active': c === devFolderDetail.avatar_color }"
                            :style="{ background: c }"
                            @click.stop="updateFolderColor(devFolderDetail, c)"
                          ></button>
                        </div>
                      </div>
                      <button class="df-detail-close" @click="closeFolderDetail">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
                      </button>
                    </div>
                  </div>

                  <div v-if="devFolderDetailLoading" style="text-align:center;padding:36px;color:var(--gray-400);">
                    <div style="width:26px;height:26px;border:3px solid #e2e8f0;border-top-color:#6366f1;border-radius:50%;animation:spin .7s linear infinite;margin:0 auto 10px;"></div>
                    Loading tickets…
                  </div>

                  <div v-else class="df-detail-body">
                    <!-- Stat row -->
                    <div class="df-detail-stats">
                      <div class="df-detail-stat" style="--sc:#4f46e5;--sc-bg:#eef2ff;--sc-bd:#c7d2fe;">
                        <div class="df-detail-stat-num">{{ folderDetailStats.total }}</div>
                        <div class="df-detail-stat-lbl">Total</div>
                      </div>
                      <div class="df-detail-stat" style="--sc:#d97706;--sc-bg:#fffbeb;--sc-bd:#fde68a;">
                        <div class="df-detail-stat-num">{{ folderDetailStats.pending }}</div>
                        <div class="df-detail-stat-lbl">Pending</div>
                      </div>
                      <div class="df-detail-stat" style="--sc:#2563eb;--sc-bg:#eff6ff;--sc-bd:#bfdbfe;">
                        <div class="df-detail-stat-num">{{ folderDetailStats.ongoing }}</div>
                        <div class="df-detail-stat-lbl">Ongoing</div>
                      </div>
                      <div class="df-detail-stat" style="--sc:#15803d;--sc-bg:#f0fdf4;--sc-bd:#bbf7d0;">
                        <div class="df-detail-stat-num">{{ folderDetailStats.completed }}</div>
                        <div class="df-detail-stat-lbl">Completed</div>
                      </div>
                    </div>

                    <!-- Bug list -->
                    <div v-if="devFolderDetailBugs.length" class="df-detail-bugs">
                      <div class="df-detail-bugs-title">Assigned Tickets</div>
                      <div v-for="bug in devFolderDetailBugs" :key="bug.id" class="df-detail-bug-row">
                        <span class="df-detail-bug-seq">#{{ bug.sequence }}</span>
                        <span class="df-detail-bug-title">{{ bug.title }}</span>
                        <span :class="['badge', priorityBadgeClass(bug.priority)]">{{ bug.priority }}</span>
                        <span :class="['badge', statusBadgeClass(bug.status)]">{{ bug.status }}</span>
                      </div>
                    </div>
                    <div v-else style="text-align:center;padding:28px 0;color:var(--gray-400);font-size:13px;">
                      No tickets assigned yet
                    </div>
                  </div>
                </div>
              </div>
            </Transition>
          </Teleport>
          </template>
        </div>

        <!-- ══ Project view ══ -->
        <div v-else>

          <!-- ── Detail Charts View ── -->
          <div v-if="detailView">
            <div class="view-header">
              <div style="display:flex;align-items:center;gap:12px;">
                <button class="btn btn-ghost btn-sm" style="gap:4px;" @click="detailView = null">
                  <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                  Back
                </button>
                <div class="project-view-name">
                  <span class="project-view-dot" :style="{ background: selectedProject.color }"></span>
                  <h1 class="view-title" style="margin:0;">{{ detailViewTitle }}</h1>
                </div>
              </div>
              <div style="font-size:13px;color:var(--gray-500);">{{ bugs.length }} total bugs · Last 30 days tracked</div>
            </div>

            <!-- Detail stat chips -->
            <div class="detail-stat-chips">
              <div
                v-for="item in detailGroupItems" :key="item.key"
                class="detail-chip"
                :style="{ '--chip-color': item.color }"
              >
                <span class="chip-dot" :style="{ background: item.color }"></span>
                <span class="chip-label">{{ item.key }}</span>
                <span class="chip-count">{{ item.count }}</span>
                <span class="chip-pct">{{ item.pct }}%</span>
              </div>
            </div>

            <!-- Charts grid -->
            <div class="charts-grid">
              <div class="chart-card">
                <div class="chart-card-header">
                  <h3 class="chart-card-title">Current Distribution</h3>
                  <span class="chart-card-sub">{{ bugs.length }} total</span>
                </div>
                <div class="chart-wrap doughnut-wrap">
                  <canvas ref="doughnutChartEl"></canvas>
                  <div class="doughnut-center">
                    <div class="doughnut-total">{{ bugs.length }}</div>
                    <div class="doughnut-label">Total</div>
                  </div>
                </div>
              </div>

              <div class="chart-card" style="flex:1.6;">
                <div class="chart-card-header">
                  <h3 class="chart-card-title">Trend — Last 30 Days</h3>
                  <span class="chart-card-sub">Cumulative bugs added</span>
                </div>
                <div class="chart-wrap line-wrap">
                  <canvas ref="lineChartEl"></canvas>
                </div>
              </div>
            </div>

            <!-- Breakdown table -->
            <div class="card" style="margin-top:20px;">
              <div class="card-header">
                <h3 class="card-title">{{ detailViewTitle }} Breakdown</h3>
              </div>
              <div class="card-body" style="padding:0;">
                <div v-for="item in detailGroupItems" :key="item.key" class="breakdown-row">
                  <div class="breakdown-left">
                    <span class="breakdown-dot" :style="{ background: item.color }"></span>
                    <span class="breakdown-name">{{ item.key }}</span>
                  </div>
                  <div class="breakdown-bar-wrap">
                    <div class="breakdown-bar" :style="{ width: item.pct + '%', background: item.color + 'cc' }"></div>
                  </div>
                  <div class="breakdown-right">
                    <span class="breakdown-count">{{ item.count }}</span>
                    <span class="breakdown-pct">{{ item.pct }}%</span>
                  </div>
                </div>
                <div v-if="!detailGroupItems.length" class="empty-state" style="padding:32px;">
                  <div class="empty-state-title">No data yet</div>
                </div>
              </div>
            </div>
          </div>

          <!-- ── Normal Bugs View ── -->
          <div v-else>
            <!-- Project header -->
            <div class="view-header">
              <div style="display:flex;align-items:center;gap:12px;">
                <button class="btn btn-ghost btn-sm" style="gap:4px;" @click="selectProject(null)">
                  <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                  All Projects
                </button>
                <div class="project-view-name">
                  <span class="project-view-dot" :style="{ background: selectedProject.color }"></span>
                  <h1 class="view-title" style="margin:0;">{{ selectedProject.name }}</h1>
                </div>
              </div>
              <div style="display:flex;gap:8px;">
                <button v-if="currentUser && isProjectOwner(selectedProject)" class="btn btn-secondary btn-sm" style="gap:5px;" @click="openShareModal(selectedProject)">
                  <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                  Share
                </button>
                <button v-if="canEditProject(selectedProject)" class="btn btn-primary" @click="openCreateModal">
                  <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                  Report Bug
                </button>
              </div>
            </div>

            <!-- Summary cards -->
            <div class="summary-grid">
              <div class="summary-card">
                <!-- Completion badge -->
                <div style="position:absolute;top:14px;right:16px;font-size:11px;font-weight:700;color:#15803d;background:#dcfce7;padding:2px 9px;border-radius:20px;letter-spacing:.01em;">
                  {{ completionPct }}% done
                </div>
                <div class="summary-card-icon">🐛</div>
                <div class="summary-card-label">Total Bugs</div>
                <div class="summary-card-value">{{ summary.total || 0 }}</div>
                <!-- Segmented progress bar -->
                <div style="margin-top:14px;height:6px;border-radius:999px;background:#f1f5f9;overflow:hidden;display:flex;gap:2px;">
                  <div v-if="pendingPct"  :style="{ width: pendingPct  + '%', background: '#f59e0b', borderRadius: '999px', flexShrink: 0 }"></div>
                  <div v-if="ongoingPct"  :style="{ width: ongoingPct  + '%', background: '#3b82f6', borderRadius: '999px', flexShrink: 0 }"></div>
                  <div v-if="completionPct" :style="{ width: completionPct + '%', background: '#22c55e', borderRadius: '999px', flexShrink: 0 }"></div>
                </div>
                <div style="display:flex;gap:10px;margin-top:8px;flex-wrap:wrap;">
                  <span style="font-size:10px;color:#92400e;display:flex;align-items:center;gap:4px;"><span style="width:7px;height:7px;border-radius:50%;background:#f59e0b;display:inline-block;flex-shrink:0;"></span>Pending</span>
                  <span style="font-size:10px;color:#1d4ed8;display:flex;align-items:center;gap:4px;"><span style="width:7px;height:7px;border-radius:50%;background:#3b82f6;display:inline-block;flex-shrink:0;"></span>Ongoing</span>
                  <span style="font-size:10px;color:#15803d;display:flex;align-items:center;gap:4px;"><span style="width:7px;height:7px;border-radius:50%;background:#22c55e;display:inline-block;flex-shrink:0;"></span>Done</span>
                </div>
              </div>

              <div class="summary-card summary-card-clickable" @click="openDetailView('status')">
                <div class="summary-card-click-hint">View Details
                  <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </div>
                <div class="summary-card-icon">📊</div>
                <div class="summary-card-label">By Status</div>
                <div class="summary-card-sub">
                  <span v-for="(count, status) in summary.by_status" :key="status" :class="['badge', statusBadgeClass(status)]">{{ status }}: {{ count }}</span>
                  <span v-if="!summary.by_status || !Object.keys(summary.by_status).length" style="color:var(--gray-400);font-size:12px;">No data yet</span>
                </div>
              </div>

              <div class="summary-card summary-card-clickable" @click="openDetailView('priority')">
                <div class="summary-card-click-hint">View Details
                  <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </div>
                <div class="summary-card-icon">🔥</div>
                <div class="summary-card-label">By Priority</div>
                <div class="summary-card-sub">
                  <span v-for="(count, priority) in summary.by_priority" :key="priority" :class="['badge', priorityBadgeClass(priority)]">{{ priority }}: {{ count }}</span>
                  <span v-if="!summary.by_priority || !Object.keys(summary.by_priority).length" style="color:var(--gray-400);font-size:12px;">No data yet</span>
                </div>
              </div>

              <div class="summary-card summary-card-clickable" @click="openDetailView('scenario')">
                <div class="summary-card-click-hint">View Details
                  <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </div>
                <div class="summary-card-icon">🎯</div>
                <div class="summary-card-label">By Scenario Type</div>
                <div class="summary-card-sub">
                  <span v-for="(count, type) in summary.by_scenario_type" :key="type" :class="['badge', scenarioBadgeClass(type)]">{{ type }}: {{ count }}</span>
                  <span v-if="!summary.by_scenario_type || !Object.keys(summary.by_scenario_type).length" style="color:var(--gray-400);font-size:12px;">No data yet</span>
                </div>
              </div>
            </div>

            <!-- Bug table -->
            <div class="card">
              <div class="card-header">
                <h2 class="card-title">Bug Reports</h2>
                <span class="result-chip">{{ filteredBugs.length }} result{{ filteredBugs.length !== 1 ? 's' : '' }}</span>
              </div>
              <div class="card-body">
                <div class="filters-bar">
                  <div class="search-input-wrap">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    <input v-model="filters.search" class="form-control" placeholder="Search bugs..." />
                  </div>
                  <select v-model="filters.status" class="form-control">
                    <option value="">All Statuses</option>
                    <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
                  </select>
                  <select v-model="filters.priority" class="form-control">
                    <option value="">All Priorities</option>
                    <option v-for="p in priorities" :key="p" :value="p">{{ p }}</option>
                  </select>
                  <select v-model="filters.scenario_type" class="form-control">
                    <option value="">All Scenarios</option>
                    <option v-for="t in scenarioTypes" :key="t" :value="t">{{ t }}</option>
                  </select>
                  <button v-if="hasActiveFilters" class="btn btn-ghost btn-sm" @click="clearFilters">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    Clear
                  </button>
                </div>

                <div class="table-wrap">
                  <table v-if="filteredBugs.length > 0">
                    <thead>
                      <tr>
                        <th style="width:64px;">#</th>
                        <th>Title</th>
                        <th style="width:110px;">Priority</th>
                        <th style="width:160px;">Scenario</th>
                        <th style="width:180px;">Status</th>
                        <th style="width:220px;">Comment</th>
                        <th style="width:150px;">Assign Dev</th>
                        <th style="width:160px;">Dev Status</th>
                        <th style="width:120px;">Due Date</th>
                        <th style="width:130px;">Resolved By</th>
                        <th style="width:130px;text-align:center;">QA Notify</th>
                        <th style="width:130px;text-align:center;">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="bug in filteredBugs" :key="bug.id" :class="{ 'bug-row-completed': bug.status === 'Completed' }">
                        <td><span class="bug-seq">#{{ bug.sequence }}</span></td>
                        <td>
                          <div class="bug-title-main bug-title-link" @click="viewBug(bug)">{{ bug.title }}</div>
                          <div v-if="bug.description" class="bug-title-desc">{{ stripHtml(bug.description) }}</div>
                        </td>
                        <td><span :class="['badge', priorityBadgeClass(bug.priority)]">{{ bug.priority }}</span></td>
                        <td><span :class="['badge', scenarioBadgeClass(bug.scenario_type)]">{{ bug.scenario_type }}</span></td>
                        <td>
                          <select :value="bug.status" :class="['status-select', 'status-select--' + bug.status.toLowerCase().replace(/\s+/g,'-')]" @change="updateStatus(bug, $event.target.value)">
                            <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
                          </select>
                        </td>
                        <td class="dev-comment-cell">
                          <div class="dev-thread-trigger" @click="openThread(bug)">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            <span v-if="bug.dev_comments && bug.dev_comments.length" class="dev-thread-count">{{ bug.dev_comments.length }}</span>
                            <span v-if="bug.dev_comments && bug.dev_comments.length" class="dev-thread-preview">{{ bug.dev_comments[bug.dev_comments.length - 1].message }}</span>
                            <span v-else class="dev-comment-empty">Add comment…</span>
                          </div>
                          <div
                            v-if="(bug.activity_log || []).filter(e => e.type === 'comment').length"
                            class="ticket-replies-badge"
                            @click="viewBug(bug)"
                            title="View ticket replies"
                          >
                            <svg width="11" height="11" fill="currentColor" viewBox="0 0 24 24"><path d="M20 2H4a2 2 0 0 0-2 2v18l4-4h14a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2z"/></svg>
                            {{ (bug.activity_log || []).filter(e => e.type === 'comment').length }} ticket {{ (bug.activity_log || []).filter(e => e.type === 'comment').length === 1 ? 'reply' : 'replies' }}
                          </div>
                        </td>
                        <!-- Assign Dev -->
                        <td style="position:relative;" @click.stop>
                          <div class="assign-dev-wrap">
                            <button
                              :class="['assign-dev-btn', { 'assign-dev-btn--assigned': bug.assigned_developers?.length }]"
                              @click.stop="openAssignDropdown(bug.id)"
                            >
                              <template v-if="bug.assigned_developers?.length">
                                <span class="assign-avatars-stack">
                                  <span
                                    v-for="(dev, i) in bug.assigned_developers.slice(0, 3)" :key="i"
                                    class="assign-avatar-xs"
                                    :style="{ marginLeft: i > 0 ? '-6px' : '0', zIndex: 3 - i }"
                                  >{{ dev.name?.[0]?.toUpperCase() }}</span>
                                </span>
                                <span v-if="bug.assigned_developers.length === 1">{{ bug.assigned_developers[0].name.split(' ')[0] }}</span>
                                <span v-else>{{ bug.assigned_developers.length }} devs</span>
                              </template>
                              <template v-else>
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                                Assign dev
                              </template>
                            </button>
                            <div v-if="assignDropdownBugId === bug.id" class="assign-dropdown" @click.stop>
                              <!-- Search / email input -->
                              <div class="assign-search-wrap">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                                <input
                                  v-model="assignSearch"
                                  class="assign-search-input"
                                  placeholder="Search name or enter email…"
                                  @click.stop
                                  ref="assignSearchInput"
                                />
                              </div>
                              <!-- Assigned devs with remove buttons -->
                              <template v-if="bug.assigned_developers?.length">
                                <div class="assign-dropdown-divider"></div>
                                <div class="assign-dropdown-section-label">Assigned ({{ bug.assigned_developers.length }})</div>
                                <div
                                  v-for="dev in bug.assigned_developers" :key="dev.email"
                                  class="assign-dropdown-item assign-dropdown-item--assigned"
                                >
                                  <span class="assign-avatar-xs" style="background:#4f46e5;">{{ dev.name?.[0]?.toUpperCase() }}</span>
                                  <div style="flex:1;min-width:0;">
                                    <div class="assign-member-name">{{ dev.name }}</div>
                                    <div class="assign-member-email">{{ dev.email }}</div>
                                  </div>
                                  <button class="assign-remove-btn" @click.stop="removeDev(bug, dev)" title="Remove">
                                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                  </button>
                                </div>
                              </template>
                              <div class="assign-dropdown-divider"></div>
                              <!-- Filtered team members -->
                              <div v-if="filteredAssignMembers.length" class="assign-dropdown-section-label">Team members</div>
                              <div
                                v-for="member in filteredAssignMembers" :key="member.id"
                                class="assign-dropdown-item"
                                :class="{ 'assign-dropdown-item--active': (bug.assigned_developers || []).some(d => d.id === member.id) }"
                                @click.stop="assignDev(bug, member)"
                              >
                                <span class="assign-avatar-xs">{{ member.name?.[0]?.toUpperCase() }}</span>
                                <div>
                                  <div class="assign-member-name">{{ member.name }}</div>
                                  <div class="assign-member-email">{{ member.email }}</div>
                                </div>
                                <svg v-if="(bug.assigned_developers || []).some(d => d.id === member.id)" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="margin-left:auto;color:#4f46e5;flex-shrink:0;"><polyline points="20 6 9 17 4 12"/></svg>
                              </div>
                              <!-- Manual email assign option -->
                              <template v-if="assignSearch.includes('@') && !(bug.assigned_developers || []).some(d => d.email === assignSearch) && !filteredAssignMembers.some(m => m.email === assignSearch)">
                                <div class="assign-dropdown-divider"></div>
                                <div class="assign-dropdown-item assign-dropdown-email-option" @click.stop="assignDevByEmail(bug, assignSearch)">
                                  <span class="assign-avatar-xs" style="background:#64748b;">@</span>
                                  <div>
                                    <div class="assign-member-name">Assign by email</div>
                                    <div class="assign-member-email">{{ assignSearch }}</div>
                                  </div>
                                  <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="margin-left:auto;color:#4f46e5;flex-shrink:0;"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                </div>
                              </template>
                              <!-- Empty state -->
                              <div v-if="!filteredAssignMembers.length && !assignSearch.includes('@')" class="assign-dropdown-empty">
                                {{ assignSearch ? 'No match — type a full email to assign' : 'No team members yet' }}
                              </div>
                            </div>
                          </div>
                        </td>

                        <!-- Dev Status -->
                        <td @click.stop>
                          <select
                            :value="bug.dev_status || 'Not Started'"
                            :class="['dev-status-select', 'dev-status--' + (bug.dev_status || 'Not Started').toLowerCase().replace(/\s+/g, '-')]"
                            @change="updateDevStatus(bug, $event.target.value)"
                          >
                            <option value="Not Started">Not Started</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Ready for QA">Ready for QA ✅</option>
                            <option value="Blocked">Blocked ⚠️</option>
                          </select>
                        </td>

                        <!-- Due Date -->
                        <td>
                          <template v-if="bug.date_to_accomplish">
                            <div style="display:flex;flex-direction:column;gap:3px;">
                              <span :class="['accomplish-date', isAccomplishOverdue(bug) ? 'accomplish-date--overdue' : '']">{{ formatBugDate(bug.date_to_accomplish) }}</span>
                              <span v-if="dueDaysLabel(bug)" class="due-days-pill-sm" :style="{ color: dueDaysLabel(bug).color, borderColor: dueDaysLabel(bug).color + '40', background: dueDaysLabel(bug).color + '12' }">{{ dueDaysLabel(bug).text }}</span>
                            </div>
                          </template>
                          <span v-else style="color:var(--gray-300);padding-left:4px;">—</span>
                        </td>

                        <!-- Resolved By -->
                        <td>
                          <span v-if="bug.resolved_by" class="resolved-by-name">{{ bug.resolved_by }}</span>
                          <span v-else style="color:var(--gray-300);padding-left:4px;">—</span>
                        </td>

                        <!-- QA Notify -->
                        <td style="text-align:center;">
                          <template v-if="bug.dev_status === 'Ready for QA'">
                            <span class="qa-notify-badge qa-notify-badge--ready">
                              🔔 QA: Verify
                            </span>
                          </template>
                          <template v-else-if="bug.dev_status === 'Blocked'">
                            <span class="qa-notify-badge qa-notify-badge--blocked">
                              ⚠️ Blocked
                            </span>
                          </template>
                          <template v-else-if="bug.dev_status === 'In Progress' && bug.ticket_sent_at">
                            <button
                              class="send-ticket-btn send-ticket-btn--resend-dev"
                              :disabled="!bug.has_assignment"
                              title="Resend ticket to developer"
                              @click="openResendTicketModal(bug)"
                            >
                              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-4.5"/></svg>
                              Send to Dev
                            </button>
                          </template>
                          <template v-else>
                            <div v-if="bug.ticket_sent_at" style="display:flex;align-items:center;gap:4px;">
                              <span class="send-ticket-btn send-ticket-btn--sent" style="pointer-events:none;">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                Sent
                              </span>
                              <button
                                class="resend-ticket-btn"
                                title="Resend ticket to developer"
                                @click="openResendTicketModal(bug)"
                              >
                                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-4.5"/></svg>
                              </button>
                            </div>
                            <button
                              v-else
                              :class="['send-ticket-btn', bug.has_assignment ? 'send-ticket-btn--active' : 'send-ticket-btn--disabled']"
                              :disabled="!bug.has_assignment"
                              :title="!bug.has_assignment ? 'Assign a developer first' : 'Send ticket to developer'"
                              @click="openSendTicketModal(bug)"
                            >
                              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                              Send
                            </button>
                          </template>
                        </td>

                        <td>
                          <div style="display:flex;gap:6px;justify-content:center;">
                            <button class="btn btn-icon action-btn-view" title="View" @click="viewBug(bug)">
                              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                            <button class="btn btn-icon action-btn-edit" title="Edit" @click="openEditModal(bug)">
                              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </button>
                            <button class="btn btn-icon action-btn-delete" title="Delete" @click="confirmDelete(bug)">
                              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                            </button>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  <div v-else class="empty-state">
                    <div class="empty-state-icon">🔍</div>
                    <div class="empty-state-title">No bugs found</div>
                    <div class="empty-state-text">{{ hasActiveFilters ? 'Try adjusting your filters' : 'Click "+ Report Bug" to log the first issue' }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </main>
    </div>

    <!-- ══ Modals ══ -->

    <!-- Create/Edit Bug -->
    <Transition name="fade">
      <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
        <div class="modal">
          <div class="modal-header">
            <h3 class="modal-title">{{ editingBug ? 'Edit Bug' : 'Report New Bug' }}</h3>
            <button class="btn btn-ghost btn-icon" @click="closeModal">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
          </div>
          <form @submit.prevent="submitForm">
            <div class="modal-body">
              <div class="form-group">
                <label class="form-label">Title <span class="required">*</span></label>
                <input v-model="form.title" class="form-control" placeholder="Brief description of the bug" required />
              </div>

              <!-- Subtitles -->
              <div class="form-group">
                <label class="form-label">Subtitles</label>
                <div class="subtitle-list">
                  <div v-for="(_, idx) in form.subtitles" :key="idx" class="subtitle-row">
                    <div class="subtitle-fields">
                      <input
                        v-model="form.subtitles[idx].text"
                        class="form-control"
                        :placeholder="`Subtitle ${idx + 1}`"
                      />
                      <div class="subtitle-link-wrap">
                        <svg class="subtitle-link-icon" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                        <input
                          v-model="form.subtitles[idx].link"
                          class="form-control"
                          placeholder="https://..."
                        />
                      </div>
                    </div>
                    <button type="button" class="btn btn-icon action-btn-delete" @click="removeSubtitle(idx)" :disabled="form.subtitles.length === 1">
                      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                  </div>
                  <button type="button" class="btn btn-ghost btn-sm" style="margin-top:6px;gap:5px;" @click="addSubtitle">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                    Add Subtitle
                  </button>
                </div>
              </div>

              <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                  <label class="form-label">Priority <span class="required">*</span></label>
                  <select v-model="form.priority" class="form-control" required>
                    <option v-for="p in priorities" :key="p" :value="p">{{ p }}</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">Scenario Type <span class="required">*</span></label>
                  <select v-model="form.scenario_type" class="form-control" required>
                    <option v-for="t in scenarioTypes" :key="t" :value="t">{{ t }}</option>
                  </select>
                </div>
              </div>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group" style="margin-bottom:0;">
                  <label class="form-label">Status <span class="required">*</span></label>
                  <select v-model="form.status" class="form-control" required>
                    <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
                  </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                  <label class="form-label">Date to Accomplish</label>
                  <input type="date" v-model="form.date_to_accomplish" class="form-control" />
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">Description</label>
                <div class="rte-wrap">
                  <div class="rte-toolbar">
                    <button type="button" class="rte-btn" :class="{ active: rteActive.bold }" @mousedown.prevent="execCmd('bold')" title="Bold"><b>B</b></button>
                    <button type="button" class="rte-btn" :class="{ active: rteActive.italic }" @mousedown.prevent="execCmd('italic')" title="Italic"><i>I</i></button>
                    <div class="rte-divider"></div>
                    <button type="button" class="rte-btn" @mousedown.prevent="execCmd('insertUnorderedList')" title="Bullet List">
                      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="9" y1="6" x2="20" y2="6"/><line x1="9" y1="12" x2="20" y2="12"/><line x1="9" y1="18" x2="20" y2="18"/><circle cx="4" cy="6" r="1.5" fill="currentColor"/><circle cx="4" cy="12" r="1.5" fill="currentColor"/><circle cx="4" cy="18" r="1.5" fill="currentColor"/></svg>
                    </button>
                    <button type="button" class="rte-btn" @mousedown.prevent="execCmd('insertOrderedList')" title="Numbered List">
                      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="10" y1="6" x2="21" y2="6"/><line x1="10" y1="12" x2="21" y2="12"/><line x1="10" y1="18" x2="21" y2="18"/><text x="1" y="8" font-size="7" fill="currentColor" stroke="none" font-weight="700">1.</text><text x="1" y="14" font-size="7" fill="currentColor" stroke="none" font-weight="700">2.</text><text x="1" y="20" font-size="7" fill="currentColor" stroke="none" font-weight="700">3.</text></svg>
                    </button>
                  </div>
                  <div
                    ref="descEditor"
                    class="rte-editor"
                    contenteditable="true"
                    data-placeholder="Steps to reproduce, expected vs actual behavior..."
                    @input="form.description = $event.target.innerHTML"
                    @keyup="updateRteState"
                    @mouseup="updateRteState"
                  ></div>
                </div>
              </div>
              <!-- Screenshots: Front-End -->
              <div class="form-group">
                <label class="form-label">Screenshots — <span style="color:var(--primary);">Front-End</span></label>
                <div class="upload-area" @click="$refs.frontendFileInput.click()" @dragover.prevent @drop.prevent="handleFrontendFileDrop">
                  <input ref="frontendFileInput" type="file" multiple accept="image/*" @change="handleFrontendFileSelect" />
                  <div style="font-size:20px;margin-bottom:4px;">🖥️</div>
                  <div style="font-weight:600;color:var(--gray-600);">Click to attach or drag &amp; drop</div>
                  <div style="font-size:11px;margin-top:3px;color:var(--gray-400);">PNG, JPG, GIF up to 5MB each</div>
                </div>
                <div v-if="frontendPreviewList.length > 0" class="image-preview-grid">
                  <div v-for="(img, idx) in frontendPreviewList" :key="idx" class="image-preview-item">
                    <img :src="img.url" :alt="`FE ${idx+1}`" />
                    <button type="button" class="image-preview-remove" @click="removeFrontendImage(idx)">✕</button>
                  </div>
                </div>
              </div>

              <!-- Screenshots: CMS -->
              <div class="form-group">
                <label class="form-label">Screenshots — <span style="color:#10b981;">CMS</span></label>
                <div class="upload-area" @click="$refs.cmsFileInput.click()" @dragover.prevent @drop.prevent="handleCmsFileDrop">
                  <input ref="cmsFileInput" type="file" multiple accept="image/*" @change="handleCmsFileSelect" />
                  <div style="font-size:20px;margin-bottom:4px;">⚙️</div>
                  <div style="font-weight:600;color:var(--gray-600);">Click to attach or drag &amp; drop</div>
                  <div style="font-size:11px;margin-top:3px;color:var(--gray-400);">PNG, JPG, GIF up to 5MB each</div>
                </div>
                <div v-if="cmsPreviewList.length > 0" class="image-preview-grid">
                  <div v-for="(img, idx) in cmsPreviewList" :key="idx" class="image-preview-item">
                    <img :src="img.url" :alt="`CMS ${idx+1}`" />
                    <button type="button" class="image-preview-remove" @click="removeCmsImage(idx)">✕</button>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="closeModal">Cancel</button>
              <button type="submit" class="btn btn-primary" :disabled="submitting">
                {{ submitting ? 'Saving...' : (editingBug ? 'Update Bug' : 'Create Bug') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <!-- Create/Edit Project -->
    <Transition name="fade">
      <div v-if="showProjectModal" class="modal-overlay" @click.self="showProjectModal = false">
        <div class="modal" style="max-width:480px;">
          <div class="modal-header">
            <h3 class="modal-title">{{ editingProject ? 'Edit Project' : 'New Project' }}</h3>
            <button class="btn btn-ghost btn-icon" @click="showProjectModal = false">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
          </div>
          <form @submit.prevent="submitProject">
            <div class="modal-body">
              <div class="form-group">
                <label class="form-label">Project Name <span class="required">*</span></label>
                <input v-model="projectForm.name" class="form-control" placeholder="e.g. Web App v2.0" required />
              </div>
              <div class="form-group">
                <label class="form-label">Description</label>
                <textarea v-model="projectForm.description" class="form-control" placeholder="What is this project about?" rows="3"></textarea>
              </div>
              <div class="form-group">
                <label class="form-label">Color</label>
                <div class="color-swatches">
                  <button v-for="c in projectColors" :key="c" type="button" class="color-swatch"
                    :class="{ selected: projectForm.color === c }" :style="{ background: c }" @click="projectForm.color = c"></button>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">Status</label>
                <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;background:var(--gray-50);border:1px solid var(--gray-200);border-radius:8px;">
                  <span style="font-size:13px;color:var(--gray-700);flex:1;">
                    {{ projectForm.is_active ? 'Active — bugs can be reported' : 'Inactive — project is closed' }}
                  </span>
                  <button type="button" @click="projectForm.is_active = !projectForm.is_active"
                    :style="{ display:'flex', alignItems:'center', width:'44px', height:'24px', borderRadius:'12px', border:'none', cursor:'pointer', padding:'2px', transition:'background .2s', background: projectForm.is_active ? '#4f46e5' : '#d1d5db', justifyContent: projectForm.is_active ? 'flex-end' : 'flex-start' }">
                    <span style="width:20px;height:20px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.2);"></span>
                  </button>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="showProjectModal = false">Cancel</button>
              <button type="submit" class="btn btn-primary" :disabled="submitting">
                {{ submitting ? 'Saving...' : (editingProject ? 'Update Project' : 'Create Project') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <!-- Delete Bug -->
    <Transition name="fade">
      <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
        <div class="modal" style="max-width:420px;">
          <div class="modal-header">
            <h3 class="modal-title">Delete Bug</h3>
            <button class="btn btn-ghost btn-icon" @click="showDeleteModal = false"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
          </div>
          <div class="modal-body">
            <div style="display:flex;gap:16px;align-items:flex-start;">
              <div style="width:42px;height:42px;border-radius:50%;background:var(--danger-light);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:20px;">🗑️</div>
              <div>
                <p style="color:var(--gray-700);font-weight:500;margin-bottom:6px;">Are you sure you want to delete this bug?</p>
                <p style="color:var(--gray-500);font-size:13px;"><strong style="color:var(--gray-800);">{{ deletingBug?.title }}</strong> will be permanently removed.</p>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="showDeleteModal = false">Cancel</button>
            <button class="btn btn-danger" :disabled="submitting" @click="deleteBug">{{ submitting ? 'Deleting...' : 'Delete Bug' }}</button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Delete Project -->
    <Transition name="fade">
      <div v-if="showDeleteProjectModal" class="modal-overlay" @click.self="showDeleteProjectModal = false">
        <div class="modal" style="max-width:420px;">
          <div class="modal-header">
            <h3 class="modal-title">Delete Project</h3>
            <button class="btn btn-ghost btn-icon" @click="showDeleteProjectModal = false"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
          </div>
          <div class="modal-body">
            <div style="display:flex;gap:16px;align-items:flex-start;">
              <div style="width:42px;height:42px;border-radius:50%;background:var(--danger-light);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:20px;">📁</div>
              <div>
                <p style="color:var(--gray-700);font-weight:500;margin-bottom:6px;">Delete <strong style="color:var(--gray-900);">{{ deletingProject?.name }}</strong>?</p>
                <p style="color:var(--gray-500);font-size:13px;">All bugs inside this project will be unlinked. This cannot be undone.</p>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="showDeleteProjectModal = false">Cancel</button>
            <button class="btn btn-danger" :disabled="submitting" @click="deleteProject">{{ submitting ? 'Deleting...' : 'Delete Project' }}</button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Bug Detail View Modal -->
    <Transition name="fade">
      <div v-if="showViewModal" class="modal-overlay" @click.self="showViewModal = false">
        <div class="modal bug-view-modal">

          <!-- Priority accent bar -->
          <div class="bug-view-priority-bar" :class="`priority-bar-${(viewingBugDetail?.priority||'').toLowerCase()}`"></div>

          <!-- Header -->
          <div class="modal-header bug-view-header">
            <div class="bug-view-header-left">
              <span class="bug-seq">#{{ viewingBugDetail?.sequence }}</span>
              <h3 class="modal-title" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ viewingBugDetail?.title }}</h3>
            </div>
            <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
              <button class="btn btn-sm action-btn-edit" style="gap:5px;" @click="openEditModal(viewingBugDetail); showViewModal = false">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit
              </button>
              <button class="btn btn-ghost btn-icon" @click="showViewModal = false">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
              </button>
            </div>
          </div>

          <!-- Meta badges row -->
          <div class="bug-view-meta-bar">
            <div class="bug-view-meta-badges">
              <span :class="['badge', priorityBadgeClass(viewingBugDetail?.priority)]">{{ viewingBugDetail?.priority }}</span>
              <span :class="['badge', scenarioBadgeClass(viewingBugDetail?.scenario_type)]">{{ viewingBugDetail?.scenario_type }}</span>
              <span :class="['badge', statusBadgeClass(viewingBugDetail?.status)]">{{ viewingBugDetail?.status }}</span>
            </div>
            <div class="bug-view-reported-date">
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              Reported {{ formatBugDate(viewingBugDetail?.created_at) }}
            </div>
          </div>

          <div class="modal-body bug-view-body">

            <!-- Subtitles -->
            <div class="bug-view-section">
              <div class="bug-view-section-label">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                Subtitles
              </div>
              <div v-if="viewingBugDetail?.subtitles && viewingBugDetail.subtitles.length" class="bug-view-subtitle-list">
                <div v-for="(sub, idx) in viewingBugDetail.subtitles" :key="idx" class="bug-view-subtitle-card">
                  <div class="bug-view-subtitle-index">{{ idx + 1 }}</div>
                  <div class="bug-view-subtitle-content">
                    <div v-if="(typeof sub === 'string' ? sub : sub.text)" class="bug-view-subtitle-text">{{ typeof sub === 'string' ? sub : sub.text }}</div>
                    <a v-if="sub.link" :href="sub.link" target="_blank" rel="noopener" class="bug-view-subtitle-link">
                      <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                      {{ sub.link }}
                    </a>
                    <span v-else-if="typeof sub !== 'string'" class="bug-view-subtitle-nolink">No link</span>
                  </div>
                </div>
              </div>
              <div v-else class="bug-view-empty">No subtitles added.</div>
            </div>

            <!-- Description -->
            <div class="bug-view-section">
              <div class="bug-view-section-label">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                Description
              </div>
              <div v-if="viewingBugDetail?.description" class="bug-view-description" v-html="viewingBugDetail.description"></div>
              <div v-else class="bug-view-empty">No description provided.</div>
            </div>

            <!-- Developer Comments -->
            <div class="bug-view-section">
              <div class="bug-view-section-label" style="justify-content:space-between;">
                <span style="display:flex;align-items:center;gap:6px;">
                  <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                  Comments
                </span>
                <span v-if="viewingBugDetail?.dev_comments?.length" class="bug-view-count-pill">{{ viewingBugDetail.dev_comments.length }}</span>
              </div>
              <div class="thread-messages-inline">
                <div v-if="viewingBugDetail?.dev_comments && viewingBugDetail.dev_comments.length" class="thread-msg-list">
                  <div v-for="(msg, i) in viewingBugDetail.dev_comments" :key="i" class="thread-msg-item">
                    <div class="thread-msg-bubble">{{ msg.message }}</div>
                    <div class="thread-msg-time">{{ formatThreadTime(msg.timestamp) }}</div>
                  </div>
                </div>
                <div v-else class="bug-view-empty">No developer comments yet.</div>
              </div>
            </div>

            <!-- Ticket Replies -->
            <div class="bug-view-section">
              <div class="bug-view-section-label" style="justify-content:space-between;">
                <span style="display:flex;align-items:center;gap:6px;">
                  <svg width="13" height="13" fill="currentColor" viewBox="0 0 24 24"><path d="M20 2H4a2 2 0 0 0-2 2v18l4-4h14a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2z"/></svg>
                  Ticket Replies
                </span>
                <span v-if="(viewingBugDetail?.activity_log || []).filter(e => e.type === 'comment').length" class="bug-view-count-pill">{{ (viewingBugDetail.activity_log || []).filter(e => e.type === 'comment').length }}</span>
              </div>
              <div class="ticket-replies-list">
                <template v-if="(viewingBugDetail?.activity_log || []).filter(e => e.type === 'comment').length">
                  <div v-for="(entry, i) in (viewingBugDetail.activity_log || []).filter(e => e.type === 'comment')" :key="i" class="ticket-reply-item">
                    <div class="ticket-reply-avatar">{{ (entry.user_name || '?')[0].toUpperCase() }}</div>
                    <div class="ticket-reply-body">
                      <div class="ticket-reply-header">
                        <span class="ticket-reply-author">{{ entry.user_name }}</span>
                        <span class="ticket-reply-time">{{ formatThreadTime(entry.timestamp) }}</span>
                      </div>
                      <div class="ticket-reply-text">{{ entry.content }}</div>
                    </div>
                  </div>
                </template>
                <div v-else class="bug-view-empty">No ticket replies yet.</div>
              </div>
            </div>

            <!-- Screenshots — side by side -->
            <div class="bug-view-screenshots-row">
              <div class="bug-view-section bug-view-screenshot-col">
                <div class="bug-view-section-label">
                  <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                  <span>Screenshots — <span class="label-fe">Front-End</span></span>
                  <span v-if="viewingBugDetail?.frontend_images?.length" class="bug-view-count-pill">{{ viewingBugDetail.frontend_images.length }}</span>
                </div>
                <div v-if="viewingBugDetail?.frontend_images && viewingBugDetail.frontend_images.length" class="bug-view-images">
                  <a v-for="(img, idx) in viewingBugDetail.frontend_images" :key="idx" :href="apiBase + img" target="_blank" class="bug-view-img-item">
                    <img :src="apiBase + img" :alt="`FE ${idx+1}`" />
                    <div class="bug-view-img-overlay">
                      <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                      <span>Open</span>
                    </div>
                  </a>
                </div>
                <div v-else class="bug-view-empty bug-view-no-images">
                  <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="opacity:.3;margin-bottom:6px;"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                  No images attached
                </div>
              </div>

              <div class="bug-view-section bug-view-screenshot-col">
                <div class="bug-view-section-label">
                  <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                  <span>Screenshots — <span class="label-cms">CMS</span></span>
                  <span v-if="viewingBugDetail?.cms_images?.length" class="bug-view-count-pill">{{ viewingBugDetail.cms_images.length }}</span>
                </div>
                <div v-if="viewingBugDetail?.cms_images && viewingBugDetail.cms_images.length" class="bug-view-images">
                  <a v-for="(img, idx) in viewingBugDetail.cms_images" :key="idx" :href="apiBase + img" target="_blank" class="bug-view-img-item">
                    <img :src="apiBase + img" :alt="`CMS ${idx+1}`" />
                    <div class="bug-view-img-overlay">
                      <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                      <span>Open</span>
                    </div>
                  </a>
                </div>
                <div v-else class="bug-view-empty bug-view-no-images">
                  <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="opacity:.3;margin-bottom:6px;"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                  No images attached
                </div>
              </div>
            </div>

          </div>

          <div class="modal-footer" style="justify-content:space-between;">
            <button class="btn btn-danger btn-sm" style="gap:5px;" @click="confirmDelete(viewingBugDetail); showViewModal = false">
              <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
              Delete
            </button>
            <button class="btn btn-secondary" @click="showViewModal = false">Close</button>
          </div>

        </div>
      </div>
    </Transition>

    <!-- Dev Comment Thread Modal -->
    <Transition name="fade">
      <div v-if="showThreadModal" class="modal-overlay" @click.self="showThreadModal = false">
        <div class="modal thread-modal">
          <div class="modal-header">
            <div style="display:flex;align-items:center;gap:10px;min-width:0;">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              <h3 class="modal-title" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Comments — {{ threadBug?.title }}</h3>
            </div>
            <button class="btn btn-ghost btn-icon" @click="showThreadModal = false">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
          </div>
          <div class="modal-body thread-modal-body">
            <div class="thread-msg-list" ref="threadListEl" @click="openMenuIdx = null">
              <div v-if="threadBug?.dev_comments && threadBug.dev_comments.length">
                <div v-for="(msg, i) in threadBug.dev_comments" :key="i" class="thread-msg-item">
                  <!-- Edit mode -->
                  <div v-if="editingMsgIndex === i" class="thread-msg-editing">
                    <textarea
                      v-model="editingMsgValue"
                      class="thread-textarea"
                      rows="3"
                      @keydown.ctrl.enter.prevent="saveEditMsg(i)"
                      @keydown.esc="cancelEditMsg"
                    ></textarea>
                    <div class="thread-edit-actions">
                      <span style="font-size:11px;color:var(--gray-400);">Ctrl+Enter to save</span>
                      <div style="display:flex;gap:6px;">
                        <button class="btn btn-ghost btn-sm" @click="cancelEditMsg">Cancel</button>
                        <button class="btn btn-primary btn-sm" :disabled="!editingMsgValue.trim()" @click="saveEditMsg(i)">Save</button>
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
                        <button class="thread-ellipsis-btn" @click.stop="openMenuIdx = (openMenuIdx === i ? null : i)">⋯</button>
                        <div v-if="openMenuIdx === i" class="thread-dropdown" @click.stop>
                          <button class="thread-dropdown-item" @click="startEditMsg(i, msg.message); openMenuIdx = null">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            Edit
                          </button>
                          <button class="thread-dropdown-item thread-dropdown-delete" @click="deletingMsgIdx = i; openMenuIdx = null">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                            Delete
                          </button>
                        </div>
                      </div>
                    </div>
                    <div class="thread-msg-bubble">{{ msg.message }}</div>
                    <div v-if="deletingMsgIdx === i" class="thread-delete-confirm">
                      <span>Delete this comment?</span>
                      <div style="display:flex;gap:6px;">
                        <button class="btn btn-ghost btn-sm" @click="deletingMsgIdx = null">Cancel</button>
                        <button class="btn btn-sm" style="background:var(--danger);color:#fff;border-color:var(--danger);" @click="deleteMsg(i); deletingMsgIdx = null">Delete</button>
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
              v-model="newThreadAuthor"
              class="thread-author-input"
              placeholder="Your name…"
              maxlength="80"
            />
            <textarea
              v-model="newThreadMessage"
              class="thread-textarea"
              placeholder="Write a comment… (Ctrl+Enter to send)"
              rows="3"
              @keydown.ctrl.enter.prevent="addThreadMessage"
            ></textarea>
            <div class="thread-input-footer">
              <span style="font-size:11px;color:var(--gray-400);">Ctrl + Enter to send</span>
              <button class="btn btn-primary" :disabled="!newThreadMessage.trim() || !newThreadAuthor.trim() || threadSending" @click="addThreadMessage">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                {{ threadSending ? 'Sending…' : 'Send' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Share Project Modal -->
    <Transition name="fade">
      <div v-if="showShareModal" class="modal-overlay" @click.self="showShareModal = false">
        <div class="modal" style="max-width:480px;">
          <div class="modal-header">
            <div style="display:flex;align-items:center;gap:8px;">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
              <h3 class="modal-title">Share "{{ sharingProject?.name }}"</h3>
            </div>
            <button class="btn btn-ghost btn-icon" @click="showShareModal = false">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
          </div>
          <div class="modal-body">
            <!-- Invite form -->
            <div style="margin-bottom:20px;">
              <label class="form-label">Invite by Gmail address</label>
              <div style="display:flex;gap:8px;margin-top:6px;">
                <input
                  v-model="shareForm.email"
                  type="email"
                  class="form-control"
                  placeholder="colleague@gmail.com"
                  style="flex:1;"
                  @keydown.enter.prevent="inviteUser"
                />
                <select v-model="shareForm.permission" class="form-control" style="width:110px;">
                  <option value="viewer">Viewer</option>
                  <option value="editor">Editor</option>
                </select>
                <button class="btn btn-primary" :disabled="!shareForm.email.trim() || shareSubmitting" @click="inviteUser">
                  {{ shareSubmitting ? '…' : 'Invite' }}
                </button>
              </div>
              <p style="font-size:12px;color:var(--gray-400);margin-top:6px;">
                <strong>Viewer</strong> — can view bugs only &nbsp;·&nbsp; <strong>Editor</strong> — can create, edit, and delete bugs
              </p>
            </div>

            <!-- Current shares list -->
            <div>
              <div class="form-label" style="margin-bottom:10px;">People with access</div>
              <div v-if="sharesLoading" style="color:var(--gray-400);font-size:13px;">Loading…</div>
              <div v-else-if="sharesList.length === 0" style="color:var(--gray-400);font-size:13px;">No one else has access yet.</div>
              <div v-else class="shares-list">
                <div v-for="share in sharesList" :key="share.id" class="share-row">
                  <div class="share-user-info">
                    <img v-if="share.user?.avatar" :src="share.user.avatar" class="share-avatar" :alt="share.user.name" />
                    <div v-else class="share-avatar share-avatar-fallback">{{ share.email[0]?.toUpperCase() }}</div>
                    <div>
                      <div class="share-name">{{ share.user?.name || share.email }}</div>
                      <div class="share-email">{{ share.email }} · <span :class="share.accepted ? 'share-accepted' : 'share-pending'">{{ share.accepted ? 'Accepted' : 'Pending' }}</span></div>
                    </div>
                  </div>
                  <div style="display:flex;align-items:center;gap:8px;">
                    <select
                      :value="share.permission"
                      class="form-control"
                      style="width:100px;font-size:12px;padding:4px 8px;"
                      @change="updateSharePermission(share, $event.target.value)"
                    >
                      <option value="viewer">Viewer</option>
                      <option value="editor">Editor</option>
                    </select>
                    <button class="btn btn-icon action-btn-delete" title="Remove access" @click="removeShare(share.id)">
                      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="showShareModal = false">Done</button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Send Ticket Confirmation Modal -->
    <Transition name="fade">
      <div v-if="showSendTicketModal" class="modal-overlay" @click.self="showSendTicketModal = false">
        <div class="modal" style="max-width:440px;">
          <div class="modal-header">
            <div style="display:flex;align-items:center;gap:8px;">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
              <h3 class="modal-title">{{ isResend ? 'Resend Ticket' : 'Send Ticket' }}</h3>
            </div>
            <button class="btn btn-ghost btn-icon" @click="showSendTicketModal = false">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
          </div>
          <div class="modal-body" style="padding:20px 24px;">
            <p style="color:var(--gray-500);font-size:14px;margin:0 0 20px;">Review the ticket details before sending.</p>
            <div class="send-ticket-preview">
              <div class="send-ticket-preview-row">
                <span class="send-ticket-preview-label">Bug</span>
                <span class="send-ticket-preview-val"><span class="bug-seq">#{{ sendTicketBug?.sequence }}</span> {{ sendTicketBug?.title }}</span>
              </div>
              <div class="send-ticket-preview-row">
                <span class="send-ticket-preview-label">Priority</span>
                <span :class="['badge', priorityBadgeClass(sendTicketBug?.priority)]">{{ sendTicketBug?.priority }}</span>
              </div>
              <div class="send-ticket-preview-row">
                <span class="send-ticket-preview-label">Developer{{ (sendTicketBug?.assigned_developers?.length || 0) > 1 ? 's' : '' }}</span>
                <span style="display:flex;flex-direction:column;gap:6px;">
                  <span
                    v-for="dev in (sendTicketBug?.assigned_developers || [])" :key="dev.email"
                    style="display:flex;align-items:center;gap:8px;"
                  >
                    <span class="assign-avatar-xs">{{ dev.name?.[0]?.toUpperCase() }}</span>
                    <span>
                      <strong>{{ dev.name }}</strong>
                      <span style="color:var(--gray-400);margin-left:6px;font-size:12px;">{{ dev.email }}</span>
                    </span>
                  </span>
                </span>
              </div>
            </div>
          </div>
          <div class="modal-footer" style="padding:16px 24px;border-top:1px solid var(--gray-100);display:flex;justify-content:flex-end;gap:10px;">
            <button class="btn btn-ghost" @click="showSendTicketModal = false">Cancel</button>
            <button class="btn btn-primary" :disabled="ticketSending" @click="isResend ? doResendTicket() : doSendTicket()">
              <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
              {{ ticketSending ? 'Sending…' : (isResend ? 'Resend ticket' : 'Send ticket') }}
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Toast -->
    <Transition name="toast-slide">
      <div v-if="toast.show" class="toast-notification" :class="'toast-' + toast.type">
        <svg v-if="toast.type === 'success'" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        <svg v-else width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        {{ toast.message }}
      </div>
    </Transition>

    <!-- Image Viewer -->
    <Transition name="fade">
      <div v-if="showImagesModal" class="modal-overlay" @click.self="showImagesModal = false">
        <div class="modal" style="max-width:820px;">
          <div class="modal-header">
            <h3 class="modal-title">Screenshots — {{ viewingBug?.title }}</h3>
            <button class="btn btn-ghost btn-icon" @click="showImagesModal = false"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
          </div>
          <div class="modal-body">
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;">
              <a v-for="(img, idx) in viewingBug?.images" :key="idx" :href="apiBase + img" target="_blank" class="img-viewer-item">
                <img :src="apiBase + img" :alt="`Screenshot ${idx+1}`" style="width:100%;height:160px;object-fit:cover;" />
              </a>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Maintenance Ticket View Modal -->
    <Transition name="fade">
      <div v-if="selectedMtTicket" class="modal-overlay" @click.self="selectedMtTicket = null">
        <div class="modal" style="max-width:920px;width:95%;max-height:90vh;overflow:hidden;display:flex;flex-direction:column;padding:0;">
          <!-- Header -->
          <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 24px;border-bottom:1px solid #f1f5f9;flex-shrink:0;">
            <div style="display:flex;align-items:center;gap:10px;">
              <span class="bug-seq" style="background:#ecfdf5;color:#059669;font-size:12px;">{{ selectedMtTicket.ticket_number }}</span>
              <span style="font-size:14px;font-weight:700;color:#1e293b;">{{ selectedMtTicket.client }}</span>
              <span v-if="selectedMtTicket._project" style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--gray-500);">
                <span style="width:7px;height:7px;border-radius:50%;display:inline-block;" :style="{ background: selectedMtTicket._project.color || '#10b981' }"></span>
                {{ selectedMtTicket._project.name }}
              </span>
            </div>
            <button class="btn btn-ghost btn-icon" @click="selectedMtTicket = null">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
          </div>

          <!-- Two-column body -->
          <div style="display:grid;grid-template-columns:1fr 280px;flex:1;overflow:hidden;min-height:0;">

            <!-- Left: main content -->
            <div style="padding:24px;display:flex;flex-direction:column;gap:18px;overflow-y:auto;border-right:1px solid #f1f5f9;">

              <!-- Title block -->
              <div style="display:flex;align-items:flex-start;gap:14px;background:#f8fafc;border-radius:12px;padding:18px 20px;">
                <span style="font-size:13px;font-weight:700;color:#059669;background:#d1fae5;padding:4px 10px;border-radius:6px;white-space:nowrap;flex-shrink:0;margin-top:2px;">{{ selectedMtTicket.ticket_number }}</span>
                <div>
                  <h2 style="font-size:20px;font-weight:700;color:#1e293b;margin:0 0 4px;line-height:1.3;">{{ selectedMtTicket.client }}</h2>
                  <div style="font-size:14px;color:#64748b;line-height:1.5;">{{ selectedMtTicket.request }}</div>
                </div>
              </div>

              <!-- Request -->
              <div style="background:#fff;border:1px solid #f1f5f9;border-radius:12px;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                <div style="display:flex;align-items:center;gap:7px;font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;margin-bottom:12px;">
                  <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                  Request
                </div>
                <div style="font-size:14px;color:#334155;line-height:1.7;white-space:pre-wrap;">{{ selectedMtTicket.request }}</div>
              </div>

              <!-- Notes -->
              <div v-if="selectedMtTicket.notes" style="background:#fff;border:1px solid #f1f5f9;border-radius:12px;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                <div style="display:flex;align-items:center;gap:7px;font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;margin-bottom:12px;">
                  <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  Notes
                </div>
                <div style="font-size:14px;color:#334155;line-height:1.7;white-space:pre-wrap;">{{ selectedMtTicket.notes }}</div>
              </div>

              <!-- Comments -->
              <div style="background:#fff;border:1px solid #f1f5f9;border-radius:12px;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
                  <div style="display:flex;align-items:center;gap:7px;font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    Comments
                  </div>
                  <span v-if="selectedMtTicket.comments?.length" style="background:#f1f5f9;color:#475569;font-size:11px;font-weight:700;padding:2px 8px;border-radius:99px;">{{ selectedMtTicket.comments.length }}</span>
                </div>
                <div v-if="selectedMtTicket.comments?.length" style="display:flex;flex-direction:column;gap:10px;">
                  <div v-for="(msg, i) in selectedMtTicket.comments" :key="i" style="display:flex;flex-direction:column;gap:5px;">
                    <div style="display:flex;align-items:center;gap:8px;">
                      <span style="width:26px;height:26px;border-radius:50%;background:#059669;color:#fff;font-size:11px;font-weight:700;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;">{{ (msg.author || '?')[0].toUpperCase() }}</span>
                      <span style="font-size:13px;font-weight:600;color:#1e293b;">{{ msg.author || 'Anonymous' }}</span>
                      <span style="font-size:11px;color:#94a3b8;">{{ formatBugDate(msg.timestamp) }}</span>
                    </div>
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;font-size:13px;color:#334155;line-height:1.6;margin-left:34px;">{{ msg.message }}</div>
                  </div>
                </div>
                <div v-else style="color:#94a3b8;font-size:13px;font-style:italic;">No comments yet.</div>
              </div>

              <!-- Attachments -->
              <div v-if="selectedMtTicket.attachments?.length" style="background:#fff;border:1px solid #f1f5f9;border-radius:12px;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                <div style="display:flex;align-items:center;gap:7px;font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;margin-bottom:12px;">
                  <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                  Attachments
                  <span style="background:#f1f5f9;color:#475569;font-size:11px;font-weight:700;padding:2px 8px;border-radius:99px;">{{ selectedMtTicket.attachments.length }}</span>
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:10px;">
                  <template v-for="(url, idx) in selectedMtTicket.attachments" :key="idx">
                    <a v-if="/\.pdf$/i.test(url)" :href="apiBase + url" target="_blank"
                      style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;color:#c2410c;font-size:12px;font-weight:600;text-decoration:none;">
                      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                      {{ url.split('/').pop() }}
                    </a>
                    <a v-else :href="apiBase + url" target="_blank"
                      style="position:relative;width:90px;height:90px;border-radius:8px;overflow:hidden;border:1px solid #e2e8f0;display:block;">
                      <img :src="apiBase + url" :alt="`Attachment ${idx+1}`" style="width:100%;height:100%;object-fit:cover;" />
                    </a>
                  </template>
                </div>
              </div>

            </div>

            <!-- Right: sidebar -->
            <div style="padding:20px;display:flex;flex-direction:column;gap:14px;background:#f8fafc;overflow-y:auto;">

              <!-- Dev Status -->
              <div style="background:#fff;border-radius:12px;padding:16px 18px;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;margin-bottom:10px;">Dev Status</div>
                <span :class="['tt-dev-status-badge', 'tt-dev-status--' + (selectedMtTicket.dev_status || 'Not Started').toLowerCase().replace(/\s+/g,'-')]">{{ selectedMtTicket.dev_status || 'Not Started' }}</span>
              </div>

              <!-- Status -->
              <div style="background:#fff;border-radius:12px;padding:16px 18px;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;margin-bottom:10px;">Status</div>
                <span :class="['badge', mtStatusBadgeClass(selectedMtTicket.status)]">{{ selectedMtTicket.status }}</span>
              </div>

              <!-- Details -->
              <div style="background:#fff;border-radius:12px;padding:16px 18px;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;margin-bottom:10px;">Details</div>
                <div style="display:flex;flex-direction:column;gap:0;">
                  <div v-if="selectedMtTicket._project" style="display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid #f1f5f9;font-size:13px;">
                    <span style="color:#64748b;font-size:12px;">Project</span>
                    <span style="color:#1e293b;font-weight:600;text-align:right;">{{ selectedMtTicket._project.name }}</span>
                  </div>
                  <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid #f1f5f9;font-size:13px;">
                    <span style="color:#64748b;font-size:12px;">Client</span>
                    <span style="color:#1e293b;text-align:right;">{{ selectedMtTicket.client }}</span>
                  </div>
                  <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid #f1f5f9;font-size:13px;">
                    <span style="color:#64748b;font-size:12px;">Sent Through</span>
                    <span style="color:#1e293b;text-align:right;">{{ selectedMtTicket.sent_thru || '—' }}</span>
                  </div>
                  <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid #f1f5f9;font-size:13px;">
                    <span style="color:#64748b;font-size:12px;">Date Received</span>
                    <span style="color:#1e293b;text-align:right;">{{ formatBugDate(selectedMtTicket.date_received) || '—' }}</span>
                  </div>
                  <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid #f1f5f9;font-size:13px;">
                    <span style="color:#64748b;font-size:12px;">Target Date</span>
                    <span :style="{ color: mtIsOverdue(selectedMtTicket) ? '#dc2626' : '#1e293b', fontWeight: mtIsOverdue(selectedMtTicket) ? '600' : '400', textAlign:'right' }">{{ formatBugDate(selectedMtTicket.target_date) || '—' }}</span>
                  </div>
                  <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 0;font-size:13px;">
                    <span style="color:#64748b;font-size:12px;">Completion</span>
                    <span style="color:#1e293b;text-align:right;">{{ formatBugDate(selectedMtTicket.completion_date) || '—' }}</span>
                  </div>
                </div>
              </div>

              <!-- Assigned -->
              <div style="background:#fff;border-radius:12px;padding:16px 18px;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;margin-bottom:10px;">Assigned</div>
                <div v-if="selectedMtTicket.assigned_devs?.length" style="margin-bottom:10px;">
                  <div style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Developers</div>
                  <div v-for="email in selectedMtTicket.assigned_devs" :key="email" style="display:flex;align-items:center;gap:8px;padding:4px 0;flex-wrap:wrap;">
                    <span style="width:26px;height:26px;border-radius:50%;background:#059669;color:#fff;font-size:11px;font-weight:700;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;">{{ email[0].toUpperCase() }}</span>
                    <span style="font-size:12px;color:#334155;flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ email }}</span>
                    <a :href="maintenanceFolderUrl(email)" target="_blank"
                      style="display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:600;color:#059669;text-decoration:none;padding:3px 8px;border-radius:6px;background:#d1fae5;border:1px solid #a7f3d0;white-space:nowrap;flex-shrink:0;">
                      <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                      View Folder
                    </a>
                  </div>
                </div>
                <div v-if="selectedMtTicket.assigned_qa?.length">
                  <div style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">QA</div>
                  <div v-for="email in selectedMtTicket.assigned_qa" :key="email" style="display:flex;align-items:center;gap:8px;padding:4px 0;flex-wrap:wrap;">
                    <span style="width:26px;height:26px;border-radius:50%;background:#7c3aed;color:#fff;font-size:11px;font-weight:700;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;">{{ email[0].toUpperCase() }}</span>
                    <span style="font-size:12px;color:#334155;flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ email }}</span>
                    <a :href="maintenanceFolderUrl(email)" target="_blank"
                      style="display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:600;color:#059669;text-decoration:none;padding:3px 8px;border-radius:6px;background:#d1fae5;border:1px solid #a7f3d0;white-space:nowrap;flex-shrink:0;">
                      <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                      View Folder
                    </a>
                  </div>
                </div>
                <div v-if="!selectedMtTicket.assigned_devs?.length && !selectedMtTicket.assigned_qa?.length" style="color:#94a3b8;font-size:13px;font-style:italic;">Not assigned</div>
              </div>

              <!-- Activity Log -->
              <div style="background:#fff;border-radius:12px;padding:16px 18px;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;margin-bottom:10px;">Activity Log</div>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 0;font-size:13px;">
                  <span style="color:#64748b;font-size:12px;">Comments</span>
                  <span style="color:#1e293b;font-weight:600;">{{ selectedMtTicket.comments?.length ?? 0 }}</span>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </Transition>

  </div>
</template>

<script setup>
definePageMeta({ middleware: 'auth' })

import { ref, computed, watch, nextTick, onMounted, onUnmounted, reactive } from 'vue'

const config     = useRuntimeConfig()
const apiBase    = config.public.apiBase.replace('/api', '')
const appLoading = useState('appLoading', () => false)

// ── Auth state ──────────────────────────────────────────────────────────────
const authToken           = ref(null)
const currentUser         = ref(null)
const profileDropdownOpen = ref(false)
const profileDropdownRef  = ref(null)

// apiFetch: wraps $fetch with Authorization header when logged in
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
  navigateTo('/login')
}

const fetchCurrentUser = async () => {
  try {
    const user = await apiFetch(`${config.public.apiBase}/auth/me`)
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

// ── Notifications ────────────────────────────────────────────────────────────
const notifications      = ref([])
const notifDropdownOpen  = ref(false)
const notifDropdownRef   = ref(null)
let   notifPollTimer     = null

const notifUnreadCount = computed(() => notifications.value.filter(n => !n.read_at).length)

const fetchNotifications = async () => {
  try {
    notifications.value = await $fetch(`${config.public.apiBase}/notifications`)
  } catch { /* silent */ }
}

const toggleNotifDropdown = () => {
  notifDropdownOpen.value = !notifDropdownOpen.value
}

const openNotif = async (n) => {
  if (!n.read_at) {
    try { await $fetch(`${config.public.apiBase}/notifications/${n.id}/read`, { method: 'PATCH' }) } catch {}
    n.read_at = new Date().toISOString()
  }
  notifDropdownOpen.value = false
  if (n.data?.bug_id) {
    // Try the already-loaded bugs first
    let bug = bugs.value.find(b => b.id === n.data.bug_id)
    if (!bug) {
      // Bug belongs to a different project — fetch it directly
      try { bug = await $fetch(`${config.public.apiBase}/bugs/${n.data.bug_id}`) } catch {}
    }
    if (bug) { viewBug(bug) }
  }
}

const dismissNotif = async (n) => {
  try { await $fetch(`${config.public.apiBase}/notifications/${n.id}`, { method: 'DELETE' }) } catch {}
  notifications.value = notifications.value.filter(x => x.id !== n.id)
}

const markAllNotifRead = async () => {
  try { await $fetch(`${config.public.apiBase}/notifications/read-all`, { method: 'PATCH' }) } catch {}
  notifications.value.forEach(n => { if (!n.read_at) n.read_at = new Date().toISOString() })
}

const notifIcon = (type) => {
  return { ready_for_qa: '🔍', ticket_created: '🐛', ticket_completed: '✅', developer_assigned: '👤', blocked: '🚫' }[type] ?? '🔔'
}
const notifIconClass = (type) => {
  return { ready_for_qa: 'notif-icon--qa', ticket_created: 'notif-icon--created', ticket_completed: 'notif-icon--done', developer_assigned: 'notif-icon--assigned', blocked: 'notif-icon--blocked' }[type] ?? ''
}

function timeAgo(dateStr) {
  if (!dateStr) return ''
  const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000)
  if (diff < 60)    return 'just now'
  if (diff < 3600)  return `${Math.floor(diff / 60)}m ago`
  if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`
  return `${Math.floor(diff / 86400)}d ago`
}

onMounted(async () => {
  const urlParams = new URLSearchParams(window.location.search)
  const token = urlParams.get('token')
  const authError = urlParams.get('auth_error')
  if (token) {
    authToken.value = token
    localStorage.setItem('auth_token', token)
    window.history.replaceState({}, '', '/')
  } else if (authError) {
    window.history.replaceState({}, '', '/')
    navigateTo(`/login?auth_error=${authError}`)
  } else {
    const stored = localStorage.getItem('auth_token')
    if (stored) authToken.value = stored
  }
  if (authToken.value) await fetchCurrentUser()

  // Auto-open views from URL params
  const view = urlParams.get('view')
  if (view === 'tickets') {
    window.history.replaceState({}, '', '/')
    openTicketTracker()
  }

  // Start notification polling
  fetchNotifications()
  notifPollTimer = setInterval(fetchNotifications, 15000)

  // Close dropdowns when clicking outside
  document.addEventListener('click', (e) => {
    if (profileDropdownRef.value && !profileDropdownRef.value.contains(e.target)) {
      profileDropdownOpen.value = false
    }
    if (notifDropdownRef.value && !notifDropdownRef.value.contains(e.target)) {
      notifDropdownOpen.value = false
    }
  })
})

// ── Permission helpers ───────────────────────────────────────────────────────
// A project is "owned" by the logged-in user if they created it, OR if it has no owner (legacy)
const isProjectOwner = (project) => {
  if (!project || !currentUser.value) return false
  if (!project.owner_id) return true   // legacy unowned project — any logged-in user can share it
  return project.owner_id === currentUser.value.id
}
// Can edit if: no owner (legacy project), or user is owner, or user has editor share
const canEditProject = (project) => {
  if (!project) return true
  if (!project.owner_id) return true          // legacy / unowned project
  if (isProjectOwner(project)) return true
  const share = projectShareMap.value[project.id]
  return share?.permission === 'editor'
}

// Share map: project_id → share info (populated from the shares list when share modal loads)
const projectShareMap = ref({})

// ── Share modal state ────────────────────────────────────────────────────────
const showShareModal   = ref(false)
const sharingProject   = ref(null)
const sharesList       = ref([])
const shareForm        = ref({ email: '', permission: 'viewer' })
const sharesLoading    = ref(false)
const shareSubmitting  = ref(false)

const openShareModal = async (project) => {
  sharingProject.value = project
  shareForm.value = { email: '', permission: 'viewer' }
  showShareModal.value = true
  await loadShares()
}

const loadShares = async () => {
  sharesLoading.value = true
  try {
    sharesList.value = await apiFetch(`${config.public.apiBase}/projects/${sharingProject.value.id}/shares`)
  } catch { sharesList.value = [] }
  finally { sharesLoading.value = false }
}

const inviteUser = async () => {
  if (!shareForm.value.email.trim() || shareSubmitting.value) return
  shareSubmitting.value = true
  try {
    const share = await apiFetch(`${config.public.apiBase}/projects/${sharingProject.value.id}/shares`, {
      method: 'POST',
      body: shareForm.value,
    })
    const idx = sharesList.value.findIndex(s => s.email === share.email)
    if (idx >= 0) sharesList.value[idx] = share
    else sharesList.value.push(share)
    shareForm.value.email = ''
  } catch (e) {
    alert(e?.data?.error || 'Failed to invite. Check the email and try again.')
  } finally { shareSubmitting.value = false }
}

const updateSharePermission = async (share, newPermission) => {
  try {
    await apiFetch(`${config.public.apiBase}/projects/${sharingProject.value.id}/shares/${share.id}`, {
      method: 'PUT',
      body: { permission: newPermission },
    })
    share.permission = newPermission
  } catch { console.error('Failed to update permission') }
}

const removeShare = async (shareId) => {
  try {
    await apiFetch(`${config.public.apiBase}/projects/${sharingProject.value.id}/shares/${shareId}`, { method: 'DELETE' })
    sharesList.value = sharesList.value.filter(s => s.id !== shareId)
  } catch { console.error('Failed to remove share') }
}

const priorities    = ['Critical', 'High', 'Medium', 'Low']
const statuses      = ['Pending', 'Out of Scope', 'Ongoing', 'Completed']
const scenarioTypes = ['UI', 'Functionality', 'UI & Functionality']
const projectColors = ['#6366f1', '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6']

// ── Color maps ──
const statusColors   = { Pending: '#f59e0b', 'Out of Scope': '#94a3b8', Ongoing: '#3b82f6', Completed: '#22c55e' }
const priorityColors = { Critical: '#ef4444', High: '#f97316', Medium: '#eab308', Low: '#22c55e' }
const scenarioColors = { UI: '#8b5cf6', Functionality: '#ec4899', 'UI & Functionality': '#06b6d4' }

// ── Project state ──
const dashboardOpen          = ref(false)
const dashboardKey           = ref(0)
const inactiveOpen           = ref(false)
const projectStatusTab       = ref('all')
const projects               = ref([])
const selectedProject        = ref(null)
const projectSearch          = ref('')
const projectFilter          = ref('')
const showProjectModal       = ref(false)
const showDeleteProjectModal = ref(false)
const editingProject         = ref(null)
const deletingProject        = ref(null)
const projectForm            = ref({ name: '', description: '', color: '#6366f1', is_active: true })

// ── Bug state ──
const bugs            = ref([])
const summary         = ref({})

// ── Bug squish animation ──────────────────────────────────────────────────
const showSquishAnim = ref(false)
let squishTimer = null
const triggerSquishAnim = () => {
  showSquishAnim.value = true
  clearTimeout(squishTimer)
  squishTimer = setTimeout(() => { showSquishAnim.value = false }, 2400)
}

const completionPct = computed(() => {
  const total = summary.value.total || 0
  if (!total) return 0
  return Math.round((summary.value.by_status?.Completed || 0) / total * 100)
})
const pendingPct = computed(() => {
  const total = summary.value.total || 0
  if (!total) return 0
  return Math.round((summary.value.by_status?.Pending || 0) / total * 100)
})
const ongoingPct = computed(() => {
  const total = summary.value.total || 0
  if (!total) return 0
  return Math.round((summary.value.by_status?.Ongoing || 0) / total * 100)
})

const showModal       = ref(false)
const showDeleteModal = ref(false)
const showImagesModal  = ref(false)
const showViewModal    = ref(false)
const editingBug       = ref(null)
const deletingBug      = ref(null)
const viewingBug       = ref(null)
const viewingBugDetail = ref(null)
const submitting      = ref(false)
const fileInput       = ref(null)
const descEditor      = ref(null)
const rteActive       = reactive({ bold: false, italic: false })
const newImageFiles        = ref([])
const existingImages       = ref([])
const newFrontendFiles     = ref([])
const existingFrontendImages = ref([])
const newCmsFiles          = ref([])
const existingCmsImages    = ref([])
const frontendFileInput    = ref(null)
const cmsFileInput         = ref(null)
const filters         = ref({ search: '', status: '', priority: '', scenario_type: '' })
const form = ref({ title: '', description: '', priority: 'Medium', scenario_type: 'UI', status: 'Pending', date_to_accomplish: '', subtitles: [{ text: '', link: '' }] })
const showThreadModal  = ref(false)
const threadBugId      = ref(null)
const newThreadMessage = ref('')
const newThreadAuthor  = ref('')
const threadSending    = ref(false)
const threadListEl     = ref(null)
const editingMsgIndex  = ref(null)
const editingMsgValue  = ref('')
const openMenuIdx      = ref(null)
const deletingMsgIdx   = ref(null)

// ── Detail view ──
const detailView     = ref(null) // null | 'status' | 'priority' | 'scenario'
const doughnutChartEl = ref(null)
const lineChartEl     = ref(null)
let chartInstances    = []

const detailViewTitle = computed(() => {
  return { status: 'By Status', priority: 'By Priority', scenario: 'By Scenario Type' }[detailView.value] || ''
})

const detailGroupKey = computed(() => {
  return { status: 'status', priority: 'priority', scenario: 'scenario_type' }[detailView.value] || 'status'
})

const detailColorMap = computed(() => {
  return { status: statusColors, priority: priorityColors, scenario: scenarioColors }[detailView.value] || statusColors
})

const detailGroupItems = computed(() => {
  if (!detailView.value) return []
  const key = detailGroupKey.value
  const colorMap = detailColorMap.value
  const total = bugs.value.length
  const counts = {}
  bugs.value.forEach(b => { counts[b[key]] = (counts[b[key]] || 0) + 1 })
  return Object.entries(counts)
    .map(([k, count]) => ({ key: k, count, pct: total ? Math.round((count / total) * 100) : 0, color: colorMap[k] || '#94a3b8' }))
    .sort((a, b) => b.count - a.count)
})

// ── Chart data helpers ──
const getLast30Days = () => {
  const dates = []
  for (let i = 29; i >= 0; i--) {
    const d = new Date()
    d.setDate(d.getDate() - i)
    dates.push(d.toISOString().split('T')[0])
  }
  return dates
}

const formatDateLabel = (iso) => {
  const d = new Date(iso + 'T00:00:00')
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

const buildLineData = (groupKey, colorMap) => {
  const dates = getLast30Days()
  const groups = Object.keys(colorMap)
  const datasets = groups.map(group => {
    let cumulative = 0
    const data = dates.map(date => {
      cumulative += bugs.value.filter(b => {
        const bugDate = b.created_at ? b.created_at.split('T')[0] : null
        return b[groupKey] === group && bugDate === date
      }).length
      return cumulative
    })
    return {
      label: group,
      data,
      borderColor: colorMap[group] || '#94a3b8',
      backgroundColor: (colorMap[group] || '#94a3b8') + '18',
      borderWidth: 2.5,
      pointRadius: 3,
      pointHoverRadius: 5,
      tension: 0.4,
      fill: true,
    }
  }).filter(ds => ds.data.some(v => v > 0))

  return { labels: dates.map(formatDateLabel), datasets }
}

const buildDoughnutData = (groupKey, colorMap) => {
  const counts = {}
  bugs.value.forEach(b => { counts[b[groupKey]] = (counts[b[groupKey]] || 0) + 1 })
  const labels = Object.keys(counts)
  return {
    labels,
    datasets: [{
      data: labels.map(l => counts[l]),
      backgroundColor: labels.map(l => colorMap[l] || '#94a3b8'),
      borderColor: '#fff',
      borderWidth: 3,
      hoverOffset: 6,
    }]
  }
}

// ── Chart init/destroy ──
const destroyCharts = () => {
  chartInstances.forEach(c => c.destroy())
  chartInstances = []
}

watch(detailView, async (newView) => {
  destroyCharts()
  if (!newView) return
  if (!import.meta.client) return

  await nextTick()

  const { Chart, registerables } = await import('chart.js')
  Chart.register(...registerables)

  const groupKey  = { status: 'status', priority: 'priority', scenario: 'scenario_type' }[newView]
  const colorMap  = { status: statusColors, priority: priorityColors, scenario: scenarioColors }[newView]

  if (doughnutChartEl.value) {
    const c = new Chart(doughnutChartEl.value, {
      type: 'doughnut',
      data: buildDoughnutData(groupKey, colorMap),
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '68%',
        animation: {
          animateRotate: true,
          animateScale: true,
          duration: 900,
          easing: 'easeInOutQuart',
        },
        plugins: {
          legend: {
            position: 'bottom',
            labels: { padding: 16, usePointStyle: true, pointStyleWidth: 10, font: { size: 12, family: 'Inter' } }
          },
          tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed} bugs` } }
        }
      }
    })
    chartInstances.push(c)
  }

  if (lineChartEl.value) {
    const lineData = buildLineData(groupKey, colorMap)
    const c = new Chart(lineChartEl.value, {
      type: 'line',
      data: lineData,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        animation: {
          duration: 1000,
          easing: 'easeInOutQuart',
        },
        animations: {
          y: { from: (ctx) => ctx.chart.scales.y.bottom },
        },
        scales: {
          x: {
            grid: { color: '#f1f5f9' },
            ticks: {
              font: { size: 11, family: 'Inter' },
              color: '#94a3b8',
              maxTicksLimit: 10,
              maxRotation: 0,
            }
          },
          y: {
            grid: { color: '#f1f5f9' },
            ticks: { font: { size: 11, family: 'Inter' }, color: '#94a3b8', stepSize: 1, precision: 0 },
            beginAtZero: true,
          }
        },
        plugins: {
          legend: {
            position: 'bottom',
            labels: { padding: 16, usePointStyle: true, pointStyleWidth: 10, font: { size: 12, family: 'Inter' } }
          },
          tooltip: {
            backgroundColor: '#1e293b',
            titleFont: { size: 12, family: 'Inter' },
            bodyFont: { size: 12, family: 'Inter' },
            padding: 10,
            callbacks: { label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y}` }
          }
        }
      }
    })
    chartInstances.push(c)
  }
})

onUnmounted(() => {
  destroyCharts()
  clearInterval(notifPollTimer)
})

const openDetailView = (type) => {
  detailView.value = type
}

// ── Computed ──
const threadBug = computed(() => bugs.value.find(b => b.id === threadBugId.value) || null)

const overallStats = computed(() => {
  const ps = projects.value
  const total    = ps.reduce((s, p) => s + (p.bugs_count    || 0), 0)
  const critical = ps.reduce((s, p) => s + (p.critical_count|| 0), 0)
  const pending  = ps.reduce((s, p) => s + (p.pending_count || 0), 0)
  const ongoing  = ps.reduce((s, p) => s + (p.ongoing_count || 0), 0)
  const completed= ps.reduce((s, p) => s + (p.completed_count|| 0), 0)
  const rate     = total > 0 ? Math.round((completed / total) * 100) : 0
  return { totalProjects: ps.length, total, critical, pending, ongoing, completed, rate }
})

const activeProjects   = computed(() => projects.value.filter(p => p.is_active !== false))
const inactiveProjects = computed(() => projects.value.filter(p => p.is_active === false))

const filteredProjects = computed(() => projects.value.filter(p => {
  if (projectSearch.value) {
    const q = projectSearch.value.toLowerCase()
    if (!p.name.toLowerCase().includes(q) && !(p.description || '').toLowerCase().includes(q)) return false
  }
  if (projectFilter.value === 'critical')  return p.critical_count > 0
  if (projectFilter.value === 'pending')   return p.pending_count > 0
  if (projectFilter.value === 'ongoing')   return p.ongoing_count > 0
  if (projectFilter.value === 'completed') return p.completed_count > 0
  return true
}))

const filteredActiveProjects = computed(() => filteredProjects.value.filter(p => p.is_active !== false))

const filteredInactiveProjects = computed(() => {
  const q = projectSearch.value.trim().toLowerCase()
  return inactiveProjects.value.filter(p =>
    !q || p.name.toLowerCase().includes(q) || (p.description || '').toLowerCase().includes(q)
  )
})

const imagePreviewList = computed(() => [
  ...existingImages.value.map(url => ({ url: apiBase + url, existing: true, src: url })),
  ...newImageFiles.value.map(f   => ({ url: URL.createObjectURL(f), existing: false, file: f })),
])
const frontendPreviewList = computed(() => [
  ...existingFrontendImages.value.map(url => ({ url: apiBase + url, existing: true, src: url })),
  ...newFrontendFiles.value.map(f => ({ url: URL.createObjectURL(f), existing: false, file: f })),
])
const cmsPreviewList = computed(() => [
  ...existingCmsImages.value.map(url => ({ url: apiBase + url, existing: true, src: url })),
  ...newCmsFiles.value.map(f => ({ url: URL.createObjectURL(f), existing: false, file: f })),
])
const hasActiveFilters = computed(() =>
  filters.value.search || filters.value.status || filters.value.priority || filters.value.scenario_type
)
const filteredBugs = computed(() => bugs.value.filter(bug => {
  if (filters.value.search) {
    const q = filters.value.search.toLowerCase()
    if (!bug.title.toLowerCase().includes(q) && !(bug.description || '').toLowerCase().includes(q)) return false
  }
  if (filters.value.status        && bug.status        !== filters.value.status)        return false
  if (filters.value.priority      && bug.priority      !== filters.value.priority)      return false
  if (filters.value.scenario_type && bug.scenario_type !== filters.value.scenario_type) return false
  return true
}))

// ── API: Projects ──
const fetchProjects = async () => {
  try {
    const data = await apiFetch(`${config.public.apiBase}/projects`)
    projects.value = data
    if (selectedProject.value) {
      const updated = data.find(p => p.id === selectedProject.value.id)
      if (updated) selectedProject.value = updated
    }
  } catch (e) { console.error('Failed to fetch projects', e) }
}

const selectProject = async (project) => {
  appLoading.value         = true
  selectedProject.value    = project
  detailView.value         = null
  devFoldersViewOpen.value = false
  inactiveOpen.value       = false
  bugs.value               = []
  summary.value            = {}
  clearFilters()
  if (project) await fetchBugs()
  else await new Promise(r => setTimeout(r, 300))
  appLoading.value = false
}

const openInactiveView = () => {
  projectStatusTab.value   = 'inactive'
  inactiveOpen.value       = false
  dashboardOpen.value      = false
  selectedProject.value    = null
  devFoldersViewOpen.value = false
  ticketTrackerOpen.value  = false
}

const openProjectModal = (project) => {
  editingProject.value = project
  projectForm.value = project
    ? { name: project.name, description: project.description || '', color: project.color, is_active: project.is_active !== false }
    : { name: '', description: '', color: '#6366f1', is_active: true }
  showProjectModal.value = true
}

const submitProject = async () => {
  submitting.value = true
  try {
    if (editingProject.value) {
      await apiFetch(`${config.public.apiBase}/projects/${editingProject.value.id}`, { method: 'PUT', body: projectForm.value })
    } else {
      await apiFetch(`${config.public.apiBase}/projects`, { method: 'POST', body: projectForm.value })
    }
    await fetchProjects()
    showProjectModal.value = false
  } catch (e) { console.error('Failed to save project', e); alert('Failed to save project.') }
  finally { submitting.value = false }
}

const confirmDeleteProject = (project) => { deletingProject.value = project; showDeleteProjectModal.value = true }

const deleteProject = async () => {
  submitting.value = true
  try {
    await apiFetch(`${config.public.apiBase}/projects/${deletingProject.value.id}`, { method: 'DELETE' })
    if (selectedProject.value?.id === deletingProject.value.id) selectProject(null)
    await fetchProjects()
    showDeleteProjectModal.value = false
    deletingProject.value = null
  } catch (e) { console.error('Failed to delete project', e) }
  finally { submitting.value = false }
}

// ── API: Bugs ──
const fetchBugs = async () => {
  if (!selectedProject.value) return
  try {
    const data = await apiFetch(`${config.public.apiBase}/bugs`, { params: { project_id: selectedProject.value.id } })
    bugs.value    = data.bugs    || []
    summary.value = data.summary || {}
  } catch (e) { console.error('Failed to fetch bugs', e) }
}

const openCreateModal = () => {
  editingBug.value = null
  form.value = { title: '', description: '', priority: 'Medium', scenario_type: 'UI', status: 'Pending', date_to_accomplish: '', subtitles: [{ text: '', link: '' }] }
  newImageFiles.value = []; existingImages.value = []
  newFrontendFiles.value = []; existingFrontendImages.value = []
  newCmsFiles.value = []; existingCmsImages.value = []
  showModal.value = true
  nextTick(() => { if (descEditor.value) descEditor.value.innerHTML = '' })
}

const openEditModal = (bug) => {
  editingBug.value = bug
  form.value = {
    title: bug.title,
    description: bug.description || '',
    priority: bug.priority,
    scenario_type: bug.scenario_type,
    status: bug.status,
    date_to_accomplish: bug.date_to_accomplish ?? '',
    subtitles: (bug.subtitles && bug.subtitles.length) ? bug.subtitles.map(s => typeof s === 'string' ? { text: s, link: '' } : { text: s.text || '', link: s.link || '' }) : [{ text: '', link: '' }],
  }
  existingImages.value = bug.images || []; newImageFiles.value = []
  existingFrontendImages.value = bug.frontend_images || []; newFrontendFiles.value = []
  existingCmsImages.value = bug.cms_images || []; newCmsFiles.value = []
  showModal.value = true
  nextTick(() => { if (descEditor.value) descEditor.value.innerHTML = bug.description || '' })
}

const closeModal = () => { showModal.value = false; editingBug.value = null }
const handleFileSelect = (e) => { newImageFiles.value.push(...Array.from(e.target.files)); e.target.value = '' }
const handleFileDrop   = (e) => { newImageFiles.value.push(...Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/'))) }
const removeImage = (idx) => {
  const item = imagePreviewList.value[idx]
  if (item.existing) existingImages.value = existingImages.value.filter(u => u !== item.src)
  else newImageFiles.value.splice(idx - existingImages.value.length, 1)
}
const handleFrontendFileSelect = (e) => { newFrontendFiles.value.push(...Array.from(e.target.files)); e.target.value = '' }
const handleFrontendFileDrop   = (e) => { newFrontendFiles.value.push(...Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/'))) }
const removeFrontendImage = (idx) => {
  const item = frontendPreviewList.value[idx]
  if (item.existing) existingFrontendImages.value = existingFrontendImages.value.filter(u => u !== item.src)
  else newFrontendFiles.value.splice(idx - existingFrontendImages.value.length, 1)
}
const handleCmsFileSelect = (e) => { newCmsFiles.value.push(...Array.from(e.target.files)); e.target.value = '' }
const handleCmsFileDrop   = (e) => { newCmsFiles.value.push(...Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/'))) }
const removeCmsImage = (idx) => {
  const item = cmsPreviewList.value[idx]
  if (item.existing) existingCmsImages.value = existingCmsImages.value.filter(u => u !== item.src)
  else newCmsFiles.value.splice(idx - existingCmsImages.value.length, 1)
}

const addSubtitle    = () => { form.value.subtitles.push({ text: '', link: '' }) }
const removeSubtitle = (idx) => { form.value.subtitles.splice(idx, 1) }

const submitForm = async () => {
  submitting.value = true
  try {
    const fd = new FormData()
    fd.append('title', form.value.title); fd.append('description', form.value.description || '')
    fd.append('priority', form.value.priority); fd.append('scenario_type', form.value.scenario_type)
    fd.append('status', form.value.status)
    fd.append('date_to_accomplish', form.value.date_to_accomplish || '')
    form.value.subtitles.filter(s => s.text.trim() || s.link.trim()).forEach((s, i) => {
      fd.append(`subtitles[${i}][text]`, s.text)
      fd.append(`subtitles[${i}][link]`, s.link || '')
    })
    if (editingBug.value) {
      fd.append('_method', 'PUT')
      existingImages.value.forEach(url => fd.append('existing_images[]', url))
      existingFrontendImages.value.forEach(url => fd.append('existing_frontend_images[]', url))
      existingCmsImages.value.forEach(url => fd.append('existing_cms_images[]', url))
    } else {
      fd.append('project_id', selectedProject.value.id)
    }
    newImageFiles.value.forEach(file => fd.append('images[]', file))
    newFrontendFiles.value.forEach(file => fd.append('frontend_images[]', file))
    newCmsFiles.value.forEach(file => fd.append('cms_images[]', file))
    const url = editingBug.value ? `${config.public.apiBase}/bugs/${editingBug.value.id}` : `${config.public.apiBase}/bugs`
    await apiFetch(url, { method: 'POST', body: fd })
    await fetchBugs(); await fetchProjects(); closeModal()
  } catch (e) { console.error('Failed to save bug', e); alert('Failed to save bug. Please try again.') }
  finally { submitting.value = false }
}

const updateStatus = async (bug, newStatus) => {
  try {
    const fd = new FormData(); fd.append('_method', 'PUT'); fd.append('status', newStatus)
    await apiFetch(`${config.public.apiBase}/bugs/${bug.id}`, { method: 'POST', body: fd })
    if (newStatus === 'Completed') triggerSquishAnim()
    await fetchBugs(); await fetchProjects()
  } catch (e) { console.error('Failed to update status', e) }
}

const confirmDelete = (bug) => { deletingBug.value = bug; showDeleteModal.value = true }
const deleteBug = async () => {
  submitting.value = true
  try {
    await apiFetch(`${config.public.apiBase}/bugs/${deletingBug.value.id}`, { method: 'DELETE' })
    await fetchBugs(); await fetchProjects()
    showDeleteModal.value = false; deletingBug.value = null
  } catch (e) { console.error('Failed to delete bug', e) }
  finally { submitting.value = false }
}

const viewImages  = (bug)  => { viewingBug.value = bug; showImagesModal.value = true }
const viewBug     = (bug)  => { viewingBugDetail.value = bug; showViewModal.value = true }

async function generateFolderLink(dev) {
  try {
    const body = {
      developer_email: dev.email,
      developer_name:  dev.name.split(' ')[0],
      visibility:      'private',
    }

    const data = await $fetch(`${config.public.apiBase}/dev-folders`, {
      method: 'POST',
      body,
    })
    await navigator.clipboard.writeText(data.url)
    alert(`Link copied!\n\n${data.url}\n\nVisibility: ${data.visibility}`)
  } catch (e) {
    const msg = e?.data?.message || e?.message || JSON.stringify(e?.data) || 'Unknown error'
    alert(`Failed to generate folder link.\n\nError: ${msg}\n\nMake sure you ran: php artisan migrate`)
  }
}

const formatBugDate = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
}
const isAccomplishOverdue = (bug) => {
  if (!bug.date_to_accomplish) return false
  return new Date(bug.date_to_accomplish) < new Date(new Date().toDateString())
}
const dueDaysLabel = (bug) => {
  if (!bug.date_to_accomplish) return null
  const today = new Date(new Date().toDateString())
  const due   = new Date(bug.date_to_accomplish)
  const diff  = Math.round((due - today) / 86400000)
  if (diff < 0)   return { text: diff === -1 ? '1 day overdue' : `${Math.abs(diff)} days overdue`, color: '#dc2626' }
  if (diff === 0) return { text: 'Due today',         color: '#dc2626' }
  if (diff <= 2)  return { text: `${diff} day${diff === 1 ? '' : 's'} left`, color: '#dc2626' }
  if (diff <= 7)  return { text: `${diff} days left`, color: '#d97706' }
  return { text: `${diff} days left`, color: '#16a34a' }
}
const clearFilters = () => { filters.value = { search: '', status: '', priority: '', scenario_type: '' } }

const openThread = (bug) => {
  threadBugId.value = bug.id
  newThreadMessage.value = ''
  if (!newThreadAuthor.value && currentUser.value?.name) {
    newThreadAuthor.value = currentUser.value.name
  }
  editingMsgIndex.value = null
  showThreadModal.value = true
  nextTick(() => {
    if (threadListEl.value) threadListEl.value.scrollTop = threadListEl.value.scrollHeight
  })
}

const addThreadMessage = async () => {
  if (!newThreadMessage.value.trim() || !newThreadAuthor.value.trim() || threadSending.value || !threadBug.value) return
  threadSending.value = true
  const message = newThreadMessage.value.trim()
  const author  = newThreadAuthor.value.trim()
  newThreadMessage.value = ''
  try {
    const updated = [
      ...(threadBug.value.dev_comments || []),
      { message, author, timestamp: new Date().toISOString() }
    ]
    const fd = new FormData()
    fd.append('_method', 'PUT')
    fd.append('dev_comments_json', JSON.stringify(updated))
    await apiFetch(`${config.public.apiBase}/bugs/${threadBug.value.id}`, { method: 'POST', body: fd })
    await fetchBugs()
    nextTick(() => {
      if (threadListEl.value) threadListEl.value.scrollTop = threadListEl.value.scrollHeight
    })
  } catch (e) {
    console.error('Failed to add comment', e)
    newThreadMessage.value = message
  }
  finally { threadSending.value = false }
}

const formatThreadTime = (iso) => {
  if (!iso) return ''
  const d = new Date(iso)
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) +
    ' · ' + d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
}

const startEditMsg  = (idx, text) => { editingMsgIndex.value = idx; editingMsgValue.value = text }
const cancelEditMsg = () => { editingMsgIndex.value = null; editingMsgValue.value = '' }

const saveEditMsg = async (idx) => {
  if (!editingMsgValue.value.trim() || !threadBug.value) return
  const updated = threadBug.value.dev_comments.map((m, i) =>
    i === idx ? { ...m, message: editingMsgValue.value.trim(), edited: true } : m
  )
  cancelEditMsg()
  await saveThreadComments(updated)
}

const deleteMsg = async (idx) => {
  if (!threadBug.value) return
  const updated = threadBug.value.dev_comments.filter((_, i) => i !== idx)
  await saveThreadComments(updated)
}

const saveThreadComments = async (comments) => {
  try {
    const fd = new FormData()
    fd.append('_method', 'PUT')
    fd.append('dev_comments_json', JSON.stringify(comments))
    await apiFetch(`${config.public.apiBase}/bugs/${threadBug.value.id}`, { method: 'POST', body: fd })
    await fetchBugs()
  } catch (e) { console.error('Failed to update comments', e) }
}

const execCmd = (cmd) => {
  document.execCommand(cmd, false, null)
  if (descEditor.value) form.value.description = descEditor.value.innerHTML
  updateRteState()
}
const updateRteState = () => {
  rteActive.bold   = document.queryCommandState('bold')
  rteActive.italic = document.queryCommandState('italic')
}
const stripHtml = (html) => html ? html.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim() : ''

const priorityBadgeClass = (p) => ({ Critical: 'badge-critical', High: 'badge-high', Medium: 'badge-medium', Low: 'badge-low' }[p] || '')
const statusBadgeClass   = (s) => ({ Pending: 'badge-pending', 'Out of Scope': 'badge-outofscope', Ongoing: 'badge-ongoing', Completed: 'badge-completed' }[s] || '')
const scenarioBadgeClass = (t) => ({ UI: 'badge-ui', Functionality: 'badge-functionality', 'UI & Functionality': 'badge-uifunctionality' }[t] || '')

// ── Ticket Tracker ───────────────────────────────────────────────────────────
const ticketTrackerOpen = ref(false)
const ticketTab         = ref('overview')
const allTickets        = ref([])
const ttFilters         = reactive({ search: '', project_id: '', status: '' })

const fetchAllTickets = async () => {
  try {
    const data = await apiFetch(`${config.public.apiBase}/bugs`, { params: {} })
    // Load with project info
    const bugsWithProject = (data.bugs || []).map(b => ({
      ...b,
      project: projects.value.find(p => p.id === b.project_id) || null,
    }))
    allTickets.value = bugsWithProject
  } catch (e) { console.error('Failed to fetch all tickets', e) }
}

// ── Maintenance tickets in Ticket Tracker ────────────────────────────────────
const allMaintenanceTickets = ref([])
const selectedMtTicket      = ref(null)
const mtSearch              = ref('')
const mtStatusPill          = ref('all')

const fetchAllMaintenanceTickets = async () => {
  try {
    const projects = await apiFetch(`${config.public.apiBase}/maintenance/projects`)
    const all = []
    await Promise.all((projects || []).map(async (proj) => {
      try {
        const tickets = await apiFetch(`${config.public.apiBase}/maintenance/projects/${proj.id}/tickets`)
        ;(tickets || []).forEach(t => all.push({ ...t, _project: proj }))
      } catch {}
    }))
    all.sort((a, b) => a._project.name.localeCompare(b._project.name) || a.sequence - b.sequence)
    allMaintenanceTickets.value = all
  } catch (e) { console.error('Failed to fetch maintenance tickets', e) }
}

const mtEmailInitials = (email) => {
  const local = email.split('@')[0]
  const parts = local.split(/[._-]/)
  return parts.length >= 2 ? (parts[0][0] + parts[1][0]).toUpperCase() : local.slice(0, 2).toUpperCase()
}

const mtStatusBadgeClass = (s) => ({
  Pending: 'badge-pending', 'In Progress': 'badge-ongoing', 'On Hold': 'badge-hold',
  Completed: 'badge-completed', Cancelled: 'badge-out-of-scope',
}[s] || '')

const mtAgeDays = (t) => {
  const start = t.date_received ? new Date(t.date_received) : new Date(t.created_at)
  const end   = t.completion_date ? new Date(t.completion_date) : new Date()
  return Math.max(0, Math.floor((end - start) / 86400000))
}

const mtIsOverdue = (t) => {
  if (!t.target_date || t.completion_date) return false
  const [y, m, d] = t.target_date.slice(0, 10).split('-').map(Number)
  const target = new Date(y, m - 1, d, 23, 59, 59, 999)
  return new Date() > target
}

const mtAgeBadgeClass = (t) => {
  const days = mtAgeDays(t)
  if (mtIsOverdue(t)) return 'age-badge--critical'
  if (days > 14) return 'age-badge--high'
  if (days > 7)  return 'age-badge--medium'
  return 'age-badge--low'
}

const filteredMtTickets = computed(() => {
  const q = mtSearch.value.trim().toLowerCase()
  return allMaintenanceTickets.value.filter(t => {
    if (mtStatusPill.value === 'pending'     && t.status !== 'Pending')     return false
    if (mtStatusPill.value === 'in-progress' && t.status !== 'In Progress') return false
    if (mtStatusPill.value === 'on-hold'     && t.status !== 'On Hold')     return false
    if (mtStatusPill.value === 'completed'   && t.status !== 'Completed')   return false
    if (mtStatusPill.value === 'overdue'     && !mtIsOverdue(t))            return false
    if (q) {
      const haystack = [t.ticket_number, t.client, t.request, t._project?.name].join(' ').toLowerCase()
      if (!haystack.includes(q)) return false
    }
    return true
  })
})

const ttDevFolders = ref([])

async function fetchTtDevFolders() {
  try {
    const data = await $fetch(`${api}/dev-folders`)
    ttDevFolders.value = data
  } catch { ttDevFolders.value = [] }
}

function devFolderToken(email) {
  if (!email) return null
  const f = ttDevFolders.value.find(f => f.developer_email?.toLowerCase() === email?.toLowerCase())
  return f?.token ?? null
}

const openTicketTracker = async () => {
  appLoading.value         = true
  ticketTrackerOpen.value  = true
  selectedProject.value    = null
  devFoldersViewOpen.value = false
  detailView.value         = null
  ticketTab.value          = 'overview'
  mtSearch.value           = ''
  mtStatusPill.value       = 'all'
  await Promise.all([fetchAllTickets(), fetchTtDevFolders(), fetchAllMaintenanceTickets()])
  appLoading.value = false
}

const sentTickets       = computed(() => allTickets.value.filter(t => t.ticket_sent_at))
const resolvedTickets   = computed(() => allTickets.value.filter(t => t.status === 'Completed'))
const pipelineTickets   = computed(() => allTickets.value.filter(t => t.status !== 'Completed'))
const inProgressTickets = computed(() => allTickets.value.filter(t => t.ticket_sent_at && t.status !== 'Completed'))
const pendingTickets    = computed(() => allTickets.value.filter(t => !t.has_assignment || !t.ticket_sent_at))
const needsAssignment   = computed(() => allTickets.value.filter(t => !t.has_assignment))
const needsSend         = computed(() => allTickets.value.filter(t => t.has_assignment && !t.ticket_sent_at))

// ── Ticket Tracker — Inline Detail ───────────────────────────────────────────
const ttDetailBug     = ref(null)
const ttNewComment    = ref('')
const ttAuthorName    = ref('')
const ttPosting       = ref(false)
const ttCurrentStatus    = ref('Pending')
const ttCurrentDevStatus = ref('Not Started')
const ttActivityListEl = ref(null)

const openTTDetail = async (ticket) => {
  try {
    const fresh = await apiFetch(`${config.public.apiBase}/bugs/${ticket.id}/ticket`)
    ttDetailBug.value        = { ...fresh, project: ticket.project || fresh.project }
    ttCurrentStatus.value    = fresh.status
    ttCurrentDevStatus.value = fresh.dev_status || 'Not Started'
    ttNewComment.value    = ''
    if (!ttAuthorName.value && currentUser.value?.name) {
      ttAuthorName.value = currentUser.value.name
    }
    nextTick(() => {
      if (ttActivityListEl.value) ttActivityListEl.value.scrollTop = ttActivityListEl.value.scrollHeight
    })
  } catch (e) { console.error('Failed to load ticket detail', e) }
}

const ttScrollBottom = () => {
  nextTick(() => {
    if (ttActivityListEl.value) ttActivityListEl.value.scrollTop = ttActivityListEl.value.scrollHeight
  })
}

const ttPostComment = async () => {
  if (!ttNewComment.value.trim() || ttPosting.value || !ttDetailBug.value) return
  ttPosting.value = true
  const message = ttNewComment.value.trim()
  ttNewComment.value = ''
  try {
    const updated = await apiFetch(`${config.public.apiBase}/bugs/${ttDetailBug.value.id}/comments`, {
      method: 'POST',
      body: { message, author: ttAuthorName.value || 'Developer' },
    })
    ttDetailBug.value = { ...updated, project: ttDetailBug.value.project }
    ttCurrentStatus.value = updated.status
    updateBugInLists(updated)
    ttScrollBottom()
  } catch (e) {
    console.error('Failed to post comment', e)
    ttNewComment.value = message
  } finally { ttPosting.value = false }
}

const ttUpdateStatus = async () => {
  if (!ttDetailBug.value) return
  try {
    const updated = await apiFetch(`${config.public.apiBase}/bugs/${ttDetailBug.value.id}/status`, {
      method: 'PATCH',
      body: { status: ttCurrentStatus.value, author: ttAuthorName.value || 'Developer' },
    })
    ttDetailBug.value = { ...updated, project: ttDetailBug.value.project }
    ttCurrentStatus.value = updated.status
    updateBugInLists(updated)
    ttScrollBottom()
  } catch (e) {
    console.error('Failed to update status', e)
    ttCurrentStatus.value = ttDetailBug.value?.status ?? 'Pending'
  }
}

const ttUpdateDevStatus = async () => {
  if (!ttDetailBug.value) return
  const newStatus = ttCurrentDevStatus.value
  try {
    const updated = await apiFetch(`${config.public.apiBase}/bugs/${ttDetailBug.value.id}/dev-status`, {
      method: 'PATCH',
      body: { dev_status: newStatus, author: ttAuthorName.value || 'Developer' },
    })
    ttDetailBug.value        = { ...updated, project: ttDetailBug.value.project }
    ttCurrentDevStatus.value = updated.dev_status || 'Not Started'
    updateBugInLists(updated)
    ttScrollBottom()
    if (newStatus === 'Ready for QA') {
      showToast(`🔔 Bug #${ttDetailBug.value.sequence} "${ttDetailBug.value.title}" is Ready for QA — please verify!`, 'success')
    } else if (newStatus === 'Blocked') {
      showToast(`⚠️ Bug #${ttDetailBug.value.sequence} "${ttDetailBug.value.title}" is Blocked — needs attention!`, 'error')
    }
  } catch (e) {
    console.error('Failed to update dev status', e)
    ttCurrentDevStatus.value = ttDetailBug.value?.dev_status || 'Not Started'
  }
}

const ttMarkResolved = async () => {
  if (ttPosting.value || !ttDetailBug.value) return
  ttPosting.value = true
  try {
    const updated = await apiFetch(`${config.public.apiBase}/bugs/${ttDetailBug.value.id}/resolve`, {
      method: 'POST',
      body: { author: ttAuthorName.value || 'Developer' },
    })
    ttDetailBug.value = { ...updated, project: ttDetailBug.value.project }
    ttCurrentStatus.value = updated.status
    updateBugInLists(updated)
    triggerSquishAnim()
    ttScrollBottom()
  } catch (e) { console.error('Failed to resolve ticket', e) }
  finally { ttPosting.value = false }
}

// ── Ticket Tracker — Search / Filter / SLA ───────────────────────────────────
const ttGlobalSearch   = ref('')
const ttFilterPill     = ref('all')
const ttPriorityFilter = ref('')
const ttAssigneeFilter = ref('')

const SLA_DAYS = { Critical: 1, High: 3, Medium: 7, Low: 14 }

const ticketAgeDays = (t) => Math.max(0, Math.floor((Date.now() - new Date(t.created_at)) / 86400000))
const ageLabel      = (t) => { const d = ticketAgeDays(t); return d === 0 ? 'Today' : `${d}d` }
const ageBadgeClass = (t) => {
  if (t.status === 'Completed') return 'age-badge--resolved'
  const d = ticketAgeDays(t)
  if (d < 3) return 'age-badge--fresh'
  if (d < 7) return 'age-badge--aging'
  return 'age-badge--old'
}
const slaProgress = (t) => {
  if (t.status === 'Completed') return 0
  const limit = SLA_DAYS[t.priority] || 7
  return Math.min(Math.round((ticketAgeDays(t) / limit) * 100), 100)
}
const slaBarColor = (pct) => pct >= 100 ? '#ef4444' : pct >= 75 ? '#f59e0b' : '#22c55e'
const isOverdue   = (t) => t.status !== 'Completed' && slaProgress(t) >= 100

const overdueTickets = computed(() => allTickets.value.filter(t => isOverdue(t)))
const avgAge = computed(() => {
  const open = allTickets.value.filter(t => t.status !== 'Completed')
  if (!open.length) return '—'
  const avg = open.reduce((s, t) => s + ticketAgeDays(t), 0) / open.length
  return avg < 1 ? '< 1d' : `${Math.round(avg)}d`
})

const filteredAllTickets = computed(() => {
  return allTickets.value.filter(t => {
    if (ttGlobalSearch.value) {
      const q = ttGlobalSearch.value.toLowerCase()
      const devName  = t.assigned_developer?.name?.toLowerCase() || ''
      const devNames = (t.assigned_developers || []).map(d => d.name?.toLowerCase()).join(' ')
      if (!t.title.toLowerCase().includes(q) && !String(t.sequence).includes(q) && !devName.includes(q) && !devNames.includes(q)) return false
    }
    if (ttFilterPill.value === 'pending')      { if (t.status !== 'Pending')    return false }
    else if (ttFilterPill.value === 'in-progress') { if (t.status !== 'Ongoing') return false }
    else if (ttFilterPill.value === 'resolved')    { if (t.status !== 'Completed') return false }
    else if (ttFilterPill.value === 'overdue')     { if (!isOverdue(t)) return false }
    else if (ttFilterPill.value === 'sent')        { if (!t.ticket_sent_at) return false }
    if (ttFilters.project_id && t.project_id !== ttFilters.project_id) return false
    if (ttPriorityFilter.value && t.priority !== ttPriorityFilter.value) return false
    if (ttAssigneeFilter.value) {
      const q = ttAssigneeFilter.value.toLowerCase()
      const devName  = t.assigned_developer?.name?.toLowerCase() || ''
      const devNames = (t.assigned_developers || []).map(d => d.name?.toLowerCase()).join(' ')
      if (!devName.includes(q) && !devNames.includes(q)) return false
    }
    return true
  })
})

const projectBreakdown = computed(() => {
  const counts = {}
  allTickets.value.forEach(t => {
    if (t.project) {
      const key = 'bug-' + t.project.id
      if (!counts[key]) counts[key] = { name: t.project.name, color: t.project.color || '#94a3b8', count: 0, type: 'bug' }
      counts[key].count++
    }
  })
  allMaintenanceTickets.value.forEach(t => {
    if (t._project) {
      const key = 'maint-' + t._project.id
      if (!counts[key]) counts[key] = { name: t._project.name + ' [M]', color: t._project.color || '#10b981', count: 0, type: 'maint' }
      counts[key].count++
    }
  })
  const rows = Object.values(counts).sort((a, b) => b.count - a.count)
  const max  = rows[0]?.count || 1
  return rows.map(r => ({ ...r, pct: Math.round((r.count / max) * 100) }))
})

const devWorkload = computed(() => {
  const counts = {}
  allTickets.value.filter(t => t.assigned_developer && t.status !== 'Completed').forEach(t => {
    const dev = t.assigned_developer
    const key = (dev.email || dev.id || dev.name).toString().toLowerCase()
    if (!counts[key]) counts[key] = { name: dev.name, email: dev.email ?? null, count: 0 }
    counts[key].count++
  })
  // include maintenance devs (email arrays) — exclude Completed/Cancelled tickets
  allMaintenanceTickets.value
    .filter(t => !['Completed', 'Cancelled'].includes(t.status))
    .forEach(t => {
      ;(t.assigned_devs || []).forEach(email => {
        const key = email.toLowerCase()
        if (!counts[key]) {
          const localPart = email.split('@')[0]
          const parts     = localPart.split(/[._-]/)
          const name      = parts.length >= 2
            ? parts[0].charAt(0).toUpperCase() + parts[0].slice(1) + ' ' + parts[1].charAt(0).toUpperCase() + parts[1].slice(1)
            : localPart.charAt(0).toUpperCase() + localPart.slice(1)
          counts[key] = { name, email, count: 0 }
        }
        counts[key].count++
      })
    })
  const rows = Object.values(counts).sort((a, b) => b.count - a.count)
  const max  = rows[0]?.count || 1
  return rows.map(r => {
    const folder = ttDevFolders.value.find(f =>
      (r.email && f.developer_email?.toLowerCase() === r.email.toLowerCase()) ||
      (f.developer_name?.toLowerCase() === r.name?.toLowerCase())
    )
    return { ...r, pct: Math.round((r.count / max) * 100), folderToken: folder?.token ?? null }
  })
})

const recentActivity = computed(() => {
  const entries = []
  allTickets.value.forEach(t => {
    (t.activity_log || []).forEach(e => {
      entries.push({ ...e, bugSequence: t.sequence, bugTitle: t.title })
    })
  })
  return entries.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp)).slice(0, 15)
})

const clearActivityLog = async () => {
  try {
    await apiFetch(`${config.public.apiBase}/activity-log`, { method: 'DELETE' })
    allTickets.value = allTickets.value.map(t => ({ ...t, activity_log: [] }))
  } catch (e) { console.error('Failed to clear activity log', e) }
}

const ticketStage = (t) => {
  if (t.status === 'Completed') return 'Resolved'
  if (t.ticket_sent_at) return 'In Progress'
  if (t.assigned_developer_id) return 'Ready'
  return 'Unassigned'
}
const ticketStageBadge = (t) => {
  const s = ticketStage(t)
  return { Resolved: 'tt-stage--resolved', 'In Progress': 'tt-stage--progress', Ready: 'tt-stage--ready', Unassigned: 'tt-stage--unassigned' }[s] || ''
}

const resolvedBy = (t) => {
  const entry = (t.activity_log || []).findLast?.(e => e.type === 'resolved')
  return entry?.user_name || t.assigned_developer?.name || null
}
const resolvedAt = (t) => {
  const entry = (t.activity_log || []).findLast?.(e => e.type === 'resolved')
  return entry?.timestamp || t.updated_at
}
const timeTaken = (t) => {
  if (!t.ticket_sent_at) return '—'
  const sentDate = new Date(t.ticket_sent_at)
  const endDate  = new Date(resolvedAt(t) || t.updated_at)
  const diffMs   = endDate - sentDate
  if (diffMs < 0) return '—'
  const days  = Math.floor(diffMs / 86400000)
  const hours = Math.floor((diffMs % 86400000) / 3600000)
  if (days > 0) return `${days}d ${hours}h`
  if (hours > 0) return `${hours}h`
  return '< 1h'
}

// ── Toast ────────────────────────────────────────────────────────────────────
const toast = reactive({ show: false, message: '', type: 'success' })
let toastTimer = null
const showToast = (message, type = 'success') => {
  if (toastTimer) clearTimeout(toastTimer)
  toast.message = message
  toast.type    = type
  toast.show    = true
  toastTimer    = setTimeout(() => { toast.show = false }, 3500)
}

// ── Assign Developer ─────────────────────────────────────────────────────────
const teamMembers         = ref([])
const assignDropdownBugId = ref(null)
const assignSearch        = ref('')
const assignSearchInput   = ref(null)

const fetchTeamMembers = async () => {
  try {
    teamMembers.value = await apiFetch(`${config.public.apiBase}/team-members`)
  } catch (e) { console.error('Failed to fetch team members', e) }
}

const filteredAssignMembers = computed(() => {
  if (!assignSearch.value.trim()) return teamMembers.value
  const q = assignSearch.value.toLowerCase()
  return teamMembers.value.filter(m =>
    m.name.toLowerCase().includes(q) || m.email.toLowerCase().includes(q)
  )
})

const openAssignDropdown = (bugId) => {
  assignDropdownBugId.value = assignDropdownBugId.value === bugId ? null : bugId
  assignSearch.value = ''
  if (assignDropdownBugId.value) {
    nextTick(() => { assignSearchInput.value?.focus() })
  }
}

const updateBugInLists = (updated, originalBug) => {
  const idx = bugs.value.findIndex(b => b.id === updated.id)
  if (idx !== -1) bugs.value[idx] = updated
  const tidx = allTickets.value.findIndex(b => b.id === updated.id)
  if (tidx !== -1) allTickets.value[tidx] = { ...updated, project: allTickets.value[tidx]?.project }
}

const assignDev = async (bug, member) => {
  // Toggle: if already assigned, remove; otherwise add
  const alreadyAssigned = (bug.assigned_developers || []).some(d => d.id === member.id)
  if (alreadyAssigned) {
    await removeDev(bug, { id: member.id, email: member.email })
    return
  }
  try {
    const updated = await apiFetch(`${config.public.apiBase}/bugs/${bug.id}/assign`, {
      method: 'PATCH',
      body: { action: 'add', developer_id: member.id },
    })
    updateBugInLists(updated)
    showToast(`${member.name.split(' ')[0]} assigned to #${bug.sequence}`)
  } catch (e) { console.error('Failed to assign developer', e) }
}

const removeDev = async (bug, dev) => {
  try {
    const body = dev.id
      ? { action: 'remove', developer_id: dev.id }
      : { action: 'remove', developer_email: dev.email }
    const updated = await apiFetch(`${config.public.apiBase}/bugs/${bug.id}/assign`, {
      method: 'PATCH',
      body,
    })
    updateBugInLists(updated)
    showToast(`${dev.name} removed from #${bug.sequence}`)
    // Close dropdown if no devs remain
    if (!(updated.assigned_developers?.length)) {
      assignDropdownBugId.value = null
      assignSearch.value = ''
    }
  } catch (e) { console.error('Failed to remove developer', e) }
}

const assignDevByEmail = async (bug, email) => {
  assignSearch.value = ''
  try {
    const updated = await apiFetch(`${config.public.apiBase}/bugs/${bug.id}/assign`, {
      method: 'PATCH',
      body: { action: 'add', developer_email: email },
    })
    updateBugInLists(updated)
    showToast(`${email.split('@')[0]} assigned to #${bug.sequence}`)
  } catch (e) { console.error('Failed to assign by email', e) }
}

// Close assign dropdown when clicking outside
onMounted(() => {
  document.addEventListener('click', () => {
    assignDropdownBugId.value = null
    assignSearch.value = ''
  })
})

// ── Dev Folders View ──────────────────────────────────────────────────────────
const devFoldersViewOpen = ref(false)
const devFolders         = ref([])
const maintenanceDevs    = ref([])
const devFoldersLoading  = ref(false)
const devFoldersSummary  = ref(null)

async function fetchDevFolders() {
  devFoldersLoading.value = true
  try {
    const [folders, summary, mDevs] = await Promise.all([
      $fetch(`${config.public.apiBase}/dev-folders`),
      $fetch(`${config.public.apiBase}/dev-folders/summary`),
      $fetch(`${config.public.apiBase}/maintenance-devs`),
    ])
    devFolders.value        = folders
    devFoldersSummary.value = summary
    maintenanceDevs.value   = mDevs
  } catch (e) {
    console.error('Failed to fetch dev folders', e)
  } finally {
    devFoldersLoading.value = false
  }
}

async function openDevFoldersView() {
  appLoading.value         = true
  devFoldersViewOpen.value = true
  ticketTrackerOpen.value  = false
  dashboardOpen.value      = false
  inactiveOpen.value       = false
  selectedProject.value    = null
  await fetchDevFolders()
  appLoading.value = false
}

async function openDashboard() {
  appLoading.value         = true
  dashboardOpen.value      = true
  selectedProject.value    = null
  ticketTrackerOpen.value  = false
  devFoldersViewOpen.value = false
  inactiveOpen.value       = false
  await new Promise(r => setTimeout(r, 300))
  dashboardKey.value++
  appLoading.value = false
}

function folderInitials(name) {
  if (!name) return '?'
  return name.split(' ').map(p => p[0]).join('').toUpperCase().slice(0, 2)
}

async function toggleFolderVisibility(folder) {
  const next = folder.visibility === 'public' ? 'private' : 'public'
  try {
    await $fetch(`${config.public.apiBase}/dev-folders/${folder.token}`, {
      method: 'PATCH',
      body: { visibility: next },
    })
    folder.visibility = next
    showToast(`Folder set to ${next}`)
  } catch (e) {
    showToast('Failed to update visibility', 'error')
  }
}

async function copyFolderLink(folder) {
  const url = `${window.location.origin}/dev-folder/${folder.token}`
  await navigator.clipboard.writeText(url)
  showToast('Folder link copied!')
}

// ── Dev Folder Detail Modal ───────────────────────────────────────────────────
const AVATAR_COLORS = [
  '#4f46e5','#7c3aed','#db2777','#dc2626','#ea580c',
  '#d97706','#16a34a','#0891b2','#2563eb','#9333ea',
  '#0d9488','#65a30d','#be185d','#0369a1','#92400e','#374151',
]

const devFolderDetail        = ref(null)
const devFolderDetailBugs    = ref([])
const devFolderDetailLoading = ref(false)
const colorPickerOpen        = ref(false)

const folderDetailStats = computed(() => {
  const bugs = devFolderDetailBugs.value
  return {
    total:     bugs.length,
    pending:   bugs.filter(b => b.status === 'Pending').length,
    ongoing:   bugs.filter(b => b.status === 'Ongoing').length,
    completed: bugs.filter(b => b.status === 'Completed').length,
  }
})

const dfsSummaryProgress = computed(() => {
  if (!devFoldersSummary.value) return null
  const o = devFoldersSummary.value.overall
  const total = o.total_completed + o.total_active + o.pending + o.sent_back
  if (!total) return { pct: 0, total: 0, rawCompleted: 0, rawTotal: 0, done: 0, active: 0, pend: 0, blocked: 0 }
  const pct  = (v) => Math.round(v / total * 100)
  return {
    pct:          pct(o.total_completed),
    rawCompleted: o.total_completed,
    rawTotal:     total,
    done:         pct(o.total_completed),
    active:       pct(o.total_active),
    pend:         pct(o.pending),
    blocked:      pct(o.sent_back),
  }
})

async function openFolderDetail(folder) {
  devFolderDetail.value        = folder
  devFolderDetailBugs.value    = []
  devFolderDetailLoading.value = true
  try {
    const res = await $fetch(`${config.public.apiBase}/dev-folders/${folder.token}/bugs`)
    devFolderDetailBugs.value = res.bugs || []
  } catch {
    devFolderDetailBugs.value = []
  } finally {
    devFolderDetailLoading.value = false
  }
}

function openFolderDetailByEmail(email) {
  const folder = devFolders.value.find(f => f.developer_email.toLowerCase() === email.toLowerCase())
  if (folder) openFolderDetail(folder)
}

function closeFolderDetail() {
  devFolderDetail.value = null
  colorPickerOpen.value = false
}

function maintenanceFolderUrl(email) {
  const base = typeof window !== 'undefined' ? window.location.origin : 'http://localhost:3000'
  return `${base}/maintenance-dev-folder?email=${encodeURIComponent(email)}`
}

async function copyMaintenanceFolderLink(email) {
  await navigator.clipboard.writeText(maintenanceFolderUrl(email))
  showToast('Folder link copied!')
}

const MAINT_DEV_COLORS = [
  '#059669','#0891b2','#7c3aed','#d97706','#dc2626',
  '#2563eb','#9333ea','#db2777','#16a34a','#0d9488',
  '#ea580c','#65a30d','#0369a1','#92400e','#374151',
]

// ── Maintenance Dev Colors (localStorage-persisted) ───────────────────────────
const maintDevColorMap = ref({})

function loadMaintDevColors() {
  try {
    const stored = localStorage.getItem('maint_dev_colors')
    if (stored) maintDevColorMap.value = JSON.parse(stored)
  } catch {}
}

function getMaintDevColor(email, idx) {
  return maintDevColorMap.value[email] || MAINT_DEV_COLORS[idx % MAINT_DEV_COLORS.length]
}

function setMaintDevColor(email, color) {
  maintDevColorMap.value = { ...maintDevColorMap.value, [email]: color }
  try { localStorage.setItem('maint_dev_colors', JSON.stringify(maintDevColorMap.value)) } catch {}
  if (maintDevDetail.value?.dev.email === email) {
    maintDevDetail.value = { ...maintDevDetail.value, color }
  }
  maintColorPickerOpen.value = false
  showToast('Color updated!')
}

// ── Maintenance Dev Visibility (localStorage-persisted) ──────────────────────
const maintDevVisibilityMap = ref({})

function loadMaintDevVisibilities() {
  try {
    const stored = localStorage.getItem('maint_dev_visibilities')
    if (stored) maintDevVisibilityMap.value = JSON.parse(stored)
  } catch {}
}

function getMaintDevVisibility(email) {
  return maintDevVisibilityMap.value[email] || 'public'
}

function toggleMaintDevVisibility(email) {
  const next = getMaintDevVisibility(email) === 'public' ? 'private' : 'public'
  maintDevVisibilityMap.value = { ...maintDevVisibilityMap.value, [email]: next }
  try { localStorage.setItem('maint_dev_visibilities', JSON.stringify(maintDevVisibilityMap.value)) } catch {}
  showToast(`Folder set to ${next}`)
}

// ── Maintenance Dev Detail Modal ──────────────────────────────────────────────
const maintDevDetail        = ref(null)
const maintDevDetailTickets = ref([])
const maintDevDetailLoading = ref(false)
const maintColorPickerOpen  = ref(false)

const maintDevDetailStats = computed(() => {
  const tix = maintDevDetailTickets.value
  return {
    pending:     tix.filter(t => t.status === 'Pending').length,
    in_progress: tix.filter(t => t.status === 'In Progress').length,
    on_hold:     tix.filter(t => t.status === 'On Hold').length,
    completed:   tix.filter(t => t.status === 'Completed').length,
  }
})

async function openMaintenanceDevDetail(dev, idx) {
  maintDevDetail.value        = { dev, color: getMaintDevColor(dev.email, idx) }
  maintDevDetailTickets.value = []
  maintDevDetailLoading.value = true
  maintColorPickerOpen.value  = false
  try {
    const data = await $fetch(`${config.public.apiBase}/maintenance-dev-folder?email=${encodeURIComponent(dev.email)}`)
    maintDevDetailTickets.value = data.tickets || []
  } catch {
    maintDevDetailTickets.value = []
  } finally {
    maintDevDetailLoading.value = false
  }
}

function closeMaintDevDetail() {
  maintDevDetail.value       = null
  maintColorPickerOpen.value = false
}

function maintStatusBadgeClass(s) {
  return {
    'Pending':     'badge-pending',
    'In Progress': 'badge-ongoing',
    'On Hold':     'badge-outofscope',
    'Completed':   'badge-completed',
    'Cancelled':   'badge-outofscope',
  }[s] || ''
}

async function updateFolderColor(folder, color) {
  try {
    await $fetch(`${config.public.apiBase}/dev-folders/${folder.token}`, {
      method: 'PATCH',
      body: { avatar_color: color },
    })
    folder.avatar_color = color
    if (devFolderDetail.value?.token === folder.token) {
      devFolderDetail.value = { ...devFolderDetail.value, avatar_color: color }
    }
    colorPickerOpen.value = false
    showToast('Color updated!')
  } catch {
    showToast('Failed to update color', 'error')
  }
}

// ── Send Ticket ──────────────────────────────────────────────────────────────
const showSendTicketModal = ref(false)
const sendTicketBug       = ref(null)
const ticketSending       = ref(false)
const isResend            = ref(false)

const openSendTicketModal = (bug) => {
  isResend.value            = false
  sendTicketBug.value       = bug
  showSendTicketModal.value = true
}

const openResendTicketModal = (bug) => {
  isResend.value            = true
  sendTicketBug.value       = bug
  showSendTicketModal.value = true
}

const doSendTicket = async () => {
  if (!sendTicketBug.value || ticketSending.value) return
  ticketSending.value = true
  try {
    const updated = await apiFetch(`${config.public.apiBase}/bugs/${sendTicketBug.value.id}/send-ticket`, {
      method: 'POST',
    })
    updateBugInLists(updated)
    showSendTicketModal.value = false
    const devNames = (updated.assigned_developers || []).map(d => d.name.split(' ')[0]).join(', ')
    showToast(`Ticket #${updated.sequence} sent to ${devNames || 'developer'}`)
  } catch (e) {
    console.error('Failed to send ticket', e)
    const msg = e?.data?.error || e?.message || ''
    if (msg.toLowerCase().includes('already sent')) {
      // Refresh stale bug state so "Sent" button appears
      try {
        const fresh = await apiFetch(`${config.public.apiBase}/bugs/${sendTicketBug.value.id}`)
        updateBugInLists(fresh)
      } catch {}
      showSendTicketModal.value = false
      showToast('Ticket was already sent.', 'error')
    } else {
      showToast('Failed to send ticket. Please try again.', 'error')
    }
  } finally {
    ticketSending.value = false
  }
}

const doResendTicket = async () => {
  if (!sendTicketBug.value || ticketSending.value) return
  ticketSending.value = true
  try {
    const updated = await apiFetch(`${config.public.apiBase}/bugs/${sendTicketBug.value.id}/resend-ticket`, {
      method: 'POST',
    })
    updateBugInLists(updated)
    showSendTicketModal.value = false
    const devNames = (updated.assigned_developers || []).map(d => d.name.split(' ')[0]).join(', ')
    showToast(`Ticket #${updated.sequence} resent to ${devNames || 'developer'}`)
  } catch (e) {
    showToast('Failed to resend ticket. Please try again.', 'error')
  } finally {
    ticketSending.value = false
  }
}

// ── Dev Status ───────────────────────────────────────────────────────────────
const updateDevStatus = async (bug, newStatus) => {
  const old = bug.dev_status || 'Not Started'
  if (old === newStatus) return
  try {
    const updated = await apiFetch(`${config.public.apiBase}/bugs/${bug.id}/dev-status`, {
      method: 'PATCH',
      body: { dev_status: newStatus },
    })
    updateBugInLists(updated)
    if (newStatus === 'Ready for QA') {
      showToast(`🔔 Bug #${bug.sequence} "${bug.title}" is Ready for QA — please verify!`, 'success')
    } else if (newStatus === 'Blocked') {
      showToast(`⚠️ Bug #${bug.sequence} "${bug.title}" is Blocked — needs attention!`, 'error')
    }
  } catch (e) { console.error('Failed to update dev status', e) }
}

await fetchTeamMembers()
await fetchProjects()
loadMaintDevColors()
loadMaintDevVisibilities()
</script>
