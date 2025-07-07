# Các agent trong NamaHealing

Tài liệu này tóm tắt ngắn gọn các thành phần chính trong hệ thống.

## 1. Học viên (student)
- Đăng nhập tại `login.php`; sau khi đăng nhập thành công sẽ được chuyển đến `dashboard.php`.
- Trên dashboard hiển thị số buổi còn lại và lịch sử gần nhất.
- Tham gia lớp qua `join.php?s=morning` hoặc `join.php?s=evening`.
  * Nếu `users.remaining` > 0, hệ thống trừ 1, ghi vào bảng `sessions` rồi đọc URL từ `zoom_links` để chuyển hướng.
  * Nếu đã hết buổi, trang thông báo hướng dẫn liên hệ admin.
- Có thể đăng xuất qua `logout.php`.

## 2. Quản trị viên (admin)
- Đăng nhập bằng tài khoản có `role='admin'`.
- `admin.php` liệt kê học viên, cho phép lọc theo tên/email và tình trạng còn/hết buổi.
- Thêm học viên ở `register.php`.
- Cộng buổi bằng `add_sessions.php`; xóa học viên qua `delete_user.php`.
- Xem lịch sử tham gia của từng học viên tại `history.php`.
- Quản lý bài viết và video ở `admin_panel.php` (lưu tại `data/articles.json`, `data/videos.json`).

## 3. Session Manager
- Dùng PHP session. Mọi file yêu cầu đăng nhập đều định nghĩa `REQUIRE_LOGIN` và gọi `config.php`:
  ```php
  session_start();
  if (defined('REQUIRE_LOGIN') && !isset($_SESSION['uid'])) {
      header('Location: login.php');
      exit;
  }
