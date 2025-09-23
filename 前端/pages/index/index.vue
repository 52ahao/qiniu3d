<template>
  <view class="container page">
    <!-- 轮播图 -->
    <swiper class="banner" indicator-dots="true" autoplay="true" interval="3000" duration="500">
      <swiper-item v-for="(item, index) in banners" :key="index">
        <image :src="item.image" class="banner-image" mode="aspectFill"></image>
      </swiper-item>
    </swiper>
    
    <!-- 功能入口 -->
    <view class="function-grid">
      <view class="function-item" @click="goToGenerate">
        <uni-icons class="function-icon" type="compose" size="28" color="#8ab4ff" />
        <text class="function-text">生成3D模型</text>
      </view>
      <view class="function-item" @click="goToModelList">
        <uni-icons class="function-icon" type="list" size="28" color="#8ab4ff" />
        <text class="function-text">我的模型</text>
      </view>
      <view class="function-item" @click="goToPoints">
        <uni-icons class="function-icon" type="wallet" size="28" color="#8ab4ff" />
        <text class="function-text">积分中心</text>
      </view>
      <view class="function-item" @click="goToProfile">
        <uni-icons class="function-icon" type="person" size="28" color="#8ab4ff" />
        <text class="function-text">个人中心</text>
      </view>
    </view>
    
    <!-- 热门模型 -->
    <view class="section">
      <view class="section-title">
        <text>热门模型</text>
        <text class="more" @click="goToModelList">更多 ></text>
      </view>
      <view class="model-grid">
        <view class="model-item" v-for="model in hotModels" :key="model.id" @click="goToModelDetail(model.id)">
          <image :src="model.original_image" class="model-image" mode="aspectFill"></image>
          <view class="model-info">
            <text class="model-name">{{ model.name }}</text>
            <text class="model-type">{{ getModelTypeText(model.model_type) }}</text>
          </view>
        </view>
      </view>
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
            original_image: 'https://cdn-3d-prod.hunyuan.tencent.com/public/3d/20250702/goodcase/31_render.png?imageMogr2/thumbnail/320x/quality/80/strip/format/webp',
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
}

// 桌面端页面居中与最大宽度
.page {
  width: 100%;
  max-width: 1280px;
  margin: 0 auto;
}

// 顶部横幅区域
.banner {
  height: 520rpx;
  margin-bottom: 24rpx;
}

.banner-image {
  width: 100%;
  height: 100%;
}

// 功能入口改为暗色卡片
.function-grid {
  display: flex;
  flex-wrap: wrap;
  padding: 24rpx;
  background: #141821;
  margin-bottom: 24rpx;
  border-top: 1rpx solid rgba(255,255,255,0.04);
  border-bottom: 1rpx solid rgba(255,255,255,0.04);
}

.function-item {
  width: 25%;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 24rpx 0;
}

.function-icon { margin-bottom: 10rpx; }

.function-text {
  font-size: 24rpx;
  color: rgba(230,232,235,0.9);
}

// 区块标题与卡片
.section {
  background: #0f1115;
  margin-bottom: 24rpx;
}

.section-title {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 30rpx 24rpx 20rpx;
  font-size: 32rpx;
  font-weight: bold;
  color: #e6e8eb;
}

.more {
  font-size: 28rpx;
  color: #8ab4ff;
}

.model-grid { padding: 0 24rpx 24rpx; display: flex; flex-wrap: wrap; gap: 20rpx; }

.model-item {
  width: calc((100% - 60rpx) / 4);
  background: #141821;
  border-radius: 16rpx;
  overflow: hidden;
  border: 1rpx solid rgba(255,255,255,0.06);
}

.model-image { width: 100%; height: 220rpx; }

.model-info {
  padding: 18rpx;
}

.model-name {
  font-size: 26rpx;
  color: #e6e8eb;
  display: block;
  margin-bottom: 6rpx;
}

.model-type {
  font-size: 22rpx;
  color: #9aa4b2;
}

.points-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 30rpx 24rpx;
  background: #141821;
  margin: 24rpx;
  border-radius: 14rpx;
  border: 1rpx solid rgba(255,255,255,0.06);
}

.points-info {
  display: flex;
  flex-direction: column;
}

.points-label {
  font-size: 28rpx;
  color: #9aa4b2;
  margin-bottom: 10rpx;
}

.points-value {
  font-size: 36rpx;
  font-weight: bold;
  color: #8ab4ff;
}

.points-btn {
  background-color: #2b74ff;
  color: #fff;
  border: none;
  border-radius: 26rpx;
  padding: 16rpx 32rpx;
  font-size: 28rpx;
}
</style>