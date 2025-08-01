<?php
require_once __DIR__ . '/i18n.php';
$pageTitle = __('ukraine_title');
$metaDescription = __('ukraine_meta_desc');
require_once 'header.php';
?>

<!-- Page-specific fonts to correctly render Vietnamese -->
<style>
  @import url('https://fonts.googleapis.com/css2?family=Noto+Serif:wght@400;500;600;700&family=Noto+Sans:wght@400;500;600;700&display=swap&subset=vietnamese');
  body { font-family: 'Noto Sans', Arial, sans-serif; }
  .font-heading { font-family: 'Noto Sans', Arial, sans-serif; }
  .font-serif { font-family: 'Noto Serif', serif; }

</style>

<div class="relative bg-cover bg-center bg-black/70 bg-blend-overlay py-8 min-h-screen flex items-center justify-center text-white" style="background-image: url('ukraine.png');">
    <div class="relative max-w-xl mx-auto p-6">
        <h1 class="text-3xl sm:text-4xl font-bold text-center mb-4 font-heading text-white"><?= __('ukraine_title') ?></h1>
        <p class="mb-4 text-center text-lg sm:text-xl font-semibold font-serif italic text-white">
            <?= __('ukraine_intro') ?>
        </p>
        <p class="mb-6 text-center text-base sm:text-lg leading-relaxed font-light text-white">
            <?= __('ukraine_desc') ?>
        </p>
        <div id="form-message" class="hidden mb-4 text-center text-sm"></div>
        <form id="google-form" class="space-y-4" novalidate>
            <input type="text" name="name" id="name" required placeholder="<?= __('placeholder_name') ?>" class="w-full border px-3 py-2 rounded bg-white text-black" />
            <input type="email" name="email" id="email" required placeholder="<?= __('placeholder_email') ?>" class="w-full border px-3 py-2 rounded bg-white text-black" />
            <textarea name="state" id="state" rows="3" required placeholder="<?= __('placeholder_state') ?>" class="w-full border px-3 py-2 rounded bg-white text-black"></textarea>

            <div class="mb-4">
                <p class="mb-2">4. <?= __('question_sadness') ?></p>
                <label class="mr-4"><input type="radio" name="q_sadness" value="Yes" required> <?= __('yes_label') ?></label>
                <label><input type="radio" name="q_sadness" value="No"> <?= __('no_label') ?></label>
            </div>

            <div class="mb-4">
                <p class="mb-2">5. <?= __('question_irritability') ?></p>
                <label class="mr-4"><input type="radio" name="q_irritability" value="Yes" required> <?= __('yes_label') ?></label>
                <label><input type="radio" name="q_irritability" value="No"> <?= __('no_label') ?></label>
            </div>

            <div class="mb-4">
                <p class="mb-2">6. <?= __('question_interest') ?></p>
                <label class="mr-4"><input type="radio" name="q_interest" value="Yes" required> <?= __('yes_label') ?></label>
                <label><input type="radio" name="q_interest" value="No"> <?= __('no_label') ?></label>
            </div>

            <div class="mb-4">
                <p class="mb-2">7. <?= __('question_sleep') ?></p>
                <label class="mr-4"><input type="radio" name="q_sleep" value="Yes" required> <?= __('yes_label') ?></label>
                <label><input type="radio" name="q_sleep" value="No"> <?= __('no_label') ?></label>
            </div>

            <div class="mb-4">
                <p class="mb-2">8. <?= __('question_energy') ?></p>
                <label class="mr-4"><input type="radio" name="q_energy" value="Yes" required> <?= __('yes_label') ?></label>
                <label><input type="radio" name="q_energy" value="No"> <?= __('no_label') ?></label>
            </div>

            <div class="mb-4">
                <p class="mb-2">9. <?= __('question_appetite') ?></p>
                <label class="mr-4"><input type="radio" name="q_appetite" value="Yes" required> <?= __('yes_label') ?></label>
                <label><input type="radio" name="q_appetite" value="No"> <?= __('no_label') ?></label>
            </div>

            <div class="mb-4">
                <p class="mb-2">10. <?= __('question_anxiety') ?></p>
                <label class="mr-4"><input type="radio" name="q_anxiety" value="Yes" required> <?= __('yes_label') ?></label>
                <label><input type="radio" name="q_anxiety" value="No"> <?= __('no_label') ?></label>
            </div>

            <div class="mb-4">
                <p class="mb-2">11. <?= __('question_slowing') ?></p>
                <label class="mr-4"><input type="radio" name="q_slowing" value="Yes" required> <?= __('yes_label') ?></label>
                <label><input type="radio" name="q_slowing" value="No"> <?= __('no_label') ?></label>
            </div>

            <div class="mb-4">
                <p class="mb-2">12. <?= __('question_worthless') ?></p>
                <label class="mr-4"><input type="radio" name="q_worthless" value="Yes" required> <?= __('yes_label') ?></label>
                <label><input type="radio" name="q_worthless" value="No"> <?= __('no_label') ?></label>
            </div>

            <div class="mb-4">
                <p class="mb-2">13. <?= __('question_concentrate') ?></p>
                <label class="mr-4"><input type="radio" name="q_concentrate" value="Yes" required> <?= __('yes_label') ?></label>
                <label><input type="radio" name="q_concentrate" value="No"> <?= __('no_label') ?></label>
            </div>

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

        const sadnessValue      = document.querySelector('input[name="q_sadness"]:checked').value;
        const irritabilityValue = document.querySelector('input[name="q_irritability"]:checked').value;
        const interestValue     = document.querySelector('input[name="q_interest"]:checked').value;
        const sleepValue        = document.querySelector('input[name="q_sleep"]:checked').value;
        const energyValue       = document.querySelector('input[name="q_energy"]:checked').value;
        const appetiteValue     = document.querySelector('input[name="q_appetite"]:checked').value;
        const anxietyValue      = document.querySelector('input[name="q_anxiety"]:checked').value;
        const slowingValue      = document.querySelector('input[name="q_slowing"]:checked').value;
        const worthlessValue    = document.querySelector('input[name="q_worthless"]:checked').value;
        const concentrateValue  = document.querySelector('input[name="q_concentrate"]:checked').value;
        
        // Tạo đối tượng FormData để gửi đi
        const formData = new FormData();
        formData.append(entryIdName, nameValue);
        formData.append(entryIdEmail, emailValue);
        formData.append(entryIdState, stateValue);
        formData.append('entry.646261214', sadnessValue);
        formData.append('entry.562184968', irritabilityValue);
        formData.append('entry.2134551142', interestValue);
        formData.append('entry.1423448482', sleepValue);
        formData.append('entry.1661659713', energyValue);
        formData.append('entry.1726162741', appetiteValue);
        formData.append('entry.113509213', anxietyValue);
        formData.append('entry.679098488', slowingValue);
        formData.append('entry.408000858', worthlessValue);
        formData.append('entry.1000541278', concentrateValue);
        
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
