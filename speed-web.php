<?php
// ─────────────────────────────────────────────────────────────────────
//  speed-web.php — تست سرعت در مرورگر (بعد از اعمال تغییرات)
//  آدرس:  https://دامنه/speed-web.php
//  دو بار اجرا می‌کند: اولی سرد، دومی با کش گرم (APCu).
//  بعد از تست، این فایل را حذف کن.
// ─────────────────────────────────────────────────────────────────────
header('Content-Type: text/plain; charset=utf-8');
echo "تست سرعت میرزا بات روی ریلوی\n";
echo "APCu فعال؟ " . (function_exists('apcu_enabled') && apcu_enabled() ? 'بله ✅' : 'خیر ❌') . "\n";
echo "=====================================\n";

function runtest($label)
{
    global $pdo;
    // مثل مسیر واقعی index.php، متغیرهای پیش‌فرض
    $from_id = 0;
    $text = 'test';
    $username = 'test';
    $Chat_type = 'private';
    $t0 = microtime(true);

    $t = microtime(true);
    require 'config.php';
    $c1 = round(microtime(true) - $t, 3);

    $t = microtime(true);
    require 'function.php';
    $c2 = round(microtime(true) - $t, 3);

    $t = microtime(true);
    require 'keyboard.php';
    $c3 = round(microtime(true) - $t, 3);

    $t = microtime(true);
    require 'panels.php';
    $c4 = round(microtime(true) - $t, 3);

    echo $label . ": config={$c1}s function={$c2}s keyboard={$c3}s panels={$c4}s | جمع: " . round(microtime(true) - $t0, 3) . "s\n";
}

echo "اجرای ۱ (سرد — اولین بار):\n";
runtest('   ');
echo "\nاجرای ۲ (کش گرم — APCu پر شده):\n";
runtest('   ');
echo "\nاگه اجرای دوم خیلی سریع‌تر بود، یعنی کش کار می‌کند ✅\n";
echo "برای تلگرام هم باید همون سرعت اجرای دوم حس بشود.\n";
