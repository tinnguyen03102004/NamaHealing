<?php
define('REQUIRE_LOGIN', true);
require 'config.php';
require_once __DIR__ . '/i18n.php';

if (!isset($_SESSION['uid']) || ($_SESSION['role'] ?? '') !== 'student') {
    header('Location: login.php');
    exit;
}

$uid = $_SESSION['uid'];
$attendanceStmt = $db->prepare('SELECT COUNT(*) FROM sessions WHERE user_id = ?');
$attendanceStmt->execute([$uid]);
$attendanceCount = (int) $attendanceStmt->fetchColumn();

if ($attendanceCount === 0) {
    $_SESSION['materials_error'] = __('student_materials_locked_flash');
    header('Location: dashboard.php');
    exit;
}

$pageTitle = __('student_materials_heading');
$metaDescription = __('student_materials_intro');

$file = __DIR__ . '/data/student_materials.json';
$categories = ['meditations', 'exercises', 'sutras'];
if (!file_exists($file)) {
    $defaultData = array_fill_keys($categories, []);
    file_put_contents($file, json_encode($defaultData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$materialsData = json_decode(file_get_contents($file), true);
if (!is_array($materialsData)) {
    $materialsData = [];
}
foreach ($categories as $cat) {
    if (!isset($materialsData[$cat]) || !is_array($materialsData[$cat])) {
        $materialsData[$cat] = [];
    }
}

if (!function_exists('student_materials_is_audio')) {
    function student_materials_is_audio(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH) ?? '';
        if ($path === '') {
            $path = $url;
        }
        return (bool) preg_match('/\.(mp3|m4a|wav|ogg)(\?.*)?$/i', $path);
    }
}

include 'header.php';
?>
<main class="max-w-4xl mx-auto px-4 py-8 min-h-[70vh]">
  <div class="bg-white/95 rounded-2xl shadow-xl border border-mint/30 p-6 md:p-8">
    <h1 class="text-2xl md:text-3xl font-bold text-mint-text mb-3"><?= __('student_materials_heading') ?></h1>
    <p class="text-sm md:text-base text-gray-600 leading-relaxed mb-6"><?= __('student_materials_intro') ?></p>

    <?php foreach ($categories as $catKey): ?>
      <?php
        $sectionTitle = __($catKey === 'meditations' ? 'student_materials_meditations' : ($catKey === 'exercises' ? 'student_materials_exercises' : 'student_materials_sutras'));
        $items = $materialsData[$catKey] ?? [];
      ?>
      <section class="mb-8">
        <div class="flex items-center justify-between mb-3">
          <h2 class="text-xl font-semibold text-mint-text"><?= htmlspecialchars($sectionTitle) ?></h2>
          <span class="text-xs font-medium text-gray-500 uppercase tracking-wide"><?= count($items) ?> <?= __('student_materials_items_label') ?></span>
        </div>
        <?php if (empty($items)): ?>
          <p class="text-sm text-gray-500 italic"><?= __('student_materials_empty') ?></p>
        <?php else: ?>
          <div class="space-y-4">
            <?php foreach ($items as $item): ?>
              <?php
                $title = htmlspecialchars($item['title'] ?? '');
                $rawLink = $item['link'] ?? '';
                $isAudio = $catKey === 'meditations' && student_materials_is_audio($rawLink);
                $rawDownload = $item['file'] ?? $rawLink;
                $link = htmlspecialchars($rawLink);
                $downloadLink = htmlspecialchars($rawDownload);
                $downloadText = htmlspecialchars(__('student_materials_download_audio'));
                $originalName = $item['original_name'] ?? '';
                if ($originalName !== '') {
                    $downloadText .= ' (' . htmlspecialchars($originalName) . ')';
                }
              ?>
              <article class="border border-mint/40 rounded-xl p-4 bg-white shadow-sm">
                <h3 class="text-lg font-semibold text-mint-text mb-2"><?= $title ?></h3>
                <?php if ($isAudio): ?>
                  <audio controls preload="none" class="w-full">
                    <source src="<?= $link ?>">
                    <?= __('student_materials_audio_fallback') ?>
                  </audio>
                  <div class="mt-2 text-xs text-gray-500 break-words">
                    <?php if ($downloadLink !== ''): ?>
                      <a href="<?= $downloadLink ?>" target="_blank" download class="underline text-blue-600"><?= $downloadText ?></a>
                    <?php else: ?>
                      <span class="italic text-gray-400"><?= __('student_materials_admin_link_missing') ?></span>
                    <?php endif; ?>
                  </div>
                <?php else: ?>
                  <a href="<?= $link ?>" target="_blank" class="inline-flex items-center gap-2 text-sm text-blue-600 font-medium hover:underline">
                    <?= __('student_materials_open_link') ?>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5h6m0 0v6m0-6L10.5 13.5m0 0h-6m6 0v6" />
                    </svg>
                  </a>
                <?php endif; ?>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
    <?php endforeach; ?>
  </div>
</main>
<?php include 'footer.php'; ?>
