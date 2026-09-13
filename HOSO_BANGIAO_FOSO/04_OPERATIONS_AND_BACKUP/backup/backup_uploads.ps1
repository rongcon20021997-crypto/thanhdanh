# ==============================================================================
# FOSO ERP - UPLOADS BACKUP SCRIPT (POWERSHELL WINDOWS)
# Nén toàn bộ thư mục uploads/ và media/ sang tệp lưu trữ .zip
# ==============================================================================

$TIMESTAMP = Get-Date -Format "yyyyMMdd_HHmmss"
$BACKUP_DIR = "e:\03121F\backups"
$SOURCE_UPLOADS = "e:\03121F\uploads"
$SOURCE_MEDIA = "e:\03121F\media"

$ZIP_FILE = "$BACKUP_DIR\foso_uploads_$TIMESTAMP.zip"

Write-Host "[$TIMESTAMP] Bắt đầu nén thư mục uploads & media..."

try {
    # Nén bằng .NET ZipFile
    Add-Type -AssemblyName "System.IO.Compression.FileSystem"
    
    $tempFolder = "$BACKUP_DIR\temp_archive_$TIMESTAMP"
    New-Item -ItemType Directory -Path $tempFolder -Force | Out-Null
    
    if (Test-Path $SOURCE_UPLOADS) {
        Copy-Item -Path "$SOURCE_UPLOADS" -Destination "$tempFolder\uploads" -Recurse -Force
    }
    if (Test-Path $SOURCE_MEDIA) {
        Copy-Item -Path "$SOURCE_MEDIA" -Destination "$tempFolder\media" -Recurse -Force
    }
    
    [System.IO.Compression.ZipFile]::CreateFromDirectory($tempFolder, $ZIP_FILE)
    Remove-Item -Path $tempFolder -Recurse -Force
    
    Write-Host "[$TIMESTAMP] [THÀNH CÔNG] Đã tạo gói sao lưu file: $ZIP_FILE"
} catch {
    Write-Host "[$TIMESTAMP] [ERROR] Lỗi nén file: $_"
}
