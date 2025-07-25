<?php
$pageTitle = 'Lớp Thiền Ukraine';
$metaDescription = 'Đây là Lớp thiền đặc biệt miễn phí dành cho người Ukraine bị ảnh hưởng bởi chiến tranh';
require_once 'header.php';
?>

<div class="relative bg-cover bg-center py-4" style="background-image: url('ukraine.png');">
    <div class="absolute inset-0 bg-black/70"></div>
    <div class="relative max-w-xl mx-auto p-6 text-white">
        <h1 class="text-3xl font-bold text-center mb-4 font-heading">Lớp Thiền Ukraine</h1>
        <p class="mb-4 text-center text-lg">Đây là Lớp thiền đặc biệt miễn phí dành cho người Ukraine bị ảnh hưởng bởi chiến tranh.</p>
        <p class="mb-6 text-center text-lg leading-relaxed">Xuất phát từ sự đồng cảm sâu sắc và thấu hiểu những vết thương lòng mà chiến tranh để lại, Tiến sĩ Võ Trọng Nghĩa đã quyết định mở một lớp thiền đặc biệt dành riêng cho người dân cũng như những người lính Ukraine. Với mong muốn được san sẻ, đồng hành và góp phần xoa dịu những nỗi đau, mất mát mà họ đang phải gánh chịu, lớp học này là một không gian an toàn, nơi mỗi người có thể tìm lại sự bình yên, sự nâng đỡ tinh thần và quá trình chữa lành từ sâu bên trong. Dưới sự hướng dẫn ân cần và tận tâm của thầy, lớp thiền mong muốn mang lại hy vọng, sự vững chãi và năng lượng mới để mỗi người vượt qua thử thách, từng bước kiến tạo lại cuộc sống từ những đổ vỡ của chiến tranh.</p>
        <div id="form-message" class="hidden mb-4 text-center text-sm"></div>
        <form id="google-form" class="space-y-4" novalidate>
            <input type="text" name="name" id="name" required placeholder="<?= __('placeholder_name') ?>" class="w-full border px-3 py-2 rounded text-black" />
            <input type="email" name="email" id="email" required placeholder="<?= __('placeholder_email') ?>" class="w-full border px-3 py-2 rounded text-black" />
            <textarea name="state" id="state" rows="3" required placeholder="<?= __('placeholder_state') ?>" class="w-full border px-3 py-2 rounded text-black"></textarea>
            <button type="submit" id="submit-button" class="w-full bg-indigo-600 text-white py-2 rounded">Gửi đăng ký</button>
        </form>
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
        const entryIdState = 'ENTRY_ID_FOR_STATE';     // Dán entry ID cho trường "Tình trạng tâm lí hiện tại"
        // --- KẾT THÚC PHẦN THAY THẾ ---

        // Kiểm tra xem người dùng đã điền thông tin chưa
        if (formActionURL.includes('YOUR_FORM_ACTION_URL')) {
            alert('Lỗi cấu hình: Vui lòng cập nhật URL và Entry ID của Google Form trong file ukraine_meditation.php');
            return;
        }

        // Lấy dữ liệu từ form trên web
        const nameValue = document.getElementById('name').value;
        const emailValue = document.getElementById('email').value;
        const stateValue = document.getElementById('state').value;
        
        // Tạo đối tượng FormData để gửi đi
        const formData = new FormData();
        formData.append(entryIdName, nameValue);
        formData.append(entryIdEmail, emailValue);
        formData.append(entryIdState, stateValue);
        
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
