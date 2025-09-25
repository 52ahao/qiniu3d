<?php
require_once '../../core/Database.php';
require_once '../../core/Response.php';
require_once '../../core/JWT.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方法不允许', 405);
}

// 验证JWT token
$headers = getallheaders();
$token = null;

if (isset($headers['Authorization'])) {
    $auth = $headers['Authorization'];
    if (preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
        $token = $matches[1];
    }
}

if (!$token) {
    Response::error('未提供认证token', 401);
}

$payload = JWT::decode($token);
if (!$payload) {
    Response::error('认证token无效', 401);
}

$input = json_decode(file_get_contents('php://input'), true);

// 验证输入
if (empty($input['points']) || empty($input['payment_method'])) {
    Response::error('积分数和支付方式不能为空', 400);
}

$points = intval($input['points']);
if ($points <= 0 || $points > 10000) {
    Response::error('积分数必须在1-10000之间', 400);
}

$allowedMethods = ['wechat', 'alipay'];
if (!in_array($input['payment_method'], $allowedMethods)) {
    Response::error('不支持的支付方式', 400);
}

try {
    $db = Database::getInstance();
    
    // 计算价格（1元=10积分）
    $amount = $points / 10;
    
    // 创建订单
    $db->query(
        "INSERT INTO orders (user_id, type, amount, points, status, payment_method) VALUES (?, 'points', ?, ?, 'pending', ?)",
        [$payload['user_id'], $amount, $points, $input['payment_method']]
    );
    
    $orderId = $db->lastInsertId();
    
    // 这里应该调用支付接口
    // 示例返回支付信息
    Response::success([
        'order_id' => $orderId,
        'amount' => $amount,
        'points' => $points,
        'payment_method' => $input['payment_method'],
        'payment_url' => 'https://pay.example.com/pay/' . $orderId, // 实际支付URL
        'qr_code' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==' // 二维码
    ], '订单创建成功');
    
} catch (Exception $e) {
    Response::error('创建订单失败: ' . $e->getMessage(), 500);
}