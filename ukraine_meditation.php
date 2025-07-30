<?php
require_once __DIR__ . '/i18n.php';
$pageTitle = __('ukraine_title');
$metaDescription = __('ukraine_meta_desc');
require_once 'header.php';
?>

<div class="relative bg-cover bg-center py-4" style="background-image: url('ukraine.png');">
    <div class="absolute inset-0 bg-black/60"></div>
    <div class="relative max-w-xl mx-auto p-6 text-white" style="font-family: Arial, Helvetica, sans-serif;">
        <h1 class="text-3xl sm:text-4xl font-bold text-center mb-4">
            <?= __('ukraine_title') ?>
        </h1>
        <p class="mb-4 text-center text-lg sm:text-xl">
            <?= __('ukraine_intro') ?>
        </p>
        <p class="mb-6 text-center text-base sm:text-lg leading-relaxed">
            <?= __('ukraine_desc') ?>
        </p>
        <div id="form-message" class="hidden mb-4 text-center text-sm"></div>
        <form id="google-form" class="space-y-4" novalidate>
            <input type="text" name="name" id="name" required placeholder="<?= __('placeholder_name') ?>" class="w-full border px-3 py-2 rounded text-black" />
            <input type="email" name="email" id="email" required placeholder="<?= __('placeholder_email') ?>" class="w-full border px-3 py-2 rounded text-black" />
            <textarea name="state" id="state" rows="3" required placeholder="<?= __('placeholder_state') ?>" class="w-full border px-3 py-2 rounded text-black"></textarea>
            <button type="submit" id="submit-button" class="w-full bg-indigo-600 text-white py-2 rounded"><?= __('ukraine_submit') ?></button>
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
        const formActionURL = 'https://docs.google.com/forms/u/2/d/e/1FAIpQLSdpq_JlCCBl4LZT5XNGAgh-Ff7o1JrfhYZQYYMj_MzyMXmhUA/formResponse';
        const entryIdName  = 'entry.1048243103';
        const entryIdEmail = 'entry.1537684843';
        const entryIdState = 'entry.733207508';
        // --- KẾT THÚC PHẦN THAY THẾ ---

        // Kiểm tra xem người dùng đã điền thông tin chưa
        if (formActionURL.includes('YOUR_FORM_ACTION_URL')) {
            alert("<?= __('ukraine_config_error') ?>");
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
        submitButton.textContent = "<?= __('ukraine_sending') ?>";

        // Gửi dữ liệu đến Google Form bằng Fetch API
        fetch(formActionURL, {
            method: 'POST',
            body: formData,
            mode: 'no-cors' // Quan trọng: chế độ no-cors để tránh lỗi CORS với Google
        })
        .then(() => {
            // Hiển thị thông báo thành công
            messageDiv.innerHTML = "<?= __('ukraine_success') ?>";
            messageDiv.className = 'block mb-4 p-4 rounded-md text-sm bg-green-100 text-green-800';
            messageDiv.classList.remove('hidden');
            form.reset();
            form.style.display = 'none';
        })
        .catch(error => {
            // Hiển thị thông báo lỗi
            messageDiv.innerHTML = "<?= __('ukraine_error') ?>";
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
