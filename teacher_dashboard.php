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
    $stmt = $pdo->prepare("\n        SELECT\n            u.id, u.full_name,\n            (SELECT COUNT(*) FROM sessions s WHERE s.user_id = u.id) AS session_count,\n            (SELECT MAX(j.meditation_at) FROM journals j WHERE j.student_id = u.id) AS latest_journal,\n            EXISTS(\n              SELECT 1 FROM journals j\n              WHERE j.student_id = u.id AND j.seen_at IS NULL\n            ) AS has_unseen\n        FROM users u\n        WHERE u.role = 'student'\n        ORDER BY u.full_name\n    ");
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
        <tr class="border-t">
          <td class="p-2">
            <?= htmlspecialchars($s['full_name'] ?? '') ?>
            <?php if (!empty($s['has_unseen'])): ?>
              <span class="ml-2 bg-red-500 text-white text-xs px-2 py-1 rounded badge-<?= (int)$s['id'] ?>">Chưa xem</span>
            <?php endif; ?>
          </td>
          <td class="p-2 text-center"><?= (int)($s['session_count'] ?? 0) ?></td>
          <td class="p-2">
            <?= !empty($s['latest_journal']) ? date('d/m/Y H:i', strtotime($s['latest_journal'])) : '-' ?>
          </td>
          <td class="p-2 text-center">
            <button class="text-blue-600 underline toggle-journal" data-id="<?= (int)$s['id'] ?>">Xem báo thiền</button>
          </td>
        </tr>
        <tr id="journal-row-<?= (int)$s['id'] ?>" class="border-t hidden">
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
  fetch('fetch_journals.php?student_id='+id)
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

document.querySelectorAll('.reply-form').forEach(frm=>{
  frm.addEventListener('submit',e=>{
    e.preventDefault();
    const id=frm.dataset.id;
    const textarea=frm.querySelector('textarea');
    const container=frm.parentElement.querySelector('.journal-messages');
    fetch('teacher_reply.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams({student_id:id,reply:textarea.value,csrf_token:csrfToken})})
      .then(r=>r.json())
      .then(d=>{
        if(d.success){
          textarea.value='';
          loadJournals(id, container);
          const badge=document.querySelector('.badge-'+id);
          if(badge) badge.remove();
        }
      });
  });
});
</script>
<?php include 'footer.php'; ?>
