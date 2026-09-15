# Real Estate Management Security Audit

**Audit date:** 11 July 2026  
**Scope:** PHP frontend/backend files supplied in this project archive  
**Overall status:** **High risk — do not deploy publicly before fixing Critical and High findings.**

This is a static source-code review, not a penetration test. Prepared PDO statements are used in many database queries, which is positive, but authentication, authorization, uploads, CSRF, and business-flow controls require major hardening.

## Priority summary

| Priority | Finding | Severity |
|---|---|---|
| 1 | User/admin identity trusted from editable cookies | Critical |
| 2 | Property update IDOR: ownership is not checked | Critical |
| 3 | Purchase can be marked completed without payment/transaction validation | Critical |
| 4 | Admin passwords use SHA-1 | High |
| 5 | File uploads trust extensions and lack MIME/content validation | High |
| 6 | No CSRF protection on state-changing requests | High |
| 7 | Seller messages stored in public JSON files | High |
| 8 | Hardcoded root database credentials | High |
| 9 | No login rate limiting or brute-force controls | Medium |
| 10 | Error/debug information enabled in production-facing files | Medium |
| 11 | Missing security headers and secure cookie attributes | Medium |
| 12 | Missing atomic transaction/unique constraint for purchases | Medium |

---

## 1. Authentication and authorization audit

### A1. Editable cookies are treated as authenticated identity
**Severity: Critical**  
**Affected code:** `login.php:25-35`, `components/user_header.php`, most authenticated pages; admin equivalent in `admin/login.php:16-18`.

The application stores raw database IDs in `user_id`, `user_type`, and `admin_id` cookies. A browser user can edit these values and impersonate another account. The cookies also omit `HttpOnly`, `Secure`, and `SameSite` attributes.

**Attack scenario**
1. Attacker registers or opens the site.
2. Attacker edits `user_id` in browser developer tools.
3. Protected pages trust the changed ID and query another account's records.
4. Changing `admin_id` may grant administrative access because pages only check whether the cookie exists.

**Secure pattern**
```php
// At application bootstrap
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// After successful password verification
session_regenerate_id(true);
$_SESSION['user_id'] = $buyer['id'];
$_SESSION['user_type'] = 'buyer';

// On protected routes
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id'];
```
Use server-side sessions or cryptographically signed, expiring tokens. Never treat a raw user-supplied cookie ID as proof of identity.

### A2. Property update endpoint does not verify ownership
**Severity: Critical**  
**Affected code:** `update_property.php:68-82`, `92-106`, and property load at `133-137`.

The update and image queries use only `WHERE id = ?`. They do not require `user_id = ?`. Any authenticated user who knows or changes a property ID can update or delete images belonging to another seller.

**Proof of concept**
Submit an update form with:
```text
property_id=ANOTHER_SELLERS_PROPERTY_ID&price=1
```

**Secure rewrite**
```php
$update = $conn->prepare(
    'UPDATE property SET property_name = ?, price = ?, address = ?
     WHERE id = ? AND user_id = ?'
);
$update->execute([$name, $price, $address, $propertyId, $userId]);

if ($update->rowCount() !== 1) {
    http_response_code(403);
    exit('Not authorized to update this property.');
}
```
Apply the same ownership condition to image updates, image deletes, and the initial property query.

### A3. Admin password storage uses SHA-1
**Severity: High**  
**Affected code:** `admin/login.php:9-13`, `admin/update.php:35-40`.

SHA-1 is a fast, obsolete password hash and is unsuitable for password storage.

**Secure rewrite**
```php
// Registration/update
$hash = password_hash($password, PASSWORD_ARGON2ID);

// Login
$stmt = $conn->prepare('SELECT id, password FROM admins WHERE name = ? LIMIT 1');
$stmt->execute([$name]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin && password_verify($password, $admin['password'])) {
    session_regenerate_id(true);
    $_SESSION['admin_id'] = $admin['id'];
}
```
Migrate existing admin hashes by forcing password resets or rehashing after a verified legacy login.

### A4. Role checks are inconsistent
**Severity: High**

Several routes only check for a user ID and then infer role from which table contains that ID. Because buyer and seller IDs are not guaranteed to be globally unique, this can create role confusion. Every protected route should explicitly require the expected role stored in a trusted server-side session.

---

## 2. Injection and input-validation audit

### B1. File uploads trust filename extensions
**Severity: High**  
**Affected code:** `post_property.php:112-129`, `155-193`; `update_property.php:98-109`.

The code copies the extension from the original filename and moves the file into a web-accessible directory. An attacker may upload executable or active content, spoof MIME types, or use crafted images.

**Example malicious filename**
```text
shell.php
```

**Secure rewrite**
```php
function storeImage(array $file): string {
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 2_000_000) {
        throw new RuntimeException('Invalid upload.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    if (!isset($allowed[$mime]) || @getimagesize($file['tmp_name']) === false) {
        throw new RuntimeException('Only valid JPG, PNG, or WebP images are allowed.');
    }

    $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    $destination = __DIR__ . '/../private_uploads/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Upload failed.');
    }
    return $name;
}
```
Store uploads outside the executable web root or configure the upload directory to prohibit script execution.

### B2. Validation is mostly sanitization rather than strict validation
**Severity: Medium**

`FILTER_SANITIZE_STRING` is deprecated and does not enforce business rules. Prices, IDs, phone numbers, enum values, and text lengths should be validated explicitly.

```php
$price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 2_000_000_000]
]);
$offer = $_POST['offer'] ?? '';
if ($price === false || !in_array($offer, ['sale', 'resale'], true)) {
    http_response_code(422);
    exit('Invalid input.');
}
```

