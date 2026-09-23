#!/usr/bin/env bash
# Balaji Royal Events — production deploy on this VPS only.
# Restarts balaji-laravel / balaji-nuxt. Never touches Fageriya, port 3001, Nginx site files, DNS, or SSL.
set -euo pipefail

APP_ROOT="/var/www/balajiroyalevents/current"
APP_USER="deploy"
HOST_HEADER="www.balajiroyalevents.com"
# Local 127.0.0.1:80 is Fageriya's bind — do not use it for Balaji Nginx checks.
PUBLIC_HTTP="http://187.127.166.231"
PUBLIC_HTTPS="https://www.balajiroyalevents.com"
LARAVEL_PORT="8000"
NUXT_PORT="3002"
PROTECTED_PORT="3001"

log() { printf '%s %s\n' "$(date -u +%Y-%m-%dT%H:%M:%SZ)" "$*"; }
die() { printf 'ERROR: %s\n' "$*" >&2; exit 1; }

[[ -d "$APP_ROOT" ]] || die "missing $APP_ROOT"
cd "$APP_ROOT"

if [[ "$(id -un)" == "root" ]]; then
  as_app() { sudo -u "$APP_USER" -H -- "$@"; }
  sys() { systemctl "$@"; }
elif [[ "$(id -un)" == "$APP_USER" ]]; then
  as_app() { "$@"; }
  sys() { sudo -n systemctl "$@"; }
else
  die "run as root or ${APP_USER}"
fi

[[ -f backend/.env ]] || die "production backend/.env is missing — aborting to protect CMS"
[[ -d backend/storage/app/public ]] || die "production storage/app/public is missing — aborting"
[[ -f frontend/package.json ]] || die "correct Nuxt app frontend/ is missing"
[[ -f backend/artisan ]] || die "Laravel artisan is missing"
[[ ! -f /etc/nginx/sites-enabled/fageriya ]] || true

ENV_INODE_BEFORE="$(ls -i backend/.env | awk '{print $1}')"
STORAGE_INODE_BEFORE="$(ls -id backend/storage/app/public | awk '{print $1}')"
FAGERIA_PID_BEFORE="$(systemctl show -p MainPID --value fageriya-frontend.service 2>/dev/null || echo 0)"
PORT_3001_BEFORE="$(ss -lptn | grep -F "127.0.0.1:${PROTECTED_PORT}" || true)"

log "HEAD before=$(git rev-parse --short HEAD)"
as_app git fetch origin
as_app git checkout main
as_app git merge --ff-only origin/main
log "HEAD after=$(git rev-parse --short HEAD) ($(git log -1 --oneline))"

[[ -f backend/.env ]] || die "backend/.env disappeared after git update"
ENV_INODE_AFTER="$(ls -i backend/.env | awk '{print $1}')"
STORAGE_INODE_AFTER="$(ls -id backend/storage/app/public | awk '{print $1}')"
[[ "$ENV_INODE_BEFORE" == "$ENV_INODE_AFTER" ]] || die "backend/.env inode changed — refusing to continue"
[[ "$STORAGE_INODE_BEFORE" == "$STORAGE_INODE_AFTER" ]] || die "storage/app/public inode changed — refusing to continue"

chmod +x "$APP_ROOT/deploy/deploy.sh" 2>/dev/null || true

if [[ "$(id -un)" == "root" && -f "$APP_ROOT/deploy/sudoers.balaji-deploy" ]]; then
  visudo -c -f "$APP_ROOT/deploy/sudoers.balaji-deploy" >/dev/null
  install -m 440 "$APP_ROOT/deploy/sudoers.balaji-deploy" /etc/sudoers.d/balaji-deploy
fi

log "Laravel composer + migrate + caches"
cd "$APP_ROOT/backend"
as_app /usr/bin/composer install --no-dev --optimize-autoloader --no-interaction
as_app /usr/bin/php artisan migrate --force
# Image protection: do not expose storage/app/public through a web symlink.
# Laravel serves display images at /protected-media and gates /storage for admin.
if [[ -L public/storage ]]; then
  rm -f public/storage
  log "removed public/storage symlink (CMS files are Laravel-gated)"
elif [[ -e public/storage ]]; then
  log "WARNING: public/storage exists and is not a symlink — not removing"
fi
as_app /usr/bin/php artisan optimize

log "Nuxt frontend build (frontend/ only)"
cd "$APP_ROOT/frontend"
export NUXT_PUBLIC_API_BASE="/api"
export NUXT_API_INTERNAL_BASE="http://127.0.0.1:${LARAVEL_PORT}/api"
as_app env NUXT_PUBLIC_API_BASE="/api" NUXT_API_INTERNAL_BASE="http://127.0.0.1:${LARAVEL_PORT}/api" \
  /usr/local/bin/pnpm install --frozen-lockfile
as_app env NUXT_PUBLIC_API_BASE="/api" NUXT_API_INTERNAL_BASE="http://127.0.0.1:${LARAVEL_PORT}/api" \
  /usr/local/bin/pnpm build > /tmp/balaji-frontend-build.log 2>&1 || { tail -n 80 /tmp/balaji-frontend-build.log >&2; die "frontend pnpm build failed"; }
[[ -f .output/server/index.mjs ]] || die "frontend/.output/server/index.mjs missing after build"

log "Restart ONLY Balaji services"
sys restart balaji-laravel.service
sys restart balaji-nuxt.service

