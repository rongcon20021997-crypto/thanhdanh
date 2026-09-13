# ==============================================================================
# FOSO ERP - DATABASE BACKUP SCRIPT (POWERSHELL WINDOWS)
# Tự động kết xuất CSDL, nén file và xoay vòng xóa bản sao lưu cũ > 30 ngày
# ==============================================================================

$DB_USER = "root"
$DB_PASS = ""
$DB_NAME = "foso_erp"
$DB_HOST = "127.0.0.1"

$TIMESTAMP = Get-Date -Format "yyyyMMdd_HHmmss"
$BACKUP_DIR = "e:\03121F\backups"
$RETENTION_DAYS = 30

if (-not (Test-Path $BACKUP_DIR)) {
    New-Item -ItemType Directory -Path $BACKUP_DIR -Force | Out-Null
}

$DUMP_FILE = "$BACKUP_DIR\foso_db_backup_$TIMESTAMP.sql"

Write-Host "[$TIMESTAMP] Bắt đầu sao lưu CSDL $DB_NAME..."

# Tìm đường dẫn mysqldump
$MYSQLDUMP = "mysqldump"
if (Test-Path "C:\xampp\mysql\bin\mysqldump.exe") {
    $MYSQLDUMP = "C:\xampp\mysql\bin\mysqldump.exe"
}

try {
    if ($DB_PASS -eq "") {
        & $MYSQLDUMP -h $DB_HOST -u $DB_USER --single-transaction --routines --triggers $DB_NAME > $DUMP_FILE
    } else {
        & $MYSQLDUMP -h $DB_HOST -u $DB_USER -p$DB_PASS --single-transaction --routines --triggers $DB_NAME > $DUMP_FILE
    }

    if (Test-Path $DUMP_FILE) {
        $FILE_SIZE = (Get-Item $DUMP_FILE).Length / 1MB
        Write-Host "[$TIMESTAMP] [THÀNH CÔNG] Đã tạo bản sao lưu: $DUMP_FILE ($([Math]::Round($FILE_SIZE, 2)) MB)"
        
        # Xóa bản sao lưu cũ hơn 30 ngày
        Get-ChildItem -Path $BACKUP_DIR -Filter "*.sql" | Where-Object { $_.LastWriteTime -lt (Get-Date).AddDays(-$RETENTION_DAYS) } | Remove-Item -Force
        Write-Host "[$TIMESTAMP] Đã dọn dẹp các bản sao lưu cũ hơn $RETENTION_DAYS ngày."
    } else {
        Write-Host "[$TIMESTAMP] [LỖI] Không tạo được tệp sao lưu CSDL."
    }
} catch {
    Write-Host "[$TIMESTAMP] [ERROR] Lỗi thực thi sao lưu: $_"
}
