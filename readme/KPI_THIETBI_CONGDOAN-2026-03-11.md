# KPI_THIETBI_CONGDOAN - 2026-03-11

## 09:54 - Bổ sung tính năng KPI Thiết Bị Công Đoạn

### 1) Mục tiêu
- Bổ sung module quản lý KPI thiết bị theo công đoạn sản xuất
- Dữ liệu gồm 27 cột theo file Excel `KPI_ThietBi_CongDoan_DayDu.xlsx`
- Hỗ trợ: danh sách DataTable, thêm/sửa/xóa từng bản ghi, import hàng loạt từ Excel

### 2) Những file đã tạo

| File | Mô tả |
|---|---|
| `sql/tbl_kpi_thietbi_congdoan.sql` | Script tạo bảng CSDL (tên cột tiếng Anh, comment tiếng Việt) |
| `application/models/Kpi_thietbi_congdoan_model.php` | Model CRUD cơ bản (get, add, update, delete) |
| `application/controllers/admin/Kpi_thietbi_congdoan.php` | Controller: index, get_list, detail, delete, import_kpi |
| `application/views/admin/kpi_thietbi_congdoan/kpi_thietbi_congdoan.php` | View danh sách DataTable 29 cột |
| `application/views/admin/kpi_thietbi_congdoan/detail_kpi_thietbi_congdoan.php` | Form thêm/sửa (5 nhóm fieldset) |
| `application/views/admin/kpi_thietbi_congdoan/import_kpi_thietbi_congdoan.php` | Form import Excel |
| `uploads/import_c/import_kpi_thietbi_congdoan.xlsx` | File Excel mẫu (copy từ `excel/KPI_ThietBi_CongDoan_DayDu.xlsx`) |

### 3) API/Endpoint

- `GET  /admin/kpi_thietbi_congdoan` — Trang danh sách
- `POST /admin/kpi_thietbi_congdoan/get_list` — DataTable server-side AJAX
- `GET  /admin/kpi_thietbi_congdoan/detail` — Form thêm mới (modal)
- `GET  /admin/kpi_thietbi_congdoan/detail/{id}` — Form sửa (modal)
- `POST /admin/kpi_thietbi_congdoan/detail/{id}` — Submit thêm/sửa, trả JSON `{result, message}`
- `GET  /admin/kpi_thietbi_congdoan/delete/{id}` — Xóa (popover confirm), trả JSON
- `GET  /admin/kpi_thietbi_congdoan/import_kpi` — Trang import Excel (modal)
- `POST /admin/kpi_thietbi_congdoan/import_kpi` — Xử lý import, trả JSON `{success, message, errors}`

### 4) Logic chính & lưu ý

**CSDL:**
- Bảng `tbl_kpi_thietbi_congdoan` với 29 cột (id + 27 cột dữ liệu + created_by + date_created)
- Cột `bao_tri_dinh_ky` và `hieu_chuan`: TINYINT 1/0 tương ứng Y/N trong Excel
- Cột ngày `lan_bao_tri_gan_nhat`, `lan_hieu_chuan_gan_nhat`: DATE NULL
- **Cần chạy SQL** trong `sql/tbl_kpi_thietbi_congdoan.sql` để tạo bảng

**Import Excel:**
- Sử dụng PHPExcel (APPPATH . `third_party/PHPExcel/PHPExcel.php`)
- Dòng 1: header, dữ liệu từ dòng 2
- Cột A=STT (bỏ qua), B=NhomCongDoan, C=MaCongDoan, …, AA=GhiChu
- Ngày dạng Excel serial (is_numeric) được convert: `($val - 25569) * 86400`
- Bỏ qua dòng trống (MaCongDoan và MaThietBi đều rỗng)

**Danh sách:**
- Trạng thái thiết bị hiển thị label màu: Hoạt động=success, Ngừng=danger, Bảo trì=warning
- Scroll ngang (`scrollX: true`) vì bảng rộng 29 cột
- URL DataTable: `POST /admin/kpi_thietbi_congdoan/get_list`

**Quyền:**
- Dùng hệ thống `has_permission('kpi_thietbi_congdoan', '', 'view/create/edit/delete')` — cần cấu hình quyền trong hệ thống nếu chưa có

### 5) Việc còn dang dở / Bước làm tiếp theo
- [ ] Chạy SQL `sql/tbl_kpi_thietbi_congdoan.sql` để tạo bảng trong database
- [ ] Đăng ký menu/navigation cho trang danh sách (nếu dự án dùng menu động)
- [ ] Cấu hình quyền `kpi_thietbi_congdoan` trong hệ thống phân quyền
- [ ] Kiểm thử import với file Excel thực tế