ok_laravel=0
ok_nuxt=0
for _ in $(seq 1 30); do
    ok_laravel=0
    if sys is-active balaji-laravel.service >/dev/null && curl -fsS --max-time 10 -o /dev/null "http://127.0.0.1:${LARAVEL_PORT}/up" >/dev/null 2>&1; then
      ok_laravel=1
    fi
    ok_nuxt=0
    if sys is-active balaji-nuxt.service >/dev/null && curl -fsS --max-time 15 -o /dev/null "http://127.0.0.1:${NUXT_PORT}/" >/dev/null 2>&1; then
      ok_nuxt=1
    fi
  if [[ "$ok_laravel" == 1 && "$ok_nuxt" == 1 ]]; then
    break
  fi
  sleep 2
done

sys is-active balaji-laravel.service >/dev/null || die "balaji-laravel.service is not active"
sys is-active balaji-nuxt.service >/dev/null || die "balaji-nuxt.service is not active"
[[ "$ok_laravel" == 1 ]] || die "Laravel :${LARAVEL_PORT} health check failed"
[[ "$ok_nuxt" == 1 ]] || die "Nuxt :${NUXT_PORT} health check failed"

ss -lptn | grep -Fq ":${LARAVEL_PORT}" || die "nothing listening on ${LARAVEL_PORT}"
ss -lptn | grep -Fq ":${NUXT_PORT}" || die "nothing listening on ${NUXT_PORT}"

API_FILE="$(mktemp /tmp/balaji-api-home.XXXXXX.json)"
HOME_FILE="$(mktemp /tmp/balaji-home.XXXXXX.html)"
trap 'rm -f "$API_FILE" "$HOME_FILE"' EXIT
export API_FILE HOME_FILE

API_CODE="$(curl -sS -o "$API_FILE" -w '%{http_code}' "${PUBLIC_HTTPS}/api/home")"
[[ "$API_CODE" == "200" ]] || die "/api/home via Nginx returned ${API_CODE}"

HOME_CODE="$(curl -sS -o "$HOME_FILE" -w '%{http_code}' "${PUBLIC_HTTPS}/")"
[[ "$HOME_CODE" == "200" ]] || die "homepage via Nginx returned ${HOME_CODE}"

if grep -Eq 'https?://(127\.0\.0\.1|localhost):8000/storage/' "$HOME_FILE" "$API_FILE"; then
  die "production HTML/API still points CMS images at loopback :8000"
fi

python3 - <<'PY'
import json, os, re, sys, urllib.request
from pathlib import Path

html = Path(os.environ["HOME_FILE"]).read_text(encoding="utf-8", errors="ignore")
api = Path(os.environ["API_FILE"]).read_text(encoding="utf-8", errors="ignore")
blob = html + "\n" + api.replace("\\/", "/")

if "http://127.0.0.1:8000/storage/" in blob or "http://localhost:8000/storage/" in blob:
    print("loopback storage URLs found", file=sys.stderr)
    sys.exit(1)

paths = re.findall(r"/storage/[A-Za-z0-9_./-]+", blob)
# Prefer real CMS images over query-string noise
uniq = []
for p in paths:
    if p not in uniq:
        uniq.append(p)

required_needles = {
    "hero": ("hero-slides",),
    "logo": ("settings/brand",),
    "gallery_cover": ("gallery/thumbnails", "gallery/previews"),
    "service": ("services/",),
}
missing = [name for name, parts in required_needles.items() if not any(any(part in p for part in parts) for p in uniq)]
if missing:
    print("missing expected image kinds in payload:", ",".join(missing), file=sys.stderr)
    print("found:", " ".join(uniq[:20]), file=sys.stderr)
    sys.exit(1)

opener = urllib.request.build_opener()
fail = []
checked = []
for path in uniq[:12]:
    req = urllib.request.Request(
        "https://www.balajiroyalevents.com" + path,
        headers={},
        method="GET",
    )
    try:
        with opener.open(req, timeout=20) as res:
            code = res.getcode()
            ctype = res.headers.get("Content-Type", "")
            if code != 200 or "image" not in ctype.lower():
                fail.append(f"{path} {code} {ctype}")
            else:
                checked.append(path)
    except Exception as exc:
        fail.append(f"{path} {exc}")

if fail:
    print("image HTTP failures:", file=sys.stderr)
    print("\n".join(fail), file=sys.stderr)
    sys.exit(1)
if not checked:
    print("no /storage images could be requested", file=sys.stderr)
    sys.exit(1)
print("storage_ok", len(checked))
PY

FAGERIA_PID_AFTER="$(systemctl show -p MainPID --value fageriya-frontend.service 2>/dev/null || echo 0)"
PORT_3001_AFTER="$(ss -lptn | grep -F "127.0.0.1:${PROTECTED_PORT}" || true)"
if [[ "$FAGERIA_PID_BEFORE" != "0" ]]; then
  [[ "$FAGERIA_PID_BEFORE" == "$FAGERIA_PID_AFTER" ]] || die "fageriya-frontend PID changed"
  systemctl is-active --quiet fageriya-frontend.service || die "fageriya-frontend stopped"
fi
[[ "$PORT_3001_BEFORE" == "$PORT_3001_AFTER" ]] || die "port ${PROTECTED_PORT} listener changed"

log "SUCCESS commit=$(git rev-parse HEAD)"
