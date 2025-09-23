<template>
  <view class="container">
    <view class="header">
      <image class="logo" src="/static/logo.png" mode="aspectFit"></image>
      <text class="title">注册账号</text>
      <text class="subtitle">开启你的3D创作之旅</text>
    </view>
    
    <view class="form-container">
      <view class="form-item">
        <input 
          class="input" 
          type="text" 
          placeholder="请输入用户名" 
          v-model="form.username"
          maxlength="20"
        />
      </view>
      
      <view class="form-item">
        <input 
          class="input" 
          type="text" 
          placeholder="请输入邮箱" 
          v-model="form.email"
        />
      </view>
      
      <view class="form-item">
        <input 
          class="input" 
          type="password" 
          placeholder="请输入密码（至少6位）" 
          v-model="form.password"
          maxlength="20"
        />
      </view>
      
      <view class="form-item">
        <input 
          class="input" 
          type="password" 
          placeholder="请确认密码" 
          v-model="form.confirmPassword"
          maxlength="20"
        />
      </view>
      
      <view class="agreement">
        <checkbox-group @change="onAgreementChange">
          <label class="agreement-item">
            <checkbox value="agree" :checked="agreed" />
            <text class="agreement-text">
              我已阅读并同意
              <text class="link" @click="showUserAgreement">《用户协议》</text>
              和
              <text class="link" @click="showPrivacyPolicy">《隐私政策》</text>
            </text>
          </label>
        </checkbox-group>
      </view>
      
      <button 
        class="register-btn" 
        :class="{ disabled: !canRegister }"
        @click="handleRegister"
        :loading="loading"
      >
        注册
      </button>
      
      <view class="login-link">
        <text>已有账号？</text>
        <text class="link" @click="goToLogin">立即登录</text>
      </view>
    </view>
  </view>
</template>

<script>
import { mapActions } from 'vuex'

export default {
  data() {
    return {
      form: {
        username: '',
        email: '',
        password: '',
        confirmPassword: ''
      },
      agreed: false,
      loading: false
    }
  },
  
  computed: {
    canRegister() {
      return this.form.username.trim() && 
             this.form.email.trim() && 
             this.form.password.trim() && 
             this.form.confirmPassword.trim() &&
             this.agreed &&
             this.form.password === this.form.confirmPassword
    }
  },
  
  methods: {
    ...mapActions(['register']),
    
    // 处理注册
    async handleRegister() {
      if (!this.validateForm()) {
        return
      }
      
      this.loading = true
      
      try {
        await this.register({
          username: this.form.username.trim(),
          email: this.form.email.trim(),
          password: this.form.password
        })
        
        uni.showToast({
          title: '注册成功',
          icon: 'success'
        })
        
        // 跳转到首页
        setTimeout(() => {
          uni.switchTab({
            url: '/pages/index/index'
          })
        }, 1500)
        
      } catch (error) {
        uni.showToast({
          title: error.message || '注册失败',
          icon: 'none'
        })
      } finally {
        this.loading = false
      }
    },
    
    // 验证表单
    validateForm() {
      if (!this.form.username.trim()) {
        uni.showToast({
          title: '请输入用户名',
          icon: 'none'
        })
        return false
      }
      
      if (this.form.username.length < 3) {
        uni.showToast({
          title: '用户名至少3个字符',
          icon: 'none'
        })
        return false
      }
      
      if (!this.form.email.trim()) {
        uni.showToast({
          title: '请输入邮箱',
          icon: 'none'
        })
        return false
      }
      
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      if (!emailRegex.test(this.form.email)) {
        uni.showToast({
          title: '邮箱格式不正确',
          icon: 'none'
        })
        return false
      }
      
      if (!this.form.password.trim()) {
        uni.showToast({
          title: '请输入密码',
          icon: 'none'
        })
        return false
      }
      
      if (this.form.password.length < 6) {
        uni.showToast({
          title: '密码至少6位',
          icon: 'none'
        })
        return false
      }
      
      if (this.form.password !== this.form.confirmPassword) {
        uni.showToast({
          title: '两次密码输入不一致',
          icon: 'none'
        })
        return false
      }
      
      if (!this.agreed) {
        uni.showToast({
          title: '请同意用户协议和隐私政策',
          icon: 'none'
        })
        return false
      }
      
      return true
    },
    
    // 协议选择变化
    onAgreementChange(e) {
      this.agreed = e.detail.value.includes('agree')
    },
    
    // 跳转到登录页面
    goToLogin() {
      uni.navigateBack()
    },
    
    // 显示用户协议
    showUserAgreement() {
      uni.showModal({
        title: '用户协议',
        content: '这里是用户协议的内容...',
        showCancel: false
      })
    },
    
    // 显示隐私政策
    showPrivacyPolicy() {
      uni.showModal({
        title: '隐私政策',
        content: '这里是隐私政策的内容...',
        showCancel: false
      })
    }
  }
}
</script>

<style lang="scss" scoped>
.container {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 60rpx 60rpx 60rpx;
}

.header {
  text-align: center;
  margin-bottom: 60rpx;
}

.logo {
  width: 100rpx;
  height: 100rpx;
  margin-bottom: 20rpx;
}

.title {
  display: block;
  font-size: 40rpx;
  font-weight: bold;
  color: #fff;
  margin-bottom: 10rpx;
}

.subtitle {
  display: block;
  font-size: 26rpx;
  color: rgba(255, 255, 255, 0.8);
}

.form-container {
  background-color: #fff;
  border-radius: 20rpx;
  padding: 50rpx 40rpx;
  box-shadow: 0 10rpx 30rpx rgba(0, 0, 0, 0.1);
}

.form-item {
  margin-bottom: 30rpx;
}

.input {
  width: 100%;
  height: 80rpx;
  border: 2rpx solid #e5e5e5;
  border-radius: 10rpx;
  padding: 0 20rpx;
  font-size: 28rpx;
  background-color: #f8f8f8;
}

.input:focus {
  border-color: #007aff;
  background-color: #fff;
}

.agreement {
  margin-bottom: 40rpx;
}

.agreement-item {
  display: flex;
  align-items: flex-start;
}

.agreement-text {
  font-size: 24rpx;
  color: #666;
  line-height: 1.5;
  margin-left: 10rpx;
}

.link {
  color: #007aff;
}

.register-btn {
  width: 100%;
  height: 80rpx;
  background-color: #007aff;
  color: #fff;
  border: none;
  border-radius: 10rpx;
  font-size: 32rpx;
  font-weight: bold;
  margin-bottom: 30rpx;
}

.register-btn.disabled {
  background-color: #ccc;
}

.login-link {
  text-align: center;
  font-size: 28rpx;
  color: #666;
}

.link {
  color: #007aff;
  margin-left: 10rpx;
}
</style>