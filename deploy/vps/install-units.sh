#!/usr/bin/env bash
# VPS引越し用: deploy/vps/systemd の unit と drop-in を /etc/systemd/system へ配置し、タイマーを有効化する。
# 注意: 新品のVPSでの通し試験は未実施。実行前に内容を確認すること。秘密(.env.*)は別途配置が必要(PORTING.md参照)。
# Install the systemd units/drop-ins from deploy/vps/systemd and enable the timers. NOT yet tested on a fresh VPS.
set -euo pipefail
SRC="$(cd "$(dirname "${BASH_SOURCE[0]}")/systemd" && pwd)"
DST=/etc/systemd/system
[ "$(id -u)" = 0 ] || { echo "run as root"; exit 1; }
cp -v "$SRC"/*.service "$SRC"/*.timer "$DST"/
for d in "$SRC"/*.service.d; do
  [ -d "$d" ] || continue
  mkdir -p "$DST/$(basename "$d")"
  cp -v "$d"/*.conf "$DST/$(basename "$d")"/
done
systemctl daemon-reload
# 先に binary/.env を配置してから有効化すること / enable only after binaries and .env files are in place
for t in audiocafe-cron-rust.timer audiocafe-cron-php.timer knowledge-sync-push.timer; do systemctl enable --now "$t"; done
for s in aruaru-llm.service audiocafe-tokyo-rust.service audiocafe-php-legacy.service aruaru.tokyo.service; do systemctl enable "$s" || true; done
echo "done. Start services after placing binaries/.env: systemctl start aruaru-llm audiocafe-tokyo-rust aruaru.tokyo"
