# CHINH-RAVAOCONG - Chỉnh sửa Phiếu mang hàng ra cổng

## 09:41 - Thêm Loại đối tác, Hàng hóa ajax, Phương tiện combobox

### 1) Mục tiêu
- Thêm "Loại đối tác" (KH / NCC / Khác) vào phần THÔNG TIN ĐỐI TÁC
- KH/NCC dùng combobox ajax select2 tìm kiếm; Khác dùng text nhập tay
- Hàng hóa dùng combobox ajax select2 từ `tbl_products`
- Phương tiện dùng combobox (không ajax) từ `tbl_transportation_vehicles`

### 2) Những file đã sửa
- `sql/alter_entrance_ticket_partner.sql` — **[NEW]** Migration thêm cột `partner_type`, `partner_id`, `item_product_id` vào `tbl_entrance_ticket`
- `application/controllers/admin/Entrance_ticket.php` — Cập nhật `detail()` + thêm 3 methods AJAX: `searchClients()`, `searchSuppliers()`, `searchProducts()`
- `application/views/admin/entrance_ticket/detail.php` — Viết lại giao diện form với radio loại đối tác, select2 động, combobox phương tiện

### 3) API/Endpoint
- `GET admin/entrance_ticket/searchClients[/{id}]`
  - Params: `term`, `limit`
  - Response: `{results:[{id, text}]}` hoặc `{row:{id, text}}`
- `GET admin/entrance_ticket/searchSuppliers[/{id}]`
  - Params: `term`, `limit`
  - Response: `{results:[{id, text}]}` hoặc `{row:{id, text}}`
- `GET admin/entrance_ticket/searchProducts[/{id}]`
  - Params: `term`, `limit`
  - Response: `{results:[{id, text}]}` hoặc `{row:{id, text}}`

### 4) Logic chính & lưu ý
- `partner_type` = 1 (KH) / 2 (NCC) / 3 (Khác) — lưu trong hidden input
- `partner_id` = userid (KH) hoặc id (NCC) — lưu trong hidden input
- `partner_name` = tên đối tác — với KH/NCC tự động lấy tên từ DB khi save; Khác lấy text nhập tay
- `item_product_id` = id sản phẩm từ `tbl_products`; `item_code_name` = tên hiển thị (name="item_code_name" trực tiếp trên thẻ select)
- `vehicle_type` lưu `name` của phương tiện (không lưu `id`)
- Khi toggle loại đối tác: ẩn/hiện các vùng tương ứng, reset `partner_id`

### 5) Việc còn dang dở / Bước làm tiếp theo
- [ ] Chạy SQL migration `sql/alter_entrance_ticket_partner.sql` trên DB
- [ ] Kiểm tra view `view.php` và `print.php` có cần hiển thị loại đối tác không
- [ ] Cập nhật `exportExcel()` nếu cần xuất thêm cột mới

### 6) Cách test nhanh
- Case 1: Tạo phiếu → chọn "Khách hàng" → gõ tên → chọn → save → DB có `partner_type=1`, `partner_id` đúng
- Case 2: Chọn "Nhà cung cấp" → tương tự
- Case 3: Chọn "Khác" → tự nhập tên → DB có `partner_type=3`, `partner_name` đúng, `partner_id=0`
- Case 4: Hàng hóa → gõ từ khóa → chọn sản phẩm → `item_product_id` ghi đúng
- Case 5: Phương tiện → dropdown hiện danh sách từ `tbl_transportation_vehicles`
- Lưu ý: Phải chạy SQL migration trước khi test

---

## 10:50 - Thêm Vị trí Phiếu mang ra vào cổng

### 1) Mục tiêu
- Quản lý các "Vị trí" (ví dụ: QA, BV) bằng cách nhóm các Role hệ thống.
- Lọc danh sách phiếu trên trang chủ theo Vị trí được chọn (lọc theo Role của người sở hữu phiếu).
- Hỗ trợ chọn nhiều Vị trí cùng lúc khi lọc.

### 2) Những file đã sửa/tạo mới
- `sql/create_entrance_ticket_locations.sql` — **[NEW]** Tạo bảng `tbl_entrance_ticket_locations`.
- `application/controllers/admin/Entrance_ticket.php` — Thêm CRUD vị trí (`locations`, `location_detail`, `delete_location`) + tích hợp lọc vào `getTable` và `exportExcel`.
- `application/views/admin/entrance_ticket/index.php` — Thêm bộ lọc Vị trí (selectpicker multiple) và nút Quản lý vị trí cho Admin.
- `application/views/admin/entrance_ticket/locations/index.php` — **[NEW]** Giao diện danh sách vị trí.
- `application/views/admin/entrance_ticket/locations/detail.php` — **[NEW]** Modal thêm/sửa vị trí (chọn Roles).

### 3) Logic lọc
- Vị trí được định nghĩa là một danh sách `role_ids`.
- Khi chọn Vị trí trên bộ lọc, hệ thống lấy tất cả `role_ids` thuộc các Vị trí đó và lọc các phiếu có `id_staff` (nhân viên phụ trách) thuộc các vai trò này.

### 4) Cách sử dụng
- Bước 1: Admin vào "Quản lý vị trí" (nút bánh răng trên trang chủ).
- Bước 2: Thêm mới hoặc sửa vị trí "QA", chọn các Role tương ứng là QA. Làm tương tự cho "BV".
- Bước 3: Ra trang chủ, chọn "QA" ở bộ lọc Vị trí -> bấm Tìm kiếm.

---

## 11:45 - Tính năng Gửi Email Tự động

### 1) Mục tiêu
- Tự động gửi email thông báo khi có phiếu mới được tạo, chỉnh sửa hoặc thay đổi trạng thái. Đảm bảo nhân viên phụ trách và các nhân viên liên quan (QA, BV) nhận được thông tin kịp thời.

### 2) Những file đã sửa/tạo mới
- `sql/create_entrance_ticket_emails.sql` — **[NEW]** Tạo bảng `tbl_entrance_ticket_emails` để lưu trữ lịch sử và trạng thái gửi email.
- `application/models/Entrance_ticket_mail_model.php` — **[NEW]** Model chịu trách nhiệm fetch danh sách email theo Vị trí (Location) và gọi thư viện form Email để gửi, đồng thời lưu log.
- `application/controllers/admin/Entrance_ticket.php` — **[MODIFY]** Tích hợp việc load model `Entrance_ticket_mail_model` và gọi các hàm `send_created_email()`, `send_edited_email()`, `send_approve_reject_email()` tại các function tương ứng.

### 3) Logic gửi mail
- **Tạo/Sửa phiếu**: Gửi email cho người phụ trách + các nhân viên thuộc nhóm vị trí QA và BV.
- **Xác nhận/Phê duyệt**: Gửi email cho người phụ trách + nhân viên thuộc nhóm vị trí **tiếp theo** trong luồng (vd: QA duyệt đi -> gửi BV; BV duyệt ra -> gửi BV hoặc QA...).
- **Từ chối**: Chỉ gửi thông báo từ chối cho người phụ trách.
- Danh sách email của nhóm QA/BV được fetch trực tiếp từ bảng mapping `tbl_entrance_ticket_location_roles`.
