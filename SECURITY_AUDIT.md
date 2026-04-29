# Security & Code Quality Audit — QA Bug Tracker
**Date:** 2026-04-28
**Scope:** Laravel 13 backend + Nuxt 4 / Vue 3 frontend
**Total Findings:** 37

---

## Table of Contents
- [Critical (7)](#critical)
- [High (7)](#high)
- [Medium (9)](#medium)
- [Low (9)](#low)
- [Bugs / Logic Errors (5)](#bugs--logic-errors)
- [Summary & Immediate Actions](#summary--immediate-actions)

---

## Critical

### 1. XSS via `v-html` on User-Controlled Content
**File:** `frontend/app/pages/index.vue:459`
**Issue:** Bug descriptions are rendered as raw HTML.
```vue
<div v-if="ttDetailBug.description" class="ticket-description" v-html="ttDetailBug.description"></div>
```
**Impact:** An attacker can inject `<script>` tags via a bug description field and execute arbitrary JavaScript in any viewer's browser.
**Fix:** Replace `v-html` with text interpolation (`{{ ttDetailBug.description }}`), or sanitize with [DOMPurify](https://github.com/cure53/DOMPurify) before rendering.

---

### 2. Auth Token Exposed in URL After OAuth Callback
**File:** `backend/app/Http/Controllers/AuthController.php:92`
**Issue:** The API token is returned in the URL query string after Google OAuth.
```php
return redirect($frontend . $returnPath . '?token=' . $token);
```
**Impact:** Tokens are recorded in browser history, server access logs, referrer headers, and any intermediate proxies. Token theft does not require XSS.
**Fix:** Use an httpOnly, Secure, SameSite=Strict cookie, or a POST redirect with a hidden form field instead of a URL parameter.

---

### 3. No Authorization on Project Update / Delete
**File:** `backend/app/Http/Controllers/ProjectController.php` — `update()`, `destroy()`
**Issue:** Neither method verifies the caller owns the project.
**Impact:** Any authenticated user can modify or delete any project.
**Fix:**
```php
if ($project->owner_id !== $request->user()?->id) {
    return response()->json(['error' => 'Forbidden'], 403);
}
```

---

### 4. No Authorization on Bug Update / Delete
**File:** `backend/app/Http/Controllers/BugController.php` — `update()` (~line 135), `destroy()` (~line 214)
**Issue:** No ownership check before modifying or deleting a bug.
**Impact:** Any authenticated user can tamper with bugs belonging to other users' projects.
**Fix:** Verify `$bug->project->owner_id === $request->user()?->id` before any write.

---

### 5. Unauthenticated Activity Log Wipe
**File:** `backend/routes/api.php:38` → `BugController::clearActivityLog()`
**Issue:** `DELETE /activity-log` sits in the `api.auth:optional` group and clears **all** bugs' activity logs with no authentication or ownership check.
```php
Bug::query()->update(['activity_log' => null]);
```
**Impact:** Any anonymous request wipes the entire audit trail for every project.
**Fix:** Move the route to the `api.auth` group and scope the delete to the authenticated user's projects.
```php
Bug::whereHas('project', fn($q) => $q->where('owner_id', auth()->id()))->update(['activity_log' => null]);
```

---

### 6. IDOR on Maintenance Project Shares (Update / Delete)
**File:** `backend/app/Http/Controllers/MaintenanceProjectShareController.php` — `update()` (~line 104), `destroy()` (~line 119)
**Issue:** Only checks that the share belongs to the correct project, but never verifies the requester owns that project.
**Impact:** Any authenticated user can modify or revoke share permissions on projects they don't own.
**Fix:** Add at the start of both methods:
```php
if ($maintenanceProject->owner_id !== $request->user()?->id) {
    return response()->json(['error' => 'Forbidden'], 403);
}
```

---

### 7. Share Index Leaks Invitations to Non-Owners
**File:** `backend/app/Http/Controllers/MaintenanceProjectShareController.php:41` — `index()`
**Issue:** Returns the full share list (including invited emails) without verifying the requester is the project owner.
**Impact:** Any user who knows a project ID can enumerate all invited email addresses.
**Fix:** Add ownership check before returning shares.

---

## High

### 8. Legacy Unowned Projects Grant Owner Access to Everyone
**Files:** `backend/app/Http/Controllers/ProjectController.php:40`, `MaintenanceProjectController.php:46`
**Issue:** Projects with `owner_id = NULL` assign `my_permission = 'owner'` to any requester.
```php
} elseif (!$p->owner_id) {
    $p->my_permission = 'owner'; // Any user becomes owner
}
```
**Impact:** Orphaned/legacy projects are fully editable by all users.
**Fix:** Add a migration to backfill `owner_id` for legacy projects, then remove the NULL fallback.

---

### 9. Auth Token Stored in `localStorage` (XSS-Accessible)
**Files:**
- `frontend/app/pages/index.vue:3295`
- `frontend/app/pages/maintenance.vue` (onMounted token handling)
- `frontend/app/pages/shared.vue:299`

**Issue:** Auth tokens are persisted in `localStorage`, which is accessible to any JavaScript running on the page.
**Impact:** If any XSS is exploited (see Finding #1), the attacker can steal the token and maintain persistent access.
**Fix:** Store tokens in httpOnly cookies (requires backend change to set `Set-Cookie` header), or use in-memory storage and accept shorter session lifetime.

---

### 10. Dev Folder Email Verification Can Be Spoofed
**File:** `backend/app/Http/Controllers/AuthController.php:44-46`
**Issue:** After Google OAuth for dev folder verification, the verified email is passed directly in the URL as a query parameter.
```php
return redirect($frontend . '/dev-folder/' . $folderToken . '?verified_email=' . urlencode($email));
```
The frontend trusts this parameter without server-side confirmation that it belongs to the folder's authorized developer.
**Impact:** If an attacker can construct or intercept the redirect URL, they can access any dev folder by substituting the email.
**Fix:** Store the verified email server-side (keyed by the folder token) and have the frontend fetch it via a secure API call instead of reading a URL parameter.

---

### 11. Any User Can Share Projects They Don't Own
**File:** `backend/app/Http/Controllers/ProjectShareController.php` — `store()` (~line 51)
**Issue:** No ownership check before creating a share invitation.
**Impact:** An authenticated user can share any project (including others' projects) with arbitrary email addresses.
**Fix:** Verify `$project->owner_id === $request->user()?->id` before creating any share record.

---

### 12. Race Condition: First User Claiming Unowned Projects
**File:** `backend/app/Http/Controllers/ProjectShareController.php` — `assertOwner()` (~line 28)
**Issue:**
```php
if (!$project->owner_id) {
    $project->update(['owner_id' => $user->id]); // First caller wins ownership
    return true;
}
```
**Impact:** In a multi-user environment, the first authenticated user to trigger this code becomes permanent owner of any legacy project.
**Fix:** Remove the auto-assign fallback; require explicit ownership assignment via an admin action.

---

### 13. No Ownership Check on `updateLinkPermission`
**File:** `backend/app/Http/Controllers/ProjectController.php` — `updateLinkPermission()` (~line 105)
**Issue:** Any authenticated user can change the link-sharing permission of any project.
**Impact:** Users can make any project publicly editable or lock others out of their own projects.
**Fix:** Add ownership check matching the pattern from `update()`/`destroy()`.

---

### 14. Missing Ownership Check on Maintenance Share `store()`
**File:** `backend/app/Http/Controllers/MaintenanceProjectShareController.php` — `store()` (~line 48)
**Issue:** No verification that the requester owns the maintenance project before creating shares.
**Impact:** Any user can invite others to maintenance projects they don't own.
**Fix:** Same pattern as Finding #6 — add owner_id check at method start.

---

## Medium

### 15. Maintenance Ticket Update / Delete Missing Permission Check
**File:** `backend/app/Http/Controllers/MaintenanceTicketController.php` — `update()` (~line 92), `destroy()` (~line 181)
**Issue:** Only validates `project_id` membership; never verifies project ownership.
**Impact:** Any authenticated user can update or delete any maintenance ticket.
**Fix:** Add `if ($maintenanceProject->owner_id !== $request->user()?->id) { return 403; }` at the start of both methods.

---

### 16. Maintenance Ticket `store()` Has No Authorization
**File:** `backend/app/Http/Controllers/MaintenanceTicketController.php` — `store()`
**Issue:** No check that the caller owns the target project before creating tickets.
**Impact:** Any user can create tickets in any maintenance project.
**Fix:** Add ownership check before `MaintenanceTicket::create()`.

---

### 17. `assigned_devs` / `activity_log` in `$fillable` (Mass Assignment Risk)
**File:** `backend/app/Models/Bug.php` (and `MaintenanceTicket.php`)
**Issue:** Sensitive audit fields are in `$fillable`, making them directly settable via `$model->fill()` or `updateOrCreate()` if validation is ever weakened.
**Fix:** Remove `activity_log` from `$fillable`; manage it exclusively through dedicated controller methods.

---

### 18. `devFolder()` Has No Access Control
**File:** `backend/app/Http/Controllers/MaintenanceTicketController.php` — `devFolder()` (~line 268)
**Issue:** Returns all tickets assigned to any email address without authentication.
**Impact:** Anyone who knows a developer's email can retrieve their full ticket list.
**Fix:** Either require authentication or verify the email against a signed token.

---

### 19. Dev Status Publicly Editable by Anyone
**File:** `frontend/app/pages/maintenance-ticket/[id].vue:403-404`
**Issue:** `canUpdateDevStatus` is hardcoded to `true`, allowing any visitor to change dev status.
```js
const canUpdateDevStatus = ref(true)
```
**Impact:** Anonymous users can manipulate developer progress indicators, producing false reports.
**Fix:** Gate dev status updates behind verified assignment (email match or authentication).

---

### 20. No Rate Limiting on Public Endpoints
**Files:** Multiple public routes (`/maintenance-tickets/{id}`, `/maintenance-dev-folder`, `/bugs/{id}`)
**Issue:** No throttle middleware is applied to public-facing endpoints.
**Impact:** Vulnerable to enumeration and denial-of-service attacks.
**Fix:**
```php
Route::middleware('throttle:60,1')->group(function () {
    // public ticket routes
});
```

---

### 21. OAuth CSRF: State Parameter Not Cryptographically Verified
**File:** `backend/app/Http/Controllers/AuthController.php:39`
**Issue:** The OAuth state (`dev_folder:<token>`) is not paired with a session-bound CSRF token.
**Impact:** A CSRF attack could force a user's browser to complete an OAuth flow controlled by an attacker.
**Fix:** Generate a random CSRF token, store it in the session, embed it in the state parameter, and verify it on callback.

---

### 22. Ticket Unauthorized Check Only on Frontend
**File:** `frontend/app/pages/maintenance-ticket/[id].vue` — `checkAuthorization()`
**Issue:** The "Access Restricted" gate is purely a UI check; the backend still serves full ticket data to anyone who calls the API directly.
**Impact:** Anyone can retrieve full ticket details (comments, assigned devs, notes) via a direct API call, bypassing the frontend modal.
**Fix:** Add authorization logic to `MaintenanceTicketController::show()` that checks the `?email` parameter against assigned devs, or requires a valid auth token.

---

### 23. N+1 Query Risk in `appendMyPermission`
**File:** `backend/app/Http/Controllers/MaintenanceProjectController.php:23-55`
**Issue:** Share lookup is batched (good), but `collect($projects)->map(...)` iterates in PHP memory over potentially large collections. Any lazy-loaded relation inside the map would cause N+1.
**Fix:** Ensure all needed relations are eager-loaded before passing to `appendMyPermission`.

---

## Low

### 24. Missing Rate Limiting on Auth Endpoints
**File:** `backend/routes/api.php:15-17`
**Issue:** `/api/auth/google` and `/api/auth/google/callback` are not rate-limited.
**Impact:** OAuth initiation can be spammed; callback endpoint open to brute-force.
**Fix:** Add `throttle:10,1` middleware to auth routes.

---

### 25. Error Logging Exposes Internal Details
**Files:** `AuthController.php`, `BugController.php`, multiple controllers
**Issue:** `\Log::error('...' . $e->getMessage())` may expose stack traces, file paths, and database details.
**Fix:** Log a safe message to app log; never expose raw exception messages to the frontend.

---

### 26. Missing Null Check in Mail — Assigned Developer
**File:** `backend/app/Mail/BugTicketMail.php` (~line 34)
**Issue:** Code accesses `assignedDeveloper->name` without null-safe operator.
**Fix:** Use `$this->bug->assignedDeveloper?->name ?? 'Developer'`.

---

### 27. Dev Folder Token in `sessionStorage`
**File:** `frontend/app/pages/dev-folder/[token].vue` (~line 230)
**Issue:** `sessionStorage.setItem('devFolderToken', token)` — sessionStorage is still accessible to JavaScript on the same page.
**Fix:** Avoid persisting the token; derive it from the URL only when needed.

---

### 28. Test Factory Uses Hardcoded Password
**File:** `backend/database/factories/UserFactory.php:31`
**Issue:** `'password' => bcrypt('password')` — if seeded into production, all accounts share a known password.
**Fix:** Ensure seeders/factories are never run in production; add a guard check.

---

### 29. Notification Endpoints Have No Per-User Scoping
**File:** `backend/app/Http/Controllers/NotificationController.php`
**Issue:** Notifications appear to be stored and returned globally, not per-user.
**Impact:** Users may see notifications intended for other users (potential data leakage).
**Fix:** Add a `user_id` column to the notifications table and scope all queries to the authenticated user.

---

### 30. Missing Input Sanitization on `notes` / `request` Fields
**Files:** `MaintenanceTicketController.php` — `store()`, `update()`
**Issue:** Free-text fields (`notes`, `request`, `client`) are stored verbatim without sanitization.
**Impact:** While the email template uses `htmlspecialchars`, any future use without escaping risks XSS.
**Fix:** Sanitize on input or enforce escaping at all render points.

---

### 31. `link_permission` Allows Null (Unintended Public Access)
**File:** `backend/app/Models/MaintenanceProject.php` — `link_permission` column
**Issue:** If `link_permission` is `null` and the unauthenticated fallback sets `my_permission = null` (current code), behaviour is correct. But if the default ever changes to a permissive value, projects become public unexpectedly.
**Fix:** Add a NOT NULL database constraint with a default of `null` or a sentinel value like `'none'`.

---

### 32. No Logging / Audit Trail for Share Changes
**Files:** `ProjectShareController.php`, `MaintenanceProjectShareController.php`
**Issue:** Share invitations, permission upgrades, and removals are not logged.
**Impact:** No forensic trail if an unauthorized share change occurs.
**Fix:** Append to `activity_log` or a dedicated `audit_log` table on every share mutation.

---

## Bugs / Logic Errors

### 33. `clearActivityLog` Wipes All Projects (Not Just Caller's)
**File:** `backend/app/Http/Controllers/BugController.php` — `clearActivityLog()`
**Issue:** `Bug::query()->update(['activity_log' => null])` has no user or project scope.
**Impact:** Clears audit logs for every bug in the entire database.
**Fix:** Scope to the authenticated user's projects (see also Finding #5).

---

### 34. `myPermission` Fallback Changed from `'owner'` to `'view'` May Break Owner Flow
**File:** `frontend/app/pages/maintenance-ticket/[id].vue:480`
**Issue:** When the project fetch fails (network error), `myPermission` defaults to `'view'` instead of the previous `'owner'`, which silently removes edit rights for authenticated owners with temporary connectivity issues.
**Fix:** Cache the permission or show a retry prompt rather than silently downgrading access.

---

### 35. Back-Navigation Email Chain Breaks If `assigned_devs` Contains Non-Email Strings
**File:** `frontend/app/pages/maintenance-ticket/[id].vue` — `devFolderEmail` computed
**Issue:** `devFolderEmail` falls back to `assigned_devs[0]`, but this field stores display names in some older tickets (e.g., "Samuel"), not email addresses.
**Impact:** Back button navigates to `/maintenance-dev-folder?email=Samuel`, which returns an empty folder.
**Fix:** Validate that the fallback value is a valid email (`/.+@.+\..+/`) before using it.

---

### 36. Unauthorized Modal Appears But Page Content Is Still Loaded
**File:** `frontend/app/pages/maintenance-ticket/[id].vue`
**Issue:** The ticket data (comments, notes, assigned devs) is fully fetched and rendered in the DOM even when `unauthorized = true`. Only the visual overlay blocks interaction.
**Impact:** Users with browser dev tools can read full ticket content despite the "Access Restricted" modal.
**Fix:** Conditionally render the content only when `!unauthorized`: `<div v-else-if="ticket && !unauthorized" class="mt-layout">`.

---

### 37. Race Condition in Concurrent Status / Dev-Status Updates
**File:** `backend/app/Http/Controllers/MaintenanceTicketController.php` (status update methods)
**Issue:** Read-modify-write on JSON `activity_log` arrays is not wrapped in a database transaction.
**Impact:** Two simultaneous updates can overwrite each other's log entry.
**Fix:** Wrap in `DB::transaction()` and use `lockForUpdate()` when reading.

---

## Summary & Immediate Actions

| Severity | Count |
|----------|-------|
| Critical | 7 |
| High | 7 |
| Medium | 9 |
| Low | 9 |
| Bugs / Logic Errors | 5 |
| **Total** | **37** |

### Priority Order (Fix Before Production)

1. **[Critical #5]** Restrict `clearActivityLog` to authenticated, scoped requests
2. **[Critical #3, #4, #6]** Add ownership/authorization checks to all resource mutations
3. **[Critical #1]** Remove `v-html` from bug descriptions or sanitize with DOMPurify
4. **[Critical #2]** Move OAuth token from URL to httpOnly cookie
5. **[High #9]** Evaluate moving tokens out of localStorage
6. **[Medium #22]** Move authorization check for ticket access to the backend (API-level), not just the frontend modal
7. **[Bug #36]** Hide ticket content in the DOM when `unauthorized = true`
8. **[High #10]** Verify dev folder email server-side instead of trusting the URL parameter
9. **[Medium #20]** Add rate limiting to all public endpoints
10. **[High #8]** Migrate legacy projects to have explicit `owner_id`; remove the NULL fallback
