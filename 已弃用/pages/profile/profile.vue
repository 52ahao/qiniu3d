<template>
  <view class="container">
    <!-- 用户信息卡片 -->
    <view class="user-card">
      <view class="user-info" v-if="isLogin">
        <image :src="user.avatar || '/static/default-avatar.png'" class="avatar" mode="aspectFill"></image>
        <view class="user-details">
          <text class="username">{{ user.username }}</text>
          <text class="user-email">{{ user.email }}</text>
          <view class="points-info">
            <text class="points-label">积分余额</text>
            <text class="points-value">{{ points }}</text>
          </view>
        </view>
        <button class="edit-btn" @click="goToEditProfile">编辑</button>
      </view>
      
      <view class="login-prompt" v-else>
        <image class="avatar" src="/static/default-avatar.png" mode="aspectFill"></image>
        <view class="login-info">
          <text class="login-text">请先登录</text>
          <button class="login-btn" @click="goToLogin">立即登录</button>
        </view>
      </view>
    </view>
    
    <!-- 功能菜单 -->
    <view class="menu-section" v-if="isLogin">
      <view class="menu-group">
        <view class="menu-item" @click="goToModelList">
          <view class="menu-icon">📦</view>
          <text class="menu-text">我的模型</text>
          <text class="menu-arrow">></text>
        </view>
        <view class="menu-item" @click="goToPoints">
          <view class="menu-icon">💰</view>
          <text class="menu-text">积分中心</text>
          <text class="menu-arrow">></text>
        </view>
        <view class="menu-item" @click="goToFavorites">
          <view class="menu-icon">❤️</view>
          <text class="menu-text">我的收藏</text>
          <text class="menu-arrow">></text>
        </view>
      </view>
      
      <view class="menu-group">
        <view class="menu-item" @click="goToSettings">
          <view class="menu-icon">⚙️</view>
          <text class="menu-text">设置</text>
          <text class="menu-arrow">></text>
        </view>
        <view class="menu-item" @click="goToHelp">
          <view class="menu-icon">❓</view>
          <text class="menu-text">帮助中心</text>
          <text class="menu-arrow">></text>
        </view>
        <view class="menu-item" @click="goToAbout">
          <view class="menu-icon">ℹ️</view>
          <text class="menu-text">关于我们</text>
          <text class="menu-arrow">></text>
        </view>
      </view>
    </view>
    
    <!-- 统计信息 -->
    <view class="stats-section" v-if="isLogin">
      <view class="stats-title">我的统计</view>
      <view class="stats-grid">
        <view class="stat-item">
          <text class="stat-value">{{ stats.modelCount }}</text>
          <text class="stat-label">生成模型</text>
        </view>
        <view class="stat-item">
          <text class="stat-value">{{ stats.downloadCount }}</text>
          <text class="stat-label">下载次数</text>
        </view>
        <view class="stat-item">
          <text class="stat-value">{{ stats.favoriteCount }}</text>
          <text class="stat-label">收藏数量</text>
        </view>
        <view class="stat-item">
          <text class="stat-value">{{ stats.pointsSpent }}</text>
          <text class="stat-label">消费积分</text>
        </view>
      </view>
    </view>
    
    <!-- 退出登录 -->
    <view class="logout-section" v-if="isLogin">
      <button class="logout-btn" @click="handleLogout">退出登录</button>
    </view>
  </view>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'

