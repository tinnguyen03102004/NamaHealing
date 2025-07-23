<?php
// Nạp header của trang
require_once 'header.php';
?>

<div class="bg-gray-50 py-12 lg:py-20">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">

            <!-- Phần giới thiệu -->
            <div class="text-center mb-12">
                <h1 class="text-3xl lg:text-4xl font-bold text-gray-800 mb-4">
                    <?php echo i18n('special_meditation_title'); ?>
                </h1>
                <p class="text-lg text-gray-600">
                    <?php echo i18n('special_meditation_intro'); ?>
                </p>
            </div>

            <!-- Form đăng ký -->
            <div class="bg-white p-8 rounded-2xl shadow-lg border border-gray-200">
                <h2 class="text-2xl font-semibold text-center text-gray-800 mb-6">
                    <?php echo i18n('registration_form_title'); ?>
                </h2>

                <!-- Vùng hiển thị thông báo (thành công hoặc lỗi) -->
                <div id="form-message" class="hidden mb-4 p-4 rounded-md text-sm"></div>

                <form id="google-form" novalidate>
                    <div class="space-y-6">
                        <!-- Trường Họ và Tên -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1"><?php echo i18n('form_name'); ?></label>
                            <input type="text" name="name" id="name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition">
                        </div>

                        <!-- Trường Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1"><?php echo i18n('form_email'); ?></label>
                            <input type="email" name="email" id="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition">
                        </div>
                        
                        <!-- Nút Gửi -->
                        <div>
                            <button type="submit" id="submit-button" class="w-full bg-indigo-600 text-white font-semibold py-3 px-4 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition shadow-md">
                                <?php echo i18n('form_submit_button'); ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
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
            messageDiv.innerHTML = '<?php echo i18n('form_success_message'); ?>';
            messageDiv.className = 'block mb-4 p-4 rounded-md text-sm bg-green-100 text-green-800';
            messageDiv.classList.remove('hidden');
            form.reset(); // Xóa các trường đã nhập
            form.style.display = 'none'; // Ẩn form đi
        })
        .catch(error => {
            // Hiển thị thông báo lỗi
            messageDiv.innerHTML = '<?php echo i18n('form_error_message'); ?>';
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
