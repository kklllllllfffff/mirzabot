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
        ['kind' => 'link', 'value' => 'vless://3896135c-1cbb-422c-a791-1ced77f24271@cdn.shop.mygharehshop.ir:2053?alpn=h2%2Chttp%2F1.1%2Ch3&authority=&encryption=none&fp=chrome&mode=multi&security=tls&serviceName=grpc&sni=cdn.shop.mygharehshop.ir&type=grpc#%F0%9F%87%B3%F0%9F%87%B1%20%F0%9D%93%9D%F0%9D%93%AE%F0%9D%93%BD%F0%9D%93%B1%F0%9D%93%AE%F0%9D%93%BB%F0%9D%93%B5%F0%9D%93%AA%F0%9D%93%B7%F0%9D%93%AD%F0%9D%93%BC%20%F0%9D%93%A3%F0%9D%93%A4%F0%9D%93%9D%F0%9D%93%9D%F0%9D%93%94%F0%9D%93%9B%20%F0%9F%92%8E-all_irancell_clients%7C%F0%9F%93%8A250.00GB', 'remark' => '🇳🇱 𝓝𝓮𝓽𝓱𝓮𝓻𝓵𝓪𝓷𝓭𝓼 𝓣𝓤𝓝𝓝𝓔𝓛 '],
        ['kind' => 'link', 'value' => 'vless://3896135c-1cbb-422c-a791-1ced77f24271@cdn.shop.mygharehshop.ir:8880?encryption=none&extra=%7B%22mode%22%3A%22auto%22%2C%22xPaddingBytes%22%3A%22100-1000%22%7D&fp=chrome&host=cdn.shop.mygharehshop.ir&mode=auto&path=%2F&pbk=VG_z6--SuKSnPjx8PAFaGGXar82C9a63LK6Q1GmW9zg&security=reality&sid=43a0d82184fe&sni=image.led.samsung.com&spx=%2F63e74edb3ee76e8&type=xhttp&x_padding_bytes=100-1000#%F0%9F%87%B9%F0%9F%87%B7%20%F0%9D%93%A3%F0%9D%93%BE%F0%9D%93%BB%F0%9D%93%B4%F0%9D%93%AE%F0%9D%94%82%20%F0%9D%93%A3%F0%9D%93%A4%F0%9D%93%9D%F0%9D%93%9D%F0%9D%93%94%F0%9D%93%9B%20%F0%9F%92%8E', 'remark' => '🇹🇷 𝓣𝓾𝓻𝓴𝓮𝔂 𝓣𝓤𝓝𝓝𝓔𝓛'],
        ['kind' => 'link', 'value' => 'vless://3896135c-1cbb-422c-a791-1ced77f24271@cdn.shop.mygharehshop.ir:28176?encryption=none&fp=chrome&pbk=G1WfZRyIBVLno18FEJ3eVwISh0H-_ZkV0ZKHckj9hCk&security=reality&sid=93&sni=music.youtube.com&spx=%2Fddfeb01f968fd65&type=tcp#%F0%9F%87%B9%F0%9F%87%B7%20Turkey%20-%20Reality(full%20tun)', 'remark' => '🇹🇷 Turkey - Reality(full tun)'],
        ['kind' => 'link', 'value' => 'vless://3896135c-1cbb-422c-a791-1ced77f24271@cdn.shop.mygharehshop.ir:19440?encryption=none&fp=chrome&pbk=3PhEYKQBMJEVb8U2OPajarRr4cDliFNvwbkMVL8eBnY&security=reality&sid=65483d&sni=www.cloudflare.com&spx=%2F4bc931d465ede08&type=tcp#%F0%9F%87%B9%F0%9F%87%B7%20Turkey%20-%20Ws(full%20tun)', 'remark' => '🇹🇷 Turkey - Ws(full tun)'],
    ],

    // آیا لینک‌های قبلیِ کلاینت هر بار جایگزین شوند؟ (true پیشنهاد می‌شود)
    'replace' => true,
];
