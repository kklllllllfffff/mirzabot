<?php
// ═══════════════════════════════════════════════════════════════════
//  link-users.php — اتصال خودکار کانفیگ‌های پنل سنایی به یوزرهای تلگرام
//
//  استفاده:
//  1) این فایل رو توی ریپو بذار و دیپلوی کن
//  2) توی ریلوی یه متغیر بساز:  LINKER_SECRET = یه رمز دلخواه
//  3) توی مرورگر باز کن:  https://دامنه/link-users.php?key=رمز
//  4) برای هر کانفیگ یه خط بنویس:
//        اسم_کانفیگ = آیدی عددی تلگرام   (مثلاً ali12 = 123456789)
//        اسم_کانفیگ = یوزرنیم تلگرام      (مثلاً ali12 = @mohammad)
//        اسم_کانفیگ = شماره موبایل        (مثلاً ali12 = 09123456789)
//  5) خودش آیدی تلگرام رو پیدا می‌کنه، حجم/زمان باقی‌مانده واقعی
//     کانفیگ رو از پنل می‌گیره و سرویس رو به یوزر وصل می‌کنه.
//
//  ⚠️ بعد از استفاده حتماً این فایل رو از ریپو حذف کن!
// ═══════════════════════════════════════════════════════════════════
require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');

$key = getenv('LINKER_SECRET') ?: 'sovra-link-2026';
if (!isset($_GET['key']) || $_GET['key'] !== $key) {
    http_response_code(403);
    echo '<meta charset="utf-8"><div dir="rtl" style="font-family:Tahoma;max-width:640px;margin:40px auto">';
    echo '<h3>🚫 دسترسی غیرمجاز</h3><p>برای استفاده، رمز رو با ?key= بفرست.</p></div>';
    exit;
}

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

function resolveTelegramId($ref, $pdo)
{
    $ref = trim($ref);
    if ($ref === '') return [false, 'خالی است'];

    // ۱) آیدی عددی (مطمئن‌ترین)
    if (ctype_digit($ref)) {
        return [true, $ref];
    }

    // ۲) یوزرنیم (با @ یا بدون)
    $u = ltrim($ref, '@');
    $stmt = $pdo->prepare("SELECT id FROM user WHERE username = ? LIMIT 1");
    $stmt->execute([$u]);
    $id = $stmt->fetchColumn();
    if ($id) return [true, $id];

    // ۳) شماره موبایل
    $phone = preg_replace('/[^0-9]/', '', $ref);
    if (strlen($phone) >= 10) {
        if (substr($phone, 0, 2) === '98') $phone = '0' . substr($phone, 2);
        $stmt = $pdo->prepare("SELECT id FROM user WHERE REPLACE(number,' ','') = ? OR number = ? LIMIT 1");
        $stmt->execute([$phone, $ref]);
        $id = $stmt->fetchColumn();
        if ($id) return [true, $id];
    }

    return [false, "کاربر تلگرام «{$ref}» پیدا نشد — باید اول به بات /start زده باشه، یا آیدی عددیش رو مستقیم بدی"];
}

function fetchConfigFromPanel($username, $panel)
{
    $url = rtrim($panel['url_panel'], '/') . "/panel/api/clients/get/" . rawurlencode($username);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 12,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Authorization: Bearer ' . $panel['password_panel'],
        ],
    ]);
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($resp === false) return [false, 'خطای اتصال به پنل: ' . $err];

    $data = json_decode($resp, true);
    if (!is_array($data) || empty($data['success'])) {
        $msg = $data['msg'] ?? $resp;
        return [false, 'پنل جواب نداد: ' . substr($msg, 0, 200)];
    }

    $obj = $data['obj'] ?? [];
    $client = $obj['client'] ?? $obj;
    $expiry = intval($client['expiryTime'] ?? 0);
    $totalGB = intval($client['totalGB'] ?? 0);

    // حجم باقی‌مانده: کل منهای مصرف
    $used = intval($obj['used'] ?? 0);
    $remaining = $totalGB > 0 ? max(0, $totalGB - $used) : 0;
    $volume = $remaining > 0 ? (int)round($remaining / (1024 ** 3)) : 0;
    if ($volume <= 0 && $totalGB > 0) $volume = 0;

    // زمان باقی‌مانده
    $service_time = 0;
    if ($expiry > 0) {
        $service_time = (int)ceil(($expiry - time() * 1000) / 86400000);
        if ($service_time < 0) $service_time = 0;
    }

    return [true, [
        'expire_days' => $service_time,
        'volume_gb' => $volume,
        'raw' => $obj,
    ]];
}

