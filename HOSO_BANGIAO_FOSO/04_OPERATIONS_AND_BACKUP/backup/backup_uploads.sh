#!/bin/bash
# ==============================================================================
# FOSO ERP - UPLOADS BACKUP SCRIPT (BASH LINUX)
# ==============================================================================

TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="/var/backups/foso"
SOURCE_UPLOADS="/var/www/html/uploads"
SOURCE_MEDIA="/var/www/html/media"

mkdir -p "$BACKUP_DIR"
ARCHIVE_FILE="$BACKUP_DIR/foso_uploads_$TIMESTAMP.tar.gz"

echo "[$TIMESTAMP] Bắt đầu nén thư mục uploads & media..."

tar -czf "$ARCHIVE_FILE" -C "/var/www/html" uploads media 2>/dev/null

if [ -f "$ARCHIVE_FILE" ]; then
    echo "[$TIMESTAMP] [THÀNH CÔNG] Đã tạo gói sao lưu file: $ARCHIVE_FILE ($(du -h "$ARCHIVE_FILE" | cut -f1))"
else
    echo "[$TIMESTAMP] [LỖI] Không tạo được tệp nén uploads."
fi
