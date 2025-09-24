<template>
  <view class="container">
    <!-- 搜索栏 -->
    <view class="search-bar">
      <view class="search-input">
        <input 
          type="text" 
          placeholder="搜索模型名称或描述" 
          v-model="searchKeyword"
          @confirm="handleSearch"
        />
        <view class="search-icon" @click="handleSearch">🔍</view>
      </view>
    </view>
    
    <!-- 筛选栏 -->
    <view class="filter-bar">
      <scroll-view class="filter-scroll" scroll-x="true">
        <view class="filter-list">
          <view 
            class="filter-item" 
            :class="{ active: currentFilter === 'all' }"
            @click="setFilter('all')"
          >
            全部
          </view>
          <view 
            class="filter-item" 
            :class="{ active: currentFilter === 'character' }"
            @click="setFilter('character')"
          >
            角色
          </view>
          <view 
            class="filter-item" 
            :class="{ active: currentFilter === 'object' }"
            @click="setFilter('object')"
          >
            物体
          </view>
          <view 
            class="filter-item" 
            :class="{ active: currentFilter === 'scene' }"
            @click="setFilter('scene')"
          >
            场景
          </view>
          <view 
            class="filter-item" 
            :class="{ active: currentFilter === 'completed' }"
            @click="setFilter('completed')"
          >
            已完成
          </view>
          <view 
            class="filter-item" 
            :class="{ active: currentFilter === 'processing' }"
            @click="setFilter('processing')"
          >
            生成中
          </view>
        </view>
      </scroll-view>
    </view>
    
    <!-- 模型列表 -->
    <view class="model-list">
      <view 
        class="model-item" 
        v-for="model in models" 
        :key="model.id"
        @click="goToModelDetail(model.id)"
      >
        <image :src="model.original_image" class="model-image" mode="aspectFill"></image>
        <view class="model-info">
          <text class="model-name">{{ model.name }}</text>
          <text class="model-description">{{ model.description || '暂无描述' }}</text>
          <view class="model-meta">
            <text class="model-type">{{ getModelTypeText(model.model_type) }}</text>
            <text class="model-status" :class="model.status">{{ getStatusText(model.status) }}</text>
          </view>
          <view class="model-stats">
            <text class="stat-item">下载 {{ model.download_count }}</text>
            <text class="stat-item">{{ formatDate(model.created_at) }}</text>
          </view>
        </view>
        <view class="model-actions" @click.stop>
          <button class="action-btn" @click="toggleFavorite(model)" v-if="model.is_favorited">
            ❤️
          </button>
          <button class="action-btn" @click="toggleFavorite(model)" v-else>
            🤍
          </button>
          <button class="action-btn" @click="deleteModel(model)" v-if="model.user_id === currentUserId">
            🗑️
          </button>
        </view>
      </view>
    </view>
    
    <!-- 空状态 -->
    <view class="empty-state" v-if="models.length === 0 && !loading">
      <view class="empty-icon">📦</view>
      <text class="empty-text">暂无模型</text>
      <text class="empty-tip">快去生成你的第一个3D模型吧</text>
      <button class="empty-btn" @click="goToGenerate">开始生成</button>
    </view>
    
    <!-- 加载状态 -->
    <view class="loading-state" v-if="loading">
      <text>加载中...</text>
    </view>
    
    <!-- 加载更多 -->
    <view class="load-more" v-if="hasMore && !loading">
      <button class="load-more-btn" @click="loadMore">加载更多</button>
    </view>
  </view>
</template>

<script>
import { mapGetters } from 'vuex'
import { modelApi } from '@/utils/api.js'

