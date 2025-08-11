<?php
define('REQUIRE_LOGIN', true);
require 'config.php';

ini_set('display_errors', 1); error_reporting(E_ALL); // tắt khi chạy thật

$pdo = $pdo ?? ($db ?? null);
if (!$pdo) { die('Không có PDO connection ($pdo/$db)'); }

if (($_SESSION['role'] ?? '') !== 'teacher') {
    header('Location: login.php'); exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT
            u.id,
            u.full_name,
            COUNT(DISTINCT s.id) AS session_count,
            COUNT(j.id) AS journal_count,
            (
                SELECT content FROM journals j2
                WHERE j2.user_id = u.id
                ORDER BY j2.meditation_at DESC
                LIMIT 1
            ) AS last_journal
        FROM users u
        LEFT JOIN sessions s ON s.user_id = u.id
        LEFT JOIN journals j ON j.user_id = u.id
        WHERE u.role = 'student'
        GROUP BY u.id, u.full_name
        ORDER BY journal_count DESC
    ");
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    die('Lỗi truy vấn: ' . htmlspecialchars($e->getMessage()));
}

$pageTitle = 'Teacher Dashboard';
require 'header.php';
?>
<main class="p-4 max-w-5xl mx-auto">
  <h2 class="text-2xl font-bold mb-4">Danh sách học viên</h2>
  <input type="text" id="student-filter" placeholder="Lọc theo tên học viên" class="mb-4 p-2 border rounded w-full max-w-sm" />
  <div class="overflow-x-auto">
    <table class="w-full table-auto bg-white shadow rounded">
      <thead class="bg-gray-100">
        <tr>
          <th class="p-2 text-left">Tên</th>
          <th class="p-2 text-left">Số buổi đã tham gia</th>
          <th class="p-2 text-left">Báo thiền hàng ngày</th>
          <th class="p-2 text-left"></th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($students as $s): ?>
        <tr class="border-t student-row" data-name="<?= htmlspecialchars((function_exists('mb_strtolower') ? mb_strtolower($s['full_name'] ?? '') : strtolower($s['full_name'] ?? ''))) ?>" data-id="<?= (int)$s['id'] ?>">
          <td class="p-2">
            <?= htmlspecialchars($s['full_name'] ?? '') ?>
          </td>
          <td class="p-2 text-center"><?= (int)($s['session_count'] ?? 0) ?></td>
          <td class="p-2 text-left">
            <?= htmlspecialchars($s['last_journal'] ?? '') ?>
          </td>
          <td class="p-2 text-center">
            <button class="text-blue-600 underline toggle-journal" data-id="<?= (int)$s['id'] ?>">Xem báo thiền</button>
          </td>
        </tr>
        <tr id="journal-row-<?= (int)$s['id'] ?>" class="border-t hidden journal-row" data-parent="<?= (int)$s['id'] ?>">
          <td colspan="4" class="p-4 bg-gray-50">
            <div class="journal-messages space-y-2 mb-4"></div>
            <form class="reply-form space-y-2" data-id="<?= (int)$s['id'] ?>">
              <textarea class="w-full border px-3 py-2 rounded" required></textarea>
              <button type="submit" class="bg-[#9dcfc3] text-white px-4 py-1 rounded">Gửi phản hồi</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>
<script>
const csrfToken = '<?= $_SESSION['csrf_token']; ?>';

function escapeHtml(str){
  const map={'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'};
  return str.replace(/[&<>"']/g,m=>map[m]);
}

function renderJournals(container, journals){
  container.innerHTML='';
  journals.forEach(j=>{
    const stu=document.createElement('div');
    stu.className='text-left';
    stu.innerHTML=`<div class="inline-block bg-gray-100 p-2 rounded"><div class="text-xs text-gray-500">${j.meditation_at}</div><div>${escapeHtml(j.content)}</div></div>`;
    container.appendChild(stu);
    if(j.teacher_reply){
      const tea=document.createElement('div');
      tea.className='text-right';
      tea.innerHTML=`<div class="inline-block bg-green-100 p-2 rounded"><div class="text-xs text-gray-500">${j.replied_at}</div><div>${escapeHtml(j.teacher_reply)}</div></div>`;
      container.appendChild(tea);
    }
  });
}

function loadJournals(id, container){
  fetch('fetch_journals.php?user_id='+id)
    .then(r=>r.json())
    .then(d=>{renderJournals(container, d.journals || []);});
}

document.querySelectorAll('.toggle-journal').forEach(btn=>{
  btn.addEventListener('click',()=>{
    const id=btn.dataset.id;
    const row=document.getElementById('journal-row-'+id);
    row.classList.toggle('hidden');
    if(!row.dataset.loaded){
      loadJournals(id, row.querySelector('.journal-messages'));
      row.dataset.loaded='1';
    }
  });
});

document.getElementById('student-filter').addEventListener('input', e=>{
  const term=e.target.value.toLowerCase();
  document.querySelectorAll('.student-row').forEach(r=>{
    const match=(r.dataset.name||'').includes(term);
    r.classList.toggle('hidden', !match);
    const jr=document.getElementById('journal-row-'+r.dataset.id);
    if(jr) jr.classList.toggle('hidden', !match);
  });
});

document.querySelectorAll('.reply-form').forEach(frm=>{
  frm.addEventListener('submit',e=>{
    e.preventDefault();
    const id=frm.dataset.id;
    const textarea=frm.querySelector('textarea');
    const container=frm.parentElement.querySelector('.journal-messages');
    fetch('teacher_reply.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams({user_id:id,reply:textarea.value,csrf_token:csrfToken})})
      .then(r=>r.json())
      .then(d=>{
        if(d.success){
          textarea.value='';
          loadJournals(id, container);
        }
      });
  });
});
</script>
<?php include 'footer.php'; ?>