### 6) Cách test nhanh

- **Test danh sách**: Truy cập `http://192.168.1.178/03121F/admin/kpi_thietbi_congdoan` — trang load, DataTable hiển thị
- **Test thêm mới**: Click Thêm → điền form → submit → kiểm tra bản ghi mới trong danh sách
- **Test import**: Click Import → chọn file `uploads/import_c/import_kpi_thietbi_congdoan.xlsx` → import → xuất hiện dữ liệu mẫu
- **Test xóa**: click dropdown actions → Delete → Confirm → bản ghi biến mất
- Lưu ý: Nếu lỗi `403 Access Denied`, cần cấu hình quyền `kpi_thietbi_congdoan` trong hệ thống

---

## 10:03 - Đổi tên sang tiếng Anh hoàn toàn (Rename to English)

### 1) Mục tiêu
- Đổi toàn bộ tên file, class, bảng DB, URL sang tiếng Anh
- Mapping: `kpi_thietbi_congdoan` → `kpi_equipment_stage`, `tbl_kpi_thietbi_congdoan` → `tbl_kpi_equipment_stage`

### 2) Những file đã thay đổi

| Cũ (tiếng Việt / hỗn hợp) | Mới (tiếng Anh hoàn toàn) |
|---|---|
| `sql/tbl_kpi_thietbi_congdoan.sql` → **XÓA** | `sql/tbl_kpi_equipment_stage.sql` ✅ |
| `models/Kpi_thietbi_congdoan_model.php` → **XÓA** | `models/Kpi_equipment_stage_model.php` ✅ |
| `controllers/admin/Kpi_thietbi_congdoan.php` → **XÓA** | `controllers/admin/Kpi_equipment_stage.php` ✅ |
| `views/admin/kpi_thietbi_congdoan/` → **XÓA** | `views/admin/kpi_equipment_stage/` ✅ |

**Tên cột DB** cũng đổi sang tiếng Anh hoàn toàn (xem `sql/tbl_kpi_equipment_stage.sql`):
- `nhom_cong_doan` → `group_stage`, `ma_cong_doan` → `stage_code`, `ten_cong_doan` → `stage_name`
- `ma_thiet_bi` → `equipment_code`, `ten_thiet_bi` → `equipment_name`, `trang_thai_thiet_bi` → `equipment_status`
- `thoi_gian_ngung_may` → `downtime_minutes`, `nguyen_nhan_ngung_may` → `downtime_reason`
- `so_lan_sua_chua` → `repair_count`, `thoi_gian_sua_chua` → `repair_minutes`
- `bao_tri_dinh_ky` → `periodic_maintenance`, `lan_bao_tri_gan_nhat` → `last_maintenance_date`
- `hieu_chuan` → `calibration`, `lan_hieu_chuan_gan_nhat` → `last_calibration_date`
- `npl_canh_bao` → `npl_warning_pct`, `so_loi` → `defect_count`, `ty_le_loi` → `defect_rate_pct`
- `nang_suat_dinh_muc` → `planned_output`, `san_luong_thuc_te` → `actual_output`, `ty_le_dat_kh` → `target_achievement_pct`
- `ngan_sach_thiet_bi` → `equipment_budget`, `chi_phi_sua_chua` → `repair_cost`, `chi_phi_bao_tri` → `maintenance_cost`, `tong_chi_phi` → `total_cost`
- `trang_thai_canh_bao` → `warning_status`, `ghi_chu` → `note`

### 3) API/Endpoint (mới)

- `GET  /admin/kpi_equipment_stage` — Trang danh sách
- `POST /admin/kpi_equipment_stage/get_list` — DataTable AJAX
- `GET  /admin/kpi_equipment_stage/detail/{id?}` — Thêm/sửa
- `POST /admin/kpi_equipment_stage/detail/{id?}` — Submit
- `GET  /admin/kpi_equipment_stage/delete/{id}` — Xóa
- `GET/POST /admin/kpi_equipment_stage/import_excel` — Import Excel

### 4) Logic chính & lưu ý
- Tên permission key đổi thành `kpi_equipment_stage`
- **Cần chạy SQL mới**: `sql/tbl_kpi_equipment_stage.sql`
- Method import Excel đổi từ `import_kpi()` → `import_excel()`

### 5) Việc còn dang dở
- [ ] Chạy SQL `sql/tbl_kpi_equipment_stage.sql` để tạo bảng mới
- [ ] Cấu hình quyền `kpi_equipment_stage` trong hệ thống phân quyền
- [ ] Đăng ký menu navigation

### 6) Test nhanh
- Truy cập `http://192.168.1.178/03121F/admin/kpi_equipment_stage`
