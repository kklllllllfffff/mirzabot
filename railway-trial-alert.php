<?php
// ═══════════════════════════════════════════════════════════════════
//  railway-trial-alert.php — هشدار پایان ترایال ریلوی به تلگرام
//
//  وقتی تعداد روزهای باقی‌مانده ترایال به حد آستانه رسید (پیش‌فرض ۲ روز)،
//  به تلگرامت پیام می‌ده که سرور جدید بسازی.
//
//  متغیرهای محیطی (توی سرویس Schedule ست کن):
//    TRIAL_END_DATE      = تاریخ پایان ترایال به فرمت YYYY-MM-DD (مهم)
//    TELEGRAM_BOT_TOKEN  = توکن بات
//    TELEGRAM_CHAT_ID    = آیدی عددی خودت (1822616028)
//    ALERT_DAYS          = چند روز قبل هشدار بده (پیش‌فرض: 2)
// ═══════════════════════════════════════════════════════════════════
$tgToken = getenv('TELEGRAM_BOT_TOKEN') ?: '';
$tgChat  = getenv('TELEGRAM_CHAT_ID') ?: '';
$endDate = getenv('TRIAL_END_DATE') ?: '';
$alertDays = (int)(getenv('ALERT_DAYS') ?: '2');

if ($tgToken === '' || $tgChat === '') {
    echo "ERROR: TELEGRAM_BOT_TOKEN / TELEGRAM_CHAT_ID is empty\n";
    exit(1);
}
if ($endDate === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
    echo "ERROR: TRIAL_END_DATE missing or invalid (format: YYYY-MM-DD)\n";
    echo "مثلاً اگه امروز 25 روز مونده، تاریخ پایان = " . date('Y-m-d', strtotime('+25 days')) . "\n";
    exit(1);
}

// ── محاسبه روزهای باقی‌مانده ──
$endTs = strtotime($endDate . ' 23:59:59');
$daysLeft = (int)ceil(($endTs - time()) / 86400);

echo "end_date={$endDate} days_left={$daysLeft}\n";

// ── اگه به آستانه رسید → پیام تلگرام ──
if ($daysLeft <= $alertDays) {
    $msg = "⚠️ <b>ترایال ریلوی داره تموم می‌شه!</b>\n\n"
         . "⏳ روزهای باقی‌مانده: <b>{$daysLeft} روز</b>\n"
         . "📅 تاریخ پایان: <b>{$endDate}</b>\n\n"
         . "🛑 بعد از این، کانتینرها قطع می‌شن!\n"
         . "🔧 همین حالا سرور ریلوی جدید بساز:\n"
         . "   1) پروژه جدید + MySQL\n"
         . "   2) بکاپ رو import کن\n"
         . "   3) وبهوک رو به دامنه جدید ست کن";

    $ctx = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query([
                'chat_id' => $tgChat,
                'text'    => $msg,
                'parse_mode' => 'HTML',
            ]),
            'timeout' => 15,
        ],
    ]);
    $resp = @file_get_contents("https://api.telegram.org/bot{$tgToken}/sendMessage", false, $ctx);
    echo $resp === false ? "Telegram send failed\n" : "Telegram alert sent ✅\n";
} else {
    echo "OK - {$daysLeft} days left (threshold {$alertDays})\n";
}
