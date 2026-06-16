# Folder Rename Recommendation: `qice-project` → `qiec-project`

## The Problem

The current folder is named `qice-project`, but the client name is **QIEC** (Quality and Innovation in Education Center). The domain is `training.qiec.sa`. This is a misspelling that will cause confusion.

---

## Recommendation: ✅ YES — Rename to `qiec-project`

**Rename now, before Git init and before any deployment configuration is set up.**

This is the ideal time because:
- ❌ No `.git` directory exists yet — no repo history to break
- ❌ No remote repository configured — no clone URLs to update
- ❌ No CI/CD pipeline — no paths to reconfigure
- ❌ No deploy scripts exist — nothing references the current folder name
- ❌ No local domain configured — `.env` still points to production URL

---

## Impact Analysis

### What WILL be affected

| Item | Impact | Fix |
|------|--------|-----|
| **Local folder path** | `c:/xampp/htdocs/qice-project` → `c:/xampp/htdocs/qiec-project` | One rename operation |
| **XAMPP vhosts** (if configured) | Virtual host path in `httpd-vhosts.conf` | Update path to new folder name |
| **Windows hosts file** | No impact (maps domain to IP, not folder) | None needed |
| **IDE/editor workspace** | Open folder will become invalid | Re-open from new path |
| **Terminal/PowerShell** working directory | Any open terminals in old path | `cd` to new path |

### What will NOT be affected

| Item | Why |
|------|-----|
| **`.env` APP_URL** | Currently points to `https://training.qiec.sa/` — no folder reference |
| **Laravel internal paths** | Laravel uses relative paths from project root — folder name doesn't matter |
| **Composer / NPM** | All paths are relative — no change needed |
| **Database** | DB name is `u873288737_markaz` — independent of folder |
| **Hostinger deployment** | Remote path is `domains/training.qiec.sa/public_html` — no local folder reference |
| **Git (future)** | Git tracks content, not the folder name — safe to rename before `git init` |
| **Any PHP code** | No hardcoded paths reference the folder name |
| **Routes** | All routes are relative URLs — no folder reference |
| **Assets / public/** | All use relative paths — no change |

---

## How to Rename

### Step 1: Close all editors/terminals using the folder
Make sure no process has the folder locked.

### Step 2: Rename via PowerShell
```powershell
Rename-Item -Path "c:\xampp\htdocs\qice-project" -NewName "qiec-project"
```

### Step 3: Update XAMPP vhosts (if applicable)
If you have a virtual host for this project in `c:\xampp\apache\conf\extra\httpd-vhosts.conf`:
```apache
# Change this:
DocumentRoot "c:/xampp/htdocs/qice-project/public"
<Directory "c:/xampp/htdocs/qice-project/public">

# To this:
DocumentRoot "c:/xampp/htdocs/qiec-project/public"
<Directory "c:/xampp/htdocs/qiec-project/public">
```

### Step 4: Restart Apache
```powershell
# Via XAMPP Control Panel, or:
c:\xampp\apache\bin\httpd.exe -k restart
```

### Step 5: Re-open in IDE
Open `c:\xampp\htdocs\qiec-project` as the workspace.

### Step 6: Verify
```powershell
# Confirm the rename
Test-Path "c:\xampp\htdocs\qiec-project"  # Should return True
Test-Path "c:\xampp\htdocs\qice-project"  # Should return False
```

---

## If You Decide NOT to Rename

If you prefer to keep `qice-project`, the only ongoing impact is:
- **Confusion** — the folder name won't match the domain, repo name, or client name
- **Documentation** — every doc will need a note about the mismatch
- **New team members** — will wonder about the typo

There is **no technical blocker** either way. It's purely an organizational decision.

---

## Final Verdict

| Factor | Keep `qice-project` | Rename to `qiec-project` |
|--------|---------------------|--------------------------|
| Consistency with domain | ❌ Mismatch | ✅ Matches `qiec.sa` |
| Consistency with client name | ❌ Misspelled | ✅ Correct |
| Risk of rename | N/A | ✅ Zero risk (no git, no deploy) |
| Effort | None | 2 minutes |
| **Recommendation** | | **✅ Rename now** |

> **Bottom line:** Rename now. It costs 2 minutes and prevents permanent confusion. This is the only window where the rename has zero risk — once Git, deploy scripts, and CI/CD are configured, renaming becomes much more involved.
