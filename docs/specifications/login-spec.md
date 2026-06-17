---
title: BASE - Login Feature
status: draft
date: 2026-06-15
version: 0.9
---

## Introduction

BASE (Bongaya Advanced Services Engine) is a Web Service Client application for Neo Feeder, responsible for synchronizing PDDIKTI data. This specification defines the Login feature, which provides user authentication and access control for the application.

The login system will authenticate users by calling the Neo Feeder Web Service API with the provided credentials. On successful authentication, a local CI4 session will be created containing user data returned by the API. No local `users` table is used for authentication -- user management is handled entirely by Neo Feeder / PDDIKTI.

### Problem Statement

Without a proper authentication system, the BASE application has no way to:
- Restrict access to authorized users only
- Track which user performed which action

### Scope

This specification covers:
- Login page with AdminLTE-styled user interface
- Email/password authentication via Neo Feeder Web Service API
- Session-based auth implementation using CodeIgniter 4 native sessions
- Token validation via Neo Feeder API for session verification
- Logout functionality
- Auth filter/middleware to protect routes
- HTTP Client integration for Neo Feeder API communication
- Minimal protected dashboard stub page as post-login landing target

## Glossary

| Term | Definition |
|------|------------|
| BASE | Bongaya Advanced Services Engine -- the application |
| Neo Feeder | External web service from PDDIKTI that BASE communicates with for authentication and data synchronization |
| PDDIKTI | Pangkalan Data Pendidikan Tinggi -- the national higher education database managed by the Indonesian Ministry of Education |
| AdminLTE | Bootstrap-based admin dashboard template (version 3.2) |
| Session-Based Auth | Authentication state stored on the server, tracked via a cookie containing a session ID |
| WS API | Neo Feeder Web Service API -- the REST/JSON endpoint used for authentication and data operations |
| Protected Route | A route/page that requires an authenticated session before it can be accessed |
| Auth Filter | CodeIgniter 4 Filter that intercepts requests and checks authentication status |
| GetToken | The Neo Feeder API action used for authentication -- accepts username and password, returns a token on success |
| GetProfilPT | The Neo Feeder API action used for token validation -- accepts a token, returns error_code=0 if valid, error_code=100 if invalid or expired |
| Token | A string returned by Neo Feeder API upon successful authentication, used as proof of authentication for subsequent API calls |

## Functional Requirements

### FR-01: Login Page Display
**Priority**: High
**Description**: The system shall display a login page styled with AdminLTE 3.2 template, containing an email input field, a password input field, a "Login" submit button, a CSRF hidden token field, and the BASE application branding/logo. The login form shall submit via POST to `/login`. The page must be responsive and render correctly on desktop and mobile browsers.
**Traces to**: AC-01

### FR-02: User Authentication via Neo Feeder API
**Priority**: High
**Description**: The system shall authenticate users by sending a POST request to the Neo Feeder Web Service API at `https://neofeeder.stiem-bongaya.ac.id/ws/live2.php`. All requests to this endpoint shall use `Content-Type: application/json` and the POST method.

**Login (GetToken):**
Request body format:
```json
{
    "act": "GetToken",
    "username": "<user_email>",
    "password": "<user_password>"
}
```

Response handling:
- `error_code: 0` with valid token -- Successful authentication. The system shall immediately create a CI4 session storing the token, username, and a `lastValidatedAt` Unix timestamp (set to the current time) under the `auth` session namespace, set the persistent prior-auth indicator, regenerate the session ID, and redirect to `/dashboard`.
- `error_code` != 0 -- Invalid credentials. Display message "Login failed. Please check your credentials." Do not create a session. Stay on login page.
- Connection failure (timeout, network error, HTTP error) -- Display message "Unable to connect to the authentication server. Please try again later." Do not create a session. Stay on login page.
- Malformed or non-JSON response -- Treat as failed login. Display message "Login failed. Please check your credentials." Do not create a session. Stay on login page.

**Token Validation (GetProfilPT):**
The system shall validate the session token on protected route access by sending a POST request to the same endpoint with the following body:

```json
{
    "act": "GetProfilPT",
    "token": "<session_token>"
}
```

