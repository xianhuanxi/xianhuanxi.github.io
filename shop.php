<?php
// 你的小程序信息
$appid = 'wx484b308667bffedd';
$secret = '0d26474b24e1486b5357af7af0ae39d7';

// 默认跳转路径（改成你的首页或常用页，如分包）
$path = $_GET['path'] ?? 'main_pages/index/index';  // 支持动态 ?path=xxx
$query = $_GET['query'] ?? '';  // 支持动态 ?query=id=123

// 获取 access_token
$token_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$secret";
$token_res = json_decode(file_get_contents($token_url), true);
$access_token = $token_res['access_token'] ?? '';

if (!$access_token) {
    die('获取 token 失败');
}

// 生成 scheme
$api_url = "https://api.weixin.qq.com/wxa/generatescheme?access_token=$access_token";
$post_data = json_encode([
    'jump_wxa' => [
        'path' => $path,
        'query' => $query
    ],
    'expire_type' => 0  // 尽量长有效期（实际最多30天）
]);

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$result = curl_exec($ch);
curl_close($ch);

$res = json_decode($result, true);
$scheme = $res['openlink'] ?? '';

if (!$scheme) {
    die('生成 scheme 失败：' . $result);
}

// 输出和原版一模一样的 Markdown 链接 + 自动跳转
echo "[打开小程序]($scheme)";

echo "\n\n----------------------------------------------------------------------------------------------------";

// 自动跳转（兼容 Android/iOS）
echo "<script>window.location.href = '$scheme';</script>";
?>
