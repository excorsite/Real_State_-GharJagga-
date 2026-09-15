# Five Reusable Security Review Prompts

Use these prompts with a code-review tool after each major project update. Never paste production secrets, private customer data, or live database exports into third-party tools.

## 1. Authentication and authorization
Review the codebase for broken authentication, insecure sessions or JWTs, missing route authorization, privilege escalation, hardcoded credentials, and weak password storage. For every finding, provide the affected file and line, severity, attack scenario, and secure replacement code.

## 2. Injection and validation
Review SQL/NoSQL/command injection, XSS, path traversal, SSRF, unsafe file handling, and missing server-side validation. For each finding, provide a harmless proof-of-concept input, root cause, severity, and secure rewrite.

## 3. API and data exposure
Review sensitive response fields, authentication gaps, rate limiting, CORS, error leakage, pagination, security headers, logging, and client-side secret exposure. List affected endpoints, risk, and remediation.

## 4. Dependencies and configuration
Review dependency manifests and lockfiles for known vulnerabilities and abandoned packages. Review production configuration, environment files, debug settings, database/IAM privileges, dangerous dynamic execution, filesystem permissions, and runtime privileges. Prioritize fixes and provide upgrade commands.

## 5. Business logic and state
Review price/quantity/ID trust boundaries, race conditions, idempotency, workflow bypasses, payment verification, file uploads, IDOR, client-side-only controls, and missing audit logs. Explain each attack path and provide a secure server-side implementation pattern.
