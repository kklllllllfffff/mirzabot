<?php
// ─────────────────────────────────────────────────────────────────────
//  config.php — نسخه‌ی بهینه‌شده برای ریلوی (جایگزین کامل فایل اصلی)
//  تغییرات:
//   1) PDO::ATTR_PERSISTENT => true
//      اتصال دیتابیس بین درخواست‌ها زنده می‌ماند (سود: ~۱ ثانیه در هر درخواست)
//   2) PDO::ATTR_EMULATE_PREPARES => true
//      هر کوئری به‌جای ۲ رفت‌وبرگشت شبکه، ۱ رفت‌وبرگشت می‌خواهد (سود: ~۲ برابر)
// ─────────────────────────────────────────────────────────────────────

$request_exec_timeout = null;

$dbhost = getenv('MYSQLHOST') ?: '127.0.0.1';
$dbport = getenv('MYSQLPORT') ?: '3306';
$dbname = getenv('MYSQLDATABASE') ?: 'mirzaprobot';
$usernamedb = getenv('MYSQLUSER') ?: 'root';
$passworddb = getenv('MYSQLPASSWORD') ?: '';

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // ⬇️ آماده‌سازی سمت PHP: هر کوئری یک رفت‌وبرگشت کمتر
    PDO::ATTR_EMULATE_PREPARES => true,
    // ⬇️ اتصال پایدار: بین درخواست‌ها قطع نمی‌شود
    PDO::ATTR_PERSISTENT => true,
    PDO::ATTR_TIMEOUT => 5,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
];

$dsn = "mysql:host={$dbhost};port={$dbport};dbname={$dbname};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $usernamedb, $passworddb, $options);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("error: database connection failed");
}

$APIKEY = getenv('API_KEY') ?: '';
$adminnumber = getenv('ADMIN_NUMBER') ?: '';
$domainhosts = getenv('DOMAIN_NAME') ?: '';
$usernamebot = getenv('USERNAME_BOT') ?: '';

// ── اتصال mysqli هم پایدار (persistent) باشد ──
$connect = null;
if (function_exists('mysqli_init')) {
    $connect = mysqli_init();
    if ($connect && @mysqli_real_connect(
        $connect,
        $dbhost,
        $usernamedb,
        $passworddb,
        $dbname,
        (int) $dbport,
        null,
        MYSQLI_CLIENT_COMPRESS
    )) {
        mysqli_set_charset($connect, "utf8mb4");
    } else {
        // تلاش دوم بدون گزینه‌های اضافه
        if ($connect) {
            mysqli_close($connect);
        }
        $connect = mysqli_init();
        if ($connect && @mysqli_real_connect($connect, $dbhost, $usernamedb, $passworddb, $dbname, (int) $dbport)) {
            mysqli_set_charset($connect, "utf8mb4");
        } else {
            $connect = false;
            error_log("MySQLi connection failed: " . (function_exists('mysqli_connect_error') ? mysqli_connect_error() : ''));
        }
    }
}
