<?php
// ─────────────────────────────────────────────────────────────────────
//  db-backup.php — بکاپ خودکار دیتابیس و ارسال به تلگرام ادمین
//  استفاده:
//    1) در ریپو قرار بده (کنار config.php)
//    2) یک سرویس Schedule در ریلوی بساز:
//         Start Command:  php db-backup.php
//         Schedule:       0 */5 * * *   (هر ۵ ساعت)
//       و این متغیرها را به آن سرویس اضافه کن:
//         MYSQL* (Add Reference → MySQL)، API_KEY، ADMIN_NUMBER
//    3) بکاپ هر ۵ ساعت به تلگرام خودت (ADMIN_NUMBER) می‌آید
// ─────────────────────────────────────────────────────────────────────
require_once 'config.php';
require_once 'botapi.php';

$dbhost = getenv('MYSQLHOST') ?: '127.0.0.1';
$dbport = getenv('MYSQLPORT') ?: '3306';
$dbname = getenv('MYSQLDATABASE') ?: 'mirzaprobot';
$dbuser = getenv('MYSQLUSER') ?: 'root';
$dbpass = getenv('MYSQLPASSWORD') ?: '';

$backup = __DIR__ . '/backup_' . date('Y-m-d_H-i') . '.sql';

// --single-transaction: بکاپ سازگار بدون قفل کردن جدول‌ها
$command = sprintf(
    'mysqldump -h %s -P %s -u %s -p%s --single-transaction %s > %s 2>&1',
    escapeshellarg($dbhost),
    escapeshellarg($dbport),
    escapeshellarg($dbuser),
    escapeshellarg($dbpass),
    escapeshellarg($dbname),
    escapeshellarg($backup)
);

exec($command, $out, $code);

if ($code !== 0 || !file_exists($backup) || filesize($backup) < 100) {
    error_log('db-backup failed: ' . implode("\n", $out));
    echo "FAILED\n";
    exit(1);
}

$admin = getenv('ADMIN_NUMBER') ?: '';
if ($admin !== '') {
    telegram('sendDocument', [
        'chat_id' => $admin,
        'document' => new CURLFile($backup),
        'caption' => '📦 بکاپ دیتابیس: ' . $dbname . ' — ' . date('Y-m-d H:i'),
    ]);
}

unlink($backup);
echo "OK\n";
