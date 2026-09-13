# Quy tắc ghi README theo ngày (Daily Notes)

## Mục đích
- Ghi lại tiến độ theo ngày để lần sau mở repo đọc là tiếp tục làm được ngay.
- Tập trung vào: mục tiêu, file đã sửa, logic chính, cách test, việc còn dang dở.

## Quy tắc bắt buộc
1) Sau khi hoàn thành mỗi task, **tạo hoặc cập nhật** file ghi chú trong thư mục `/readme`.
2) Nếu chưa có thư mục `/readme` thì **tự tạo**.
3) Tên file theo ngày: `{CONGVIEC}-YYYY-MM-DD.md` (theo timezone **Asia/Ho_Chi_Minh**).
4) Nếu file của ngày hôm nay đã tồn tại thì **KHÔNG ghi đè**:
   - **Append** thêm 1 section mới có timestamp `HH:mm`.
5) Nội dung phải bám sát code thực tế:
   - Liệt kê đúng **path file** đã chỉnh.
   - Nếu có suy đoán do thiếu thông tin thì ghi rõ **(giả định)**.

---

## Format section trong file theo ngày

Mỗi lần hoàn thành 1 task, thêm section theo mẫu:

## HH:mm - <Tên task ngắn>
### 1) Mục tiêu
- ...

### 2) Những file đã sửa
- `path/to/file.ext` — mô tả ngắn thay đổi
- `path/to/another.ext` — mô tả ngắn thay đổi

### 3) API/Endpoint (nếu có)
- `METHOD /api/...`
  - Params:
    - `param1`: ...
    - `param2`: ...
  - Response mẫu:
    - ...

### 4) Logic chính & lưu ý
- Mô tả luồng xử lý chính, các điểm quan trọng
- Nếu có AJAX / selectAjax / filter / pagination… ghi rõ:
  - Trigger ở đâu
  - Gọi hàm nào
  - Format data đầu vào/đầu ra

### 5) Việc còn dang dở / Bước làm tiếp theo
- [ ] ...
- [ ] ...

### 6) Cách test nhanh (case test)
- Case 1: ...
- Case 2: ...
- Lưu ý: ...