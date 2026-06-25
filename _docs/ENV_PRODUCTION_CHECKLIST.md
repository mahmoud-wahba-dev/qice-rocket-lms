# Production `.env` — Validation Checklist

Compare local vs production `.env` before go-live and after any server edit.

---

## Local vs production (expected differences)

| Key | Local | Production |
|-----|-------|------------|
| `APP_ENV` | `local` | `production` |
| `APP_DEBUG` | `true` (OK locally) | **`false` required** |
| `APP_URL` | `http://training.qiec.local` | `https://training.qiec.sa` (no trailing `/`) |
| `DB_DATABASE` | `qiec` | `u873288737_markaz` |
| `DB_USERNAME` | `root` | `u873288737_user_markaz` |
| `DB_PASSWORD` | empty | Hostinger MySQL password |
| `MAIL_*` | Same Hostinger mailbox | Same Hostinger mailbox |

## Should match (or be equivalent)

| Key | Notes |
|-----|--------|
| `MAIL_HOST` | `smtp.hostinger.com` |
| `MAIL_PORT` | `465` |
| `MAIL_USERNAME` | `contact@training.qiec.sa` |
| `MAIL_PASSWORD` | Same mailbox password — **see quoting rules below** |
| `MAIL_ENCRYPTION` | `ssl` (lowercase) |
| `MAIL_FROM_ADDRESS` | Same as `MAIL_USERNAME` |

## Cosmetic only

| Key | Local | Production |
|-----|-------|------------|
| `APP_NAME` | `qiec` | `training` |
| `MAIL_FROM_NAME` | `QIEC Training` | `Centre for Training` |

`APP_KEY` and `JWT_SECRET` may match between environments if you copied `.env`; that is fine for this project.

---

## Critical rules (avoid future bugs)

### 1. Passwords with `$` must NOT use double quotes

**Wrong** (dotenv expands `$EI5` as a variable → wrong password):

```env
MAIL_PASSWORD="Y:&LPZ$EI5~k"
```

**Correct:**

```env
MAIL_PASSWORD='Y:&LPZ$EI5~k'
```

Or escape the dollar:

```env
MAIL_PASSWORD="Y:&LPZ\$EI5~k"
```

### 2. Passwords with `;`, `@`, `#`, spaces — quote them

```env
DB_PASSWORD="CBKc;6@z"
```

### 3. Never enable debug on production

```env
APP_DEBUG=false
```

With `APP_DEBUG=true`, errors expose paths, queries, and secrets.

### 4. No trailing slash on `APP_URL`

```env
APP_URL=https://training.qiec.sa
```

Not `https://training.qiec.sa/`

### 5. After any `.env` change on the server

```bash
rm -f ~/domains/training.qiec.sa/public_html/bootstrap/cache/config.php
```

### 6. Admin **Site email** must match SMTP user

Admin → Settings → General → **Site email** = `contact@training.qiec.sa`

---

## Why local mail seemed to work

| Environment | On SMTP failure |
|-------------|-----------------|
| **Local** | Verification code is still written to `storage/logs/laravel.log` |
| **Production** | No log fallback — user receives nothing |

So local can appear fine even when SMTP auth fails.

---

## Verify mail

**Local:**

```powershell
php artisan config:clear
php artisan qiec:test-mail your@gmail.com
```

**Production:** try registration, then:

```bash
grep "Verification email" ~/domains/training.qiec.sa/public_html/storage/logs/laravel.log | tail -3
```

No new `535 authentication failed` = SMTP OK.

---

## Security

- Never commit `.env` to Git
- Never paste `.env` in chat or tickets
- Rotate passwords if they were exposed

---

## Related

- [PRODUCTION_MAIL.md](PRODUCTION_MAIL.md) — full mail setup
