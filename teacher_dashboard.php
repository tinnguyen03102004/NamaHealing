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
  <div class="flex gap-2 mb-4">
    <select id="filter-type" class="p-2 border rounded">
      <option value="name">Tên</option>
      <option value="journal">Báo thiền mới nhất</option>
      <option value="id">ID</option>
    </select>
    <input type="text" id="student-filter" placeholder="Nhập từ khóa" class="p-2 border rounded flex-1 max-w-sm" />
  </div>
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
        <tr class="border-t student-row" data-name="<?= htmlspecialchars((function_exists('mb_strtolower') ? mb_strtolower($s['full_name'] ?? '') : strtolower($s['full_name'] ?? ''))) ?>" data-id="<?= (int)$s['id'] ?>" data-journal="<?= htmlspecialchars((function_exists('mb_strtolower') ? mb_strtolower($s['last_journal'] ?? '') : strtolower($s['last_journal'] ?? ''))) ?>">
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
            <div class="journal-messages space-y-2 mb-4 max-h-96 overflow-y-auto" data-stick="1"></div>
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

function renderMessages(container, messages){
  const stick = container.dataset.stick !== '0';
  container.innerHTML='';
  let curDate='';
  messages.forEach(m=>{
    const d=new Date(m.created_at);
    const ds=d.toLocaleDateString('vi-VN');
    if(ds!==curDate){
      curDate=ds;
      const sep=document.createElement('div');
      sep.className='text-center text-xs text-gray-500';
      sep.innerHTML=`<span class="px-2 py-1 bg-gray-200 rounded-full">${ds}</span>`;
      container.appendChild(sep);
    }
    const wrap=document.createElement('div');
    wrap.className=m.role==='teacher'?'text-right':'text-left';
    const bubble=document.createElement('div');
    bubble.className='inline-block p-2 rounded '+(m.role==='teacher'?'bg-green-100':'bg-gray-100');
    bubble.textContent=m.content;
    wrap.appendChild(bubble);
    container.appendChild(wrap);
  });
  if(stick){
    container.scrollTop=container.scrollHeight;
  }
}

function loadJournals(id, container){
  if(!container.dataset.init){
    container.addEventListener('scroll',()=>{
      container.dataset.stick = (container.scrollTop + container.clientHeight >= container.scrollHeight - 10) ? '1' : '0';
    });
    container.dataset.init='1';
  }
  fetch('fetch_journals.php?user_id='+id)
    .then(r=>r.json())
    .then(d=>{renderMessages(container, d.messages || []);});
}

document.querySelectorAll('.toggle-journal').forEach(btn=>{
  btn.addEventListener('click',()=>{
    const id=btn.dataset.id;
    const row=document.getElementById('journal-row-'+id);
    const isHidden=row.classList.contains('hidden');
    document.querySelectorAll('.journal-row').forEach(r=>{if(r!==row) r.classList.add('hidden');});
    if(isHidden){
      row.classList.remove('hidden');
      if(!row.dataset.loaded){
        loadJournals(id, row.querySelector('.journal-messages'));
        row.dataset.loaded='1';
      }
    } else {
      row.classList.add('hidden');
    }
  });
});

const filterInput=document.getElementById('student-filter');
const filterType=document.getElementById('filter-type');
function applyFilter(){
  const term=filterInput.value.toLowerCase();
  const type=filterType.value;
  document.querySelectorAll('.student-row').forEach(r=>{
    let value='';
    if(type==='name') value=r.dataset.name||'';
    else if(type==='journal') value=r.dataset.journal||'';
    else if(type==='id') value=r.dataset.id||'';
    const match=value.includes(term);
    r.classList.toggle('hidden', !match);
    const jr=document.getElementById('journal-row-'+r.dataset.id);
    if(jr) jr.classList.toggle('hidden', !match);
  });
}
filterInput.addEventListener('input', applyFilter);
filterType.addEventListener('change', applyFilter);

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
