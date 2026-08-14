<?php
// ═══════════════════════════════════════════════════════════════════
//  link-users.php — اتصال خودکار کانفیگ‌های پنل سنایی به یوزرهای تلگرام
//
//  استفاده:
//  1) این فایل رو توی ریپو بذار و دیپلوی کن
//  2) توی ریلوی یه متغیر بساز:  LINKER_SECRET = یه رمز دلخواه
//  3) توی مرورگر باز کن:  https://دامنه/link-users.php?key=رمز
//  4) برای هر کانفیگ یه خط بنویس:
//        اسم_کانفیگ = یوزرنیم_تلگرام   (با @ یا بدون)
//        اسم_کانفیگ = آیدی عددی تلگرام
//        اسم_کانفیگ = 09123456789      (شماره موبایل هم قبول میشه)
//  5) خودش آیدی تلگرام رو پیدا می‌کنه، حجم/زمان واقعی کانفیگ رو از
//     پنل می‌گیره و سرویس رو به یوزر وصل می‌کنه.
//
//  ⚠️ بعد از استفاده حتماً این فایل رو از ریپو حذف کن!
// ═══════════════════════════════════════════════════════════════════
require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');

// ── درِ ورود با رمز ──
$key = getenv('LINKER_SECRET') ?: 'sovra-link-2026';
if (!isset($_GET['key']) || $_GET['key'] !== $key) {
    http_response_code(403);
    echo '<meta charset="utf-8"><div dir="rtl" style="font-family:Tahoma;max-width:640px;margin:40px auto">';
    echo '<h3>🚫 دسترسی غیرمجاز</h3><p>برای استفاده، رمز رو با ?key= بفرست.</p></div>';
    exit;
}

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

// ── پیدا کردن آیدی تلگرام از یوزرنیم / شماره / آیدی ──
function resolveTelegramId($ref, $pdo)
{
    $ref = trim($ref);
    if ($ref === '') return [false, 'خالی است'];

    // ۱) آیدی عددی
    if (ctype_digit($ref)) {
        return [true, $ref];
    }

    // ۲) یوزرنیم (با @ یا بدون)
    $u = ltrim($ref, '@');
    $stmt = $pdo->prepare("SELECT id FROM user WHERE username = ? LIMIT 1");
    $stmt->execute([$u]);
    $id = $stmt->fetchColumn();
    if ($id) return [true, $id];

    // ۳) شماره موبایل (۰۹... یا ۹۸... یا +۹۸...)
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

// ── گرفتن اطلاعات واقعی کانفیگ از پنل سنایی ──
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
    $expiry = intval($obj['expiryTime'] ?? 0);
    $totalGB = intval($obj['totalGB'] ?? 0);

    $service_time = 0;
    if ($expiry > 0) {
        $service_time = (int)ceil(($expiry - time() * 1000) / 86400000);
        if ($service_time < 0) $service_time = 0;
    }
    $volume = 0;
    if ($totalGB > 0) {
        $volume = (int)round($totalGB / (1024 ** 3));
    }

    return [true, ['expire_days' => $service_time, 'volume_gb' => $volume, 'raw' => $obj]];
}

