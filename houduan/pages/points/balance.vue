<template>
  <view class="container">
    <!-- 积分余额卡片 -->
    <view class="balance-card">
      <view class="balance-info">
        <text class="balance-label">当前积分</text>
        <text class="balance-value">{{ points }}</text>
      </view>
      <button class="recharge-btn" @click="goToPurchase">立即充值</button>
    </view>
    
    <!-- 积分记录 -->
    <view class="records-section">
      <view class="section-header">
        <text class="section-title">积分记录</text>
        <view class="filter-tabs">
          <text 
            class="filter-tab" 
            :class="{ active: currentFilter === 'all' }"
            @click="setFilter('all')"
          >
            全部
          </text>
          <text 
            class="filter-tab" 
            :class="{ active: currentFilter === 'earn' }"
            @click="setFilter('earn')"
          >
            获得
          </text>
          <text 
            class="filter-tab" 
            :class="{ active: currentFilter === 'spend' }"
            @click="setFilter('spend')"
          >
            消费
          </text>
        </view>
      </view>
      
      <view class="records-list">
        <view 
          class="record-item" 
          v-for="record in records" 
          :key="record.id"
        >
          <view class="record-info">
            <text class="record-desc">{{ record.description }}</text>
            <text class="record-time">{{ formatDate(record.created_at) }}</text>
          </view>
          <view class="record-amount" :class="record.type">
            <text v-if="record.type === 'earn'">+{{ record.amount }}</text>
            <text v-else>-{{ record.amount }}</text>
          </view>
        </view>
      </view>
      
      <!-- 空状态 -->
      <view class="empty-state" v-if="records.length === 0 && !loading">
        <view class="empty-icon">💰</view>
        <text class="empty-text">暂无积分记录</text>
      </view>
      
      <!-- 加载状态 -->
      <view class="loading-state" v-if="loading">
        <text>加载中...</text>
      </view>
    </view>
    
    <!-- 积分说明 -->
    <view class="info-section">
      <view class="info-title">积分说明</view>
      <view class="info-list">
        <view class="info-item">
          <text class="info-label">获得积分：</text>
          <text class="info-text">注册奖励20积分，分享模型奖励2积分</text>
        </view>
        <view class="info-item">
          <text class="info-label">消费积分：</text>
          <text class="info-text">生成3D模型消耗10积分，生成图片消耗5积分</text>
        </view>
        <view class="info-item">
          <text class="info-label">积分购买：</text>
          <text class="info-text">1元=10积分，支持微信支付和支付宝</text>
        </view>
      </view>
    </view>
  </view>
</template>

<script>
import { mapGetters } from 'vuex'
import { pointsApi } from '@/utils/api.js'