echo '<meta charset="utf-8"><div dir="rtl" style="font-family:Tahoma;max-width:720px;margin:30px auto">';
echo '<h2>🔗 اتصال کانفیگ‌های پنل به یوزرهای تلگرام</h2>';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $panelName = trim($_POST['panel'] ?? '');
    $lines = explode("\n", (string)($_POST['map'] ?? ''));
    $nameProduct = trim($_POST['product'] ?? 'کانفیگ واردشده');
    $searchAll = isset($_POST['searchall']) && $_POST['searchall'] === '1';

    $panel = null;
    if ($panelName !== '') {
        $stmt = $pdo->prepare("SELECT * FROM marzban_panel WHERE name_panel = ? LIMIT 1");
        $stmt->execute([$panelName]);
        $panel = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    if (!$panel) {
        echo '<p style="background:#ffebee;padding:12px">❌ پنل «' . e($panelName) . '» پیدا نشد.</p>';
        echo '<p><a href="?key=' . e($_GET['key']) . '">🔙 بازگشت</a></p></div>';
        exit;
    }

    // ── حالت: جستجوی خودکار همه کانفیگ‌ها ──
    if ($searchAll) {
        // لیست همه کلاینت‌ها از پنل
        $listUrl = rtrim($panel['url_panel'], '/') . '/panel/api/inbounds/list';
        $ch = curl_init($listUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => ['Accept: application/json', 'Authorization: Bearer ' . $panel['password_panel']],
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);
        $inbounds = json_decode($resp, true);
        $allClients = [];
        if (is_array($inbounds) && !empty($inbounds['success'])) {
            foreach ($inbounds['obj'] ?? [] as $inb) {
                foreach ($inb['clientStats'] ?? [] as $cs) {
                    $allClients[$cs['email']] = [
                        'total' => intval($cs['total'] ?? 0),
                        'used' => intval($cs['down'] ?? 0) + intval($cs['up'] ?? 0),
                        'expiry' => intval($cs['expiryTime'] ?? 0),
                    ];
                }
            }
        }

        $okCount = 0; $failCount = 0; $skipped = 0;
        echo '<h3>🔍 جستجوی خودکار همه کانفیگ‌ها</h3>';
        foreach ($allClients as $email => $info) {
            // تکراری نباشه
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM invoice WHERE username = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) { $skipped++; continue; }

            // جستجوی یوزر با این ایمیل به عنوان یوزرنیم تلگرام
            $stmt = $pdo->prepare("SELECT id FROM user WHERE username = ? LIMIT 1");
            $stmt->execute([$email]);
            $tgId = $stmt->fetchColumn();

            if (!$tgId) {
                // جستجوی یوزر با آیدی عددی (اگه ایمیل خودش عدد باشه)
                if (ctype_digit($email)) {
                    $stmt = $pdo->prepare("SELECT id FROM user WHERE id = ? LIMIT 1");
                    $stmt->execute([$email]);
                    $tgId = $stmt->fetchColumn();
                }
            }

            if (!$tgId) {
                echo "⏭ کانفیگ «" . e($email) . "» → یوزر تلگرام پیدا نشد (رد شد)<br>";
                $failCount++;
                continue;
            }

            // ساخت رکورد سرویس با حجم/زمان باقی‌مانده
            $remaining = $info['total'] > 0 ? max(0, $info['total'] - $info['used']) : 0;
            $volume = $remaining > 0 ? (int)round($remaining / (1024 ** 3)) : 0;
            $time = 0;
            if ($info['expiry'] > 0) {
                $time = (int)ceil(($info['expiry'] - time() * 1000) / 86400000);
                if ($time < 0) $time = 0;
            }

            $id_invoice = bin2hex(random_bytes(4));
            $notifctions = json_encode(['volume' => false, 'time' => false]);
            $stmt = $pdo->prepare("INSERT IGNORE INTO invoice (id_user, id_invoice, username, time_sell, Service_location, name_product, price_product, Volume, Service_time, Status, notifctions)
                VALUES (?, ?, ?, ?, ?, ?, '0', ?, ?, 'active', ?)");
            $stmt->execute([$tgId, $id_invoice, $email, time(), $panel['name_panel'], $nameProduct, $volume, $time, $notifctions]);

            $volTxt = $volume == 0 ? 'نامحدود/اتمام' : $volume . ' گیگ';
            $timeTxt = $time == 0 ? 'نامحدود/اتمام' : $time . ' روز';
            echo "✅ «" . e($email) . "» → آیدی " . e($tgId) . " وصل شد (باقی: $volTxt، $timeTxt)<br>";
            $okCount++;
        }
        echo "<hr><p><b>نتیجه: $okCount وصل شد، $failCount یوزر نداشت، $skipped تکراری</b></p>";
        echo '<p><a href="?key=' . e($_GET['key']) . '">🔙 بازگشت و ادامه</a></p></div>';
        exit;
    }

    // ── حالت: لیست دستی ──
    $okCount = 0; $failCount = 0;
    foreach ($lines as $lineNo => $lineRaw) {
        $line = trim($lineRaw);
        if ($line === '' || str_starts_with($line, '#')) continue;
        $lineNum = $lineNo + 1;

        $parts = preg_split('/\s*[=،:]\s*/', $line, 2);
        if (count($parts) < 2) $parts = [$line, $line];
        $configUser = trim($parts[0]);
        $tgRef = trim($parts[1]);

        if ($configUser === '') { echo "خط $lineNum: نام کانفیگ خالی است<br>"; $failCount++; continue; }

        [$ok, $tgId] = resolveTelegramId($tgRef, $pdo);
        if (!$ok) { echo "خط $lineNum: ❌ «" . e($configUser) . "» → " . e($tgId) . "<br>"; $failCount++; continue; }

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM invoice WHERE username = ?");
        $stmt->execute([$configUser]);
        if ($stmt->fetchColumn() > 0) { echo "خط $lineNum: ⏭ «" . e($configUser) . "» از قبل متصل است<br>"; $failCount++; continue; }

        [$ok, $cfg] = fetchConfigFromPanel($configUser, $panel);
        if (!$ok) { echo "خط $lineNum: ❌ «" . e($configUser) . "» → " . e($cfg) . "<br>"; $failCount++; continue; }

        $id_invoice = bin2hex(random_bytes(4));
        $notifctions = json_encode(['volume' => false, 'time' => false]);
        $stmt = $pdo->prepare("INSERT IGNORE INTO invoice (id_user, id_invoice, username, time_sell, Service_location, name_product, price_product, Volume, Service_time, Status, notifctions)
            VALUES (?, ?, ?, ?, ?, ?, '0', ?, ?, 'active', ?)");
        $stmt->execute([$tgId, $id_invoice, $configUser, time(), $panel['name_panel'], $nameProduct, $cfg['volume_gb'], $cfg['expire_days'], $notifctions]);

        $volTxt = $cfg['volume_gb'] == 0 ? 'نامحدود' : $cfg['volume_gb'] . ' گیگ';
        $timeTxt = $cfg['expire_days'] == 0 ? 'نامحدود' : $cfg['expire_days'] . ' روز';
        echo "خط $lineNum: ✅ «" . e($configUser) . "» → آیدی <b>" . e($tgId) . "</b> وصل شد (باقی: $volTxt، $timeTxt)<br>";
        $okCount++;
    }

    echo "<hr><p><b>نتیجه: $okCount موفق، $failCount ناموفق</b></p>";
    echo '<p><a href="?key=' . e($_GET['key']) . '">🔙 بازگشت و ادامه</a></p></div>';
    exit;
}

$stmt = $pdo->query("SELECT name_panel, type FROM marzban_panel ORDER BY name_panel");
$panels = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo '<form method="post">';
echo '<label><b>۱) پنل:</b></label><br><select name="panel" style="width:100%;padding:8px">';
foreach ($panels as $p) {
    echo '<option value="' . e($p['name_panel']) . '">' . e($p['name_panel']) . ' (' . e($p['type']) . ')</option>';
}
echo '</select><br><br>';

echo '<label><b>۲) نام محصول (توی «سرویس‌های من» نمایش داده می‌شه):</b></label><br>';
echo '<input name="product" value="کانفیگ واردشده" style="width:100%;padding:8px"><br><br>';

echo '<div style="background:#f0fdf4;border:1px solid #86efac;padding:12px;border-radius:8px;margin-bottom:12px">';
echo '<h3 style="margin-top:0">🔍 حالت خودکار: همه کانفیگ‌ها رو جستجو کن</h3>';
echo '<p>اگه اسم کانفیگ = یوزرنیم تلگرامِ همون شخص باشه، این گزینه همه رو خودکار وصل می‌کنه (بدون نوشتن لیست).</p>';
echo '<button type="submit" name="searchall" value="1" style="padding:10px 24px;background:#2f855a;color:#fff;border:0;border-radius:6px;cursor:pointer">🔍 جستجوی خودکار همه</button>';
echo '</div>';

echo '<hr>';

echo '<label><b>۳) حالت دستی — لیست کانفیگ‌ها (هر خط یک نفر):</b></label><br>';
echo '<textarea name="map" rows="15" style="width:100%;padding:8px;font-family:monospace" placeholder="ali12 = 123456789&#10;mahdi = @mohammad&#10;sara = 09123456789&#10;reza = 987654321">';
echo '</textarea><br><br>';

echo '<button type="submit" style="padding:10px 30px;background:#2f855a;color:#fff;border:0;border-radius:6px;cursor:pointer">🚀 اتصال همه</button>';
echo '</form>';

echo '<hr><p style="color:#666;font-size:13px">';
echo '💡 راهنما:<br>';
echo '• <b>آیدی عددی</b> بهترینه: از @userinfobot بگیر (مثلاً 123456789)<br>';
echo '• یوزرنیم تلگرام (با @ یا بدون) هم قبوله<br>';
echo '• شماره موبایل قبوله (اگه موقع استفاده از بات فرستاده باشه)<br>';
echo '• ⚠️ هر یوزر باید قبلاً به بات /start زده باشه<br>';
echo '• حجم/زمان <b>باقی‌مانده</b> واقعی از پنل خونده می‌شه<br>';
echo '• بعد از اتصال، توی «سرویس‌های من» میاد<br>';
echo '• ⚠️ بعد از استفاده این فایل رو حذف کن!';
echo '</p></div>';
