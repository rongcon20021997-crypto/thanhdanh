Task: Bổ xung thêm “Gửi thông báo phiếu mang hàng ra vào cổng”.

Yêu cầu:
1) Phiếu mang hàng ra cổng file `admin/Entrance_ticket.php`
2) Thêm mục gửi mail tự động những tác vụ sau
 - Gửi mail cho nhân viên phụ trách khi tạo phiếu mới và nhân viên trong vị trí QA và BV
 - Gửi mail cho nhân viên phụ trách khi phiếu được sửa và nhân viên trong vị trí QA và BV
 - Khi phê duyệt xác nhận, không duyệt gửi mail cho nhân viên phụ trách và nhân viên trong vị trí QA và BV tiếp theo

3) Nội dung mail phải thể hiện được nội dung cần thiết cửa từng hành động và có link để link vào phần mềm và mở phiếu lên đúng phiếu của mình
4) Tạo cho tôi 1 model riêng để xử lý gửi mail
5) Tạo tôi sql để lưu những mail đã gửi trong phiếu và kết quả gửi mail thành công không
6) Có thể tham khảo hàm gửi mail trong file `application/controllers/Cron_approval.php`

README:
- Áp dụng quy tắc ghi README theo ngày trong /readme/README_RULES.md
  (tạo/cập nhật /readme/CHINH-RAVAOCONG-YYYY-MM-DD.md và append section mới nếu file đã có)