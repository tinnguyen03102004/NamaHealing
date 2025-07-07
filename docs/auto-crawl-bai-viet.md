# Tự động lấy metadata cho bài viết

Khi thêm bài viết trong `admin_panel.php`, admin chỉ cần nhập link gốc và bấm **Lấy dữ liệu**.
Hệ thống gọi `fetch_meta.php` để lấy các thẻ Open Graph của trang và tự điền:

- Tiêu đề (og:title hoặc &lt;title&gt;)
- Mô tả (og:description hoặc meta description)
- Ảnh đại diện nếu có

Nếu trang không cung cấp ảnh, một ô tải ảnh lên sẽ xuất hiện để admin chọn hình thủ công.
Các giá trị sau đó vẫn có thể chỉnh sửa trước khi lưu.

