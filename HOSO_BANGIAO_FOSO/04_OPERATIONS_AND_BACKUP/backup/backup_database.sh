#!/bin/bash
# ==============================================================================
# FOSO ERP - DATABASE BACKUP SCRIPT (BASH LINUX)
# ==============================================================================

DB_USER="root"
DB_PASS=""
DB_NAME="foso_erp"
DB_HOST="127.0.0.1"

TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="/var/backups/foso"
RETENTION_DAYS=30

mkdir -p "$BACKUP_DIR"

DUMP_FILE="$BACKUP_DIR/foso_db_backup_$TIMESTAMP.sql.gz"

echo "[$TIMESTAMP] Bắt đầu sao lưu CSDL $DB_NAME..."

if [ -z "$DB_PASS" ]; then
    mysqldump -h "$DB_HOST" -u "$DB_USER" --single-transaction --routines --triggers "$DB_NAME" | gzip > "$DUMP_FILE"
else
    mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" --single-transaction --routines --triggers "$DB_NAME" | gzip > "$DUMP_FILE"
fi

if [ -f "$DUMP_FILE" ]; then
    echo "[$TIMESTAMP] [THÀNH CÔNG] Đã tạo bản sao lưu: $DUMP_FILE ($(du -h "$DUMP_FILE" | cut -f1))"
    # Dọn dẹp backup cũ
    find "$BACKUP_DIR" -type f -name "*.sql.gz" -mtime +$RETENTION_DAYS -exec rm -f {} \;
    echo "[$TIMESTAMP] Đã dọn dẹp các bản sao lưu cũ hơn $RETENTION_DAYS ngày."
else
    echo "[$TIMESTAMP] [LỖI] Không tạo được tệp sao lưu CSDL."
fi
