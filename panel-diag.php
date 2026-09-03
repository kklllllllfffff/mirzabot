<?php
// ═══════════════════════════════════════════════════════════════════════════
//  panel-diag.php — تشخیصِ پنل 3x-ui (اینباندها + کلاینت‌ها)
//
//  کاربرد: برای رفع خطای «record not found» هنگام ساخت کلاینت.
//  این صفحه لیستِ اینباندها (id + remark + protocol) و کلاینت‌های پنل را نشان می‌دهد
//  تا بتوانی «inbounds» تنظیم‌شده در تنظیماتِ پنلِ بات را با اینباندهای موجود مطابقت بدهی.
//
//  استفاده:
//   1) این فایل را در ریپو بگذار و دیپلوی کن.
//   2) در ریلوی متغیر بساز:  TUNNEL_SECRET = رمز دلخواه  (اگر نبود، پیش‌فرض sovra-link-999)
//   3) باز کن:  https://دامنه/panel-diag.php?key=رمز
//   4) پنلِ x-ui_single را انتخاب کن و «نمایش» را بزن.
//
//  ⚠️ بعد از استفاده این فایل را از ریپو حذف کن.
// ═══════════════════════════════════════════════════════════════════════════
require_once 'config.php';

$key = getenv('TUNNEL_SECRET') ?: (getenv('LINKER_SECRET') ?: 'sovra-link-999');
if (!isset($_GET['key']) || !hash_equals($key, (string) $_GET['key'])) {
    http_response_code(403);
    echo '<meta charset="utf-8"><div dir="rtl" style="font-family:Tahoma;max-width:640px;margin:40px auto">';
    echo '<h3>🚫 دسترسی غیرمجاز</h3><p>برای استفاده، رمز را با ?key= بفرست.</p></div>';
    exit;
}

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

function panelJson($panel, $path) {
    $url = rtrim($panel['url_panel'], '/') . $path;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_HTTPHEADER => ['Accept: application/json', 'Authorization: Bearer ' . $panel['password_panel']],
    ]);
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($resp === false) return ['ok' => false, 'msg' => 'اتصال: ' . $err];
    $j = json_decode($resp, true);
    if (!is_array($j)) return ['ok' => false, 'msg' => 'پاسخ نامعتبر: ' . substr($resp, 0, 200)];
    return ['ok' => true, 'data' => $j];
}

$panel = null;
$inbounds = [];
$clients = [];
$error = '';
$shown = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $panelName = trim($_POST['panel'] ?? '');
    if ($panelName !== '') {
        $stmt = $pdo->prepare("SELECT * FROM marzban_panel WHERE name_panel = ? LIMIT 1");
        $stmt->execute([$panelName]);
        $panel = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    $shown = true;
    if (!$panel) {
        $error = 'پنل «' . $panelName . '» پیدا نشد.';
    } else {
        // اینباندها
        $r = panelJson($panel, '/panel/api/inbounds/list');
        if (!$r['ok']) {
            $error = 'اینباندها: ' . $r['msg'];
        } else {
            $obj = $r['data']['obj'] ?? [];
            foreach ((array)$obj as $inb) {
                $inbounds[] = [
                    'id' => intval($inb['id'] ?? 0),
                    'remark' => $inb['remark'] ?? '',
                    'protocol' => $inb['protocol'] ?? '',
                    'port' => $inb['port'] ?? '',
                    'enable' => ($inb['enable'] ?? false) ? 'فعال' : 'غیرفعال',
                ];
            }
        }
        // کلاینت‌ها
        $rc = panelJson($panel, '/panel/api/clients/list/paged?page=1&pageSize=50');
        if ($rc['ok'] && isset($rc['data']['obj']['items'])) {
            foreach ($rc['data']['obj']['items'] as $c) {
                $clients[] = $c['email'] ?? '';
            }
        }
    }
}

