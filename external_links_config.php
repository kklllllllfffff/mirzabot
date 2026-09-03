<?php
/**
 * external_links_config.php  —  تنظیمات «لینک‌های خارجی / سابِ remote» برای بات Mirza
 * ==================================================================================
 * وقتی کلاینتِ اپراتورش «ایرانسل» باشد، این لینک‌های تانل‌شده به‌صورت خودکار در بخش
 * «Add external link» پنل 3x-ui برای آن کلاینت ثبت می‌شود (هنگام تحویل کانفیگ).
 *
 * ⚠️ فقط همین فایل را ویرایش کن؛ فایل‌های دیگر را دست نزن.
 *
 * ⚠️ این فایل را در «ریشه‌ی بات» (کنار index.php) قرار بده.
 * ==================================================================================
 */

return [

    // ─── اطلاعات پنل 3x-ui ───
    // وقتی بات صدا می‌زند، base_url و token به‌صورت خودکار از تنظیماتِ همان پنل
    // خوانده می‌شود؛ این دو مقدار فقط برای «ابزار CLI» (روی سرور) استفاده می‌شود.
    'base_url' => 'https://your-panel.example.com:2096',  // فقط برای CLI
    'token'    => 'YOUR_API_TOKEN',                       // فقط برای CLI

    // ─── لیست کانفیگ‌های تانل‌شده از پنل دیگر ───
    // هر آیتم:
    //   'kind'  → 'link' (لینک مستقیم vless/vmess/...)  یا  'subscription' (سابِ remote)
    //   'value' → خودِ لینک یا آدرس ساب
    //   'remark'→ نام گره در خروجی ساب
    //   'namePrefix' → (اختیاری، فقط subscription) پیشوندِ نام گره‌های آن ساب
    'links' => [
        ['kind' => 'subscription', 'value' => 'https://tunnel.example/sub/REPLACE_1', 'remark' => 'Tunnel-IR-1', 'namePrefix' => '[IR] '],
        ['kind' => 'subscription', 'value' => 'https://tunnel.example/sub/REPLACE_2', 'remark' => 'Tunnel-IR-2', 'namePrefix' => '[IR] '],
        ['kind' => 'link', 'value' => 'vless://REPLACE_WITH_REAL_NODE_3', 'remark' => 'Tunnel-IR-3'],
        ['kind' => 'link', 'value' => 'vmess://REPLACE_WITH_REAL_NODE_4', 'remark' => 'Tunnel-IR-4'],
    ],

    // آیا لینک‌های قبلیِ کلاینت هر بار جایگزین شوند؟ (true پیشنهاد می‌شود)
    'replace' => true,
];
