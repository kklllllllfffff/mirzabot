<?php
// ═══════════════════════════════════════════════════════════════════════════
//  apply-tunnel-links.php — افزودن لینک‌های تانل‌شده به «کلاینت‌های» پنل 3x-ui
//
//  تفاوتش با link-users.php: اینجا کلاینت‌ها از دیتابیسِ ربات ساخته نمی‌شوند،
//  بلکه به «کلاینت‌های موجود در خودِ پنل» (با email/نامِ همون) در بخش
//  «Add external link» لینک‌های خارجی/سابِ remote اضافه می‌شود.
//
//  استفاده:
//   1) این فایل را در ریپو بگذار و دیپلوی کن
//   2) در ریلوی یک متغیر بساز:  TUNNEL_SECRET = رمز دلخواه  (اگر نبود، پیش‌فرض sovra-link-999)
//   3) در مرورگر باز کن:  https://دامنه/apply-tunnel-links.php?key=رمز
//   4) پنلِ x-ui_single را انتخاب کن، ایمیل/نامِ کلاینت‌ها را هر خط یکی بنویس،
//      و «افزودن» یا «نمایش» را بزن.
//
//  ⚠️ بعد از استفاده حتماً این فایل را از ریپو حذف کن!  (و/یا رمز TUNNEL_SECRET را قوی کن)
// ═══════════════════════════════════════════════════════════════════════════
require_once 'config.php';
require_once __DIR__ . '/external_links.php';
require_once __DIR__ . '/external_links_config.php';

$key = getenv('TUNNEL_SECRET') ?: (getenv('LINKER_SECRET') ?: 'sovra-link-999');
if (!isset($_GET['key']) || !hash_equals($key, (string) $_GET['key'])) {
    http_response_code(403);
    echo '<meta charset="utf-8"><div dir="rtl" style="font-family:Tahoma;max-width:640px;margin:40px auto">';
    echo '<h3>🚫 دسترسی غیرمجاز</h3><p>برای استفاده، رمز را با ?key= بفرست.</p></div>';
    exit;
}

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

// ── لیست لینک‌های کانفیگ‌شده (برای نمایش به کاربر) ──
function linksSummary(): array
{
    $cfg = external_links_config();
    $out = [];
    foreach ($cfg['links'] ?? [] as $L) {
        $v = trim((string)($L['value'] ?? ''));
        if ($v === '' || stripos($v, 'REPLACE') !== false) {
            continue; // placeholder ها را نشان نده
        }
        $kind = ($L['kind'] ?? 'link') === 'subscription' ? 'ساب (remote)' : 'لینک مستقیم';
        $out[] = ['kind' => $kind, 'remark' => $L['remark'] ?? '', 'value' => $v];
    }
    return $out;
}

// ── وضعیت کلاینت‌ها + اعمال/نمایش ──
$panel    = null;
$results  = [];
$mode     = $_POST['mode'] ?? ($_GET['mode'] ?? 'apply');
$emailsIn = trim((string)($_POST['emails'] ?? ($_GET['emails'] ?? '')));
$replace  = !(isset($_POST['keep']) && $_POST['keep'] === '1');
$emails   = array_values(array_unique(array_filter(array_map('trim', preg_split('/[\r\n,;]+/', $emailsIn)))));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $panelName = trim($_POST['panel'] ?? '');
    if ($panelName !== '') {
        $stmt = $pdo->prepare("SELECT * FROM marzban_panel WHERE name_panel = ? AND type = 'x-ui_single' LIMIT 1");
        $stmt->execute([$panelName]);
        $panel = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    if (!$panel) {
        $results[] = ['email' => '', 'status' => 'error', 'msg' => 'پنل x-ui_single «' . $panelName . '» پیدا نشد.'];
    } else {
        foreach ($emails as $em) {
            if ($em === '') continue;
            if ($mode === 'list') {
                $r = external_links_get($panel['url_panel'], $panel['password_panel'], $em);
                if (!$r['success']) {
                    $results[] = ['email' => $em, 'status' => 'error', 'msg' => 'خطا: ' . ($r['raw']['error'] ?? ('HTTP ' . var_export($r['raw']['status'] ?? null, true)))];
                } else {
                    $n = count($r['data']);
                    $results[] = ['email' => $em, 'status' => 'ok', 'msg' => 'دارای ' . $n . ' لینک', 'links' => $r['data']];
                }
            } else {
                $res = external_links_apply($panel['url_panel'], $panel['password_panel'], $em, $replace);
                $results[] = ['email' => $em, 'status' => $res['success'] ? 'ok' : 'error', 'msg' => $res['success'] ? 'OK' : ('خطا: ' . $res['msg'])];
            }
        }
    }
}

// ── لیست پنل‌ها ──
$panels = [];
try {
    $stmt = $pdo->query("SELECT name_panel FROM marzban_panel WHERE type = 'x-ui_single' ORDER BY name_panel");
    $panels = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (count($panels) === 0) {
        // شاید پنل تایپ‌بندی متفاوتی دارد؛ همه را بیاور
        $stmt2 = $pdo->query("SELECT name_panel, type FROM marzban_panel ORDER BY type, name_panel");
        $panels = array_map(fn($p) => $p['name_panel'], $stmt2->fetchAll(PDO::FETCH_ASSOC));
    }
} catch (Throwable $e) {
    $results[] = ['email' => '', 'status' => 'error', 'msg' => 'خطای دیتابیس: ' . $e->getMessage()];
}

