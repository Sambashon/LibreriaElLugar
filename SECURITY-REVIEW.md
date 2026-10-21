# Security review

**Scope:** Static review of the application code and Docker/Nginx configuration for access control, SQL injection, price and quantity tampering, IDOR, password storage, XSS, uploads, request abuse, order and stock handling, and deployment secrets/configuration.

**Limitations:** The application was not executed and endpoints were not tested. Firewall rules, external proxy/TLS settings, deployed port accessibility, and effective environment values could not be verified.

## Findings

### 1. Public endpoint creates an administrator with a fixed password

- **Severity:** High
- **Confidence:** 10/10
- **Evidence:** `docs/php/scripts/crearUsuarioAdmin.php` lines 3–8 and 15–29 creates `admin@ejemplo.com` with the fixed password `123` and administrator privileges without checking the request method or authorization. `Private/docker/nginx.conf` lines 13–18 routes PHP requests to PHP-FPM.
- **Impact:** If the account does not already exist, a direct request to the endpoint can create a known administrator account and allow administrator login.
- **Recommendation:** Remove the endpoint from the public document root or restrict it to CLI use; do not provision administrators with fixed credentials.

### 2. Stored XSS through imported catalogue data

- **Severity:** Medium
- **Confidence:** 8/10
- **Evidence:** `docs/php/clases/importador.php` lines 84 and 95–109 retains imported titles after checking only that they are non-empty. `docs/php/scripts/cargarLibros.php` lines 8–17 exposes the import flow to administrators. `docs/catalogue.php` lines 293–303 embeds the values in a script block using `json_encode` without escaping HTML-sensitive characters.
- **Impact:** A malicious spreadsheet title containing a script-closing sequence such as `</script>` may terminate the script block and execute injected script in visitors' browsers after an administrator imports the file.
- **Recommendation:** Escape `<` when embedding JSON in an HTML script element, use context-appropriate encoding, and avoid assembling HTML with unescaped catalogue values.

### 3. Session cookies may be sent over HTTP

- **Severity:** Medium
- **Confidence:** 8/10
- **Evidence:** `docs/php/clases/managers/SessionManager.php` lines 13–17 starts sessions without explicitly configuring cookie attributes. `Private/docker/docker-compose.yaml` lines 17–25 publishes Nginx on port `8080:80`; `Private/docker/nginx.conf` lines 1–3 and 13–18 serve HTTP without redirecting to HTTPS. `docs/php/config.php` lines 11 and 24–25 defaults `APP_ENV` to `development`.
- **Impact:** If the published HTTP port is reachable, session cookies may be transmitted without transport encryption; PHP's default session cookie is not marked `Secure`.
- **Recommendation:** Do not expose the HTTP origin directly; require HTTPS and configure session cookies with `Secure`, `HttpOnly`, and `SameSite` before starting the session.

## Areas reviewed without a confirmed finding

- **SQL injection:** Inspected queries use prepared statements; no controllable input reaching concatenated SQL was confirmed.
- **Price, quantities, orders, and stock:** Order creation uses cart/database prices, checks stock, and updates within a transaction; no client-controlled order total was found.
- **IDOR and other users' orders:** Order management is admin-protected; cart and favorites are scoped to the session user.
- **Password storage:** Registration and login use `password_hash` and `password_verify`. The fixed administrator credential is covered by finding 1.
- **Cover uploads:** Admin-protected; size and format are constrained, images are decoded and converted, and output is stored outside the document root.
- **Payment webhook:** The callback checks the identifier, currency, and amount against the persisted order. No exploitable external identifier was confirmed.
- **Brute force:** No login-attempt rate limit was observed. This is not reported as a finding under the review's reporting policy.
- **Secrets:** No additional secrets were found in the versioned files reviewed. The local `.env` is not versioned and its values were not assessed.
- **Docker/Nginx:** Compose also publishes port `3306`; external reachability and network rules could not be determined from the workspace.