export default {
  data() {
    return {
      stats: {
        modelCount: 0,
        downloadCount: 0,
        favoriteCount: 0,
        pointsSpent: 0
      }
    }
  },
  
  computed: {
    ...mapGetters(['isLogin', 'user', 'points'])
  },
  
  onLoad() {
    if (this.isLogin) {
      this.loadUserStats()
    }
  },
  
  onShow() {
    if (this.isLogin) {
      this.loadUserStats()
    }
  },
  
  methods: {
    ...mapActions(['logout']),
    
    // 加载用户统计
    async loadUserStats() {
      try {
        // 这里应该调用API获取用户统计信息
        // const res = await userApi.getStats()
        // this.stats = res.data
        
        // 模拟数据
        this.stats = {
          modelCount: 12,
          downloadCount: 45,
          favoriteCount: 8,
          pointsSpent: 120
        }
      } catch (error) {
        console.error('加载用户统计失败:', error)
      }
    },
    
    // 跳转到编辑资料
    goToEditProfile() {
      uni.navigateTo({
        url: '/pages/profile/edit'
      })
    },
    
    // 跳转到登录
    goToLogin() {
      uni.navigateTo({
        url: '/pages/login/login'
      })
    },
    
    // 跳转到模型列表
    goToModelList() {
      uni.navigateTo({
        url: '/pages/model/list'
      })
    },
    
    // 跳转到积分中心
    goToPoints() {
      uni.navigateTo({
        url: '/pages/points/balance'
      })
    },
    
    // 跳转到收藏
    goToFavorites() {
      uni.showToast({
        title: '收藏功能开发中',
        icon: 'none'
      })
    },
    
    // 跳转到设置
    goToSettings() {
      uni.showToast({
        title: '设置功能开发中',
        icon: 'none'
      })
    },
    
    // 跳转到帮助中心
    goToHelp() {
      uni.showToast({
        title: '帮助中心功能开发中',
        icon: 'none'
      })
    },
    
    // 跳转到关于我们
    goToAbout() {
      uni.showModal({
        title: '关于我们',
        content: '七牛三弟 v1.0.0\nAI驱动的3D模型生成平台\n\n让每个人都能轻松创建3D内容',
        showCancel: false
      })
    },
    
    // 处理退出登录
    handleLogout() {
      uni.showModal({
        title: '确认退出',
        content: '确定要退出登录吗？',
        success: (res) => {
          if (res.confirm) {
            this.logout()
            uni.showToast({
              title: '已退出登录',
              icon: 'success'
            })
            setTimeout(() => {
              uni.navigateTo({
                url: '/pages/index/index'
              })
            }, 1500)
          }
        }
      })
    }
  }
}
</script>

<style lang="scss" scoped>
.container {
  background-color: #f5f5f5;
  min-height: 100vh;
}

.user-card {
  background-color: #fff;
  margin: 20rpx;
  border-radius: 15rpx;
  padding: 30rpx;
  box-shadow: 0 4rpx 20rpx rgba(0,0,0,0.1);
}

.user-info {
  display: flex;
  align-items: center;
}

.avatar {
  width: 120rpx;
  height: 120rpx;
  border-radius: 60rpx;
  margin-right: 20rpx;
}

.user-details {
  flex: 1;
}

.username {
  display: block;
  font-size: 32rpx;
  font-weight: bold;
  color: #333;
  margin-bottom: 10rpx;
}

.user-email {
  display: block;
  font-size: 24rpx;
  color: #666;
  margin-bottom: 15rpx;
}

.points-info {
  display: flex;
  align-items: center;
}

.points-label {
  font-size: 24rpx;
  color: #666;
  margin-right: 10rpx;
}

.points-value {
  font-size: 28rpx;
  font-weight: bold;
  color: #007aff;
}

.edit-btn {
  background-color: #007aff;
  color: #fff;
  border: none;
  border-radius: 20rpx;
  padding: 10rpx 20rpx;
  font-size: 24rpx;
}

.login-prompt {
  display: flex;
  align-items: center;
}

.login-info {
  flex: 1;
  margin-left: 20rpx;
}

.login-text {
  display: block;
  font-size: 28rpx;
  color: #333;
  margin-bottom: 15rpx;
}

.login-btn {
  background-color: #007aff;
  color: #fff;
  border: none;
  border-radius: 20rpx;
  padding: 15rpx 30rpx;
  font-size: 26rpx;
}

.menu-section {
  margin: 20rpx;
}

.menu-group {
  background-color: #fff;
  border-radius: 15rpx;
  margin-bottom: 20rpx;
  overflow: hidden;
}

.menu-item {
  display: flex;
  align-items: center;
  padding: 30rpx;
  border-bottom: 1rpx solid #f0f0f0;
}

.menu-item:last-child {
  border-bottom: none;
}

.menu-icon {
  font-size: 40rpx;
  margin-right: 20rpx;
  width: 40rpx;
  text-align: center;
}

.menu-text {
  flex: 1;
  font-size: 28rpx;
  color: #333;
}

.menu-arrow {
  font-size: 24rpx;
  color: #ccc;
}

.stats-section {
  background-color: #fff;
  margin: 20rpx;
  border-radius: 15rpx;
  padding: 30rpx;
}

.stats-title {
  font-size: 32rpx;
  font-weight: bold;
  color: #333;
  margin-bottom: 30rpx;
}

.stats-grid {
  display: flex;
  flex-wrap: wrap;
}

.stat-item {
  width: 50%;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 20rpx 0;
}

.stat-value {
  font-size: 36rpx;
  font-weight: bold;
  color: #007aff;
  margin-bottom: 10rpx;
}

.stat-label {
  font-size: 24rpx;
  color: #666;
}

.logout-section {
  margin: 20rpx;
}

.logout-btn {
  width: 100%;
  height: 80rpx;
  background-color: #ff3b30;
  color: #fff;
  border: none;
  border-radius: 15rpx;
  font-size: 28rpx;
}
</style>