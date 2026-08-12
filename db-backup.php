<?php
// ─────────────────────────────────────────────────────────────────────
//  db-backup.php v2 — بکاپ خودکار دیتابیس و ارسال به تلگرام ادمین
//  تغییرات v2:
//    - اگر mysqldump نصب نباشد، خطای واضح می‌دهد (نه FAILED بی‌معنی)
//    - نتیجه‌ی موفق/ناموفق به تلگرام ADMIN_NUMBER هم پیام می‌دهد
// ─────────────────────────────────────────────────────────────────────
require_once 'config.php';
require_once 'botapi.php';

$dbhost = getenv('MYSQLHOST') ?: '127.0.0.1';
$dbport = getenv('MYSQLPORT') ?: '3306';
$dbname = getenv('MYSQLDATABASE') ?: 'mirzaprobot';
$dbuser = getenv('MYSQLUSER') ?: 'root';
$dbpass = getenv('MYSQLPASSWORD') ?: '';
$admin = getenv('ADMIN_NUMBER') ?: '';

function notify($text)
{
    global $admin;
    echo $text . "\n";
    if ($admin !== '') {
        @telegram('sendMessage', ['chat_id' => $admin, 'text' => $text, 'parse_mode' => 'HTML']);
    }
}

// 1) آیا mysqldump اصلاً نصب است؟
$has = trim((string) @shell_exec('command -v mysqldump 2>/dev/null')) !== '';
if (!$has) {
    notify('❌ بکاپ انجام نشد: mysqldump در این ایمیج نیست. '
        . 'Dockerfile دارای default-mysql-client را push کن و سرویس backup را Redeploy کن.');
    exit(1);
}

// 2) ساخت بکاپ
$backup = __DIR__ . '/backup_' . date('Y-m-d_H-i') . '.sql';
$command = sprintf(
    'mysqldump -h %s -P %s -u %s -p%s --single-transaction --ssl-mode=DISABLED %s > %s 2>&1',
    escapeshellarg($dbhost),
    escapeshellarg($dbport),
    escapeshellarg($dbuser),
    escapeshellarg($dbpass),
    escapeshellarg($dbname),
    escapeshellarg($backup)
);
exec($command, $out, $code);

if ($code !== 0 || !file_exists($backup) || filesize($backup) < 100) {
    $detail = trim(implode("\n", $out));
    notify('❌ بکاپ دیتابیس ناموفق بود (کد=' . $code . ")\n"
        . ($detail !== '' ? $detail : '(بدون خروجی — احتمالاً mysqldump نصب نیست)'));
    exit(1);
}

// 3) ارسال به تلگرام
if ($admin !== '') {
    $r = telegram('sendDocument', [
        'chat_id' => $admin,
        'document' => new CURLFile($backup),
        'caption' => '📦 بکاپ دیتابیس: ' . $dbname . ' — ' . date('Y-m-d H:i'),
    ]);
    if (empty($r['ok'])) {
        notify('⚠️ بکاپ ساخته شد ولی ارسال به تلگرام نشد: ' . json_encode($r));
    }
}

@unlink($backup);
echo "OK\n";
