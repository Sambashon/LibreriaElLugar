# Security review

**Scope:** Static review of the application code and Docker/Nginx configuration for access control, SQL injection, price and quantity tampering, IDOR, password storage, XSS, uploads, request abuse, order and stock handling, and deployment secrets/configuration.

**Validation:** PHP syntax was checked for all PHP files. Local HTTP checks confirmed that login POST requests without CSRF tokens are rejected and administrator provisioning is blocked over HTTP. A valid login and rate-limit threshold were not exercised.

**Limitations:** Firewall rules, external proxy/TLS settings, deployed port accessibility, and effective production environment values could not be verified.

## Findings

### 1. Public endpoint creates an administrator with a fixed password

- **Severity:** High
- **Confidence:** 10/10
- **Status:** Fixed. The script now refuses web requests and requires administrator details from environment variables, including a password of at least 12 characters.
- **Evidence:** `docs/php/scripts/crearUsuarioAdmin.php` is restricted to CLI and no longer contains default credentials.
- **Impact before fix:** If the account did not already exist, a direct request could create a known administrator account and allow administrator login.
- **Resolution:** Continue provisioning through CLI only, using deployment-managed credentials; do not expose this script over HTTP.

### 2. Stored XSS through imported catalogue data

- **Severity:** Medium
- **Confidence:** 8/10
- **Status:** Fixed by encoding HTML-sensitive characters in the JSON embedded by `docs/catalogue.php`.
- **Evidence:** `docs/php/clases/importador.php` lines 84 and 95–109 retains imported titles after checking only that they are non-empty. `docs/php/scripts/cargarLibros.php` lines 8–17 exposes the import flow to administrators. `docs/catalogue.php` lines 293–303 embeds the values in a script block using `json_encode` without escaping HTML-sensitive characters.
- **Impact:** A malicious spreadsheet title containing a script-closing sequence such as `</script>` may terminate the script block and execute injected script in visitors' browsers after an administrator imports the file.
- **Resolution:** `docs/catalogue.php` uses `JSON_HEX_TAG`, `JSON_HEX_AMP`, `JSON_HEX_APOS`, and `JSON_HEX_QUOT` when embedding catalogue data.

### 3. Session cookies may be sent over HTTP

- **Severity:** Medium
- **Confidence:** 8/10
- **Status:** Fixed in application configuration: session cookies use `Secure` by default in production and retain `HttpOnly` and `SameSite=Lax`.
- **Evidence:** `docs/php/config.php` defaults to production; `Private/docker/docker-compose.yaml` explicitly sets `APP_ENV=production`; `docs/php/clases/managers/SessionManager.php` sets all three cookie attributes before starting the session.
- **Impact before fix:** Session cookies could have been transmitted without transport encryption when the published HTTP port was reachable.
- **Deployment note:** Keep the production origin behind HTTPS. Set `APP_ENV=development` only for local HTTP testing.

## Areas reviewed without a confirmed finding

- **SQL injection:** Inspected queries use prepared statements; no controllable input reaching concatenated SQL was confirmed.
- **Price, quantities, orders, and stock:** Order creation uses cart/database prices, checks stock, and updates within a transaction; no client-controlled order total was found.
- **IDOR and other users' orders:** Order management is admin-protected; cart and favorites are scoped to the session user.
- **Password storage:** Registration and login use `password_hash` and `password_verify`; new registrations enforce an 8-character minimum.
- **Login abuse:** Login attempts are limited per email and source IP. Apply `Private/migrations/2026_11_09_login_rate_limits.sql` to existing databases before deploying; new databases create this table from `Private/sql/database.sql`.
- **CSRF:** State-changing POST requests require a session CSRF token; admin APIs additionally check the administrator role.
- **Cover and import uploads:** Admin-protected; MIME and extension are checked, image/import sizes are capped, and covers are decoded and converted outside the document root.
- **PHP errors:** PHP display is disabled in the application and Docker configuration; unexpected API exceptions are logged and return generic messages.
- **Payment webhook:** The callback checks the identifier, currency, and amount against the persisted order. No exploitable external identifier was confirmed.
- **Brute force:** Login attempts are limited to 5 per email and 20 per IP in a 15-minute window.
- **Secrets:** No additional secrets were found in the versioned files reviewed. The local `.env` is not versioned and its values were not assessed.
- **Docker/Nginx:** Compose also publishes port `3306`; external reachability and network rules could not be determined from the workspace.
