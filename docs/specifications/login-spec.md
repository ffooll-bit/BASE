---
title: BASE - Login Feature
status: draft
date: 2026-06-12
version: 0.5
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
- Role information storage from Neo Feeder response (reserved for future access control)
- Logout functionality
- Auth filter/middleware to protect routes
- HTTP Client integration for Neo Feeder API communication

## Glossary

| Term | Definition |
|------|------------|
| BASE | Bongaya Advanced Services Engine -- the application |
| Neo Feeder | External web service from PDDIKTI that BASE communicates with for authentication and data synchronization |
| PDDIKTI | Pangkalan Data Pendidikan Tinggi -- the national higher education database managed by the Indonesian Ministry of Education |
| AdminLTE | Bootstrap-based admin dashboard template (version 3.2) |
| Session-Based Auth | Authentication state stored on the server, tracked via a cookie containing a session ID |
| WS API | Neo Feeder Web Service API -- the SOAP/REST endpoint used for authentication and data operations |
| Protected Route | A route/page that requires an authenticated session before it can be accessed |
| Auth Filter | CodeIgniter 4 Filter that intercepts requests and checks authentication status |
| GetToken | The Neo Feeder API action used for authentication -- accepts username and password, returns a token on success |
| Token | A string returned by Neo Feeder API upon successful authentication, used as proof of authentication for subsequent API calls |

## Functional Requirements

### FR-01: Login Page Display
**Priority**: High
**Description**: The system shall display a login page styled with AdminLTE 3.2 template, containing an email input field, a password input field, a "Login" submit button, and the BASE application branding/logo. The page must be responsive and render correctly on desktop and mobile browsers.
**Traces to**: AC-01

### FR-02: User Authentication via Neo Feeder API
**Priority**: High
**Description**: The system shall authenticate users by sending a POST request to the Neo Feeder Web Service API with `act=GetToken`, `username`, and `password`. The API endpoint is `http://51.79.235.64:8100/ws/live2.php`. 

Request body format:
```json
{
    "act": "GetToken",
    "username": "<user_email>",
    "password": "<user_password>"
}
```

On success (error_code: 0), the API returns a token. The system shall treat a valid token response as successful authentication and create a CI4 session containing the token and basic user info (username). On failed response (error_code != 0) or connection error, the system shall return an appropriate error message without revealing specifics about the cause.

**Traces to**: AC-02, AC-03

### FR-03: Protected Routes
**Priority**: High
**Description**: The system shall implement a CodeIgniter 4 Filter (middleware) that intercepts incoming requests and verifies the user's session. Requests to protected routes without a valid authenticated session shall be redirected to the login page. Routes that are publicly accessible (login page, assets) must be explicitly whitelisted.
**Traces to**: AC-04, AC-05

### FR-04: Role Information Storage
**Priority**: Low
**Description**: The system shall store the user's role (if provided by the Neo Feeder API response) in the session for potential future use. No role-based access control shall be enforced at this stage. The architecture must remain extensible so that RBAC can be added later without significant refactoring.
**Traces to**: (reserved for future use)

### FR-05: Logout Functionality
**Priority**: High
**Description**: The system shall provide a logout action that destroys the current authenticated session and redirects the user to the login page. The logout button/link shall be accessible from the AdminLTE navigation bar when the user is authenticated.
**Traces to**: AC-06

### FR-06: Neo Feeder API Connection Configuration
**Priority**: High
**Description**: The system shall provide a configuration interface (via `.env` file) for setting the Neo Feeder Web Service API base URL. Default value: `http://51.79.235.64:8100/ws/live2.php`. Additional connection parameters (timeout) must also be configurable. The system must handle connection failures gracefully and report meaningful errors to the user.
**Traces to**: AC-09

## Non-Functional Requirements

### NFR-01: Credential Security
**Priority**: High
**Description**: User credentials (username and password) must never be stored, logged, or cached locally. Passwords shall only be transmitted to the Neo Feeder API endpoint for validation. Note that the current Neo Feeder endpoint uses HTTP (not HTTPS); this is an accepted limitation of the external service.
**Traces to**: AC-08

