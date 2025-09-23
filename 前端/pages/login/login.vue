<template>
  <view class="login-page">
    <view class="login-dialog">
      <view class="dialog-left">
        <view class="brand">
          <image class="brand-logo" src="/static/logo.png" mode="aspectFit"></image>
          <text class="brand-title">七牛元宝</text>
          <text class="brand-sub">轻松工作，多点生活</text>
        </view>
        <view class="left-graphic"></view>
      </view>

      <view class="dialog-right">
        <view class="tabs">
          <view class="tab" :class="{ active: activeTab==='wechat' }" @click="switchTab('wechat')">
            <uni-icons type="weixin" size="20" color="#2bb908"></uni-icons>
            <text>微信</text>
          </view>
          <view class="tab" :class="{ active: activeTab==='qq' }" @click="switchTab('qq')">
            <uni-icons type="qq" size="20" color="#12b7f5"></uni-icons>
            <text>QQ</text>
          </view>
          <view class="tab" :class="{ active: activeTab==='email' }" @click="switchTab('email')">
            <uni-icons type="email" size="20" color="#2b74ff"></uni-icons>
            <text>邮箱</text>
          </view>
        </view>

        <view class="hint" v-if="activeTab!=='email'">
          <text>当前仅展示样式，{{ activeTab==='wechat' ? '微信' : 'QQ' }}登录开发中</text>
        </view>

        <view class="form" v-if="activeTab==='email'">
          <view class="form-item">
            <input
              class="input"
              type="text"
              placeholder="请输入邮箱地址"
              v-model="form.username"
            />
          </view>

          <view class="form-item code-row">
            <input
              class="input"
              type="text"
              placeholder="请输入邮箱验证码"
              v-model="form.password"
            />
            <button class="code-btn" @click="getEmailCode">获取验证码</button>
          </view>

          <view class="agreements">
            <label class="agree-item" @click="agree = !agree">
              <view class="checkbox" :class="{ checked: agree }"></view>
              <text>我已阅读并同意</text>
              <text class="link" @click.stop="openProtocol('privacy')">隐私协议</text>
              <text>和</text>
              <text class="link" @click.stop="openProtocol('user')">用户服务协议</text>
            </label>
          </view>

          <button
            class="login-btn"
            :class="{ disabled: !canLogin || !agree }"
            @click="handleLogin"
            :loading="loading"
          >登录</button>
        </view>

        <view class="other">
          <text class="other-text" @click="goToRegister">其他方式登录</text>
        </view>
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
      loading: false,
      activeTab: 'email',
      agree: true
    }
  },
  
  computed: {
    canLogin() {
      return this.form.username.trim() && this.form.password.trim()
    }
  },
  
  methods: {
    ...mapActions(['login']),
    switchTab(tab) {
      this.activeTab = tab
      if (tab !== 'email') {
        uni.showToast({ title: '功能开发中', icon: 'none' })
      }
    },
    getEmailCode() {
      uni.showToast({ title: '验证码已发送(示例)', icon: 'none' })
    },
    openProtocol(type) {
      const title = type === 'privacy' ? '隐私协议' : '用户服务协议'
      uni.showModal({ title, content: '协议内容示例', showCancel: false })
    },
    
    // 处理登录
    async handleLogin() {
      if (!this.canLogin) {
        uni.showToast({
          title: '请填写完整信息',
          icon: 'none'
        })
        return
      }
      if (!this.agree) {
        uni.showToast({ title: '请先同意相关协议', icon: 'none' })
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
.login-page {
  min-height: 100vh;
  background: radial-gradient(1200rpx 600rpx at 50% -100rpx, rgba(43,116,255,0.28) 0%, rgba(15,17,21,0) 60%), #0f1115;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 60rpx 40rpx;
}

.login-dialog {
  width: 88vw;
  max-width: 1100rpx;
  background: #fff;
  border-radius: 20rpx;
  display: flex;
  overflow: hidden;
  box-shadow: 0 30rpx 120rpx rgba(0, 0, 0, 0.5);
}

.dialog-left {
  width: 46%;
  background: linear-gradient(180deg, #f3f7ff 0%, #eaf1ff 100%);
  padding: 50rpx 40rpx;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.brand {
  color: #1f2329;
}

.brand-logo {
  width: 72rpx;
  height: 72rpx;
  margin-bottom: 20rpx;
}

.brand-title {
  display: block;
  font-size: 40rpx;
  font-weight: 700;
  margin-bottom: 8rpx;
}

.brand-sub {
  color: #5e6d82;
  font-size: 26rpx;
}

.left-graphic {
  height: 280rpx;
  border-radius: 16rpx;
  background: radial-gradient(200rpx 200rpx at 30% 30%, rgba(43,116,255,0.25), rgba(43,116,255,0));
}

.dialog-right {
  width: 54%;
  padding: 40rpx;
}

.tabs {
  display: flex;
  gap: 16rpx;
  margin-bottom: 30rpx;
}

.tab {
  height: 64rpx;
  padding: 0 24rpx;
  border-radius: 36rpx;
  background: #f5f7fa;
  display: flex;
  align-items: center;
  gap: 12rpx;
  color: #5e6d82;
}

.tab.active {
  background: #e8f0ff;
  color: #2b74ff;
}

.hint {
  font-size: 26rpx;
  color: #9aa4b2;
  margin-bottom: 30rpx;
}

.form-item {
  margin-bottom: 24rpx;
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

.code-row {
  display: flex;
  gap: 16rpx;
}

.code-row .input {
  flex: 1;
}

.code-btn {
  width: 200rpx;
  height: 84rpx;
  border-radius: 12rpx;
  background: #eef3ff;
  color: #2b74ff;
  font-size: 26rpx;
}

.agreements {
  margin: 10rpx 0 20rpx;
  color: #5e6d82;
  font-size: 24rpx;
}

.agree-item {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
}

.checkbox {
  width: 28rpx;
  height: 28rpx;
  border-radius: 6rpx;
  background: #f2f3f5;
  margin-right: 12rpx;
}

.checkbox.checked {
  background: #2b74ff;
}

.link {
  color: #2b74ff;
  margin: 0 8rpx;
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
}

.login-btn.disabled {
  background-color: #9aa4b2;
}

.other {
  margin-top: 24rpx;
  text-align: center;
}

.other-text {
  color: #5e6d82;
  font-size: 24rpx;
}
</style>