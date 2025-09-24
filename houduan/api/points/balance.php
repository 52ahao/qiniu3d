<?php
require_once '../../core/Database.php';
require_once '../../core/Response.php';
require_once '../../core/JWT.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
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

try {
    $db = Database::getInstance();
    
    // 获取用户积分余额
    $user = $db->fetch(
        "SELECT points FROM users WHERE id = ?",
        [$payload['user_id']]
    );
    
    if (!$user) {
        Response::error('用户不存在', 404);
    }
    
    // 获取积分记录
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(50, intval($_GET['limit']))) : 20;
    $offset = ($page - 1) * $limit;
    
    $records = $db->fetchAll(
        "SELECT type, amount, description, created_at 
         FROM point_records 
         WHERE user_id = ? 
         ORDER BY created_at DESC 
         LIMIT ? OFFSET ?",
        [$payload['user_id'], $limit, $offset]
    );
    
    // 获取总数
    $total = $db->fetch(
        "SELECT COUNT(*) as count FROM point_records WHERE user_id = ?",
        [$payload['user_id']]
    )['count'];
    
    Response::success([
        'balance' => $user['points'],
        'records' => $records,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ]);
    
} catch (Exception $e) {
    Response::error('获取积分信息失败: ' . $e->getMessage(), 500);
}