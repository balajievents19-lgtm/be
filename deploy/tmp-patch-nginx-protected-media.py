from pathlib import Path

# Balaji Royal Events only. Do not touch Fageriya nginx.
conf = Path("/etc/nginx/sites-available/balajiroyalevents.conf")
backup = Path("/etc/nginx/sites-available/balajiroyalevents.conf.bak-protected-media-20260923")

text = conf.read_text()
if not backup.exists():
    backup.write_text(text)

storage_block = """    # Original CMS files are gated by Laravel (admin session). Never static-serve them.
    location /storage/ {
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_pass http://127.0.0.1:8000;
        proxy_read_timeout 60s;
    }
"""

media_block = """    # Public display images (tokenized). Keep /media (Nuxt videos page) on Nuxt.
    location /protected-media/ {
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_pass http://127.0.0.1:8000;
        proxy_read_timeout 60s;
    }
"""

import re

text2 = re.sub(
    r"\n    location /storage[^\n]*\{.*?\n    \}\n",
    "\n" + storage_block,
    text,
    count=1,
    flags=re.S,
)

if text2 == text:
    if "location /storage/" in text and "proxy_pass http://127.0.0.1:8000" in text:
        print("storage location already proxied?")
    else:
        raise SystemExit("could not replace location /storage")

text = text2

if "location /protected-media/" not in text:
    needle = "    location / {"
    if needle not in text:
        raise SystemExit("needle location / missing")
    text = text.replace(needle, media_block + "\n" + needle, 1)

conf.write_text(text)
print("wrote", conf)
print("has protected-media", "location /protected-media/" in conf.read_text())
print("storage try_files gone", "location /storage" in conf.read_text() and "try_files $uri =404;" not in conf.read_text())