// ── اجرا ──
echo '<meta charset="utf-8"><div dir="rtl" style="font-family:Tahoma;max-width:720px;margin:30px auto">';
echo '<h2>🔗 اتصال کانفیگ‌های پنل به یوزرهای تلگرام</h2>';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $panelName = trim($_POST['panel'] ?? '');
    $lines = explode("\n", (string)($_POST['map'] ?? ''));
    $nameProduct = trim($_POST['product'] ?? 'کانفیگ واردشده');

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

    $okCount = 0; $failCount = 0;
    foreach ($lines as $lineNo => $lineRaw) {
        $line = trim($lineRaw);
        if ($line === '' || str_starts_with($line, '#')) continue;
        $lineNum = $lineNo + 1;

        // جداسازی:  اسم_کانفیگ = یوزرنیم
        $parts = preg_split('/\s*[=،:]\s*/', $line, 2);
        if (count($parts) < 2) {
            // اگه فقط یه مقدار بود، فرض می‌کنیم یوزرنیم تلگرام = اسم کانفیگ
            $parts = [$line, $line];
        }
        $configUser = trim($parts[0]);
        $tgRef = trim($parts[1]);

        if ($configUser === '') { echo "خط $lineNum: نام کانفیگ خالی است<br>"; $failCount++; continue; }

        // ۱) آیدی تلگرام
        [$ok, $tgId] = resolveTelegramId($tgRef, $pdo);
        if (!$ok) { echo "خط $lineNum: ❌ کانفیگ «" . e($configUser) . "» → " . e($tgId) . "<br>"; $failCount++; continue; }

        // ۲) تکراری نباشه
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM invoice WHERE username = ?");
        $stmt->execute([$configUser]);
        if ($stmt->fetchColumn() > 0) { echo "خط $lineNum: ⏭ کانفیگ «" . e($configUser) . "» از قبل متصل است — رد شد<br>"; $failCount++; continue; }

        // ۳) اطلاعات واقعی از پنل
        [$ok, $cfg] = fetchConfigFromPanel($configUser, $panel);
        if (!$ok) { echo "خط $lineNum: ❌ کانفیگ «" . e($configUser) . "» → " . e($cfg) . "<br>"; $failCount++; continue; }

        // ۴) ساخت رکورد سرویس
        $id_invoice = bin2hex(random_bytes(4));
        $notifctions = json_encode(['volume' => false, 'time' => false]);
        $stmt = $pdo->prepare("INSERT IGNORE INTO invoice (id_user, id_invoice, username, time_sell, Service_location, name_product, price_product, Volume, Service_time, Status, notifctions)
            VALUES (?, ?, ?, ?, ?, ?, '0', ?, ?, 'active', ?)");
        $stmt->execute([
            $tgId, $id_invoice, $configUser, time(),
            $panel['name_panel'], $nameProduct,
            $cfg['volume_gb'], $cfg['expire_days'], $notifctions,
        ]);

        $volTxt = $cfg['volume_gb'] == 0 ? 'نامحدود' : $cfg['volume_gb'] . ' گیگ';
        $timeTxt = $cfg['expire_days'] == 0 ? 'نامحدود' : $cfg['expire_days'] . ' روز';
        echo "خط $lineNum: ✅ کانفیگ «" . e($configUser) . "» به آیدی <b>" . e($tgId) . "</b> وصل شد (حجم: $volTxt، زمان: $timeTxt)<br>";
        $okCount++;
    }

    echo "<hr><p><b>نتیجه: $okCount موفق، $failCount ناموفق</b></p>";
    echo '<p><a href="?key=' . e($_GET['key']) . '">🔙 بازگشت و ادامه</a></p></div>';
    exit;
}

// ── فرم ──
$stmt = $pdo->query("SELECT name_panel, type FROM marzban_panel ORDER BY name_panel");
$panels = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo '<form method="post">';
echo '<label><b>۱) پنل:</b></label><br><select name="panel" style="width:100%;padding:8px">';
foreach ($panels as $p) {
    echo '<option value="' . e($p['name_panel']) . '">' . e($p['name_panel']) . ' (' . e($p['type']) . ')</option>';
}
echo '</select><br><br>';

echo '<label><b>۲) نام محصول (اختیاری — توی «سرویس‌های من» نمایش داده می‌شه):</b></label><br>';
echo '<input name="product" value="کانفیگ واردشده" style="width:100%;padding:8px"><br><br>';

echo '<label><b>۳) لیست کانفیگ‌ها — هر خط یک نفر:</b></label><br>';
echo '<textarea name="map" rows="15" style="width:100%;padding:8px;font-family:monospace" placeholder="ali12 = @mohammad&#10;mahdi = 123456789&#10;sara = 09123456789&#10;reza = @reza_vpn">';
echo '</textarea><br><br>';

echo '<button type="submit" style="padding:10px 30px;background:#2f855a;color:#fff;border:0;border-radius:6px;cursor:pointer">🚀 اتصال همه</button>';
echo '</form>';

echo '<hr><p style="color:#666;font-size:13px">';
echo '💡 راهنما:<br>';
echo '• یوزرنیم تلگرام: همون @یوزرنیمِ خودش (بدون @ هم قبوله)<br>';
echo '• آیدی عددی: از ربات‌هایی مثل @userinfobot می‌تونی بگیری<br>';
echo '• شماره موبایل: اگه موقع استفاده از بات، شماره‌ش رو فرستاده باشه، قبول می‌شه<br>';
echo '• ⚠️ مهم: هر یوزر باید قبلاً یک بار به بات /start زده باشه تا یوزرنیمش توی دیتابیس ثبت شده باشه<br>';
echo '• حجم و زمان واقعی هر کانفیگ از خود پنل سنایی خونده می‌شه<br>';
echo '• بعد از اتصال، یوزر توی «سرویس‌های من» بات، سرویسش رو می‌بینه و می‌تونه مدیریتش کنه<br>';
echo '• ⚠️ بعد از استفاده، این فایل رو از ریپو حذف کن!';
echo '</p></div>';