### B3. Path manipulation risk in image deletion
**Severity: High**  
**Affected code:** `update_property.php:91-109`.

`old_image_*` comes from hidden form fields and is concatenated into `unlink()`. A crafted request could attempt path traversal.

**PoC value**
```text
old_image_01=../components/connect.php
```

**Secure pattern**
Never trust a submitted old filename. Fetch the current filename from the database using both property ID and owner ID, then delete only `basename($dbFilename)` from a fixed directory.

---

## 3. API and data-exposure audit

### C1. Seller messages are stored in predictable public JSON files
**Severity: High**  
**Affected code:** `contact_seller.php:46-56`; existing `seller_messages_*.json` file.

These files contain buyer name, email, phone, subject, message, property ID, and timestamps. If the web server serves JSON files, personal data may be downloaded without authentication. Concurrent writes may also corrupt or overwrite data.

**Remediation**
Store messages in a database table with access checks:
```sql
CREATE TABLE seller_messages (
  id CHAR(32) PRIMARY KEY,
  buyer_id CHAR(20) NOT NULL,
  seller_id CHAR(20) NOT NULL,
  property_id CHAR(20) NOT NULL,
  subject VARCHAR(120) NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (seller_id), INDEX (buyer_id)
);
```
Never place PII-bearing files in a public directory.

### C2. Database root credentials are hardcoded
**Severity: High**  
**Affected code:** `components/connect.php:3-7`.

The application connects as MySQL `root` with a blank password. A compromised application would have unrestricted database privileges.

**Secure pattern**
```php
$dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', getenv('DB_HOST'), getenv('DB_NAME'));
$conn = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'), [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);
```
Use a dedicated database user with only the permissions needed by this application. Keep `.env` outside version control.

### C3. Missing brute-force protection
**Severity: Medium**  
**Affected code:** `login.php`, `admin/login.php`.

There is no rate limiting, delay, account lockout, or IP/user-based attempt tracking. Add per-IP and per-account throttling, generic error messages, logging, and optional MFA for administrators.

### C4. Missing security headers
**Severity: Medium**

Add centrally before output:
```php
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Permissions-Policy: camera=(), microphone=(), geolocation=(self)");
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' https://cdnjs.cloudflare.com https://fonts.googleapis.com; font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com; script-src 'self' https://cdnjs.cloudflare.com");
// Enable only over HTTPS:
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
```

### C5. Debug and stack/error exposure
**Severity: Medium**  
**Affected code:** `my_listings.php:2-3`, `debug_properties.php`.

Disable display errors in production, remove debug endpoints, and log errors outside the web root.

---

## 4. Dependency and configuration audit

No Composer, npm, or other dependency manifest was found, so automated package CVE comparison was not possible. The project loads Font Awesome and SweetAlert from external CDNs without Subresource Integrity. Pin exact versions, use SRI hashes, or self-host production assets.

**Configuration priorities**
1. Remove `debug_properties.php` from production.
2. Move secrets to environment variables.
3. Use `display_errors=Off`, `log_errors=On`, and `expose_php=Off` in production.
4. Disable PHP execution in upload directories.
5. Enforce HTTPS.
6. Restrict database privileges.
7. Add backups and protected audit logs.

---

## 5. Business logic and state-management audit

### E1. A property purchase is marked completed without payment verification
**Severity: Critical**  
**Affected code:** `components/buy_send.php:57-69`.

Pressing the purchase action inserts a row with status `completed`. There is no payment provider, reservation, seller approval, legal-transfer workflow, or server-verified transaction amount.

**Attack scenario**
1. Buyer submits any property ID.
2. Server creates a purchase record.
3. Status immediately becomes `completed`.
4. The UI may represent ownership as transferred despite no payment or legal confirmation.

**Secure state machine**
```text
inquiry -> reserved -> payment_pending -> payment_verified -> seller_confirmed -> transfer_pending -> completed
```
Only the server should advance states. Payment completion must be triggered by a verified payment-provider webhook, with signature validation and an idempotency key.

### E2. Duplicate-purchase race condition
**Severity: Medium**

The code checks for an existing purchase and inserts later. Two simultaneous requests can pass the check before either insert commits.

**Secure pattern**
```sql
ALTER TABLE purchases ADD CONSTRAINT uq_buyer_property UNIQUE (buyer_id, property_id);
```
Use a database transaction and handle duplicate-key exceptions. For exclusive property sales, also enforce one active/completed buyer per property.

### E3. No CSRF tokens
**Severity: High**

Property deletion, profile changes, purchase actions, admin approval/deletion, saves, and messages accept state-changing POST requests without CSRF tokens.

```php
// Generate
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));

// Form
<input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

// Verify
if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
    http_response_code(403);
    exit('Invalid CSRF token.');
}
```

### E4. Missing audit trail
**Severity: Medium**

Log login successes/failures, property creation/update/deletion, approvals, role changes, message access, purchase state changes, and administrative actions. Include actor, action, target, timestamp, IP, and request ID. Logs must be append-only and inaccessible from the public web root.

---

## Recommended implementation order

1. Replace identity cookies with secure server-side sessions.
2. Add ownership/role authorization to every write and sensitive read.
3. Replace SHA-1 admin hashes with Argon2id or bcrypt.
4. Add CSRF protection globally.
5. Rebuild upload handling and isolate uploaded files.
6. Replace JSON message storage with a protected database table.
7. Introduce a verified purchase workflow and database uniqueness constraints.
8. Move credentials to environment variables and restrict DB permissions.
9. Add rate limiting, headers, HTTPS, logging, and production error settings.
10. Run dynamic tests using OWASP ZAP against a non-production environment.
