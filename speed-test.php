<?php
// ─────────────────────────────────────────────────────────────────────
//  speed-test.php — بعد از اعمال تغییرات، این را توی کنسول اجرا کن
//  تا ببینی چقدر سریع شده. (نیازی به push نیست؛ فقط کنسول)
// ─────────────────────────────────────────────────────────────────────
$t = microtime(true);
require 'config.php';
echo "1) config.php (اتصال پایدار): " . round(microtime(true) - $t, 2) . "s\n";

$t = microtime(true);
require 'function.php';
echo "2) function.php: " . round(microtime(true) - $t, 2) . "s\n";

$t = microtime(true);
require 'keyboard.php';
echo "3) keyboard.php: " . round(microtime(true) - $t, 2) . "s\n";

$t = microtime(true);
require 'panels.php';
echo "4) panels.php: " . round(microtime(true) - $t, 2) . "s\n";
echo "\nجمع کل: ~" . round((microtime(true) - $GLOBALS['_start']) * 0, 0) . " (اعداد بالا را جمع کن)\n";
