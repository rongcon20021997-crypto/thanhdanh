Task: Chỉnh sửa “Phiếu mang hàng ra cổng”.

Yêu cầu:
1) Phiếu mang hàng ra cổng file `admin/Entrance_ticket.php`
2) Tạo + sửa lại phiếu mang hàng ra công
 - THÔNG TIN ĐỐI TÁC & NGƯỜI THỰC HIỆN
  + Thêm loại đối tác (1: khách hàng, 2: Nhà cung cấp, 3: Đối tác khác)
  + Nếu khách hàng Tên đối tác / KH / NCC hiện combobox khách hàng sài ajax select2 có thể tham khảo trong `core.js` hàm `ajaxSelectParamsCallback`, NCC hiện combobox nhà cung cấp và khác thì hiện text cho tự nhập
  + Hàng hóa lấy trong `tbl_products` combobox sài `ajaxSelectParamsCallback`
  + Phương tiện trong `transportation_vehicles` sài combobox không cần ajax
3) Tạo database những trường chưa có trong folder sql
4) Chỉnh thêm, sửa trước cho tôi, nếu có gì sai sót thì tôi sẽ báo lại để chỉnh tiếp

README:
- Áp dụng quy tắc ghi README theo ngày trong /readme/README_RULES.md
  (tạo/cập nhật /readme/CHINH-RAVAOCONG-YYYY-MM-DD.md và append section mới nếu file đã có)