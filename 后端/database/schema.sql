-- 七牛3D项目数据库结构
CREATE DATABASE IF NOT EXISTS qiniu3d CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE qiniu3d;

-- 用户表
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    points INT DEFAULT 20, -- 注册奖励20积分
    status TINYINT DEFAULT 1, -- 1:正常 0:禁用
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 用户认证表（第三方登录）
CREATE TABLE user_auths (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    provider VARCHAR(20) NOT NULL, -- wechat, qq, weibo
    provider_id VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_provider (provider, provider_id)
);

-- 积分记录表
CREATE TABLE point_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type VARCHAR(20) NOT NULL, -- earn, spend, admin
    amount INT NOT NULL,
    description VARCHAR(255) NOT NULL,
    related_id INT DEFAULT NULL, -- 关联的订单ID或模型ID
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 3D模型表
CREATE TABLE models (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    original_image VARCHAR(255) NOT NULL,
    model_file VARCHAR(255) DEFAULT NULL,
    model_type VARCHAR(50) NOT NULL, -- character, object, scene
    status VARCHAR(20) DEFAULT 'pending', -- pending, processing, completed, failed
    points_cost INT DEFAULT 0,
    download_count INT DEFAULT 0,
    is_public TINYINT DEFAULT 0, -- 是否公开
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 模型标签表
CREATE TABLE model_tags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    model_id INT NOT NULL,
    tag VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (model_id) REFERENCES models(id) ON DELETE CASCADE
);

-- 模型收藏表
CREATE TABLE model_favorites (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    model_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (model_id) REFERENCES models(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, model_id)
);

-- 模型评论表
CREATE TABLE model_comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    model_id INT NOT NULL,
    content TEXT NOT NULL,
    rating TINYINT DEFAULT 5, -- 1-5星评分
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (model_id) REFERENCES models(id) ON DELETE CASCADE
);

-- 订单表
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type VARCHAR(20) NOT NULL, -- points, model
    amount DECIMAL(10,2) NOT NULL,
    points INT DEFAULT NULL, -- 购买积分数
    model_id INT DEFAULT NULL, -- 购买的模型ID
    status VARCHAR(20) DEFAULT 'pending', -- pending, paid, cancelled
    payment_method VARCHAR(20) DEFAULT NULL, -- wechat, alipay
    payment_id VARCHAR(100) DEFAULT NULL, -- 第三方支付ID
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (model_id) REFERENCES models(id) ON DELETE SET NULL
);

-- 系统配置表
CREATE TABLE system_config (
    id INT PRIMARY KEY AUTO_INCREMENT,
    config_key VARCHAR(50) UNIQUE NOT NULL,
    config_value TEXT NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 插入默认配置
INSERT INTO system_config (config_key, config_value, description) VALUES
('model_generation_cost', '10', '生成3D模型消耗积分'),
('image_generation_cost', '5', '生成图片消耗积分'),
('share_reward', '2', '分享奖励积分'),
('register_reward', '20', '注册奖励积分'),
('max_upload_size', '10485760', '最大上传文件大小(字节)'),
('allowed_file_types', 'jpg,jpeg,png,gif,webp', '允许上传的文件类型');