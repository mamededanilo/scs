<?php
function ping_url(string $url): array {
    $url = trim($url);
    if (!preg_match('#^https?://#i', $url)) $url = 'https://' . $url;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_NOBODY => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT => 'SCS-Ping/1.0',
        CURLOPT_RETURNTRANSFER => true,
    ]);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_errno($ch);
    curl_close($ch);
    if ($err || $code === 0) {
        return ['ok' => false, 'status' => null, 'message' => 'Inacessível'];
    }
    if ($code >= 400 && !in_array($code, [401, 403])) {
        // tentar GET
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'SCS-Ping/1.0',
        ]);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    }
    $ok = $code >= 200 && $code < 400;
    return ['ok' => $ok, 'status' => $code, 'message' => $ok ? "HTTP $code" : 'Inacessível'];
}
