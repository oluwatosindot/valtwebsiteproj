# Web Security Checklist
**Universal — applies to every web project regardless of language, framework, or stack**
**PHP · Node.js · Python · Ruby · Java · .NET · WordPress · Shopify · any CMS**

Use this before launch, after every major feature, and during any security review.
Check off each item. If you cannot check it, document why and accept the risk in writing.

---

## How to Use This Checklist

| Symbol | Meaning |
|--------|---------|
| ✅ | Done / confirmed safe |
| ❌ | Not done — must fix before go-live |
| ⚠️ | Accepted risk — documented and signed off |
| N/A | Not applicable to this project |

---

## 1. Authentication & Passwords

- [ ] Passwords are hashed with a strong algorithm (bcrypt, Argon2) — never MD5, SHA1, or plain text
- [ ] Password minimum length enforced (12+ chars recommended for financial/sensitive apps)
- [ ] "Forgot password" uses a cryptographically random token (not sequential IDs or guessable values)
- [ ] Password reset tokens expire (1 hour max) and are single-use (invalidated after first click)
- [ ] Default/test passwords are never used in production (no `admin/admin`, `password123`)
- [ ] Login error messages are generic — never reveal whether the email OR password was wrong separately
- [ ] Account lockout or progressive delay after repeated failed logins (5–10 attempts)
- [ ] Re-authentication required before sensitive actions (change password, delete account, transfer funds)
- [ ] Multi-factor authentication (MFA/2FA) available for privileged accounts (admin, finance)
- [ ] Passwords are never logged, printed in errors, or stored anywhere in plain text
- [ ] API keys and secrets are stored in environment variables — never hardcoded in source code
- [ ] Passwords must be secrets the user controls — never publicly known data (e.g. ID numbers, DOB, phone)

---

## 2. Session Management

- [ ] Session ID regenerated immediately after every login (prevents session fixation attacks)
- [ ] Sessions expire after a reasonable inactivity period (15–30 min for financial apps)
- [ ] Absolute session timeout enforced regardless of activity (4–8 hours max)
- [ ] Session cookies flagged: `HttpOnly`, `Secure` (HTTPS only), `SameSite=Lax` or `Strict`
- [ ] Sessions fully invalidated on logout — server-side destruction, not just cookie deletion
- [ ] No sensitive data stored in session beyond what is necessary (no passwords, no full card numbers)
- [ ] Session IDs never passed in URLs (appear in logs, browser history, and referrer headers)

---

## 3. SQL Injection

- [ ] Every database query uses parameterized statements / prepared statements — no string concatenation with user input
- [ ] ORM or query builder used consistently — never bypassed with raw strings containing variables
- [ ] `PDO::ATTR_EMULATE_PREPARES` set to `false` (PHP PDO) — forces native prepared statements at DB level
- [ ] Dynamic ORDER BY / LIMIT values are whitelisted from a fixed allowed list — never passed raw from user input
- [ ] Database user has minimum necessary privileges (no DROP, no full admin access from the application)
- [ ] Database error messages never shown to the user — logged server-side only
- [ ] Separate database credentials per environment (dev, staging, prod) — prod credentials never in dev code

---

## 4. Cross-Site Scripting (XSS)

- [ ] All user-supplied data HTML-escaped before rendering (`htmlspecialchars()`, template auto-escaping)
- [ ] JavaScript output uses `JSON.stringify()` / proper encoding — never raw `echo $var` inside `<script>` blocks
- [ ] `Content-Security-Policy` (CSP) header set — even a basic policy blocks many XSS attacks
- [ ] `'unsafe-inline'` avoided in CSP `script-src`; use nonces or hashes if inline scripts are needed
- [ ] `X-Content-Type-Options: nosniff` header set — prevents MIME-type sniffing attacks
- [ ] Rich text / HTML input from users sanitised with an allowlist library (DOMPurify, HTMLPurifier) — never a blocklist
- [ ] Uploaded files cannot be HTML, SVG with scripts, or JavaScript files served directly from the web root

---

## 5. Cross-Site Request Forgery (CSRF)

- [ ] Every state-changing form (POST, DELETE, PUT) includes a unique CSRF token
- [ ] Every AJAX endpoint that mutates data validates the CSRF token server-side
- [ ] CSRF token is tied to the user's session and validated server-side — not just present in the form
- [ ] `SameSite=Lax` or `SameSite=Strict` cookie attribute set (partial CSRF mitigation)
- [ ] JSON APIs verify the `Origin` or `Referer` header when relying on `Content-Type: application/json`

---

## 6. File Uploads

