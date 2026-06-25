# Production Mail — Registration Verification Codes

Registration sends a **5-digit code by email** (or SMS if register method is mobile).  
On production this uses **Hostinger SMTP** via `.env`.

---

## How it works

```
Register → VerificationController → Verification::sendEmailCode()
         → SendVerificationEmailCode notification → SMTP (smtp.hostinger.com)
```

| Environment | Behaviour |
|-------------|-----------|
| **Local** (`APP_ENV=local`) | Sends email if SMTP is configured; **also logs the code** to `storage/logs/laravel.log` |
| **Production** (`APP_ENV=production`) | Sends email only — **no code in logs** |

---

## Required production `.env` settings

Edit on server: `domains/training.qiec.sa/public_html/.env`

```env
APP_ENV=production
APP_DEBUG=false

MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=contact@training.qiec.sa
MAIL_PASSWORD='your-email-password-here'
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=contact@training.qiec.sa
MAIL_FROM_NAME="QIEC Training"
```

### Hostinger email password

1. hPanel → **Emails** → `contact@training.qiec.sa` → **Manage**
2. **Reset password** if unsure
3. Paste into `MAIL_PASSWORD` in `.env`
4. If the password contains `$`, use **single quotes** — double quotes break dotenv (see [ENV_PRODUCTION_CHECKLIST.md](ENV_PRODUCTION_CHECKLIST.md))

### After changing `.env`

Delete cached config on the server (SSH):

```bash
rm -f ~/domains/training.qiec.sa/public_html/bootstrap/cache/config.php
```

Or if `php artisan` works on your server:

```bash
php artisan config:clear
```

> On some Hostinger plans, `php artisan` from SSH fails (ionCube CLI) but the **website still works**. Deleting `bootstrap/cache/config.php` is enough.

---

## Admin settings must match

In **Admin → Settings → General**:

| Field | Should be |
|-------|-----------|
| **Site email** | Same as `MAIL_USERNAME` / `MAIL_FROM_ADDRESS` (e.g. `contact@training.qiec.sa`) |

Hostinger SMTP requires the **From address** to match the authenticated mailbox.  
If `site_email` in the database is different (e.g. old `info@siematplus.com`), emails may fail or go to spam.

---

## Test mail

From your **local** machine (if `.env` has the same SMTP settings):

```powershell
cd c:\xampp\htdocs\qiec-project
php artisan qiec:test-mail your@gmail.com
```

Check platform status:

```powershell
php artisan qiec:platform-status
```

On production, check logs after a registration attempt:

```bash
ssh hostinger-qiec
grep -i "verification email" ~/domains/training.qiec.sa/public_html/storage/logs/laravel.log | tail -5
```

### Common log error

```
535 5.7.8 Error: authentication failed
```

**Fix:** Wrong `MAIL_PASSWORD` — reset in hPanel and update `.env`.

---

## Register method: email vs mobile

Controlled in **Admin → Settings → General → Register method**:

| Method | Verification sends |
|--------|-------------------|
| `email` | Code to user's email (SMTP) |
| `mobile` | SMS (needs SMS gateway configured in RocketLMS) |

For QIEC, email registration uses Hostinger SMTP.

---

## Troubleshooting

| Problem | Cause | Fix |
|---------|-------|-----|
| Works locally, not on production | Production `.env` has wrong/old password | Update `MAIL_PASSWORD` on server |
| 535 authentication failed | Bad password or wrong username | Reset email password in hPanel |
| Email not received, no error | Spam folder / wrong recipient | Check spam; verify user email in DB |
| Code page shows but no email | SMTP failed silently (logged) | Check `storage/logs/laravel.log` |
| `MAIL_PASSWORD` with `$` in double quotes | Dotenv expands `$VAR` → wrong password | Use single quotes: `MAIL_PASSWORD='...'` |
| From address rejected | `site_email` ≠ `MAIL_USERNAME` | Align in Admin → Settings |

---

## Security

- Never commit `.env` to Git
- Use a dedicated mailbox (`contact@training.qiec.sa`), not personal Gmail
- Keep `APP_DEBUG=false` on production

---

## Related

- [DEPLOYMENT.md](DEPLOYMENT.md) — server setup
- [LOCAL_TO_PRODUCTION.md](LOCAL_TO_PRODUCTION.md) — syncing DB and files
