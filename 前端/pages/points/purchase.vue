<template>
  <view class="container">
    <!-- 当前积分 -->
    <view class="current-points">
      <text class="points-label">当前积分</text>
      <text class="points-value">{{ points }}</text>
    </view>
    
    <!-- 充值套餐 -->
    <view class="packages-section">
      <view class="section-title">选择充值套餐</view>
      <view class="packages-grid">
        <view 
          class="package-item" 
          v-for="package in packages" 
          :key="package.id"
          :class="{ active: selectedPackage === package.id }"
          @click="selectPackage(package.id)"
        >
          <view class="package-points">{{ package.points }}</view>
          <view class="package-price">¥{{ package.price }}</view>
          <view class="package-bonus" v-if="package.bonus">送{{ package.bonus }}积分</view>
        </view>
      </view>
    </view>
    
    <!-- 自定义金额 -->
    <view class="custom-section">
      <view class="section-title">自定义金额</view>
      <view class="custom-input">
        <text class="currency">¥</text>
        <input 
          type="number" 
          placeholder="请输入金额" 
          v-model="customAmount"
          @input="onCustomAmountChange"
        />
        <text class="points-preview" v-if="customPoints > 0">= {{ customPoints }} 积分</text>
      </view>
    </view>
    
    <!-- 支付方式 -->
    <view class="payment-section">
      <view class="section-title">选择支付方式</view>
      <view class="payment-methods">
        <view 
          class="payment-method" 
          :class="{ active: selectedPayment === 'wechat' }"
          @click="selectPayment('wechat')"
        >
          <view class="payment-icon">💬</view>
          <text class="payment-name">微信支付</text>
          <view class="payment-check" v-if="selectedPayment === 'wechat'">✓</view>
        </view>
        <view 
          class="payment-method" 
          :class="{ active: selectedPayment === 'alipay' }"
          @click="selectPayment('alipay')"
        >
          <view class="payment-icon">💰</view>
          <text class="payment-name">支付宝</text>
          <view class="payment-check" v-if="selectedPayment === 'alipay'">✓</view>
        </view>
      </view>
    </view>
    
    <!-- 订单信息 -->
    <view class="order-section" v-if="orderAmount > 0">
      <view class="order-info">
        <view class="order-item">
          <text class="order-label">充值积分</text>
          <text class="order-value">{{ orderPoints }}</text>
        </view>
        <view class="order-item">
          <text class="order-label">支付金额</text>
          <text class="order-value">¥{{ orderAmount }}</text>
        </view>
        <view class="order-item total">
          <text class="order-label">实付金额</text>
          <text class="order-value">¥{{ orderAmount }}</text>
        </view>
      </view>
    </view>
    
    <!-- 支付按钮 -->
    <view class="payment-section">
      <button 
        class="pay-btn" 
        :class="{ disabled: !canPay }"
        @click="handlePayment"
        :loading="paying"
      >
        {{ paying ? '支付中...' : `立即支付 ¥${orderAmount}` }}
      </button>
    </view>
    
    <!-- 充值说明 -->
    <view class="info-section">
      <view class="info-title">充值说明</view>
      <view class="info-list">
        <view class="info-item">
          <text class="info-text">• 1元=10积分，充值后立即到账</text>
        </view>
        <view class="info-item">
          <text class="info-text">• 积分可用于生成3D模型和图片</text>
        </view>
        <view class="info-item">
          <text class="info-text">• 积分永久有效，不支持退款</text>
        </view>
        <view class="info-item">
          <text class="info-text">• 如有问题请联系客服</text>
        </view>
      </view>
    </view>
  </view>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'
import { pointsApi } from '@/utils/api.js'

export default {
  data() {
    return {
      packages: [
        { id: 1, points: 100, price: 10, bonus: 0 },
        { id: 2, points: 300, price: 30, bonus: 20 },
        { id: 3, points: 500, price: 50, bonus: 50 },
        { id: 4, points: 1000, price: 100, bonus: 150 },
        { id: 5, points: 2000, price: 200, bonus: 400 },
        { id: 6, points: 5000, price: 500, bonus: 1200 }
      ],
      selectedPackage: null,
      customAmount: '',
      selectedPayment: 'wechat',
      paying: false
    }
  },
  
  computed: {
    ...mapGetters(['points']),
    
    customPoints() {
      const amount = parseFloat(this.customAmount) || 0
      return Math.floor(amount * 10) // 1元=10积分
    },
    
    orderPoints() {
      if (this.selectedPackage) {
        const package = this.packages.find(p => p.id === this.selectedPackage)
        return package ? package.points + (package.bonus || 0) : 0
      } else if (this.customAmount) {
        return this.customPoints
      }
      return 0
    },
    
    orderAmount() {
      if (this.selectedPackage) {
        const package = this.packages.find(p => p.id === this.selectedPackage)
        return package ? package.price : 0
      } else if (this.customAmount) {
        return parseFloat(this.customAmount) || 0
      }
      return 0
    },
    
    canPay() {
      return this.orderAmount > 0 && this.selectedPayment
    }
  },
  
  methods: {
    ...mapActions(['updatePoints']),
    
    // 选择套餐
    selectPackage(packageId) {
      this.selectedPackage = packageId
      this.customAmount = ''
    },
    
    // 自定义金额变化
    onCustomAmountChange() {
      this.selectedPackage = null
    },
    
    // 选择支付方式
    selectPayment(payment) {
      this.selectedPayment = payment
    },
    
    // 处理支付
    async handlePayment() {
      if (!this.canPay) {
        uni.showToast({
          title: '请选择充值金额和支付方式',
          icon: 'none'
        })
        return
      }
      
      this.paying = true
      
      try {
        const res = await pointsApi.purchase({
          points: this.orderPoints,
          payment_method: this.selectedPayment
        })
        
        // 这里应该调用真实的支付接口
        // 示例中直接模拟支付成功
        uni.showModal({
          title: '支付确认',
          content: `确认支付 ¥${this.orderAmount} 购买 ${this.orderPoints} 积分？`,
          success: async (modalRes) => {
            if (modalRes.confirm) {
              // 模拟支付成功
              setTimeout(() => {
                this.handlePaymentSuccess()
              }, 2000)
            } else {
              this.paying = false
            }
          }
        })
        
      } catch (error) {
        this.paying = false
        uni.showToast({
          title: error.message || '支付失败',
          icon: 'none'
        })
      }
    },
    
    // 支付成功处理
    handlePaymentSuccess() {
      this.paying = false
      
      // 更新积分
      this.updatePoints(this.points + this.orderPoints)
      
      uni.showToast({
        title: '充值成功',
        icon: 'success'
      })
      
      // 重置表单
      this.selectedPackage = null
      this.customAmount = ''
      
      // 返回上一页
      setTimeout(() => {
        uni.navigateBack()
      }, 1500)
    }
  }
}
</script>

