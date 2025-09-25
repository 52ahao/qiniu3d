<template>
  <view class="container">
    <!-- 上传图片区域 -->
    <view class="upload-section">
      <view class="upload-area" @click="chooseImage" v-if="!selectedImage">
        <view class="upload-icon">📷</view>
        <text class="upload-text">点击上传图片</text>
        <text class="upload-tip">支持JPG、PNG格式，最大10MB</text>
      </view>
      
      <view class="image-preview" v-else>
        <image :src="selectedImage" class="preview-image" mode="aspectFit"></image>
        <view class="image-actions">
          <button class="action-btn" @click="chooseImage">重新选择</button>
          <button class="action-btn delete" @click="removeImage">删除</button>
        </view>
      </view>
    </view>
    
    <!-- 模型信息表单 -->
    <view class="form-section">
      <view class="form-item">
        <text class="label">模型名称</text>
        <input 
          class="input" 
          type="text" 
          placeholder="请输入模型名称" 
          v-model="form.name"
          maxlength="50"
        />
      </view>
      
      <view class="form-item">
        <text class="label">模型描述</text>
        <textarea 
          class="textarea" 
          placeholder="请输入模型描述（可选）" 
          v-model="form.description"
          maxlength="200"
        ></textarea>
      </view>
      
      <view class="form-item">
        <text class="label">模型类型</text>
        <view class="type-options">
          <view 
            class="type-option" 
            :class="{ active: form.model_type === 'character' }"
            @click="selectType('character')"
          >
            <view class="type-icon">👤</view>
            <text class="type-text">角色</text>
          </view>
          <view 
            class="type-option" 
            :class="{ active: form.model_type === 'object' }"
            @click="selectType('object')"
          >
            <view class="type-icon">📦</view>
            <text class="type-text">物体</text>
          </view>
          <view 
            class="type-option" 
            :class="{ active: form.model_type === 'scene' }"
            @click="selectType('scene')"
          >
            <view class="type-icon">🏞️</view>
            <text class="type-text">场景</text>
          </view>
        </view>
      </view>
    </view>
    
    <!-- 积分消耗提示 -->
    <view class="cost-section">
      <view class="cost-info">
        <text class="cost-label">消耗积分</text>
        <text class="cost-value">{{ modelCost }}</text>
      </view>
      <view class="points-info">
        <text class="points-label">当前积分</text>
        <text class="points-value">{{ points }}</text>
      </view>
    </view>
    
    <!-- 生成按钮 -->
    <view class="action-section">
      <button 
        class="generate-btn" 
        :class="{ disabled: !canGenerate }"
        @click="handleGenerate"
        :loading="generating"
      >
        {{ generating ? '生成中...' : '开始生成' }}
      </button>
    </view>
    
    <!-- 生成历史 -->
    <view class="history-section" v-if="recentModels.length > 0">
      <view class="section-title">最近生成</view>
      <view class="history-list">
        <view 
          class="history-item" 
          v-for="model in recentModels" 
          :key="model.id"
          @click="goToModelDetail(model.id)"
        >
          <image :src="model.original_image" class="history-image" mode="aspectFill"></image>
          <view class="history-info">
            <text class="history-name">{{ model.name }}</text>
            <text class="history-status" :class="model.status">{{ getStatusText(model.status) }}</text>
          </view>
        </view>
      </view>
    </view>
  </view>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'
import { modelApi } from '@/utils/api.js'

