<template>
  <view class="container">
    <view class="header">
      <image class="logo" src="/static/logo.png" mode="aspectFit"></image>
      <text class="title">七牛3D</text>
      <text class="subtitle">AI驱动的3D模型生成平台</text>
    </view>
    
    <view class="form-container">
      <view class="form-item">
        <input 
          class="input" 
          type="text" 
          placeholder="请输入用户名或邮箱" 
          v-model="form.username"
        />
      </view>
      
      <view class="form-item">
        <input 
          class="input" 
          type="password" 
          placeholder="请输入密码" 
          v-model="form.password"
        />
      </view>
      
      <button 
        class="login-btn" 
        :class="{ disabled: !canLogin }"
        @click="handleLogin"
        :loading="loading"
      >
        登录
      </button>
      
      <view class="register-link">
        <text>还没有账号？</text>
        <text class="link" @click="goToRegister">立即注册</text>
      </view>
      
      <view class="divider">
        <text>或</text>
      </view>
      
      <view class="third-party">
        <button class="third-btn wechat" @click="loginWithWechat">
          <text class="icon">💬</text>
          <text>微信登录</text>
        </button>
        <button class="third-btn qq" @click="loginWithQQ">
          <text class="icon">🐧</text>
          <text>QQ登录</text>
        </button>
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
        password: ''
      },
      loading: false
    }
  },
  
  computed: {
    canLogin() {
      return this.form.username.trim() && this.form.password.trim()
    }
  },
  
  methods: {
    ...mapActions(['login']),
    
    // 处理登录
    async handleLogin() {
      if (!this.canLogin) {
        uni.showToast({
          title: '请填写完整信息',
          icon: 'none'
        })
        return
      }
      
      this.loading = true
      
      try {
        await this.login({
          username: this.form.username.trim(),
          password: this.form.password
        })
        
        uni.showToast({
          title: '登录成功',
          icon: 'success'
        })
        
        // 返回上一页或跳转到首页
        setTimeout(() => {
          const pages = getCurrentPages()
          if (pages.length > 1) {
            uni.navigateBack()
          } else {
            uni.switchTab({
              url: '/pages/index/index'
            })
          }
        }, 1500)
        
      } catch (error) {
        uni.showToast({
          title: error.message || '登录失败',
          icon: 'none'
        })
      } finally {
        this.loading = false
      }
    },
    
    // 跳转到注册页面
    goToRegister() {
      uni.navigateTo({
        url: '/pages/register/register'
      })
    },
    
    // 微信登录
    loginWithWechat() {
      uni.showToast({
        title: '微信登录功能开发中',
        icon: 'none'
      })
    },
    
    // QQ登录
    loginWithQQ() {
      uni.showToast({
        title: 'QQ登录功能开发中',
        icon: 'none'
      })
    }
  }
}
</script>

<style lang="scss" scoped>
.container {
  min-height: 100vh;
  background: radial-gradient(1200rpx 600rpx at 50% -100rpx, rgba(43,116,255,0.28) 0%, rgba(15,17,21,0) 60%), #0f1115;
  padding: 120rpx 60rpx 60rpx;
}

.header {
  text-align: center;
  margin-bottom: 60rpx;
}

.logo {
  width: 120rpx;
  height: 120rpx;
  margin-bottom: 20rpx;
}

.title {
  display: block;
  font-size: 48rpx;
  font-weight: bold;
  color: #e6e8eb;
  margin-bottom: 10rpx;
}

.subtitle {
  display: block;
  font-size: 28rpx;
  color: #9aa4b2;
}

.form-container {
  background-color: #ffffff;
  border-radius: 24rpx;
  padding: 60rpx 40rpx;
  box-shadow: 0 20rpx 80rpx rgba(0, 0, 0, 0.35);
}

.form-item {
  margin-bottom: 30rpx;
}

.input {
  width: 100%;
  height: 84rpx;
  border: 2rpx solid #e6e8eb;
  border-radius: 12rpx;
  padding: 0 22rpx;
  font-size: 28rpx;
  background-color: #f7f7f8;
}

.input:focus {
  border-color: #2b74ff;
  background-color: #fff;
}

.login-btn {
  width: 100%;
  height: 84rpx;
  background-color: #2b74ff;
  color: #fff;
  border: none;
  border-radius: 12rpx;
  font-size: 32rpx;
  font-weight: bold;
  margin-bottom: 30rpx;
}

.login-btn.disabled {
  background-color: #9aa4b2;
}

.register-link {
  text-align: center;
  margin-bottom: 40rpx;
  font-size: 28rpx;
  color: #9aa4b2;
}

.link {
  color: #2b74ff;
  margin-left: 10rpx;
}

.divider {
  text-align: center;
  margin-bottom: 40rpx;
  position: relative;
  font-size: 24rpx;
  color: #9aa4b2;
}

.divider::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  height: 2rpx;
  background-color: #e6e8eb;
  z-index: 1;
}

.divider text {
  background-color: #fff;
  padding: 0 20rpx;
  position: relative;
  z-index: 2;
}

.third-party {
  display: flex;
  gap: 20rpx;
}

.third-btn {
  flex: 1;
  height: 80rpx;
  border: 2rpx solid #e6e8eb;
  border-radius: 12rpx;
  background-color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28rpx;
  color: #333;
}

.third-btn.wechat {
  border-color: #1aad19;
  color: #1aad19;
}

.third-btn.qq {
  border-color: #12b7f5;
  color: #12b7f5;
}

.icon {
  margin-right: 10rpx;
  font-size: 32rpx;
}
</style>