<template>
  <view class="container">
    <!-- 轮播图 -->
    <swiper class="banner" indicator-dots="true" autoplay="true" interval="3000" duration="500">
      <swiper-item v-for="(item, index) in banners" :key="index">
        <image :src="item.image" class="banner-image" mode="aspectFill"></image>
      </swiper-item>
    </swiper>
    
    <!-- 功能入口 -->
    <view class="function-grid">
      <view class="function-item" @click="goToGenerate">
        <view class="function-icon">🎨</view>
        <text class="function-text">生成3D模型</text>
      </view>
      <view class="function-item" @click="goToModelList">
        <view class="function-icon">📦</view>
        <text class="function-text">我的模型</text>
      </view>
      <view class="function-item" @click="goToPoints">
        <view class="function-icon">💰</view>
        <text class="function-text">积分中心</text>
      </view>
      <view class="function-item" @click="goToProfile">
        <view class="function-icon">👤</view>
        <text class="function-text">个人中心</text>
      </view>
    </view>
    
    <!-- 热门模型 -->
    <view class="section">
      <view class="section-title">
        <text>热门模型</text>
        <text class="more" @click="goToModelList">更多 ></text>
      </view>
      <scroll-view class="model-scroll" scroll-x="true">
        <view class="model-list">
          <view class="model-item" v-for="model in hotModels" :key="model.id" @click="goToModelDetail(model.id)">
            <image :src="model.original_image" class="model-image" mode="aspectFill"></image>
            <view class="model-info">
              <text class="model-name">{{ model.name }}</text>
              <text class="model-type">{{ getModelTypeText(model.model_type) }}</text>
            </view>
          </view>
        </view>
      </scroll-view>
    </view>
    
    <!-- 积分余额 -->
    <view class="points-card" v-if="isLogin">
      <view class="points-info">
        <text class="points-label">当前积分</text>
        <text class="points-value">{{ points }}</text>
      </view>
      <button class="points-btn" @click="goToPoints">充值</button>
    </view>
  </view>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  data() {
    return {
      banners: [
        {
          image: '/static/banner1.jpg',
          title: 'AI生成3D模型'
        },
        {
          image: '/static/banner2.jpg',
          title: '二次元角色建模'
        },
        {
          image: '/static/banner3.jpg',
          title: '设计师专用工具'
        }
      ],
      hotModels: []
    }
  },
  
  computed: {
    ...mapGetters(['isLogin', 'points'])
  },
  
  onLoad() {
    this.loadHotModels()
  },
  
  onPullDownRefresh() {
    this.loadHotModels()
    setTimeout(() => {
      uni.stopPullDownRefresh()
    }, 1000)
  },
  
  methods: {
    // 加载热门模型
    async loadHotModels() {
      try {
        // 这里应该调用API获取热门模型
        // const res = await modelApi.getHotModels()
        // this.hotModels = res.data
        
        // 模拟数据
        this.hotModels = [
          {
            id: 1,
            name: '可爱角色',
            original_image: '/static/model1.jpg',
            model_type: 'character'
          },
          {
            id: 2,
            name: '科幻建筑',
            original_image: '/static/model2.jpg',
            model_type: 'object'
          },
          {
            id: 3,
            name: '动漫场景',
            original_image: '/static/model3.jpg',
            model_type: 'scene'
          }
        ]
      } catch (error) {
        console.error('加载热门模型失败:', error)
      }
    },
    
    // 获取模型类型文本
    getModelTypeText(type) {
      const typeMap = {
        'character': '角色',
        'object': '物体',
        'scene': '场景'
      }
      return typeMap[type] || '未知'
    },
    
    // 跳转到生成页面
    goToGenerate() {
      if (!this.isLogin) {
        uni.navigateTo({
          url: '/pages/login/login'
        })
        return
      }
      uni.navigateTo({
        url: '/pages/model/generate'
      })
    },
    
    // 跳转到模型列表
    goToModelList() {
      if (!this.isLogin) {
        uni.navigateTo({
          url: '/pages/login/login'
        })
        return
      }
      uni.switchTab({
        url: '/pages/model/list'
      })
    },
    
    // 跳转到积分中心
    goToPoints() {
      if (!this.isLogin) {
        uni.navigateTo({
          url: '/pages/login/login'
        })
        return
      }
      uni.navigateTo({
        url: '/pages/points/balance'
      })
    },
    
    // 跳转到个人中心
    goToProfile() {
      if (!this.isLogin) {
        uni.navigateTo({
          url: '/pages/login/login'
        })
        return
      }
      uni.switchTab({
        url: '/pages/profile/profile'
      })
    },
    
    // 跳转到模型详情
    goToModelDetail(id) {
      uni.navigateTo({
        url: `/pages/model/detail?id=${id}`
      })
    }
  }
}
</script>

<style lang="scss" scoped>
.container {
  padding: 0;
  background-color: #f5f5f5;
}

.banner {
  height: 400rpx;
  margin-bottom: 20rpx;
}

.banner-image {
  width: 100%;
  height: 100%;
}

.function-grid {
  display: flex;
  flex-wrap: wrap;
  padding: 20rpx;
  background-color: #fff;
  margin-bottom: 20rpx;
}

.function-item {
  width: 25%;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 20rpx 0;
}

.function-icon {
  font-size: 60rpx;
  margin-bottom: 10rpx;
}

.function-text {
  font-size: 24rpx;
  color: #666;
}

.section {
  background-color: #fff;
  margin-bottom: 20rpx;
}

.section-title {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 30rpx 20rpx 20rpx;
  font-size: 32rpx;
  font-weight: bold;
}

.more {
  font-size: 28rpx;
  color: #007aff;
}

.model-scroll {
  white-space: nowrap;
}

.model-list {
  display: flex;
  padding: 0 20rpx 20rpx;
}

.model-item {
  width: 200rpx;
  margin-right: 20rpx;
  background-color: #f8f8f8;
  border-radius: 10rpx;
  overflow: hidden;
}

.model-image {
  width: 100%;
  height: 150rpx;
}

.model-info {
  padding: 15rpx;
}

.model-name {
  font-size: 24rpx;
  color: #333;
  display: block;
  margin-bottom: 5rpx;
}

.model-type {
  font-size: 20rpx;
  color: #999;
}

.points-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 30rpx 20rpx;
  background-color: #fff;
  margin: 20rpx;
  border-radius: 10rpx;
  box-shadow: 0 2rpx 10rpx rgba(0,0,0,0.1);
}

.points-info {
  display: flex;
  flex-direction: column;
}

.points-label {
  font-size: 28rpx;
  color: #666;
  margin-bottom: 10rpx;
}

.points-value {
  font-size: 36rpx;
  font-weight: bold;
  color: #007aff;
}

.points-btn {
  background-color: #007aff;
  color: #fff;
  border: none;
  border-radius: 25rpx;
  padding: 15rpx 30rpx;
  font-size: 28rpx;
}
</style>