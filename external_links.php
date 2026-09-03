<?php
/**
 * external_links.php
 * ----------------------------------------------------------------------------
 * یک ماژول مستقل (بدون وابستگی) برای افزودن «لینک خارجی / ساب اسکریپشنِ remote»
 * به کلاینت‌های پنل 3x-ui (Sanaei / MHSanaei) از طریق:
 *
 *     POST  /panel/api/clients/{email}/externalLinks
 *
 * هم در ابزار CLI  (add_external_links.php)  و هم در بات (mirzabot) استفاده می‌شود.
 * ----------------------------------------------------------------------------
 */

/**
 * بارگذاری تنظیمات از external_links_config.php
 * @return array
 */
function external_links_config(): array
{
    static $cfg = null;
    if ($cfg === null) {
        $path = $GLOBALS['__ext_config_path'] ?? (__DIR__ . '/external_links_config.php');
        $cfg = is_file($path) ? (array) include $path : [];
        $cfg = array_merge([
            'base_url' => '',
            'token'    => '',
            'links'    => [],
            'replace'  => true,
        ], $cfg);
    }
    return $cfg;
}

/**
 * درخواست HTTP ساده (بر پایه curl) بدون وابستگی به request.php بات.
 * @return array{status:int|null, body:string|null, error:?string}
 */
function external_links_http(string $base_url, string $token, string $method, string $path, $payload = null): array
{
    $url = rtrim($base_url, '/') . $path;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => strtoupper($method),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT_MS     => 15000,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
        ],
    ]);
    if ($payload !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    $body = curl_exec($ch);
    if ($body === false) {
        $error = curl_error($ch);
        curl_close($ch);
        return ['status' => null, 'body' => null, 'error' => $error];
    }
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['status' => $status, 'body' => $body, 'error' => null];
}

/**
 * خواندن لینک‌های خارجی فعلیِ یک کلاینت.
 * @return array{success:bool, data:array, raw:?array}  data = آرایه اِی نتایج
 */
function external_links_get(string $base_url, string $token, string $email): array
{
    $r = external_links_http($base_url, $token, 'GET', '/panel/api/clients/' . rawurlencode($email) . '/externalLinks');
    if ($r['status'] !== 200) {
        return ['success' => false, 'data' => [], 'raw' => $r];
    }
    $json = json_decode($r['body'], true);
    if (!is_array($json) || empty($json['success'])) {
        return ['success' => false, 'data' => [], 'raw' => $r];
    }
    $data = $json['obj'] ?? [];
    $external = isset($data['externalLinks']) && is_array($data['externalLinks']) ? $data['externalLinks'] : [];
    // 3x-ui sometimes nests under ['obj']['externalLinks']; normalize
    return ['success' => true, 'data' => $external, 'raw' => $r, 'obj' => $data];
}

/**
 * نوشتن (جایگزینی) لینک‌های خارجی و/یا ساب اسکریپشن‌های remote برای یک کلاینت.
 * @param string   $email          شناسه کلاینت در پنل (مثل alireza@test.com)
 * @param array    $externalLinks  آرایه‌ای از آیتم‌های {kind,value,remark,enable,expiryTime,namePrefix}
 * @return array{success:bool, status:int|null, msg:string, raw:?array}
 */
function external_links_set(string $base_url, string $token, string $email, array $externalLinks): array
{
    $payload = ['externalLinks' => array_values($externalLinks)];
    $r = external_links_http($base_url, $token, 'POST', '/panel/api/clients/' . rawurlencode($email) . '/externalLinks', $payload);
    $success = $r['status'] === 200;
    $msg = $success ? 'OK' : ('HTTP ' . var_export($r['status'], true) . ($r['error'] ? ' - ' . $r['error'] : ''));
    if (!$success && $r['body']) {
        $j = json_decode($r['body'], true);
        if (is_array($j) && !empty($j['msg'])) {
            $msg .= ' | ' . $j['msg'];
        }
    }
    return ['success' => $success, 'status' => $r['status'], 'msg' => $msg, 'raw' => $r];
}

/**
 * نرمال‌سازیِ هر آیتم لینک خارجی.
 */
function external_links_normalize_item(array $item): ?array
{
    $kind = strtolower((string) ($item['kind'] ?? ''));
    $value = trim((string) ($item['value'] ?? ''));
    if (!in_array($kind, ['link', 'subscription'], true)) {
        return null;
    }
    if ($value === '' || stripos($value, 'REPLACE') !== false) {
        return null;
    }
    $out = [
        'kind'  => $kind,
        'value' => $value,
        'enable' => array_key_exists('enable', $item) ? (bool) $item['enable'] : true,
    ];
    if (!empty($item['remark'])) {
        $out['remark'] = (string) $item['remark'];
    }
    if ($kind === 'subscription' && !empty($item['namePrefix'])) {
        $out['namePrefix'] = (string) $item['namePrefix'];
    }
    if (array_key_exists('expiryTime', $item)) {
        $out['expiryTime'] = (int) $item['expiryTime'];
    } else {
        $out['expiryTime'] = 0;
    }
    return $out;
}

/**
 * اعمال لینک‌های کانفیگ‌شده روی یک کلاینت.
 * @param string|null $override_links  اگر null باشد از فایل کانفیگ خوانده می‌شود
 */
function external_links_apply(string $base_url, string $token, string $email, ?bool $replace = null, ?array $override_links = null): array
{
    $cfg = external_links_config();
    if ($base_url === '') {
        $base_url = $cfg['base_url'] ?? '';
    }
    if ($token === '') {
        $token = $cfg['token'] ?? '';
    }
    if ($base_url === '' || $token === '') {
        return ['success' => false, 'status' => null, 'msg' => 'base_url/token not configured', 'raw' => null];
    }
    if ($replace === null) {
        $replace = (bool) ($cfg['replace'] ?? true);
    }
    $links = $override_links !== null ? $override_links : ($cfg['links'] ?? []);

    $normalized = [];
    foreach ($links as $item) {
        $n = external_links_normalize_item($item);
        if ($n !== null) {
            $normalized[] = $n;
        }
    }
    if (count($normalized) === 0) {
        return ['success' => false, 'status' => null, 'msg' => 'no valid links configured', 'raw' => null];
    }

    if (!$replace) {
        $current = external_links_get($base_url, $token, $email);
        $existing = $current['success'] ? array_values($current['data']) : [];
        // merge by kind+value (پنل بر اساس kind+value تطبیق می‌دهد)
        $map = [];
        foreach ($existing as $e) {
            $map[strtolower(($e['kind'] ?? '') . '|' . ($e['value'] ?? ''))] = $e;
        }
        foreach ($normalized as $n) {
            $map[strtolower($n['kind'] . '|' . $n['value'])] = $n;
        }
        $normalized = array_values($map);
    }

    return external_links_set($base_url, $token, $email, $normalized);
}
