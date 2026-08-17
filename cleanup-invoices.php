<?php
// ═══════════════════════════════════════════════════════════════════
//  cleanup-invoices.php — پاکسازی خودکار سرویس‌های مرده از بات
//
//  کارها:
//  ۱) سرویس‌های با وضعیت مرده رو پاک می‌کنه:
//     removebyadmin, Unsuccessful, disabled, disabledn, Unpaid
//  ۲) سرویس‌های تموم‌شده (end_of_time, end_of_volume) رو پاک می‌کنه
//  ۳) سرویس‌هایی که توی پنل سنایی وجود ندارن رو پاک می‌کنه
//
//  راه‌اندازی: یه سرویس Schedule توی ریلوی بساز:
//     Start Command: php cleanup-invoices.php
//     Schedule: */30 * * * *   (هر ۳۰ دقیقه)
// ═══════════════════════════════════════════════════════════════════
require_once 'config.php';
require_once 'function.php';

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// ── ۱) پاک کردن وضعیت‌های مرده ──
$dead = $pdo->exec("DELETE FROM invoice WHERE status IN ('removebyadmin','Unsuccessful','disabled','disabledn','Unpaid')");
echo "۱) پاک شد (وضعیت مرده): {$dead}\n";

// ── ۲) پاک کردن سرویس‌های تموم‌شده ──
$expired = $pdo->exec("DELETE FROM invoice WHERE status IN ('end_of_time','end_of_volume')");
echo "۲) پاک شد (تموم‌شده): {$expired}\n";

// ── ۳) سرویس‌هایی که توی پنل سنایی نیستن ──
$stmt = $pdo->query("SELECT DISTINCT Service_location FROM invoice WHERE status = 'active'");
$locations = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($locations as $loc) {
    $panel = select("marzban_panel", "*", "name_panel", $loc, "select");
    if (!$panel) continue;

    // لیست کلاینت‌های واقعی از پنل
    $clients = [];
    $url = rtrim($panel['url_panel'], '/') . '/panel/api/inbounds/list';
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => ['Accept: application/json', 'Authorization: Bearer ' . $panel['password_panel']],
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($resp, true);
    if (is_array($data) && !empty($data['success'])) {
        foreach ($data['obj'] ?? [] as $inb) {
            foreach ($inb['clientStats'] ?? [] as $cs) {
                $clients[$cs['email']] = true;
            }
        }
    }

    // سرویس‌های بات که توی پنل نیستن رو حذف کن
    $stmt2 = $pdo->prepare("SELECT id_invoice, username FROM invoice WHERE Service_location = :loc AND status = 'active'");
    $stmt2->execute([':loc' => $loc]);
    $removed = 0;
    while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
        if (!isset($clients[$row['username']])) {
            $pdo->prepare("DELETE FROM invoice WHERE id_invoice = ?")->execute([$row['id_invoice']]);
            echo "   ❌ حذف شد (توی پنل نیست): {$row['username']}\n";
            $removed++;
        }
    }
    echo "۳) پنل {$loc}: {$removed} سرویس ناموجود حذف شد\n";
}

echo "پاکسازی کامل شد ✅\n";