export default {
  data() {
    return {
      selectedImage: '',
      form: {
        name: '',
        description: '',
        model_type: 'character'
      },
      generating: false,
      recentModels: []
    }
  },
  
  computed: {
    ...mapGetters(['points']),
    
    modelCost() {
      return 10 // 生成3D模型消耗10积分
    },
    
    canGenerate() {
      return this.selectedImage && 
             this.form.name.trim() && 
             this.form.model_type && 
             this.points >= this.modelCost
    }
  },
  
  onLoad() {
    this.loadRecentModels()
  },
  
  methods: {
    ...mapActions(['updatePoints']),
    
    // 选择图片
    chooseImage() {
      uni.chooseImage({
        count: 1,
        sizeType: ['original', 'compressed'],
        sourceType: ['album', 'camera'],
        success: (res) => {
          const tempFilePath = res.tempFilePaths[0]
          this.uploadImage(tempFilePath)
        }
      })
    },
    
    // 上传图片
    async uploadImage(filePath) {
      uni.showLoading({
        title: '上传中...'
      })
      
      try {
        const res = await modelApi.uploadImage(filePath)
        this.selectedImage = res.data.url
        uni.hideLoading()
      } catch (error) {
        uni.hideLoading()
        uni.showToast({
          title: error.message || '上传失败',
          icon: 'none'
        })
      }
    },
    
    // 删除图片
    removeImage() {
      this.selectedImage = ''
    },
    
    // 选择模型类型
    selectType(type) {
      this.form.model_type = type
    },
    
    // 处理生成
    async handleGenerate() {
      if (!this.canGenerate) {
        if (this.points < this.modelCost) {
          uni.showModal({
            title: '积分不足',
            content: `生成3D模型需要${this.modelCost}积分，当前积分${this.points}，是否前往充值？`,
            success: (res) => {
              if (res.confirm) {
                uni.navigateTo({
                  url: '/pages/points/purchase'
                })
              }
            }
          })
        } else {
          uni.showToast({
            title: '请完善信息',
            icon: 'none'
          })
        }
        return
      }
      
      this.generating = true
      
      try {
        const res = await modelApi.generate({
          name: this.form.name.trim(),
          description: this.form.description.trim(),
          image_url: this.selectedImage,
          model_type: this.form.model_type
        })
        
        uni.showToast({
          title: '生成任务已提交',
          icon: 'success'
        })
        
        // 更新积分
        this.updatePoints(this.points - this.modelCost)
        
        // 重置表单
        this.resetForm()
        
        // 刷新最近生成列表
        this.loadRecentModels()
        
        // 跳转到模型列表
        setTimeout(() => {
          uni.navigateTo({
            url: '/pages/model/list'
          })
        }, 1500)
        
      } catch (error) {
        uni.showToast({
          title: error.message || '生成失败',
          icon: 'none'
        })
      } finally {
        this.generating = false
      }
    },
    
    // 重置表单
    resetForm() {
      this.selectedImage = ''
      this.form = {
        name: '',
        description: '',
        model_type: 'character'
      }
    },
    
    // 加载最近生成的模型
    async loadRecentModels() {
      try {
        const res = await modelApi.getList({ limit: 5 })
        this.recentModels = res.data.models
      } catch (error) {
        console.error('加载最近模型失败:', error)
      }
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
  padding: 20rpx;
  background-color: #f5f5f5;
  min-height: 100vh;
}

.upload-section {
  background-color: #fff;
  border-radius: 10rpx;
  margin-bottom: 20rpx;
  overflow: hidden;
}

.upload-area {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 300rpx;
  border: 2rpx dashed #ddd;
  margin: 20rpx;
  border-radius: 10rpx;
}

.upload-icon {
  font-size: 80rpx;
  margin-bottom: 20rpx;
}

.upload-text {
  font-size: 32rpx;
  color: #333;
  margin-bottom: 10rpx;
}

.upload-tip {
  font-size: 24rpx;
  color: #999;
}

.image-preview {
  padding: 20rpx;
}

.preview-image {
  width: 100%;
  height: 300rpx;
  border-radius: 10rpx;
}

.image-actions {
  display: flex;
  gap: 20rpx;
  margin-top: 20rpx;
}

.action-btn {
  flex: 1;
  height: 60rpx;
  background-color: #007aff;
  color: #fff;
  border: none;
  border-radius: 5rpx;
  font-size: 28rpx;
}

.action-btn.delete {
  background-color: #ff3b30;
}

.form-section {
  background-color: #fff;
  border-radius: 10rpx;
  padding: 30rpx;
  margin-bottom: 20rpx;
}

.form-item {
  margin-bottom: 30rpx;
}

.label {
  display: block;
  font-size: 28rpx;
  color: #333;
  margin-bottom: 15rpx;
  font-weight: bold;
}

.input {
  width: 100%;
  height: 70rpx;
  border: 2rpx solid #e5e5e5;
  border-radius: 5rpx;
  padding: 0 20rpx;
  font-size: 28rpx;
  background-color: #f8f8f8;
}

.textarea {
  width: 100%;
  height: 120rpx;
  border: 2rpx solid #e5e5e5;
  border-radius: 5rpx;
  padding: 20rpx;
  font-size: 28rpx;
  background-color: #f8f8f8;
}

.type-options {
  display: flex;
  gap: 20rpx;
}

.type-option {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 20rpx;
  border: 2rpx solid #e5e5e5;
  border-radius: 10rpx;
  background-color: #f8f8f8;
}

.type-option.active {
  border-color: #007aff;
  background-color: #e3f2fd;
}

.type-icon {
  font-size: 40rpx;
  margin-bottom: 10rpx;
}

.type-text {
  font-size: 24rpx;
  color: #333;
}

.cost-section {
  background-color: #fff;
  border-radius: 10rpx;
  padding: 30rpx;
  margin-bottom: 20rpx;
  display: flex;
  justify-content: space-between;
}

.cost-info, .points-info {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.cost-label, .points-label {
  font-size: 24rpx;
  color: #666;
  margin-bottom: 10rpx;
}

.cost-value {
  font-size: 32rpx;
  font-weight: bold;
  color: #ff3b30;
}

.points-value {
  font-size: 32rpx;
  font-weight: bold;
  color: #007aff;
}

.action-section {
  margin-bottom: 30rpx;
}

.generate-btn {
  width: 100%;
  height: 80rpx;
  background-color: #007aff;
  color: #fff;
  border: none;
  border-radius: 10rpx;
  font-size: 32rpx;
  font-weight: bold;
}

.generate-btn.disabled {
  background-color: #ccc;
}

.history-section {
  background-color: #fff;
  border-radius: 10rpx;
  padding: 30rpx;
}

.section-title {
  font-size: 32rpx;
  font-weight: bold;
  color: #333;
  margin-bottom: 20rpx;
}

.history-list {
  display: flex;
  flex-direction: column;
  gap: 20rpx;
}

.history-item {
  display: flex;
  align-items: center;
  padding: 20rpx;
  background-color: #f8f8f8;
  border-radius: 10rpx;
}

.history-image {
  width: 100rpx;
  height: 100rpx;
  border-radius: 10rpx;
  margin-right: 20rpx;
}

.history-info {
  flex: 1;
}

.history-name {
  display: block;
  font-size: 28rpx;
  color: #333;
  margin-bottom: 10rpx;
}

.history-status {
  font-size: 24rpx;
  padding: 5rpx 15rpx;
  border-radius: 15rpx;
  color: #fff;
}

.history-status.pending {
  background-color: #ff9500;
}

.history-status.processing {
  background-color: #007aff;
}

.history-status.completed {
  background-color: #34c759;
}

.history-status.failed {
  background-color: #ff3b30;
}
</style>