Response handling:
- `error_code: 0` -- Valid token. The system shall update `auth.lastValidatedAt` to the current Unix timestamp (sliding TTL) and allow access.
- `error_code: 100` -- Invalid or expired token with message "Invalid Token. Token tidak ada atau token sudah expired." The system shall clear the `auth` session namespace and redirect to `/login` with a "session expired" notification.
- Any other `error_code` (e.g., 1, 99) -- The system shall deny access, display a generic error message ("Unable to verify session. Please try again later."), and keep the session intact. The user is not logged out.
- Connection failure (timeout, network error, HTTP error) -- If a cached validation result exists within TTL, use it. If no valid cached result exists, deny access, display an error message ("Unable to verify session. Please try again later."), and keep the session intact. Only `error_code: 100` may clear the authentication session.
- Malformed or non-JSON response -- Treat as an invalid token. The system shall clear the `auth` session namespace and redirect to `/login` with a "session expired" notification. A broken API response cannot be trusted.

**Validation Caching:**
Token validation results shall be cached using the `auth.lastValidatedAt` Unix timestamp stored in the session. Default TTL is 5 minutes (300 seconds), configurable via `.env` setting. The TTL slides: each successful validation resets `lastValidatedAt` to the current time. The Auth Filter shall re-validate via GetProfilPT only when `lastValidatedAt + TTL < current time`. If `lastValidatedAt` is null, the token shall be re-validated immediately via GetProfilPT.

**Session Schema (auth namespace):**
The `auth` session namespace shall contain the following keys:

| Key | Type | Description | Set At |
|-----|------|-------------|--------|
| `username` | string | The authenticated user's email/username | Login (GetToken success) |
| `token` | string | Neo Feeder API token returned by GetToken | Login (GetToken success) |
| `lastValidatedAt` | int (Unix timestamp) | Timestamp of last successful token validation | Login (GetToken success); updated on each successful GetProfilPT validation |

**Input Validation:**
The system shall validate that both username and password are non-empty before submitting to the API. No additional format, length, or complexity validation shall be applied; credential validation is the responsibility of the Neo Feeder API. Empty input shall display the message: "Please enter your username and password."

**Traces to**: AC-02, AC-03, AC-10

### FR-03: Protected Routes
**Priority**: High
**Description**: The system shall implement a CodeIgniter 4 Filter (middleware) that intercepts incoming requests and verifies the user's session. The filter shall apply globally (deny-by-default): all routes are protected except those explicitly whitelisted. Requests to protected routes without a valid authenticated session shall be redirected to the login page with a "session expired" notification (delivered via CI4 flashdata) if applicable. Routes that are publicly accessible (`/login`) must be explicitly whitelisted in the filter configuration. Static assets (CSS, JS, images) are served directly by the web server from the `public/` directory and do not require CI4 route whitelisting. An authenticated user navigating to `/login` shall be redirected to `/dashboard`.

A minimal protected dashboard stub page shall be included as part of this feature to provide a post-login landing target and a route for testing protected access. The stub shall render a simple welcome view (displaying the authenticated username) within the AdminLTE layout and shall not implement any dashboard-specific functionality beyond confirming authentication status.
**Traces to**: AC-04, AC-05, AC-11

### FR-05: Logout Functionality
**Priority**: High
**Description**: The system shall provide a logout action that destroys the current authenticated session and redirects the user to the login page. The logout action shall use the POST HTTP method (for CSRF protection) and shall be accessible as a logout button in the AdminLTE navigation bar when the user is authenticated.
**Traces to**: AC-06

### FR-06: Neo Feeder API Connection Configuration
**Priority**: High
**Description**: The system shall provide a configuration interface (via `.env` file) for setting the Neo Feeder Web Service API base URL. Default value: `https://neofeeder.stiem-bongaya.ac.id/ws/live2.php`. Additional connection parameters must also be configurable: connection timeout (default 10 seconds) and overall request timeout (default 30 seconds). The system must handle connection failures gracefully and report meaningful errors to the user.
**Traces to**: AC-09

## Non-Functional Requirements

### NFR-01: Credential Security
**Priority**: High
**Description**: User credentials (username and password) must never be stored, logged, or cached locally. Passwords shall only be transmitted to the Neo Feeder API endpoint via HTTPS for validation. The Neo Feeder API connection shall use the official domain `neofeeder.stiem-bongaya.ac.id` with HTTPS encryption.
**Traces to**: AC-08

### NFR-02: Session Security
**Priority**: High
**Description**: The authentication session must use CodeIgniter 4's native session handling with the following security measures: session encryption enabled via a secret key set in `encryption.key` (`.env`), HTTP-only cookies, session regeneration upon login, CSRF protection enabled on the login route, and a reasonable session timeout (configurable via CI4's native session expiration, default 120 minutes of inactivity). On session timeout, the next request to a protected route shall redirect to the login page with a "session expired" notification message delivered via CI4 flashdata. The session shall not store a separate `logged_in` flag; authentication status is determined by the presence of a valid token in the `auth` session namespace.