<style lang="scss" scoped>
.container {
  background-color: #f5f5f5;
  min-height: 100vh;
  padding-bottom: 100rpx;
}

.current-points {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  margin: 20rpx;
  border-radius: 15rpx;
  padding: 40rpx;
  text-align: center;
  color: #fff;
}

.points-label {
  display: block;
  font-size: 28rpx;
  margin-bottom: 10rpx;
  opacity: 0.9;
}

.points-value {
  font-size: 48rpx;
  font-weight: bold;
}

.packages-section {
  background-color: #fff;
  margin: 20rpx;
  border-radius: 15rpx;
  padding: 30rpx;
}

.section-title {
  font-size: 32rpx;
  font-weight: bold;
  color: #333;
  margin-bottom: 30rpx;
}

.packages-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 20rpx;
}

.package-item {
  width: calc(50% - 10rpx);
  background-color: #f8f8f8;
  border: 2rpx solid #e5e5e5;
  border-radius: 15rpx;
  padding: 30rpx 20rpx;
  text-align: center;
  position: relative;
}

.package-item.active {
  border-color: #007aff;
  background-color: #e3f2fd;
}

.package-points {
  font-size: 36rpx;
  font-weight: bold;
  color: #333;
  margin-bottom: 10rpx;
}

.package-price {
  font-size: 28rpx;
  color: #007aff;
  margin-bottom: 10rpx;
}

.package-bonus {
  font-size: 20rpx;
  color: #ff3b30;
  background-color: #fff2f0;
  padding: 5rpx 10rpx;
  border-radius: 10rpx;
  display: inline-block;
}

.custom-section {
  background-color: #fff;
  margin: 20rpx;
  border-radius: 15rpx;
  padding: 30rpx;
}

.custom-input {
  display: flex;
  align-items: center;
  background-color: #f8f8f8;
  border-radius: 10rpx;
  padding: 0 20rpx;
  height: 80rpx;
}

.currency {
  font-size: 32rpx;
  color: #333;
  margin-right: 10rpx;
}

.custom-input input {
  flex: 1;
  font-size: 32rpx;
  color: #333;
}

.points-preview {
  font-size: 24rpx;
  color: #007aff;
  margin-left: 20rpx;
}

.payment-section {
  background-color: #fff;
  margin: 20rpx;
  border-radius: 15rpx;
  padding: 30rpx;
}

.payment-methods {
  display: flex;
  flex-direction: column;
  gap: 20rpx;
}

.payment-method {
  display: flex;
  align-items: center;
  padding: 25rpx;
  background-color: #f8f8f8;
  border: 2rpx solid #e5e5e5;
  border-radius: 10rpx;
  position: relative;
}

.payment-method.active {
  border-color: #007aff;
  background-color: #e3f2fd;
}

.payment-icon {
  font-size: 40rpx;
  margin-right: 20rpx;
}

.payment-name {
  flex: 1;
  font-size: 28rpx;
  color: #333;
}

.payment-check {
  font-size: 32rpx;
  color: #007aff;
  font-weight: bold;
}

.order-section {
  background-color: #fff;
  margin: 20rpx;
  border-radius: 15rpx;
  padding: 30rpx;
}

.order-info {
  display: flex;
  flex-direction: column;
  gap: 20rpx;
}

.order-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.order-item.total {
  border-top: 1rpx solid #e5e5e5;
  padding-top: 20rpx;
  font-weight: bold;
}

.order-label {
  font-size: 28rpx;
  color: #666;
}

.order-value {
  font-size: 28rpx;
  color: #333;
}

.pay-btn {
  width: 100%;
  height: 80rpx;
  background-color: #007aff;
  color: #fff;
  border: none;
  border-radius: 15rpx;
  font-size: 32rpx;
  font-weight: bold;
  margin-top: 20rpx;
}

.pay-btn.disabled {
  background-color: #ccc;
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
  gap: 15rpx;
}

.info-item {
  display: flex;
  align-items: flex-start;
}

.info-text {
  font-size: 26rpx;
  color: #666;
  line-height: 1.5;
}
</style>