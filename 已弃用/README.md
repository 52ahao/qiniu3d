# 七牛三弟前端项目

基于uni-app开发的3D模型生成平台前端应用。

## 技术栈

- **框架**: uni-app + Vue 3
- **UI组件**: uni-ui
- **状态管理**: Vuex
- **构建工具**: Vite

## 功能特性

- 🎨 AI驱动的3D模型生成
- 👤 用户注册登录系统
- 💰 积分点数系统
- 📦 模型管理和分享
- 💳 支付充值功能
- 📱 多端适配（H5、小程序、App）

## 项目结构

```
前端/
├── pages/                 # 页面文件
│   ├── index/            # 首页
│   ├── login/            # 登录页
│   ├── register/         # 注册页
│   ├── profile/          # 个人中心
│   ├── model/            # 模型相关页面
│   └── points/           # 积分相关页面
├── static/               # 静态资源
├── utils/                # 工具类
│   └── api.js           # API接口封装
├── store/                # 状态管理
│   └── index.js         # Vuex store
├── App.vue              # 应用入口
├── main.js              # 主入口文件
├── pages.json           # 页面配置
└── manifest.json        # 应用配置
```

## 开发指南

### 环境要求

- Node.js >= 16.0.0
- HBuilderX 或 VS Code + uni-app插件

### 安装依赖

```bash
npm install
```

### 开发运行

```bash
# H5开发
npm run dev:h5

# 微信小程序开发
npm run dev:mp-weixin
```

### 构建发布

```bash
# H5构建
npm run build:h5

# 微信小程序构建
npm run build:mp-weixin
```

## API接口

项目使用RESTful API与后端通信，主要接口包括：

- 用户认证：`/api/auth/`
- 模型管理：`/api/model/`
- 积分系统：`/api/points/`
- 文件上传：`/api/upload/`

## 配置说明

### API地址配置

在 `utils/api.js` 中修改 `BASE_URL` 常量：

```javascript
const BASE_URL = 'http://localhost:8080/api/'
```

### 页面路由

在 `pages.json` 中配置页面路由和导航栏样式。

## 部署说明

### H5部署

1. 执行构建命令：`npm run build:h5`
2. 将 `dist/build/h5` 目录部署到Web服务器

### 微信小程序部署

1. 执行构建命令：`npm run build:mp-weixin`
2. 使用微信开发者工具打开 `dist/build/mp-weixin` 目录
3. 上传代码并提交审核

## 注意事项

1. 确保后端API服务正常运行
2. 配置正确的API地址
3. 检查网络请求权限设置
4. 测试各平台兼容性

## 更新日志

### v1.0.0 (2025-01-23)

- 初始版本发布
- 实现基础功能模块
- 支持H5和微信小程序平台