To detect session timeout after the CI4 session has expired (e.g., due to inactivity exceeding the session timeout), the system shall maintain a **persistent prior-auth indicator** -- a signed/encrypted cookie set on successful login and cleared on logout. The indicator shall use CI4's Encryption service (with the same `encryption.key` used for session encryption) for signing, and its lifetime shall match the 120-minute session timeout. When the Auth Filter encounters a request without a valid CI4 session but with this persistent indicator present, it shall show a "session expired" notification (via CI4 flashdata) and clear the indicator. When both session and indicator are absent, the user is simply redirected to login without notification.
**Traces to**: AC-02, AC-05, AC-11

### NFR-03: Code Extensibility & Reusability
**Priority**: High
**Description**: The authentication logic must be encapsulated in a reusable service class (`app/Libraries/Auth.php` or similar) that is decoupled from controllers. This service shall expose methods for: login, logout, check authentication status (`isLoggedIn()` returns bool), get current user (`getCurrentUser()` returns the username string or null if not authenticated), and token validation. Controllers and filters shall depend on this service, not on session manipulation directly.

API communication with Neo Feeder (HTTP requests, response parsing) shall be encapsulated in a separate `NeoFeeder` service (`app/Libraries/NeoFeeder.php` or similar). The Auth service shall depend on the NeoFeeder service for API calls, not on the HTTP client directly. This keeps auth logic decoupled from transport concerns.
**Traces to**: AC-09

### NFR-04: AdminLTE Template Compliance
**Priority**: Medium
**Description**: The login page must use AdminLTE 3.2 login page styling and components. All UI elements (buttons, inputs, cards, alerts) must follow AdminLTE/AdminLTE design patterns. The authenticated pages must render within the AdminLTE wrapper (navbar, sidebar, content area).
**Traces to**: AC-01

### NFR-05: Framework Compatibility
**Priority**: High
**Description**: The implementation must be compatible with CodeIgniter 4.x, PHP ^8.1, and MySQL. No external authentication packages or libraries shall be introduced beyond what CI4 provides natively. For API communication, CI4's built-in HTTP Client (CURLRequest) shall be used.
**Traces to**: AC-04

### NFR-06: API Communication Reliability
**Priority**: High
**Description**: The system shall handle Neo Feeder API communication failures gracefully, including: connection timeout, HTTP error responses, malformed response data, and network errors. Appropriate error messages must be displayed to the user without exposing technical details. Configurable timeouts shall be applied to all API calls: connection timeout (default 10 seconds) and overall request timeout (default 30 seconds).
**Traces to**: AC-08

## Acceptance Criteria

### AC-01: Login Page Renders Correctly (traces to FR-01, NFR-04)
**Given** a user who is not authenticated
**When** they navigate to `/login`
**Then** the system shall render a full AdminLTE-styled login page containing: BASE branding/logo, email input field, password input field, a CSRF hidden token field, and a "Login" submit button. The form shall submit via POST to `/login`.

### AC-02: Successful Login Creates Session via Neo Feeder API (traces to FR-02, NFR-02)
**Given** a registered user with valid Neo Feeder credentials (username and password)
**When** they submit the login form with correct credentials
**Then** the system shall:
- Send a POST request to `https://neofeeder.stiem-bongaya.ac.id/ws/live2.php` with `Content-Type: application/json` and body `{"act":"GetToken","username":"...","password":"..."}`
- Receive a successful response: `{"error_code":0,"error_desc":"","data":{"token":"..."}}`
- Store the token, username, and current Unix timestamp as `lastValidatedAt` in the `auth` session namespace
- Set a persistent prior-auth indicator (signed/encrypted cookie) to track that the user was authenticated
- Redirect the user to `/dashboard`
- Regenerate the session ID to prevent session fixation

### AC-03: Failed Login Shows Appropriate Error (traces to FR-02, FR-06, NFR-06)
**Given** a user on the login page
**When** they submit the login form with empty username or password
**Then** the system shall:
- Not call the Neo Feeder API
- Display an error message: "Please enter your username and password."
- Not create any session
- Stay on the login page

**Given** a user on the login page
**When** they submit the login form with invalid credentials (wrong username or password)
**Then** the system shall:
- Call the Neo Feeder API with the provided credentials
- Receive a failure response (`error_code` != 0 or no token in response)
- Display an error message: "Login failed. Please check your credentials."
- Not create any session
- Stay on the login page