export default {
  data() {
    return {
      models: [],
      loading: false,
      hasMore: true,
      currentPage: 1,
      searchKeyword: '',
      currentFilter: 'all',
      currentUserId: null
    }
  },
  
  computed: {
    ...mapGetters(['isLogin', 'user'])
  },
  
  onLoad() {
    if (this.isLogin) {
      this.currentUserId = this.user.id
      this.loadModels()
    } else {
      uni.navigateTo({
        url: '/pages/login/login'
      })
    }
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
    // 加载模型列表
    async loadModels(refresh = false) {
      if (this.loading) return
      
      this.loading = true
      
      if (refresh) {
        this.currentPage = 1
        this.models = []
        this.hasMore = true
      }
      
      try {
        const params = {
          page: this.currentPage,
          limit: 20
        }
        
        if (this.searchKeyword) {
          params.search = this.searchKeyword
        }
        
        if (this.currentFilter !== 'all') {
          if (['character', 'object', 'scene'].includes(this.currentFilter)) {
            params.type = this.currentFilter
          } else {
            params.status = this.currentFilter
          }
        }
        
        const res = await modelApi.getList(params)
        
        if (refresh) {
          this.models = res.data.models
        } else {
          this.models = [...this.models, ...res.data.models]
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
      this.loadModels(true)
    },
    
    // 加载更多
    loadMore() {
      this.loadModels()
    },
    
    // 搜索
    handleSearch() {
      this.refreshData()
    },
    
    // 设置筛选
    setFilter(filter) {
      this.currentFilter = filter
      this.refreshData()
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
    
    // 获取状态文本
    getStatusText(status) {
      const statusMap = {
        'pending': '等待中',
        'processing': '生成中',
        'completed': '已完成',
        'failed': '生成失败'
      }
      return statusMap[status] || '未知'
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
      } else {
        return Math.floor(diff / 86400000) + '天前'
      }
    },
    
    // 跳转到模型详情
    goToModelDetail(id) {
      uni.navigateTo({
        url: `/pages/model/detail?id=${id}`
      })
    },
    
    // 跳转到生成页面
    goToGenerate() {
      uni.navigateTo({
        url: '/pages/model/generate'
      })
    },
    
    // 切换收藏
    async toggleFavorite(model) {
      try {
        // 这里应该调用收藏API
        uni.showToast({
          title: '收藏功能开发中',
          icon: 'none'
        })
      } catch (error) {
        uni.showToast({
          title: '操作失败',
          icon: 'none'
        })
      }
    },
    
    // 删除模型
    async deleteModel(model) {
      uni.showModal({
        title: '确认删除',
        content: '确定要删除这个模型吗？删除后无法恢复。',
        success: async (res) => {
          if (res.confirm) {
            try {
              await modelApi.delete(model.id)
              uni.showToast({
                title: '删除成功',
                icon: 'success'
              })
              this.refreshData()
            } catch (error) {
              uni.showToast({
                title: error.message || '删除失败',
                icon: 'none'
              })
            }
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

.search-bar {
  background-color: #fff;
  padding: 20rpx;
  border-bottom: 1rpx solid #e5e5e5;
}

.search-input {
  display: flex;
  align-items: center;
  background-color: #f8f8f8;
  border-radius: 25rpx;
  padding: 0 20rpx;
  height: 70rpx;
}

.search-input input {
  flex: 1;
  font-size: 28rpx;
  color: #333;
}

.search-icon {
  font-size: 32rpx;
  color: #999;
  margin-left: 10rpx;
}

.filter-bar {
  background-color: #fff;
  padding: 20rpx 0;
  border-bottom: 1rpx solid #e5e5e5;
}

.filter-scroll {
  white-space: nowrap;
}

.filter-list {
  display: flex;
  padding: 0 20rpx;
}

.filter-item {
  padding: 10rpx 20rpx;
  margin-right: 20rpx;
  background-color: #f8f8f8;
  border-radius: 20rpx;
  font-size: 24rpx;
  color: #666;
  white-space: nowrap;
}

.filter-item.active {
  background-color: #007aff;
  color: #fff;
}

.model-list {
  padding: 20rpx;
}

.model-item {
  display: flex;
  background-color: #fff;
  border-radius: 10rpx;
  margin-bottom: 20rpx;
  padding: 20rpx;
  box-shadow: 0 2rpx 10rpx rgba(0,0,0,0.1);
}

.model-image {
  width: 120rpx;
  height: 120rpx;
  border-radius: 10rpx;
  margin-right: 20rpx;
}

.model-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.model-name {
  font-size: 28rpx;
  font-weight: bold;
  color: #333;
  margin-bottom: 10rpx;
}

.model-description {
  font-size: 24rpx;
  color: #666;
  margin-bottom: 10rpx;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.model-meta {
  display: flex;
  align-items: center;
  margin-bottom: 10rpx;
}

.model-type {
  font-size: 20rpx;
  color: #007aff;
  background-color: #e3f2fd;
  padding: 5rpx 10rpx;
  border-radius: 10rpx;
  margin-right: 10rpx;
}

.model-status {
  font-size: 20rpx;
  padding: 5rpx 10rpx;
  border-radius: 10rpx;
  color: #fff;
}

.model-status.pending {
  background-color: #ff9500;
}

.model-status.processing {
  background-color: #007aff;
}

.model-status.completed {
  background-color: #34c759;
}

.model-status.failed {
  background-color: #ff3b30;
}

.model-stats {
  display: flex;
  gap: 20rpx;
}

.stat-item {
  font-size: 20rpx;
  color: #999;
}

.model-actions {
  display: flex;
  flex-direction: column;
  gap: 10rpx;
}

.action-btn {
  width: 60rpx;
  height: 60rpx;
  background-color: #f8f8f8;
  border: none;
  border-radius: 50%;
  font-size: 24rpx;
  display: flex;
  align-items: center;
  justify-content: center;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 100rpx 40rpx;
}

.empty-icon {
  font-size: 120rpx;
  margin-bottom: 30rpx;
}

.empty-text {
  font-size: 32rpx;
  color: #333;
  margin-bottom: 10rpx;
}

.empty-tip {
  font-size: 24rpx;
  color: #999;
  margin-bottom: 40rpx;
}

.empty-btn {
  background-color: #007aff;
  color: #fff;
  border: none;
  border-radius: 25rpx;
  padding: 20rpx 40rpx;
  font-size: 28rpx;
}

.loading-state {
  text-align: center;
  padding: 40rpx;
  color: #999;
}

.load-more {
  padding: 40rpx;
  text-align: center;
}

.load-more-btn {
  background-color: #f8f8f8;
  color: #666;
  border: none;
  border-radius: 25rpx;
  padding: 20rpx 40rpx;
  font-size: 28rpx;
}
</style>