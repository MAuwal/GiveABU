# Forgot Password Link Flow (Backend)

## Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/donor-sessions/forgot-password` | Accepts `{ "email": "user@example.com" }`. Always returns a generic success message. When the account exists (non-Google), generates a token, stores it in `password_resets`, and emails a link (`{{ FRONTEND_URL }}/reset-password?token=...`). Tokens expire in 10 minutes and we cap to 5 requests per hour per session. |
| GET | `/api/donor-sessions/reset/{token}` | Validates the token (exists, unused, not expired). Returns `{ "username": "user@example.com" }` so the frontend can show the email on the reset form. |
| POST | `/api/donor-sessions/reset/{token}` | Body `{ "password": "...", "password_confirmation": "..." }`. Revalidates token, updates the donor session password (mutator auto-hashes), marks the token as used, and invalidates any other pending tokens for that session. |

## Database

`password_resets` table:

| Column | Notes |
|--------|-------|
| `id` | Primary key |
| `donor_session_id` | FK → `donor_sessions.id`, cascades on delete |
| `token` | Random 64-char string, unique |
| `expires_at` | `now() + 10 minutes` |
| `used` | Boolean flag |
| `created_at` / `updated_at` | Timestamps |

Legacy `password_reset_tokens` migration now skips creation if the table already exists so older deployments stay compatible.

## Mailer

`PasswordResetLinkMail` renders `resources/views/emails/password-reset-link.blade.php`. It receives:

- `resetUrl` – frontend link (`FRONTEND_URL` env fallback to `APP_URL`)
- `username` – email/username shown in greeting

## Rate limiting

- Up to 5 reset attempts per donor session per rolling hour.
- Additional attempts silently succeed but no new email is sent.

## Frontend expectations

1. **Forgot Password Page (`/forgot-password`)**
   - POST email to `/api/donor-sessions/forgot-password`.
   - Show success toast regardless of actual account state.

2. **Reset Password Page (`/reset-password?token=...`)**
   - On load, call `GET /api/donor-sessions/reset/{token}`.
   - If 404, show “link expired/invalid”.
   - Otherwise display reset form + email returned.

3. **Submit Reset**
   - POST new password + confirmation to `/api/donor-sessions/reset/{token}`.
   - On success, redirect to login page with confirmation message.

## Configuration

- Set `FRONTEND_URL=https://your-frontend-domain` in `.env` for accurate links.
- Ensure mail credentials are valid in `.env` (`MAIL_MAILER`, `MAIL_HOST`, etc.).

## Optional Enhancements

- Queue the mail job (`implements ShouldQueue`) for heavy traffic.
- Track IPs/device for additional throttling if needed.

Backend is ready—please proceed with the frontend pages when convenient! 