**Given** a user on the login page
**When** the Neo Feeder API is unreachable or returns a connection error
**Then** the system shall:
- Display an error message: "Unable to connect to the authentication server. Please try again later."
- Not create any session
- Stay on the login page

### AC-04: Authenticated User Can Access Protected Routes (traces to FR-03, NFR-05)
**Given** an authenticated user with a valid session
**When** they navigate to any protected route (e.g., `/dashboard`)
**Then** the system shall allow access and render the requested page

### AC-05: Unauthenticated User Is Redirected to Login (traces to FR-03, NFR-02)
**Given** a user who is not authenticated (no valid CI4 session)
**When** they attempt to access any protected route
**Then** the system shall:
- Intercept the request via the Auth Filter
- If the persistent prior-auth indicator (signed/encrypted cookie) is present: display a "session expired" notification message, clear the indicator, and redirect to login
- If both the CI4 session and the persistent indicator are absent: redirect to login without any notification message

**Given** a user whose session token is invalid or expired (error_code 100 from GetProfilPT)
**When** the Auth Filter checks authentication status
**Then** the system shall:
- Clear the `auth` session namespace
- Redirect to the login page with a "session expired" notification
- Require re-authentication for any subsequent protected route access

### AC-06: Logout Destroys Session (traces to FR-05)
**Given** an authenticated user
**When** they click the "Logout" button
**Then** the system shall:
- Destroy the current session completely
- Clear the persistent prior-auth indicator
- Redirect to the login page
- Require re-authentication for any subsequent protected route access

### AC-07: Neo Feeder API Connection is Configurable (traces to FR-06)
**Given** a developer or system administrator
**When** they configure the Neo Feeder API endpoint URL (default: `https://neofeeder.stiem-bongaya.ac.id/ws/live2.php`) and connection parameters in `.env`
**Then** the system shall use those parameters for all API communication without requiring code changes

### AC-08: Credentials Are Not Stored or Logged (traces to NFR-01, NFR-06)
**Given** a user submitting their email and password via the login form
**When** the login request is processed
**Then** the system shall:
- Transmit credentials only to the Neo Feeder API endpoint via HTTPS using the official domain `neofeeder.stiem-bongaya.ac.id`
- Not store credentials in any local database, file, or log
- Not cache the password in session data
- Handle API connection errors gracefully with user-friendly messages

### AC-09: Auth and Neo Feeder Services Are Injectable (traces to NFR-03)
**Given** a controller or filter requiring authentication or API communication
**When** it uses the Auth service or Neo Feeder API service
**Then** it shall obtain the service via dependency injection or CI4 service configuration (e.g., `service('auth')`, `service('neoFeeder')`), not by instantiating a concrete class directly. Service names shall follow CI4 camelCase conventions.

### AC-10: Token Validation on Protected Route Access (traces to FR-02, NFR-02)
**Given** an authenticated user with a valid token stored in the session
**When** the Auth Filter checks authentication status on a protected route request and no valid cached result exists (lastValidatedAt + TTL < current time)
**Then** the system shall:
- Send a POST request with `Content-Type: application/json` and body `{"act":"GetProfilPT","token":"<session_token>"}` to `https://neofeeder.stiem-bongaya.ac.id/ws/live2.php`
- On `error_code: 0` response: update `auth.lastValidatedAt` to current Unix timestamp, allow access
- On `error_code: 100` response: clear the `auth` session namespace, redirect to `/login` with "session expired" notification
- On any other `error_code` response: deny access, display "Unable to verify session. Please try again later.", keep session intact
- On malformed or non-JSON response: treat as invalid token, clear the `auth` session namespace, redirect to `/login` with "session expired" notification

**Given** an authenticated user and a cached token validation result
**When** the Auth Filter checks authentication status and `auth.lastValidatedAt + TTL >= current time` (TTL default is 300 seconds)
**Then** the system shall use the cached result and allow access without calling the Neo Feeder API

**Given** an authenticated user
**When** the Auth Filter attempts token validation and the Neo Feeder API is unreachable (connection/timeout/network error)
**Then** the system shall:
- If a cached validation result exists within TTL (`lastValidatedAt + TTL >= current time`): use the cached result and allow access
- If no valid cached result exists (`lastValidatedAt` is null or `lastValidatedAt + TTL < current time`): deny access, display "Unable to verify session. Please try again later.", keep the session intact
- Under no circumstances shall a connection failure clear the `auth` session namespace -- only an explicit `error_code: 100` response may do so

