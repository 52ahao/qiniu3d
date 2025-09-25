<?php
require_once '../../core/Database.php';
require_once '../../core/Response.php';
require_once '../../core/JWT.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// 读取JWT（GET可选，其它方法必需）
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

$modelId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$modelId) {
    Response::error('模型ID不能为空', 400);
}

try {
    $db = Database::getInstance();
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // 获取模型详情（匿名可读公开模型，登录可读自己模型）
        if ($payload && isset($payload['user_id'])) {
            $model = $db->fetch(
                "SELECT m.*, u.username, u.avatar 
                 FROM models m 
                 LEFT JOIN users u ON m.user_id = u.id 
                 WHERE m.id = ? AND (m.user_id = ? OR m.is_public = 1)",
                [$modelId, $payload['user_id']]
            );
        } else {
            $model = $db->fetch(
                "SELECT m.*, u.username, u.avatar 
                 FROM models m 
                 LEFT JOIN users u ON m.user_id = u.id 
                 WHERE m.id = ? AND m.is_public = 1",
                [$modelId]
            );
        }
        
        if (!$model) {
            Response::error('模型不存在或无权限访问', 404);
        }
        
        // 获取标签
        $tags = $db->fetchAll(
            "SELECT tag FROM model_tags WHERE model_id = ?",
            [$modelId]
        );
        $model['tags'] = array_column($tags, 'tag');
        
        // 获取评论
        $comments = $db->fetchAll(
            "SELECT c.*, u.username, u.avatar 
             FROM model_comments c 
             LEFT JOIN users u ON c.user_id = u.id 
             WHERE c.model_id = ? 
             ORDER BY c.created_at DESC",
            [$modelId]
        );
        $model['comments'] = $comments;
        
        // 检查是否已收藏（匿名时默认为false）
        if ($payload && isset($payload['user_id'])) {
            $favorite = $db->fetch(
                "SELECT id FROM model_favorites WHERE user_id = ? AND model_id = ?",
                [$payload['user_id'], $modelId]
            );
            $model['is_favorited'] = !empty($favorite);
        } else {
            $model['is_favorited'] = false;
        }
        
        Response::success($model);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // 删除需要鉴权
        if (!$payload || !isset($payload['user_id'])) {
            Response::error('未提供认证token', 401);
        }
        // 删除模型
        $model = $db->fetch(
            "SELECT * FROM models WHERE id = ? AND user_id = ?",
            [$modelId, $payload['user_id']]
        );
        
        if (!$model) {
            Response::error('模型不存在或无权限删除', 404);
        }
        
        // 删除相关数据
        $db->query("DELETE FROM model_tags WHERE model_id = ?", [$modelId]);
        $db->query("DELETE FROM model_favorites WHERE model_id = ?", [$modelId]);
        $db->query("DELETE FROM model_comments WHERE model_id = ?", [$modelId]);
        $db->query("DELETE FROM models WHERE id = ?", [$modelId]);
        
        // 删除文件
        if ($model['model_file'] && file_exists('../../uploads/' . $model['model_file'])) {
            unlink('../../uploads/' . $model['model_file']);
        }
        if ($model['original_image'] && file_exists('../../uploads/' . $model['original_image'])) {
            unlink('../../uploads/' . $model['original_image']);
        }
        
        Response::success(null, '模型删除成功');
    }
    
} catch (Exception $e) {
    Response::error('操作失败: ' . $e->getMessage(), 500);
}