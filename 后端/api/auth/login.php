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

$input = json_decode(file_get_contents('php://input'), true);

// 验证输入
if (empty($input['username']) || empty($input['password'])) {
    Response::error('用户名和密码不能为空', 400);
}

try {
    $db = Database::getInstance();
    
    // 查找用户
    $user = $db->fetch(
        "SELECT id, username, email, password, avatar, points, status FROM users WHERE username = ? OR email = ?",
        [$input['username'], $input['username']]
    );
    
    if (!$user) {
        Response::error('用户名或密码错误', 401);
    }
    
    if ($user['status'] != 1) {
        Response::error('账户已被禁用', 401);
    }
    
    if (!password_verify($input['password'], $user['password'])) {
        Response::error('用户名或密码错误', 401);
    }
    
    // 生成JWT token
    $token = JWT::encode([
        'user_id' => $user['id'],
        'username' => $user['username'],
        'exp' => time() + 7 * 24 * 3600
    ]);
    
    Response::success([
        'token' => $token,
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'avatar' => $user['avatar'],
            'points' => $user['points']
        ]
    ], '登录成功');
    
} catch (Exception $e) {
    Response::error('登录失败: ' . $e->getMessage(), 500);
}