- [ ] Allowed file types are whitelisted (not blocklisted) — e.g. only PDF, JPG, PNG, DOCX
- [ ] MIME type validated server-side using file content inspection (PHP `finfo`, Python `python-magic`) — not just extension
- [ ] Uploaded files stored outside the web root OR in a directory blocked by `.htaccess` / nginx config
- [ ] Uploaded filenames sanitised or replaced with a UUID — original filename never used directly on disk
- [ ] Maximum file size enforced server-side (not just `MAX_FILE_SIZE` in HTML)
- [ ] Uploaded files never executed by the server (no PHP, no `.htaccess` files uploadable)
- [ ] Images re-processed (resized/re-encoded) to strip embedded payloads
- [ ] Virus/malware scanning in place for uploads on high-risk applications (HR, finance, healthcare)

---

## 7. Access Control — IDOR & Authorisation

- [ ] Every request accessing a record by ID verifies the current user owns or is permitted to access it
- [ ] Authorisation checks happen server-side — never rely on client-side role checks alone
- [ ] ID comparisons use strict equality with correct types (`===`, `!==`, explicit `(int)` cast) — never loose `==`
- [ ] Admin and privileged pages check role/permission on every request — not just at login
- [ ] API endpoints return only data belonging to the authenticated user — no cross-account leakage
- [ ] Horizontal privilege escalation tested: can User A access User B's records by changing an ID in the URL?
- [ ] Vertical privilege escalation tested: can a regular user reach admin-only endpoints?
- [ ] Sensitive operations (approve, disburse, delete, change role) require a specific elevated permission, not just "is logged in"
- [ ] Permission checks fail closed — unauthenticated users denied by default, never allowed when a flag is absent

---

## 8. Sensitive Data Exposure

- [ ] HTTPS enforced everywhere — HTTP redirects to HTTPS (`301`)
- [ ] `Strict-Transport-Security` (HSTS) header set with a long `max-age`
- [ ] Sensitive fields (passwords, tokens, card numbers) never logged anywhere
- [ ] PII (phone numbers, ID numbers, emails, addresses) not written to error logs or debug output
- [ ] API responses do not expose other users' IDs, stack traces, or internal status codes
- [ ] `.env` files web-inaccessible and listed in `.gitignore`
- [ ] SQL schema files, database backups, and seed data not in a publicly accessible directory
- [ ] `autocomplete="off"` on sensitive form fields (passwords, card numbers, ID numbers)
- [ ] Sensitive data at rest encrypted in the database where required (full card numbers, medical data)
- [ ] Migration/setup scripts removed from production or protected and then deleted after use

---

## 9. Security Headers (HTTP)

Set these on every response. One-liners in `.htaccess`, `nginx.conf`, or middleware.

- [ ] `Content-Security-Policy` — controls what scripts, styles, and frames can load
- [ ] `X-Frame-Options: SAMEORIGIN` — prevents clickjacking
- [ ] `X-Content-Type-Options: nosniff` — prevents MIME sniffing
- [ ] `Referrer-Policy: strict-origin-when-cross-origin` — limits referrer leakage
- [ ] `Strict-Transport-Security: max-age=31536000; includeSubDomains` — enforces HTTPS
- [ ] `Permissions-Policy` — disables unused browser features (camera, microphone, geolocation)
- [ ] `X-Powered-By` and `Server` headers removed — do not reveal tech stack version to attackers

Test your headers free: **https://securityheaders.com**

---

## 10. Input Validation

- [ ] All user input validated on the server — client-side JS validation is UX only, not security
- [ ] Input length limits enforced server-side (prevents DoS via massive payloads)
- [ ] Numeric fields cast to `int` or `float` before use — never passed as raw strings to queries
- [ ] Email addresses validated with a proper method (`filter_var($e, FILTER_VALIDATE_EMAIL)`)
- [ ] Phone numbers, ID numbers, postcodes validated against expected format (regex or library)
- [ ] File path inputs use `realpath()` and compared against an allowed base directory — prevents path traversal
- [ ] Shell/system calls (if any) use argument arrays, never string interpolation — prevents command injection
- [ ] XML inputs parsed with external entity expansion disabled (XXE protection)
- [ ] `unserialize()` / `pickle.loads()` never called on untrusted data
- [ ] Open redirect: redirect targets validated to be internal paths only — strip or reject external URLs

---

## 11. Error Handling & Information Disclosure

- [ ] Production error pages show a friendly message — never a stack trace or raw database error
- [ ] Debug mode disabled in production (`APP_DEBUG=false`, `display_errors=Off`)
- [ ] Error logs written server-side only — never output to the browser
- [ ] 404 pages do not reveal directory structure or file paths
- [ ] API errors return generic messages — raw exception text never sent to the client
- [ ] Version numbers of frameworks, servers, CMS hidden from HTTP headers and HTML source
- [ ] Directory listing disabled (`Options -Indexes` in Apache, `autoindex off` in Nginx)

