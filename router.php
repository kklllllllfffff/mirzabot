<?php
// ═══════════════════════════════════════════════════════════════════
//  router.php — دروازه‌ی وب‌سرور php -S (ریلوی)
//  کارش: بلاک کردن فایل‌های حساس/مخفی قبل از اینکه سرو بشن.
//  این فایل توی Dockerfile به عنوان router استفاده می‌شود.
// ═══════════════════════════════════════════════════════════════════
$uri = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');
$base = basename($uri);

// فایل‌ها / الگوهایی که هرگز نباید عمومی سرو بشن
$blockedPatterns = [
    'error_log',
    'log.txt',
    'backup_',
    '.sql',
    '.env',
    'composer.json',
    'composer.lock',
    'cronbot/info',
    'cronbot/users.json',
    'api/hash.txt',
    'storage/',
    'vendor/',
    'speed-',
    'import.php',
    'db-test',
    'railway-check',
    'mirzabot-railway',
    'cookie.txt',
    '.user.ini',
    'install.sh',
];

foreach ($blockedPatterns as $p) {
    if (stripos($uri, $p) !== false || stripos($base, $p) !== false) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Not Found';
        exit;
    }
}

// اجازه بده php -S بقیه‌ی فایل‌ها را به‌صورت عادی سرو کند
return false;
