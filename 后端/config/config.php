<?php
/**
 * 应用配置文件
 */
return [
    'app_name' => '七牛3D',
    'app_version' => '1.0.0',
    'debug' => true,
    
    // API配置
    'api' => [
        'base_url' => 'http://localhost/qiniu3d/后端/api/',
        'timeout' => 30,
    ],
    
    // 文件上传配置
    'upload' => [
        'max_size' => 10 * 1024 * 1024, // 10MB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'upload_path' => '../uploads/',
    ],
    
    // 积分系统配置
    'points' => [
        'model_generation_cost' => 10, // 生成3D模型消耗积分
        'image_generation_cost' => 5,  // 生成图片消耗积分
        'share_reward' => 2,           // 分享奖励积分
        'register_reward' => 20,       // 注册奖励积分
    ],
    
    // 第三方API配置
    'third_party' => [
        'hunyuan' => [
            'api_key' => '', // 需要配置混元API密钥
            'base_url' => 'https://api.hunyuan.tencentcloudapi.com/',
        ],
    ],
    
    // JWT配置
    'jwt' => [
        'secret' => 'your-secret-key-here',
        'expire' => 7 * 24 * 3600, // 7天
    ],
];