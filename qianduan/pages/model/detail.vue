<template>
  <view class="container">
    <view class="card" v-if="loading">
      <text>加载中...</text>
    </view>

    <view class="card" v-else-if="!model">
      <text>未找到模型</text>
    </view>

    <view class="card" v-else>
      <image v-if="model.original_image" :src="fullUrl(model.original_image)" class="cover" mode="aspectFit"></image>
      <view class="info">
        <text class="name">{{ model.name }}</text>
        <text class="desc">{{ model.description || '暂无描述' }}</text>
        <text class="meta">类型：{{ typeText(model.model_type) }} | 积分：{{ model.points_cost }}</text>
        <text class="meta">状态：{{ statusText(model.status) }}</text>
        <view v-if="model.model_file" class="file">
          <text>模型文件：</text>
          <uni-link :href="fullUrl(model.model_file)" text="点击下载"></uni-link>
        </view>
      </view>
    </view>
  </view>
</template>

<script>
import { modelApi } from '@/utils/api.js'

export default {
  data() {
    return {
      id: null,
      model: null,
      loading: true
    }
  },
  onLoad(query) {
    this.id = query && query.id
    this.fetchDetail()
  },
  methods: {
    async fetchDetail() {
      this.loading = true
      try {
        const res = await modelApi.getDetail(this.id)
        this.model = res.data
      } catch (e) {
        this.model = null
        uni.showToast({ title: e.message || '加载失败', icon: 'none' })
      } finally {
        this.loading = false
      }
    },
    statusText(status) {
      const map = { pending: '等待中', processing: '生成中', completed: '已完成', failed: '生成失败' }
      return map[status] || status
    },
    typeText(t) {
      const map = { character: '角色', object: '物体', scene: '场景' }
      return map[t] || t
    },
    fullUrl(path) {
      if (!path) return ''
      if (/^https?:\/\//.test(path)) return path
      // 兼容后端返回的相对路径
      return `${getApp().globalData?.BASE_URL || 'http://localhost:3344/api/../'}`.replace(/\/$/, '/') + path
    }
  }
}
</script>

<style lang="scss" scoped>
.container {
  padding: 20rpx;
}
.card {
  background: #fff;
  border-radius: 12rpx;
  padding: 24rpx;
}
.cover {
  width: 100%;
  height: 360rpx;
  border-radius: 8rpx;
  margin-bottom: 16rpx;
}
.info {
  display: flex;
  flex-direction: column;
  gap: 12rpx;
}
.name { font-size: 34rpx; font-weight: 600; }
.desc { color: #666; font-size: 26rpx; }
.meta { color: #888; font-size: 24rpx; }
.file { margin-top: 8rpx; }
</style>

