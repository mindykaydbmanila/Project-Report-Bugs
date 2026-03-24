<template>
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
          <button v-if="selectedProject && !detailView" class="btn btn-primary" @click="openCreateModal">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Report Bug
          </button>
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
        <div class="sidebar-section-label">Projects</div>
        <nav class="sidebar-nav">
          <button class="sidebar-item" :class="{ active: !selectedProject }" @click="selectProject(null)">
            <span class="sidebar-item-icon">🏠</span>
            <span class="sidebar-item-name">All Projects</span>
            <span class="sidebar-item-count">{{ projects.length }}</span>
          </button>
          <div v-if="projects.length" class="sidebar-divider"></div>
          <button
            v-for="p in projects" :key="p.id"
            class="sidebar-item" :class="{ active: selectedProject?.id === p.id }"
            @click="selectProject(p)"
          >
            <span class="sidebar-color-dot" :style="{ background: p.color }"></span>
            <span class="sidebar-item-name">{{ p.name }}</span>
            <span class="sidebar-item-count">{{ p.bugs_count }}</span>
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

        <!-- ══ All Projects view ══ -->
        <div v-if="!selectedProject">
          <div class="view-header">
            <div>
              <h1 class="view-title">All Projects</h1>
              <p class="view-subtitle">{{ filteredProjects.length }} project{{ filteredProjects.length !== 1 ? 's' : '' }}</p>
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
            <select v-model="projectFilter" class="form-control">
              <option value="">All Projects</option>
              <option value="critical">Has Critical Bugs</option>
              <option value="pending">Has Pending Bugs</option>
              <option value="ongoing">Has Ongoing Bugs</option>
              <option value="completed">Has Completed Bugs</option>
            </select>
            <button v-if="projectSearch || projectFilter" class="btn btn-ghost btn-sm" @click="projectSearch = ''; projectFilter = ''">
              <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
              Clear
            </button>
          </div>

          <div v-if="filteredProjects.length > 0" class="projects-grid">
            <div v-for="p in filteredProjects" :key="p.id" class="project-card" @click="selectProject(p)">
              <div class="project-card-stripe" :style="{ background: p.color }"></div>
              <div class="project-card-body">
                <div class="project-card-icon" :style="{ background: p.color + '22', color: p.color }">📁</div>
                <h3 class="project-card-name">{{ p.name }}</h3>
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

          <div v-else class="empty-state" style="margin-top:60px;">
            <div class="empty-state-icon">{{ projects.length ? '🔍' : '📁' }}</div>
            <div class="empty-state-title">{{ projects.length ? 'No matching projects' : 'No projects yet' }}</div>
            <div class="empty-state-text">{{ projects.length ? 'Try adjusting your search or filter' : 'Create your first project to start tracking bugs' }}</div>
            <button v-if="!projects.length" class="btn btn-primary" style="margin-top:20px;" @click="openProjectModal(null)">+ Create Project</button>
          </div>
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
                <div class="summary-card-icon">🐛</div>
                <div class="summary-card-label">Total Bugs</div>
                <div class="summary-card-value">{{ summary.total || 0 }}</div>
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
                        <th style="width:220px;">Dev Comment</th>
                        <th style="width:90px;">Images</th>
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
                        <td class="dev-comment-cell" @click="openThread(bug)">
                          <div class="dev-thread-trigger">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            <span v-if="bug.dev_comments && bug.dev_comments.length" class="dev-thread-count">{{ bug.dev_comments.length }}</span>
                            <span v-if="bug.dev_comments && bug.dev_comments.length" class="dev-thread-preview">{{ bug.dev_comments[bug.dev_comments.length - 1].message }}</span>
                            <span v-else class="dev-comment-empty">Add comment…</span>
                          </div>
                        </td>
                        <td>
                          <button v-if="bug.images && bug.images.length > 0" class="btn btn-ghost btn-sm" style="gap:4px;" @click="viewImages(bug)">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                            {{ bug.images.length }}
                          </button>
                          <span v-else style="color:var(--gray-300);padding-left:4px;">—</span>
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
              <div class="form-group">
                <label class="form-label">Status <span class="required">*</span></label>
                <select v-model="form.status" class="form-control" required>
                  <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
                </select>
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
                  Developer Comments
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
              <h3 class="modal-title" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Dev Comments — {{ threadBug?.title }}</h3>
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
                    <div class="thread-msg-row">
                      <div class="thread-msg-bubble">{{ msg.message }}</div>
                      <div class="thread-msg-menu-wrap">
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
                    <div v-if="deletingMsgIdx === i" class="thread-delete-confirm">
                      <span>Delete this comment?</span>
                      <div style="display:flex;gap:6px;">
                        <button class="btn btn-ghost btn-sm" @click="deletingMsgIdx = null">Cancel</button>
                        <button class="btn btn-sm" style="background:var(--danger);color:#fff;border-color:var(--danger);" @click="deleteMsg(i); deletingMsgIdx = null">Delete</button>
                      </div>
                    </div>
                  </template>
                  <div class="thread-msg-time">{{ formatThreadTime(msg.timestamp) }}{{ msg.edited ? ' · edited' : '' }}</div>
                </div>
              </div>
              <div v-else class="thread-empty">
                <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--gray-300);margin-bottom:8px;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <div>No comments yet. Be the first to add one.</div>
              </div>
            </div>
          </div>
          <div class="thread-input-area">
            <textarea
              v-model="newThreadMessage"
              class="thread-textarea"
              placeholder="Write a developer comment… (Ctrl+Enter to send)"
              rows="3"
              @keydown.ctrl.enter.prevent="addThreadMessage"
            ></textarea>
            <div class="thread-input-footer">
              <span style="font-size:11px;color:var(--gray-400);">Ctrl + Enter to send</span>
              <button class="btn btn-primary" :disabled="!newThreadMessage.trim() || threadSending" @click="addThreadMessage">
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

  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted, reactive } from 'vue'

