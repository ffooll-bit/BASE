# Security Policy

## Supported Versions

Only the latest release is actively supported. Security fixes are released for the latest version and backported only when a backport release is explicitly announced.

| Version | Supported |
|---------|-----------|
| latest (`main`) | :white_check_mark: |
| older releases | :x: |

## Reporting a Vulnerability

Please do **not** open a public issue for security vulnerabilities.

Report vulnerabilities privately through **Security Advisories** on GitHub:

1. Open the repository page and go to the **Security** tab.
2. Click **Report a vulnerability**.
3. Fill in the advisory form. See the [GitHub documentation](https://docs.github.com/code-security/security-advisories/guidance-on-reporting-and-writing-information-about-vulnerabilities/creating-a-repository-security-advisory) for guidance.

When reporting, include:

- The version or commit the issue affects
- Steps to reproduce (PoC, if possible)
- The impact you observed
- A suggested fix, if you have one

## Disclosure Policy

- Reports are acknowledged within a few days.
- The report and the fix are kept private until a patched release is published.
- Once a fix ships, the advisory is published together with the release notes.

## Security considerations for BASE

- BASE is a Web Service Client for the Neo Feeder (PDDIKTI) API. Credentials (`neofeeder.*`, `encryption.key`) live only in `.env` and are never committed to the repository.
- Authentication is delegated to Neo Feeder via `GetToken`; the application stores the token in the session, never in plaintext logs.
- All user input is validated server-side and all HTML output is escaped with `esc()` before rendering.
- Any change that weakens input validation, output escaping, or session handling must go through the review process described in `CONTRIBUTING.md`.