export default {
  data() {
    return {
      records: [],
      loading: false,
      currentFilter: 'all',
      currentPage: 1,
      hasMore: true
    }
  },
  
  computed: {
    ...mapGetters(['points'])
  },
  
  onLoad() {
    this.loadRecords()
  },
  
  onPullDownRefresh() {
    this.refreshData()
    setTimeout(() => {
      uni.stopPullDownRefresh()
    }, 1000)
  },
  
  onReachBottom() {
    if (this.hasMore && !this.loading) {
      this.loadMore()
    }
  },
  
  methods: {
    // 加载积分记录
    async loadRecords(refresh = false) {
      if (this.loading) return
      
      this.loading = true
      
      if (refresh) {
        this.currentPage = 1
        this.records = []
        this.hasMore = true
      }
      
      try {
        const params = {
          page: this.currentPage,
          limit: 20
        }
        
        if (this.currentFilter !== 'all') {
          params.type = this.currentFilter
        }
        
        const res = await pointsApi.getBalance(params)
        
        if (refresh) {
          this.records = res.data.records
        } else {
          this.records = [...this.records, ...res.data.records]
        }
        
        this.hasMore = this.currentPage < res.data.pagination.pages
        this.currentPage++
        
      } catch (error) {
        uni.showToast({
          title: error.message || '加载失败',
          icon: 'none'
        })
      } finally {
        this.loading = false
      }
    },
    
    // 刷新数据
    refreshData() {
      this.loadRecords(true)
    },
    
    // 加载更多
    loadMore() {
      this.loadRecords()
    },
    
    // 设置筛选
    setFilter(filter) {
      this.currentFilter = filter
      this.refreshData()
    },
    
    // 格式化日期
    formatDate(dateString) {
      const date = new Date(dateString)
      const now = new Date()
      const diff = now - date
      
      if (diff < 60000) { // 1分钟内
        return '刚刚'
      } else if (diff < 3600000) { // 1小时内
        return Math.floor(diff / 60000) + '分钟前'
      } else if (diff < 86400000) { // 1天内
        return Math.floor(diff / 3600000) + '小时前'
      } else if (diff < 604800000) { // 7天内
        return Math.floor(diff / 86400000) + '天前'
      } else {
        return date.toLocaleDateString()
      }
    },
    
    // 跳转到购买页面
    goToPurchase() {
      uni.navigateTo({
        url: '/pages/points/purchase'
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

.balance-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  margin: 20rpx;
  border-radius: 15rpx;
  padding: 40rpx;
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: #fff;
}

.balance-info {
  display: flex;
  flex-direction: column;
}

.balance-label {
  font-size: 28rpx;
  margin-bottom: 10rpx;
  opacity: 0.9;
}

.balance-value {
  font-size: 48rpx;
  font-weight: bold;
}

.recharge-btn {
  background-color: rgba(255, 255, 255, 0.2);
  color: #fff;
  border: 2rpx solid rgba(255, 255, 255, 0.3);
  border-radius: 25rpx;
  padding: 15rpx 30rpx;
  font-size: 28rpx;
}

.records-section {
  background-color: #fff;
  margin: 20rpx;
  border-radius: 15rpx;
  padding: 30rpx;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30rpx;
}

.section-title {
  font-size: 32rpx;
  font-weight: bold;
  color: #333;
}

.filter-tabs {
  display: flex;
  gap: 20rpx;
}

.filter-tab {
  font-size: 24rpx;
  color: #666;
  padding: 10rpx 20rpx;
  border-radius: 15rpx;
  background-color: #f8f8f8;
}

.filter-tab.active {
  background-color: #007aff;
  color: #fff;
}

.records-list {
  max-height: 600rpx;
  overflow-y: auto;
}

.record-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 25rpx 0;
  border-bottom: 1rpx solid #f0f0f0;
}

.record-item:last-child {
  border-bottom: none;
}

.record-info {
  flex: 1;
}

.record-desc {
  display: block;
  font-size: 28rpx;
  color: #333;
  margin-bottom: 10rpx;
}

.record-time {
  font-size: 24rpx;
  color: #999;
}

.record-amount {
  font-size: 28rpx;
  font-weight: bold;
}

.record-amount.earn {
  color: #34c759;
}

.record-amount.spend {
  color: #ff3b30;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 80rpx 40rpx;
}

.empty-icon {
  font-size: 80rpx;
  margin-bottom: 20rpx;
}

.empty-text {
  font-size: 28rpx;
  color: #999;
}

.loading-state {
  text-align: center;
  padding: 40rpx;
  color: #999;
}

.info-section {
  background-color: #fff;
  margin: 20rpx;
  border-radius: 15rpx;
  padding: 30rpx;
}

.info-title {
  font-size: 32rpx;
  font-weight: bold;
  color: #333;
  margin-bottom: 30rpx;
}

.info-list {
  display: flex;
  flex-direction: column;
  gap: 20rpx;
}

.info-item {
  display: flex;
  align-items: flex-start;
}

.info-label {
  font-size: 26rpx;
  color: #666;
  width: 150rpx;
  flex-shrink: 0;
}

.info-text {
  font-size: 26rpx;
  color: #333;
  flex: 1;
  line-height: 1.5;
}
</style>