$summary = linksSummary();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>افزودن لینک‌های تانل‌شده به کلاینت‌ها</title>
<style>
  body{font-family:Tahoma,Arial,sans-serif;background:#f1f5f9;margin:0;padding:20px}
  .wrap{max-width:760px;margin:0 auto;background:#fff;border-radius:12px;padding:24px;box-shadow:0 1px 4px rgba(0,0,0,.1)}
  h2{margin-top:0}
  label{display:block;font-weight:bold;margin:14px 0 4px}
  select,input[type=text],textarea{width:100%;box-sizing:border-box;padding:9px;border:1px solid #cbd5e1;border-radius:8px;font-family:inherit}
  textarea{font-family:monospace;direction:ltr;text-align:left;font-size:13px}
  .btn{display:inline-block;padding:10px 22px;border:0;border-radius:8px;cursor:pointer;font-size:15px;margin:4px 4px 0 0}
  .btn-apply{background:#2563eb;color:#fff}
  .btn-list{background:#475569;color:#fff}
  .box{border:1px solid #e2e8f0;border-radius:8px;padding:12px;margin-top:8px;background:#f8fafc}
  .ok{color:#15803d}.err{color:#b91c1c}.warn{color:#b45309}
  table{width:100%;border-collapse:collapse;margin-top:8px;font-size:13px}
  td,th{border:1px solid #e2e8f0;padding:6px 8px;text-align:right}
  th{background:#f1f5f9}
  .muted{color:#64748b;font-size:12px}
</style>
</head>
<body>
<div class="wrap">
  <h2>🔗 افزودن لینک‌های تانل‌شده به کلاینت‌ها</h2>
  <p class="muted">نام/ایمیل کلاینت‌هایی که «اپراتورشون ایرانسله» را بنویس؛ لینک‌های تانل‌شده از طریق <b>Add external link</b> به آن‌ها اضافه می‌شود.</p>

  <?php if (count($summary) > 0): ?>
  <div class="box">
    <b>📦 کانفیگ‌های تانل‌شده‌ای که اضافه می‌شوند (<?= count($summary) ?>):</b>
    <ul style="margin:6px 0 0;padding-right:18px">
      <?php foreach ($summary as $s): ?>
        <li><b><?= e($s['remark']) ?></b> — <?= e($s['kind']) ?> <span class="muted">(<?= e(substr($s['value'], 0, 40)) ?>…)</span></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php else: ?>
  <div class="box" style="border-color:#fca5a5;background:#fef2f2">
    <b>⚠️ هیچ کانفیگِ معتبری تنظیم نشده.</b> در <code>external_links_config.php</code> آرایه‌ی <code>links</code> را با لینک‌های واقعی پر کن (مقادیرِ REPLACE نادیده گرفته می‌شوند).
  </div>
  <?php endif; ?>

  <form method="post" action="?key=<?= e($_GET['key']) ?>">
    <label>۱) پنل (نوع x-ui_single):</label>
    <select name="panel">
      <?php if (count($panels) === 0): ?>
        <option value="">— پنلی پیدا نشد —</option>
      <?php endif; ?>
      <?php foreach ($panels as $p): ?>
        <option value="<?= e($p) ?>"><?= e($p) ?></option>
      <?php endforeach; ?>
    </select>

    <label>۲) ایمیل/نام کلاینت‌ها (هر خط یک کلاینت):</label>
    <textarea name="emails" rows="8" placeholder="alireza@test.com&#10;user2_irancell&#10;user3_normal"><?= e($emailsIn) ?></textarea>

    <label style="display:flex;align-items:center;gap:8px;font-weight:normal">
      <input type="checkbox" name="keep" value="1" <?= $replace ? '' : 'checked' ?>> افزودن (حفظ لینک‌های قبلی کلاینت) — اگر تیک نزنی، جایگزین می‌شود
    </label>

    <div style="margin-top:12px">
      <button class="btn btn-apply" type="submit" name="mode" value="apply">🚀 افزودن لینک‌ها</button>
      <button class="btn btn-list" type="submit" name="mode" value="list">👁 نمایش لینک‌های فعلی</button>
    </div>
  </form>

  <?php if (count($results) > 0): ?>
  <hr>
  <h3>نتیجه</h3>
  <table>
    <tr><th>کلاینت</th><th>وضعیت</th><th>پیام</th></tr>
    <?php foreach ($results as $r): $cls = $r['status'] === 'ok' ? 'ok' : 'err'; ?>
      <tr>
        <td style="direction:ltr;text-align:left"><?= e($r['email']) ?></td>
        <td class="<?= $cls ?>"><?= $r['status'] === 'ok' ? '✅ OK' : '❌' ?></td>
        <td>
          <?= e($r['msg']) ?>
          <?php if (!empty($r['links'])): ?>
            <ul style="margin:4px 0 0;padding-right:16px">
              <?php foreach ($r['links'] as $L): ?>
                <li><?= e($L['kind'] ?? '?') ?> — <span style="direction:ltr;display:inline-block"><?= e(substr($L['value'] ?? '', 0, 70)) ?></span></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
  <p style="margin-top:10px"><a href="?key=<?= e($_GET['key']) ?>">↺ شروع مجدد</a></p>
  <?php endif; ?>

  <hr>
  <p class="muted">⚠️ بعد از استفاده، این فایل را از ریپو حذف کن (یا رمز <code>TUNNEL_SECRET</code> را قوی کن).</p>
</div>
</body>
</html>
