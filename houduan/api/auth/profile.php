<?php
require_once '../../core/Database.php';
require_once '../../core/Response.php';
require_once '../../core/JWT.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
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
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // 获取用户信息
        $user = $db->fetch(
            "SELECT id, username, email, avatar, points, created_at FROM users WHERE id = ?",
            [$payload['user_id']]
        );
        
        if (!$user) {
            Response::error('用户不存在', 404);
        }
        
        Response::success($user);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        // 更新用户信息
        $input = json_decode(file_get_contents('php://input'), true);
        
        $updateFields = [];
        $params = [];
        
        if (isset($input['username'])) {
            // 检查用户名是否已存在
            $existingUser = $db->fetch(
                "SELECT id FROM users WHERE username = ? AND id != ?",
                [$input['username'], $payload['user_id']]
            );
            
            if ($existingUser) {
                Response::error('用户名已存在', 400);
            }
            
            $updateFields[] = "username = ?";
            $params[] = $input['username'];
        }
        
        if (isset($input['email'])) {
            if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                Response::error('邮箱格式不正确', 400);
            }
            
            // 检查邮箱是否已存在
            $existingUser = $db->fetch(
                "SELECT id FROM users WHERE email = ? AND id != ?",
                [$input['email'], $payload['user_id']]
            );
            
            if ($existingUser) {
                Response::error('邮箱已存在', 400);
            }
            
            $updateFields[] = "email = ?";
            $params[] = $input['email'];
        }
        
        if (isset($input['password'])) {
            if (strlen($input['password']) < 6) {
                Response::error('密码长度至少6位', 400);
            }
            
            $updateFields[] = "password = ?";
            $params[] = password_hash($input['password'], PASSWORD_DEFAULT);
        }
        
        if (isset($input['avatar'])) {
            $updateFields[] = "avatar = ?";
            $params[] = $input['avatar'];
        }
        
        if (empty($updateFields)) {
            Response::error('没有需要更新的字段', 400);
        }
        
        $params[] = $payload['user_id'];
        
        $db->query(
            "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?",
            $params
        );
        
        Response::success(null, '更新成功');
    }
    
} catch (Exception $e) {
    Response::error('操作失败: ' . $e->getMessage(), 500);
}