### AC-11: Authenticated User at Login Redirects to Dashboard (traces to FR-03, NFR-02)
**Given** an authenticated user with a valid session
**When** they navigate to `/login`
**Then** the system shall:
- Detect the user is already authenticated
- Redirect to `/dashboard` without rendering the login page

## Out of Scope

- Public user self-registration (future feature)
- Password reset / "Forgot Password" flow (future feature)
- Email verification for new accounts (future feature)
- "Remember Me" persistent login
- Multi-factor authentication (MFA/2FA)
- Single Sign-On (SSO) integration
- Rate limiting on login attempts (future enhancement)
- "Logout from all devices" feature
- OAuth or social login integration
- API token-based authentication (JWT/API keys) for REST endpoints
- User group management or complex permission hierarchies
- Audit logging of login attempts (future enhancement)
- User profile editing (name, email, password change)
- Local user management (all users are managed in Neo Feeder / PDDIKTI)
- Account locking or suspension

## Changelog

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 0.1 | 2026-06-12 | - | Initial draft |
| 0.2 | 2026-06-12 | - | Revised authentication architecture: local DB changed to Neo Feeder API-based auth. Updated FR-02, FR-04, FR-06, NFR-01, NFR-05, AC-02, AC-03, AC-06, AC-09, AC-10, AC-11, Out of Scope, and Glossary accordingly. |
| 0.3 | 2026-06-12 | - | Added specific Neo Feeder API endpoint details (URL, request/response format for GetToken). Updated FR-02, FR-06, AC-02, AC-03, AC-09, and Glossary. |
| 0.4 | 2026-06-12 | - | Fixed NFR-01: changed HTTPS requirement to HTTP to match actual Neo Feeder endpoint. |
| 0.5 | 2026-06-12 | - | Removed active RBAC enforcement (FR-04 simplified, AC-06/AC-07 removed). Renumbered ACs (now 9 items). Set status back to draft. |
| 0.6 | 2026-06-15 | - | Spec review resolutions applied: migrated Neo Feeder endpoint to `https://neofeeder.stiem-bongaya.ac.id/`; removed FR-04 (role storage) entirely; enabled CSRF protection on login; added input validation (non-empty only); defined session schema (`auth` namespace with `username` and `token` only, no `logged_in` flag); added token validation via GetProfilPT with caching; clarified static assets do not require whitelisting; removed post-login URL redirect deferral; defined session timeout redirect behavior; corrected service naming to camelCase conventions. Renumbered ACs to 10 items. |
| 0.7 | 2026-06-15 | - | Second review resolutions applied: added full endpoint path `/ws/live2.php`; defined GetProfilPT error handling (only error_code=100 clears session; other codes show error, keep session); set validation cache TTL default to 5 minutes with sliding TTL via `auth.lastValidatedAt`; added authenticated-user-at-login redirect to `/dashboard`; split timeout configuration into connection (10s) and request (30s); specified encryption key via `encryption.key` in `.env`; clarified `getCurrentUser()` returns username string; defined connection failure handling for token validation (deny access, keep session, show retry error); updated AC-10 scenarios. |
| 0.8 | 2026-06-15 | - | Third review resolutions applied: added persistent prior-auth indicator mechanism (signed/encrypted cookie) for session timeout detection (M1); clarified `lastValidatedAt` as Unix timestamp format across all occurrences (m1); added malformed/non-JSON response handling for GetProfilPT (m2); specified TTL explicitly in seconds: 300 seconds default (m3); added consolidated session schema table to FR-02 (m4); added null `lastValidatedAt` edge case to validation caching section (m5). Updated AC-02, AC-05, AC-06, AC-10, FR-02, NFR-02 accordingly. |
| 0.9 | 2026-06-15 | - | Planning review resolutions applied: restructured FR-02 GetToken response handling with per-case error messages matching AC-03 (M2); added malformed/non-JSON response guard for GetToken (M3); specified `.env` for TTL configuration (M5); defined global deny-by-default filter approach in FR-03 (C1); specified CI4 flashdata as notification transport mechanism (C2); detailed persistent prior-auth indicator implementation (CI4 Encryption service, 120-min lifetime) in NFR-02 (C3); specified CI4 native session config for timeout (M6); added minimal dashboard stub page to scope and FR-03 (C4); specified POST method for logout in FR-05 (m4); clarified Auth/NeoFeeder service boundaries in NFR-03 (M7); fixed AC-05 second scenario to include "session expired" notification (M1); added AC-11 for authenticated-user-at-login redirect (m3); updated AC-03 traces to include FR-06/NFR-06 (m8). |
