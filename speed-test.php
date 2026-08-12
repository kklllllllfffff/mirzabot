<?php
// ─────────────────────────────────────────────────────────────────────
//  speed-test.php — بعد از اعمال تغییرات، توی کنسول ریلوی اجرا کن:
//      php speed-test.php
//  و اعداد را برایم بفرست.
// ─────────────────────────────────────────────────────────────────────
$start = microtime(true);

$t = microtime(true);
require 'config.php';
echo "1) config.php: " . round(microtime(true) - $t, 2) . "s\n";

$t = microtime(true);
require 'function.php';
echo "2) function.php: " . round(microtime(true) - $t, 2) . "s\n";

$t = microtime(true);
require 'keyboard.php';
echo "3) keyboard.php: " . round(microtime(true) - $t, 2) . "s\n";

$t = microtime(true);
require 'panels.php';
echo "4) panels.php: " . round(microtime(true) - $t, 2) . "s\n";

echo "جمع کل: " . round(microtime(true) - $start, 2) . "s\n";
