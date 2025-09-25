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

// 读取JWT（可选）
$headers = getallheaders();
$token = null;
$payload = null;

if (isset($headers['Authorization'])) {
    $auth = $headers['Authorization'];
    if (preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
        $token = $matches[1];
    }
}

if ($token) {
    $payload = JWT::decode($token);
}

try {
    $db = Database::getInstance();
    
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(50, intval($_GET['limit']))) : 20;
    $offset = ($page - 1) * $limit;
    
    // 未登录：只返回公开模型；已登录：返回公开模型或当前用户的模型
    if ($payload && isset($payload['user_id'])) {
        $where = "WHERE (is_public = 1 OR user_id = ?)";
        $params = [$payload['user_id']];
    } else {
        $where = "WHERE is_public = 1";
        $params = [];
    }
    
    // 状态筛选
    if (isset($_GET['status'])) {
        $where .= " AND status = ?";
        $params[] = $_GET['status'];
    }
    
    // 类型筛选
    if (isset($_GET['type'])) {
        $where .= " AND model_type = ?";
        $params[] = $_GET['type'];
    }
    
    // 搜索
    if (isset($_GET['search'])) {
        $where .= " AND (name LIKE ? OR description LIKE ?)";
        $searchTerm = '%' . $_GET['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    // 获取总数
    $countParams = $params;
    $total = $db->fetch("SELECT COUNT(*) as count FROM models $where", $countParams)['count'];
    
    // 获取模型列表
    $params[] = $limit;
    $params[] = $offset;
    
    $models = $db->fetchAll(
        "SELECT id, name, description, original_image, model_file, model_type, status, points_cost, download_count, is_public, created_at, updated_at 
         FROM models $where 
         ORDER BY created_at DESC 
         LIMIT ? OFFSET ?",
        $params
    );
    
    // 为每个模型添加标签
    foreach ($models as &$model) {
        $tags = $db->fetchAll(
            "SELECT tag FROM model_tags WHERE model_id = ?",
            [$model['id']]
        );
        $model['tags'] = array_column($tags, 'tag');
    }
    
    Response::success([
        'models' => $models,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ]);
    
} catch (Exception $e) {
    Response::error('获取模型列表失败: ' . $e->getMessage(), 500);
}