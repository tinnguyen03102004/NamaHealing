<?php
$pageTitle = 'Lớp Thiền Ukraine';
require_once 'header.php';
?>

<div class="max-w-xl mx-auto p-6">
    <h1 class="text-3xl font-bold text-center mb-4">Lớp Thiền Ukraine</h1>
    <p class="mb-6 text-center">Nếu bạn đang ở Ukraine và muốn tham gia lớp thiền của NamaHealing, hãy để lại thông tin liên hệ của bạn bên dưới.</p>

    <div id="form-message" class="hidden mb-4 text-center text-sm"></div>
    <form id="google-form" class="space-y-4" novalidate>
        <input type="text" name="name" id="name" required placeholder="Họ và Tên" class="w-full border px-3 py-2 rounded" />
        <input type="email" name="email" id="email" required placeholder="Email" class="w-full border px-3 py-2 rounded" />
        <button type="submit" id="submit-button" class="w-full bg-indigo-600 text-white py-2 rounded">Gửi đăng ký</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('google-form');
    const messageDiv = document.getElementById('form-message');
    const submitButton = document.getElementById('submit-button');
    const originalButtonText = submitButton.textContent;

    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Ngăn form gửi theo cách truyền thống

        // --- THAY THẾ THÔNG TIN CỦA BẠN VÀO ĐÂY ---
        // Hướng dẫn lấy các giá trị này có trong file Hướng dẫn.
        const formActionURL = 'YOUR_FORM_ACTION_URL'; // Dán link action của Google Form vào đây
        const entryIdName = 'ENTRY_ID_FOR_NAME';       // Dán entry ID cho trường "Họ Tên"
        const entryIdEmail = 'ENTRY_ID_FOR_EMAIL';     // Dán entry ID cho trường "Email"
        // --- KẾT THÚC PHẦN THAY THẾ ---

        // Kiểm tra xem người dùng đã điền thông tin chưa
        if (formActionURL.includes('YOUR_FORM_ACTION_URL')) {
            alert('Lỗi cấu hình: Vui lòng cập nhật URL và Entry ID của Google Form trong file ukraine_meditation.php');
            return;
        }

        // Lấy dữ liệu từ form trên web
        const nameValue = document.getElementById('name').value;
        const emailValue = document.getElementById('email').value;
        
        // Tạo đối tượng FormData để gửi đi
        const formData = new FormData();
        formData.append(entryIdName, nameValue);
        formData.append(entryIdEmail, emailValue);
        
        // Vô hiệu hóa nút gửi và hiển thị trạng thái đang gửi
        submitButton.disabled = true;
        submitButton.textContent = 'Đang gửi...';

        // Gửi dữ liệu đến Google Form bằng Fetch API
        fetch(formActionURL, {
            method: 'POST',
            body: formData,
            mode: 'no-cors' // Quan trọng: chế độ no-cors để tránh lỗi CORS với Google
        })
        .then(() => {
            // Hiển thị thông báo thành công
            messageDiv.innerHTML = 'Thông tin của bạn đã được gửi thành công.';
            messageDiv.className = 'block mb-4 p-4 rounded-md text-sm bg-green-100 text-green-800';
            messageDiv.classList.remove('hidden');
            form.reset();
            form.style.display = 'none';
        })
        .catch(error => {
            // Hiển thị thông báo lỗi
            messageDiv.innerHTML = 'Có lỗi xảy ra. Vui lòng thử lại.';
            messageDiv.className = 'block mb-4 p-4 rounded-md text-sm bg-red-100 text-red-800';
            messageDiv.classList.remove('hidden');
        })
        .finally(() => {
            // Kích hoạt lại nút gửi
            submitButton.disabled = false;
            submitButton.textContent = originalButtonText;
        });
    });
});
</script>

<?php
// Nạp footer của trang
require_once 'footer.php';
?>