$panels = [];
try {
    $stmt = $pdo->query("SELECT name_panel, type FROM marzban_panel ORDER BY type, name_panel");
    $panels = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $ex) {
    $error .= ' | دیتابیس: ' . $ex->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>تشخیص پنل 3x-ui</title>
<style>
  body{font-family:Tahoma,Arial,sans-serif;background:#f1f5f9;margin:0;padding:20px}
  .wrap{max-width:820px;margin:0 auto;background:#fff;border-radius:12px;padding:24px;box-shadow:0 1px 4px rgba(0,0,0,.1)}
  h2{margin-top:0}
  label{display:block;font-weight:bold;margin:14px 0 4px}
  select{width:100%;padding:9px;border:1px solid #cbd5e1;border-radius:8px}
  .btn{display:inline-block;padding:10px 26px;border:0;border-radius:8px;cursor:pointer;font-size:15px;margin-top:12px}
  .btn-ok{background:#2563eb;color:#fff}
  table{width:100%;border-collapse:collapse;margin-top:12px;font-size:13px}
  td,th{border:1px solid #e2e8f0;padding:6px 8px;text-align:right}
  th{background:#f1f5f9}
  .err{color:#b91c1c;font-weight:bold}
  .muted{color:#64748b;font-size:12px}
  .cols{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px}
</style>
</head>
<body>
<div class="wrap">
  <h2>🩺 تشخیص پنل 3x-ui</h2>
  <p class="muted">این صفحه برای رفع خطای «record not found» هنگام ساخت کلاینت مفید است. لیست اینباندها و کلاینت‌های پنل را نشان می‌دهد.</p>

  <form method="post" action="?key=<?= e($_GET['key']) ?>">
    <label>پنل:</label>
    <select name="panel">
      <?php foreach ($panels as $p): ?>
        <option value="<?= e($p['name_panel']) ?>"><?= e($p['name_panel']) ?> (<?= e($p['type']) ?>)</option>
      <?php endforeach; ?>
    </select>
    <button class="btn btn-ok" type="submit">👁 نمایش اطلاعات پنل</button>
  </form>

  <?php if ($shown): ?>
    <?php if ($error): ?><p class="err">❌ <?= e($error) ?></p><?php endif; ?>

    <hr>
    <div class="cols">
      <div>
        <h3>🧩 اینباندها (<?= count($inbounds) ?>)</h3>
        <?php if (count($inbounds) === 0): ?>
          <p class="muted">اینباندی یافت نشد (یا دسترسیِ list ندارد).</p>
        <?php else: ?>
          <table>
            <tr><th>ID</th><th>برچسب</th><th>پروتکل</th><th>پورت</th><th>وضعیت</th></tr>
            <?php foreach ($inbounds as $i): ?>
              <tr>
                <td><?= $i['id'] ?></td>
                <td><?= e($i['remark']) ?></td>
                <td><?= e($i['protocol']) ?></td>
                <td><?= e($i['port']) ?></td>
                <td><?= e($i['enable']) ?></td>
              </tr>
            <?php endforeach; ?>
          </table>
        <?php endif; ?>
      </div>
      <div>
        <h3>👥 کلاینت‌ها (<?= count($clients) ?>)</h3>
        <?php if (count($clients) === 0): ?>
          <p class="muted">کلاینتی یافت نشد.</p>
        <?php else: ?>
          <table>
            <tr><th>email</th></tr>
            <?php foreach ($clients as $c): ?>
              <tr><td style="direction:ltr;text-align:left"><?= e($c) ?></td></tr>
            <?php endforeach; ?>
          </table>
        <?php endif; ?>
      </div>
    </div>

    <p class="muted" style="margin-top:16px">
      💡 راهنما: در تنظیماتِ پنلِ بات، فیلد <code>inbounds</code> باید شاملِ «id»های همین اینباندهای موجود باشد (مثلاً <code>[1]</code> یا <code>[1,2]</code>). اگر idای را بدهی که اینجا نیست، پنل خطای «record not found» می‌دهد.
    </p>
    <p><a href="?key=<?= e($_GET['key']) ?>">↺ بازگشت</a></p>
  <?php endif; ?>

  <hr>
  <p class="muted">⚠️ بعد از استفاده این فایل را از ریپو حذف کن (یا رمز <code>TUNNEL_SECRET</code> را قوی کن).</p>
</div>
</body>
</html>
