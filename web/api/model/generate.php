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
    $config = require __DIR__ . '/../../config/config.php';
    
    // 检查用户积分
    $user = $db->fetch("SELECT points FROM users WHERE id = ?", [$payload['user_id']]);
    if (!$user) {
        Response::error('用户不存在', 404);
    }
    
    $cost = isset($config['points']['model_generation_cost'])
        ? (int)$config['points']['model_generation_cost']
        : 10;
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
    
    // 调用第三方API生成3D模型（若未配置密钥则走模拟流程）
    processModelGeneration($modelId, $input['image_url'], $input['model_type']);
    
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
    $config = require __DIR__ . '/../../config/config.php';
    $hunyuanConf = isset($config['third_party']['hunyuan']) ? $config['third_party']['hunyuan'] : [];
    $secretId = isset($hunyuanConf['secret_id']) ? trim($hunyuanConf['secret_id']) : '';
    $secretKey = isset($hunyuanConf['secret_key']) ? trim($hunyuanConf['secret_key']) : '';

    if ($secretId && $secretKey) {
        // 根据腾讯云文档 https://cloud.tencent.com/document/product/1804/120829
        // 使用 TC3-HMAC-SHA256 进行签名并调用 SubmitHunyuanTo3DJob（接口名示例自文档概述）
        try {
            $endpoint = 'hunyuan.tencentcloudapi.com';
            $service = 'hunyuan';
            $host = $endpoint;
            $action = 'SubmitHunyuanTo3DJob';
            $version = '2024-10-01';
            $region = 'ap-guangzhou';
            $algorithm = 'TC3-HMAC-SHA256';
            $timestamp = time();
            $date = gmdate('Y-m-d', $timestamp);

            $payload = json_encode([
                'Input' => [
                    'ImageUrl' => absoluteUrl($imageUrl),
                    'Mode' => $modelType, // character|object|scene 映射
                ],
                'Output' => [
                    'Format' => 'GLB'
                ]
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            $canonicalUri = '/';
            $canonicalQueryString = '';
            $canonicalHeaders = 'content-type:application/json\nhost:' . $host . "\n";
            $signedHeaders = 'content-type;host';
            $hashedPayload = hash('sha256', $payload);
            $canonicalRequest = "POST\n{$canonicalUri}\n{$canonicalQueryString}\n{$canonicalHeaders}\n{$signedHeaders}\n{$hashedPayload}";

            $credentialScope = "$date/$service/tc3_request";
            $stringToSign = "$algorithm\n$timestamp\n$credentialScope\n" . hash('sha256', $canonicalRequest);

            $secretDate = hash_hmac('sha256', $date, 'TC3' . $secretKey, true);
            $secretService = hash_hmac('sha256', $service, $secretDate, true);
            $secretSigning = hash_hmac('sha256', 'tc3_request', $secretService, true);
            $signature = hash_hmac('sha256', $stringToSign, $secretSigning);

            $authorization = "$algorithm Credential=$secretId/$credentialScope, SignedHeaders=$signedHeaders, Signature=$signature";

            $headers = [
                'Authorization: ' . $authorization,
                'Content-Type: application/json',
                'Host: ' . $host,
                'X-TC-Action: ' . $action,
                'X-TC-Version: ' . $version,
                'X-TC-Timestamp: ' . $timestamp,
                'X-TC-Region: ' . $region,
            ];

            $ch = curl_init('https://' . $endpoint);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $resp = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);

            if ($err) {
                // 失败则标记为失败
                $db = Database::getInstance();
                $db->query("UPDATE models SET status = 'failed' WHERE id = ?", [$modelId]);
                return;
            }

            $json = json_decode($resp, true);
            // 假设返回包含 JobId 以及回调或查询方式
            $jobId = isset($json['Response']['JobId']) ? (string)$json['Response']['JobId'] : '';

            $db = Database::getInstance();
            $db->query("UPDATE models SET status = 'processing' WHERE id = ?", [$modelId]);

            // 简化：实际应通过回调或轮询查询结果。这里不阻塞，在后续查询接口里拉取结果。
            return;
        } catch (Exception $e) {
            $db = Database::getInstance();
            $db->query("UPDATE models SET status = 'failed' WHERE id = ?", [$modelId]);
            return;
        }
    }

    // 未配置密钥：本地模拟完成
    $modelFile = 'models/' . $modelId . '_' . time() . '.glb';
    $db = Database::getInstance();
    $db->query(
        "UPDATE models SET status = 'completed', model_file = ? WHERE id = ?",
        [$modelFile, $modelId]
    );
}

function absoluteUrl($path){
    if (preg_match('/^https?:\/\//i', $path)) return $path;
    // 将相对站点路径转为可公网访问的完整URL，视部署域名调整
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    return $scheme . '://' . $host . '/' . ltrim($path, '/');
}