### NFR-02: Session Security
**Priority**: High
**Description**: The authentication session must use CodeIgniter 4's native session handling with the following security measures: session encryption enabled, HTTP-only cookies, session regeneration upon login, and a reasonable session timeout (configurable, default 120 minutes of inactivity).
**Traces to**: AC-02, AC-05

### NFR-03: Code Extensibility & Reusability
**Priority**: High
**Description**: The authentication logic must be encapsulated in a reusable service class (`app/Libraries/Auth.php` or similar) that is decoupled from controllers. This service shall expose methods for: login, logout, check authentication status, get current user. Controllers and filters shall depend on this service, not on session manipulation directly.
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
**Description**: The system shall handle Neo Feeder API communication failures gracefully, including: connection timeout, HTTP error responses, malformed response data, and network errors. Appropriate error messages must be displayed to the user without exposing technical details. A configurable timeout (default 30 seconds) shall be applied to all API calls.
**Traces to**: AC-08

## Acceptance Criteria

### AC-01: Login Page Renders Correctly (traces to FR-01, NFR-04)
**Given** a user who is not authenticated
**When** they navigate to the login URL (e.g., `/login`)
**Then** the system shall render a full AdminLTE-styled login page containing: BASE branding/logo, email input field, password input field, and a "Login" button

### AC-02: Successful Login Creates Session via Neo Feeder API (traces to FR-02, NFR-02)
**Given** a registered user with valid Neo Feeder credentials (username and password)
**When** they submit the login form with correct credentials
**Then** the system shall:
- Send a POST request to `http://51.79.235.64:8100/ws/live2.php` with body `{"act":"GetToken","username":"...","password":"..."}`
- Receive a successful response: `{"error_code":0,"error_desc":"","data":{"token":"..."}}`
- Store the token and username in CI4 session data
- Redirect the user to the application dashboard (or post-login landing page)
- Regenerate the session ID to prevent session fixation

### AC-03: Failed Login Shows Appropriate Error (traces to FR-02)
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
**When** they navigate to any protected route (e.g., `/dashboard`, `/sync`, `/settings`)
**Then** the system shall allow access and render the requested page

### AC-05: Unauthenticated User Is Redirected to Login (traces to FR-03, NFR-02)
**Given** a user who is not authenticated (no valid session)
**When** they attempt to access any protected route
**Then** the system shall:
- Intercept the request via the Auth Filter
- Redirect to the login page
- Optionally store the intended URL to redirect back after login

### AC-06: Logout Destroys Session (traces to FR-05)
**Given** an authenticated user
**When** they click the "Logout" button
**Then** the system shall:
- Destroy the current session completely
- Redirect to the login page
- Require re-authentication for any subsequent protected route access

### AC-07: Neo Feeder API Connection is Configurable (traces to FR-06)
**Given** a developer or system administrator
**When** they configure the Neo Feeder API endpoint URL (default: `http://51.79.235.64:8100/ws/live2.php`) and connection parameters in `.env`
**Then** the system shall use those parameters for all API communication without requiring code changes

### AC-08: Credentials Are Not Stored or Logged (traces to NFR-01, NFR-06)
**Given** a user submitting their email and password via the login form
**When** the login request is processed
**Then** the system shall:
- Transmit credentials only to the Neo Feeder API endpoint via HTTPS
- Not store credentials in any local database, file, or log
- Not cache the password in session data
- Handle API connection errors gracefully with user-friendly messages

### AC-09: Auth and Neo Feeder Services Are Injectable (traces to NFR-03)
**Given** a controller or filter requiring authentication or API communication
**When** it uses the Auth service or Neo Feeder API service
**Then** it shall obtain the service via dependency injection or CI4 service configuration (e.g., `service('auth')`, `service('neo-feeder')`), not by instantiating a concrete class directly

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
