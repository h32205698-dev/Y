<?php
// =========================
// TikTok Username Checker API (PHP Version)
// حماية API KEY + Rate Limiting
// =========================

// الإعدادات (بدل القيم بما يناسبك)
$API_KEY = "secret12345"; // مفتاح الحماية
$USER_AGENT = "com.zhiliaoapp.musically.go/330802 (Linux; U; Android 13; ar_EG; Infinix X6525; Build/TP1A.220624.014;tt-ok/3.12.13.2-alpha.68-quictest)";
$TT_TOKEN = "034c270747e6235dad0a79598b910f872b00d99edfe563639b952887bec2165747...";
$COOKIE = "store-idc=alisg; store-country-code=iq; install_id=7545220997499815700; ...";

// نظام تتبع الطلبات (Rate Limiting)
session_start();
if (!isset($_SESSION['requests'])) {
    $_SESSION['requests'] = [];
}

function rate_limit($limit = 35, $time_window = 1);
 {
    $ip = $_SERVER['REMOTE_ADDR'];
    $now = time();

    if (!isset($_SESSION['requests'][$ip])) {
        $_SESSION['requests'][$ip] = [];
    }

    // تنظيف الطلبات القديمة
    $_SESSION['requests'][$ip] = array_filter($_SESSION['requests'][$ip], function($t) use ($now, $time_window) {
        return ($now - $t) < $time_window;
    });

    // تحقق من العدد
    if (count($_SESSION['requests'][$ip]) >= $limit) {
        http_response_code(429);
        echo json_encode(["detail" => "Too many requests, try again later."]);
        exit;
    }

    // سجل الطلب الحالي
    $_SESSION['requests'][$ip][] = $now;
}

// نوع الإخراج JSON
header("Content-Type: application/json");

// تحقق من مفتاح API
if (!isset($_SERVER['HTTP_X_API_KEY']) || $_SERVER['HTTP_X_API_KEY'] !== $API_KEY) {
    http_response_code(401);
    echo json_encode(["detail" => "Invalid or missing API Key"]);
    exit;
}

// تطبيق Rate Limiting
rate_limit();

// المعالجة
if (isset($_GET['username'])) {
    $username = $_GET['username'];

    $url = "https://api16-normal-c-alisg.tiktokv.com/aweme/v1/unique/id/check/?unique_id=" . urlencode($username) . "&request_tag_from=h5";

    $headers = [
        "User-Agent: " . $USER_AGENT,
        "x-tt-token: " . $TT_TOKEN,
        "Cookie: " . $COOKIE
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    $is_available = false;
    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['is_valid']) && $data['is_valid'] === true) {
            $is_available = true;
        }
    }

    $result = [
        "result" => "User check is done...\nUsername : $username\nis_available : " . ($is_available ? "true" : "false") . "\nDEV TELEGRAM @A8_jz"
    ];

    echo json_encode($result, JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(["detail" => "Please provide a username"], JSON_UNESCAPED_UNICODE);
}
