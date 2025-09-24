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
if (empty($input['name']) || empty($input['image_url']) || empty($input['model_type'])) {
    Response::error('模型名称、图片URL和模型类型不能为空', 400);
}

$allowedTypes = ['character', 'object', 'scene'];
if (!in_array($input['model_type'], $allowedTypes)) {
    Response::error('模型类型不正确', 400);
}

try {
    $db = Database::getInstance();
    $config = require_once '../../config/config.php';
    
    // 检查用户积分
    $user = $db->fetch("SELECT points FROM users WHERE id = ?", [$payload['user_id']]);
    if (!$user) {
        Response::error('用户不存在', 404);
    }
    
    $cost = $config['points']['model_generation_cost'];
    if ($user['points'] < $cost) {
        Response::error('积分不足，需要' . $cost . '积分', 400);
    }
    
    // 创建模型记录
    $db->query(
        "INSERT INTO models (user_id, name, description, original_image, model_type, points_cost, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')",
        [
            $payload['user_id'],
            $input['name'],
            $input['description'] ?? '',
            $input['image_url'],
            $input['model_type'],
            $cost
        ]
    );
    
    $modelId = $db->lastInsertId();
    
    // 扣除积分
    $db->query(
        "UPDATE users SET points = points - ? WHERE id = ?",
        [$cost, $payload['user_id']]
    );
    
    // 记录积分消费
    $db->query(
        "INSERT INTO point_records (user_id, type, amount, description, related_id) VALUES (?, 'spend', ?, '生成3D模型', ?)",
        [$payload['user_id'], $cost, $modelId]
    );
    
    // 这里应该调用第三方API生成3D模型
    // 由于是示例，我们模拟异步处理
    $this->processModelGeneration($modelId, $input['image_url'], $input['model_type']);
    
    Response::success([
        'model_id' => $modelId,
        'status' => 'pending',
        'message' => '模型生成任务已提交，请稍后查看结果'
    ], '模型生成任务已提交');
    
} catch (Exception $e) {
    Response::error('生成失败: ' . $e->getMessage(), 500);
}

/**
 * 处理模型生成（异步任务）
 */
function processModelGeneration($modelId, $imageUrl, $modelType) {
    // 这里应该调用混元API或其他3D生成服务
    // 示例代码，实际需要根据API文档实现
    
    // 模拟异步处理
    $config = require_once '../../config/config.php';
    
    // 这里应该实现真实的API调用
    // $result = callHunyuanAPI($imageUrl, $modelType);
    
    // 模拟成功结果
    $modelFile = 'models/' . $modelId . '_' . time() . '.obj';
    
    // 更新模型状态
    $db = Database::getInstance();
    $db->query(
        "UPDATE models SET status = 'completed', model_file = ? WHERE id = ?",
        [$modelFile, $modelId]
    );
}