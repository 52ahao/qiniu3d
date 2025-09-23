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
if (empty($input['username']) || empty($input['email']) || empty($input['password'])) {
    Response::error('用户名、邮箱和密码不能为空', 400);
}

if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    Response::error('邮箱格式不正确', 400);
}

if (strlen($input['password']) < 6) {
    Response::error('密码长度至少6位', 400);
}

try {
    $db = Database::getInstance();
    
    // 检查用户名和邮箱是否已存在
    $existingUser = $db->fetch(
        "SELECT id FROM users WHERE username = ? OR email = ?",
        [$input['username'], $input['email']]
    );
    
    if ($existingUser) {
        Response::error('用户名或邮箱已存在', 400);
    }
    
    // 创建用户
    $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
    $config = require_once '../../config/config.php';
    $registerReward = $config['points']['register_reward'];
    
    $db->query(
        "INSERT INTO users (username, email, password, points) VALUES (?, ?, ?, ?)",
        [$input['username'], $input['email'], $hashedPassword, $registerReward]
    );
    
    $userId = $db->lastInsertId();
    
    // 记录积分奖励
    $db->query(
        "INSERT INTO point_records (user_id, type, amount, description) VALUES (?, 'earn', ?, '注册奖励')",
        [$userId, $registerReward]
    );
    
    // 生成JWT token
    $token = JWT::encode([
        'user_id' => $userId,
        'username' => $input['username'],
        'exp' => time() + 7 * 24 * 3600
    ]);
    
    Response::success([
        'token' => $token,
        'user' => [
            'id' => $userId,
            'username' => $input['username'],
            'email' => $input['email'],
            'points' => $registerReward
        ]
    ], '注册成功');
    
} catch (Exception $e) {
    Response::error('注册失败: ' . $e->getMessage(), 500);
}