const config  = useRuntimeConfig()
const apiBase = config.public.apiBase.replace('/api', '')

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
    alert('Google sign-in failed. Please try again.')
  } else {
    const stored = localStorage.getItem('auth_token')
    if (stored) authToken.value = stored
  }
  if (authToken.value) await fetchCurrentUser()

  // Close profile dropdown when clicking outside
  document.addEventListener('click', (e) => {
    if (profileDropdownRef.value && !profileDropdownRef.value.contains(e.target)) {
      profileDropdownOpen.value = false
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
const projects               = ref([])
const selectedProject        = ref(null)
const projectSearch          = ref('')
const projectFilter          = ref('')
const showProjectModal       = ref(false)
const showDeleteProjectModal = ref(false)
const editingProject         = ref(null)
const deletingProject        = ref(null)
const projectForm            = ref({ name: '', description: '', color: '#6366f1' })

// ── Bug state ──
const bugs            = ref([])
const summary         = ref({})
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
const form = ref({ title: '', description: '', priority: 'Medium', scenario_type: 'UI', status: 'Pending', subtitles: [{ text: '', link: '' }] })
const showThreadModal  = ref(false)
const threadBugId      = ref(null)
const newThreadMessage = ref('')
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

onUnmounted(() => destroyCharts())

const openDetailView = (type) => {
  detailView.value = type
}

// ── Computed ──
const threadBug = computed(() => bugs.value.find(b => b.id === threadBugId.value) || null)

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

const selectProject = (project) => {
  selectedProject.value = project
  detailView.value = null
  bugs.value = []
  summary.value = {}
  clearFilters()
  if (project) fetchBugs()
}

const openProjectModal = (project) => {
  editingProject.value = project
  projectForm.value = project
    ? { name: project.name, description: project.description || '', color: project.color }
    : { name: '', description: '', color: '#6366f1' }
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
  form.value = { title: '', description: '', priority: 'Medium', scenario_type: 'UI', status: 'Pending', subtitles: [{ text: '', link: '' }] }
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

const formatBugDate = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
}
const clearFilters = () => { filters.value = { search: '', status: '', priority: '', scenario_type: '' } }

const openThread = (bug) => {
  threadBugId.value = bug.id
  newThreadMessage.value = ''
  editingMsgIndex.value = null
  showThreadModal.value = true
  nextTick(() => {
    if (threadListEl.value) threadListEl.value.scrollTop = threadListEl.value.scrollHeight
  })
}

const addThreadMessage = async () => {
  if (!newThreadMessage.value.trim() || threadSending.value || !threadBug.value) return
  threadSending.value = true
  const message = newThreadMessage.value.trim()
  newThreadMessage.value = ''
  try {
    const updated = [
      ...(threadBug.value.dev_comments || []),
      { message, timestamp: new Date().toISOString() }
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

await fetchProjects()
</script>
