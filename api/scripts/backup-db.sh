#!/usr/bin/env bash
set -euo pipefail

DB_PATH="/var/data/partai/database.sqlite"
BACKUP_DIR="/var/data/partai/backups"
TIMESTAMP="$(date +%Y%m%d-%H%M%S)"
BACKUP_PATH="${BACKUP_DIR}/database-${TIMESTAMP}.sqlite"
TEMP_PATH="${BACKUP_DIR}/.database-${TIMESTAMP}-$$.sqlite.tmp"

if ! command -v sqlite3 >/dev/null 2>&1; then
    echo "Error: sqlite3 is required to create a database backup." >&2
    exit 1
fi

if [[ ! -f "$DB_PATH" ]]; then
    echo "Error: database file not found: $DB_PATH" >&2
    exit 1
fi

if [[ -e "$BACKUP_PATH" ]]; then
    echo "Error: backup already exists: $BACKUP_PATH" >&2
    exit 1
fi

umask 077
mkdir -p "$BACKUP_DIR"
trap 'rm -f -- "$TEMP_PATH"' EXIT

sqlite3 "$DB_PATH" ".backup '$TEMP_PATH'"

integrity_check="$(sqlite3 "$TEMP_PATH" 'PRAGMA integrity_check;')"
if [[ "$integrity_check" != "ok" ]]; then
    echo "Error: backup integrity check failed: $integrity_check" >&2
    exit 1
fi

mv -- "$TEMP_PATH" "$BACKUP_PATH"
echo "Backup created and verified: $BACKUP_PATH"

find "$BACKUP_DIR" -type f -name 'database-*.sqlite' -mmin +10080 -print -delete