---

## 12. Rate Limiting & Brute Force Protection

- [ ] Login endpoints rate-limited per IP (e.g. 5 attempts / 15 minutes)
- [ ] Login endpoints rate-limited per account (e.g. 10 attempts, then 30-min lockout)
- [ ] Password reset endpoints rate-limited per IP and per user/email
- [ ] OTP / verification code endpoints rate-limited
- [ ] Public API endpoints rate-limited to prevent scraping or abuse
- [ ] CAPTCHA or proof-of-work on high-traffic public forms (registration, contact, login)
- [ ] Monitoring alerts set up for unusual login failure spikes

---

## 13. Dependency & Supply Chain Security

- [ ] All third-party packages/libraries kept up to date
- [ ] `npm audit` / `composer audit` / `pip-audit` run regularly — ideally in CI/CD pipeline
- [ ] Package lock files (`package-lock.json`, `composer.lock`) committed to version control
- [ ] No packages installed from untrusted sources (random GitHub forks, unknown URLs)
- [ ] CDN-loaded scripts use Subresource Integrity (SRI) hashes
- [ ] Old/abandoned packages with known CVEs are replaced

---

## 14. Infrastructure & Deployment

- [ ] Sensitive config (DB credentials, API keys) stored in server environment variables — not in code or web-accessible files
- [ ] `.env`, `.git`, `composer.json`, `package.json`, `*.sql`, `*.log` files are NOT web-accessible
- [ ] Migration scripts and setup tools removed or disabled after deployment
- [ ] Staging and dev environments use separate credentials from production
- [ ] Database backups encrypted and stored offsite
- [ ] Firewall rules allow only necessary ports (80, 443; SSH with key auth only)
- [ ] SSH root login disabled; key-based auth only — password SSH disabled
- [ ] Unnecessary services and ports closed on the server
- [ ] Server OS and software patched regularly — enable automatic security updates
- [ ] Deployment config (`.cpanel.yml`, CI/CD pipeline) excludes sensitive dev-only files from production

---

## 15. Logging & Monitoring

- [ ] Successful and failed logins logged with timestamp and IP address
- [ ] Privileged actions (admin changes, role changes, data deletion, financial approvals) logged with user identity
- [ ] Log entries include who, what, when, and from where — never passwords, tokens, or raw PII
- [ ] Logs stored in a location not accessible via the web
- [ ] Log rotation configured so disk does not fill up
- [ ] Alerts set up for: repeated failed logins, unexpected admin activity, server error spikes
- [ ] Audit trail for financial/sensitive operations is immutable (append-only, no delete allowed)

---

## 16. API Security

- [ ] All API endpoints require authentication — no unauthenticated data access
- [ ] API keys / bearer tokens sent in headers (`Authorization: Bearer ...`) — not in query strings (appear in logs)
- [ ] Sensitive API responses include `Cache-Control: no-store`
- [ ] GraphQL introspection disabled in production
- [ ] Webhooks validate the HMAC signature of incoming payloads before processing
- [ ] API rate limiting and abuse prevention in place per key/user

---

## 17. CMS & Third-Party Platforms (WordPress, Shopify, Wix, etc.)

- [ ] Default admin username changed (no `admin` username in WordPress)
- [ ] WordPress `/wp-admin` and `/wp-login.php` restricted by IP or extra auth layer
- [ ] Only necessary plugins/themes installed — all unused ones deleted (not just deactivated)
- [ ] All plugins and themes kept updated; auto-updates enabled where safe
- [ ] File editor in CMS admin panel disabled (prevents in-browser PHP code editing)
- [ ] Security plugin installed and configured (Wordfence, Sucuri for WordPress)
- [ ] `xmlrpc.php` disabled if not needed (WordPress — a common brute force target)
- [ ] Third-party API access tokens scoped to minimum required permissions

---

## 18. Pre-Launch Final Checks

Run these in the week before every go-live:

- [ ] Run automated scanner: **OWASP ZAP** (free) or **Burp Suite Community** against staging environment
- [ ] Check security headers: **https://securityheaders.com**
- [ ] Check SSL/TLS grade: **https://www.ssllabs.com/ssltest/** — aim for A or A+
- [ ] Google search `site:yourdomain.com filetype:sql OR filetype:env OR filetype:log` — confirm nothing sensitive indexed
- [ ] Review all `TODO` / `FIXME` / `HACK` comments — fix or formally document as accepted risk
- [ ] Remove all debug routes, test endpoints, and seed scripts from the production build
- [ ] Verify error pages (403, 404, 500) are custom and do not leak stack traces or file paths
- [ ] Manual login test as a non-admin user: try to reach admin pages — confirm blocked
- [ ] Manual IDOR test: change a record ID in the URL — confirm you cannot access another user's data
- [ ] Confirm `.env` is in `.gitignore` and has never been committed: `git log -- .env`
- [ ] Confirm no credentials in git history: `git log -p | grep -iE "password|secret|api_key"`

