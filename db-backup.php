<?php
// ─────────────────────────────────────────────────────────────────────
//  db-backup.php v3 — بکاپ خودکار دیتابیس و ارسال به تلگرام ادمین
//  تغییرات v3:
//    - حذف --ssl-mode (سازگار با کلاینت MariaDB که روی دبیان نصب می‌شود)
//    - خطای واقعی از فایل خطا خوانده و در پیام/تلگرام نمایش داده می‌شود
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

// 1) آیا mysqldump نصب است؟
$dump = trim((string) @shell_exec('command -v mysqldump 2>/dev/null'));
if ($dump === '') {
    notify('❌ بکاپ انجام نشد: mysqldump در این ایمیج نیست. '
        . 'Dockerfile دارای default-mysql-client را push کن و سرویس را Redeploy کن.');
    exit(1);
}

// 2) ساخت بکاپ — --skip-ssl: SSL را خاموش می‌کند (سازگار با کلاینت MariaDB/MySQL روی دبیان
//    و رفع خطای self-signed certificate که Railway برای اتصال SSL الزامی کرده)
$backup = __DIR__ . '/backup_' . date('Y-m-d_H-i') . '.sql';
$errfile = $backup . '.err';
$cmd = sprintf(
    '%s -h %s -P %s -u %s -p%s --skip-ssl --single-transaction %s > %s 2> %s',
    escapeshellarg($dump),
    escapeshellarg($dbhost),
    escapeshellarg($dbport),
    escapeshellarg($dbuser),
    escapeshellarg($dbpass),
    escapeshellarg($dbname),
    escapeshellarg($backup),
    escapeshellarg($errfile)
);
exec($cmd, $out, $code);

$err = @file_get_contents($errfile);
@unlink($errfile);
$err = ($err !== false && trim($err) !== '') ? trim($err) : '';

if ($code !== 0 || !file_exists($backup) || filesize($backup) < 100) {
    notify('❌ بکاپ دیتابیس ناموفق بود (کد=' . $code . ")\n"
        . ($err !== '' ? $err : '(بدون جزئیات — خروجی دستور را ببین)'));
    if (file_exists($backup)) @unlink($backup);
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