---

## 19. Things That Are NOT Vulnerabilities (Common Misconceptions)

| "Vulnerability" | Reality |
|-----------------|---------|
| CSRF token visible in HTML page source | **Normal and expected** — it must be in the form. Only dangerous if there is also XSS. |
| Server returning a 404 on missing pages | **Normal** — 404 is not information disclosure |
| Login page accessible without HTTPS on localhost | **Fine in dev** — enforce HTTPS in production only |
| Session cookie visible in browser DevTools | **Normal** — the browser needs it. `HttpOnly` stops JS from reading it. |
| API returning field names in JSON | **Usually fine** — only a problem if it exposes another user's data or internal secrets |
| SQL comment `--` not blocked by HTML `required` attribute | **HTML validation is not security** — the server must enforce independently; these serve different purposes |
| Source maps or minified JS visible in browser | **Normal for client-side code** — never put secrets in frontend JavaScript |

---

## 20. Top Attacks & How to Stop Them — Quick Reference

| Attack | What the attacker does | How to stop it |
|--------|----------------------|----------------|
| **SQL Injection** | Injects SQL through inputs to dump, modify, or delete data | Parameterized queries / prepared statements — always, no exceptions |
| **XSS** | Injects scripts into pages to run in other users' browsers | Escape all output; Content-Security-Policy header |
| **CSRF** | Tricks a logged-in user's browser into making unwanted requests | CSRF tokens on all state-changing forms and AJAX endpoints |
| **IDOR** | Changes an ID in the URL to access another user's data | Always verify ownership server-side with strict type comparison |
| **Path Traversal** | Uses `../../../etc/passwd` to read arbitrary files | `realpath()` + allowed base path check on all file operations |
| **Brute Force** | Tries many passwords/tokens until one works | Rate limiting + account lockout + MFA |
| **Session Hijacking** | Steals a session cookie to impersonate a user | `HttpOnly`, `Secure`, `SameSite`; regenerate session ID on login |
| **Clickjacking** | Embeds your site in an iframe to trick users into clicking | `X-Frame-Options: SAMEORIGIN` + `frame-ancestors` in CSP |
| **Open Redirect** | Redirects users to malicious external sites via your URL | Validate redirect targets are internal paths only |
| **Command Injection** | Passes shell commands through user input | Never use `shell_exec`/`system` with user input; use argument arrays |
| **File Upload Attack** | Uploads a PHP/shell script disguised as an image | Whitelist types; inspect MIME; store outside web root; rename files |
| **Credential Stuffing** | Uses leaked username/password combos from other breaches | Rate limiting; MFA; HaveIBeenPwned API monitoring |
| **XXE Injection** | Crafts malicious XML to read server files or make internal requests | Disable external entity loading in all XML parsers |

---

## Free Tools to Use on Every Project

| Tool | What it does | Link |
|------|-------------|-------|
| OWASP ZAP | Automated full-site vulnerability scan | https://www.zaproxy.org |
| Burp Suite Community | Manual interception and attack testing | https://portswigger.net/burp |
| Security Headers | Grades your HTTP response headers | https://securityheaders.com |
| SSL Labs | Grades your HTTPS / TLS configuration | https://www.ssllabs.com/ssltest |
| Mozilla Observatory | Combined header + TLS + cookie check | https://observatory.mozilla.org |
| HaveIBeenPwned | Check if credentials appear in breaches | https://haveibeenpwned.com |
| Retire.js | Detects outdated JS libraries with known CVEs | https://retirejs.github.io |
| Snyk | Dependency vulnerability scanning | https://snyk.io |

---

## Further Reading

- **OWASP Top 10** — the 10 most critical web application risks: https://owasp.org/www-project-top-ten/
- **OWASP Testing Guide** — detailed manual testing methodology (free PDF download)
- **OWASP ASVS** — Application Security Verification Standard: Level 1 = baseline, Level 3 = high security
- **PortSwigger Web Security Academy** — free hands-on labs for every attack type: https://portswigger.net/web-security

---

*Version 1.0 — Created April 2026*
*Covers: PHP, Node.js, Python/Django/Flask, Ruby on Rails, Java/Spring, .NET, WordPress, Shopify, Wix, and any custom-built web application*
