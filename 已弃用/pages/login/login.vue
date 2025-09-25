<template>
  <view class="login-page">
    <view class="login-dialog">
      <view class="dialog-left">
        <view class="brand">
          <image class="brand-logo" src="/static/logo.png" mode="aspectFit"></image>
          <text class="brand-title">七牛三弟</text>
          <text class="brand-sub">轻松工作，多点生活</text>
        </view>
        <view class="left-graphic"></view>
      </view>

      <view class="dialog-right">
        <view class="tabs">
          <view class="tab" :class="{ active: activeTab==='wechat' }" @click="navigateTo('wechat')">
            <uni-icons type="weixin" size="20" color="#2bb908"></uni-icons>

          </view>
          <view class="tab" :class="{ active: activeTab==='qq' }" @click="navigateTo('qq')">
            <uni-icons type="qq" size="20" color="#12b7f5"></uni-icons>

          </view>
          <view class="tab" :class="{ active: activeTab==='email' }" @click="navigateTo('email')">
            <uni-icons type="email" size="20" color="#2b74ff"></uni-icons>

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
              placeholder="请输入用户名或邮箱"
              :placeholder-style="'color:#9aa4b2'"
              v-model="form.username"
            />
          </view>

          <view class="form-item">
            <input
              class="input"
              type="password"
              placeholder="请输入密码"
              :placeholder-style="'color:#9aa4b2'"
              v-model="form.password"
            />
          </view>

          <view class="agreements">
            <checkbox-group @change="changeAgree">
              <label class="agree-item">
                <checkbox value="agree" :checked="agree" color="#2b74ff" />
                <text>我已阅读并同意</text>
                <text class="link" @click.stop="openProtocol('privacy')">隐私协议</text>
                <text>和</text>
                <text class="link" @click.stop="openProtocol('user')">用户服务协议</text>
              </label>
            </checkbox-group>
          </view>

          <button
            class="login-btn"
            :class="{ disabled: !canLogin || !agree }"
            @click="handleLogin"
            :loading="loading"
          >登录</button>
        </view>

        <view class="other">
          <text class="other-text" @click="goToRegister">注册</text>
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
    navigateTo(tab) {
      this.activeTab = tab
      if (tab !== 'email') {
        uni.showToast({ title: '功能开发中', icon: 'none' })
      }
    },
    
    openProtocol(type) {
      const title = type === 'privacy' ? '隐私协议' : '用户服务协议'
      uni.showModal({ title, content: '协议内容示例', showCancel: false })
    },
    changeAgree(e) {
      const values = (e && e.detail && e.detail.value) || []
      this.agree = values.indexOf('agree') > -1
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
            uni.navigateTo({
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
// 整体页面：深色背景+居中弹窗
.login-page {
  min-height: 100vh;
  background: #0b0c0f;
  background-image: radial-gradient(80% 60% at 50% 65%, rgba(40, 50, 60, 0.6) 0%, rgba(11, 12, 15, 0) 60%),
    radial-gradient(120% 90% at 50% 120%, rgba(32, 40, 52, 0.8) 0%, rgba(11, 12, 15, 0) 60%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40rpx 30rpx;
  box-sizing: border-box;
}

.login-dialog {
  width: 1120rpx; // 560px
  max-width: 92vw;
  min-height: 700rpx;
  border-radius: 20rpx;
  overflow: hidden;
  background: #ffffff;
  display: flex;
  box-shadow: 0 30rpx 80rpx rgba(0, 0, 0, 0.45);
}

// 左侧品牌区
.dialog-left {
  width: 520rpx; // 260px
  background: linear-gradient(180deg, #f6fbff 0%, #eef6ff 100%);
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40rpx;
  box-sizing: border-box;
}

.brand {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 16rpx;
  z-index: 2;
}

.brand-logo {
  width: 56rpx;
  height: 56rpx;
}

.brand-title {
  font-size: 40rpx;
  font-weight: 600;
  color: #1f2937;
}

.brand-sub {
  font-size: 24rpx;
  color: #6b7280;
}

.left-graphic {
  position: absolute;
  right: -40rpx;
  bottom: -60rpx;
  width: 440rpx;
  height: 440rpx;
  background: radial-gradient(closest-side, #d8ecff, rgba(216, 236, 255, 0) 70%);
  border-radius: 50%;
  opacity: 0.7;
}

// 右侧内容区
.dialog-right {
  flex: 1;
  padding: 60rpx 56rpx;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
}

.tabs {
  display: flex;
  gap: 20rpx;
  margin-bottom: 30rpx;
}

.tab {
  height: 64rpx;
  padding: 0 26rpx;
  border-radius: 32rpx;
  background: #f3f4f6;
  color: #6b7280;
  display: inline-flex;
  align-items: center;
  gap: 12rpx;
  line-height: 64rpx;
}

.tab.active {
  background: #e8efff;
  color: #2b74ff;
}

.hint {
  background: #f9fafb;
  border: 2rpx dashed #e5e7eb;
  color: #6b7280;
  padding: 20rpx 24rpx;
  border-radius: 12rpx;
  margin-bottom: 28rpx;
}

.form {
  margin-top: 8rpx;
}

.form-item {
  margin-bottom: 26rpx;
}

.input {
  width: 100%;
  height: 88rpx;
  padding: 0 28rpx;
  border-radius: 12rpx;
  border: 2rpx solid #e5e7eb;
  background-color: #ffffff;
  box-sizing: border-box;
  font-size: 28rpx;
  color: #111827;
}

.input:focus {
  border-color: #2b74ff;
}

.code-row {
  display: flex;
  align-items: center;
  gap: 16rpx;
}

.code-btn {
  flex-shrink: 0;
  height: 88rpx;
  padding: 0 28rpx;
  border-radius: 12rpx;
  background: #eef4ff;
  color: #2b74ff;
  font-size: 26rpx;
}

.agreements {
  margin: 6rpx 0 24rpx;
}

.agree-item {
  display: inline-flex;
  align-items: center;
  gap: 10rpx;
  color: #6b7280;
  font-size: 24rpx;
}

.link {
  color: #2b74ff;
}

.login-btn {
  width: 100%;
  height: 92rpx;
  border-radius: 14rpx;
  background: #111827;
  color: #ffffff;
  font-weight: 600;
  font-size: 30rpx;
}

.login-btn.disabled {
  opacity: 0.6;
}

.other {
  margin-top: auto;
  display: flex;
  justify-content: center;
}

.other-text {
  color: #6b7280;
  font-size: 26rpx;
}

// 响应式：窄屏改为上下布局
@media screen and (max-width: 800px) {
  .login-dialog {
    flex-direction: column;
    width: 100%;
    min-height: auto;
  }
  .dialog-left {
    width: 100%;
    height: 300rpx;
    align-items: flex-start;
  }
  .dialog-right {
    padding: 40rpx 32rpx 48rpx;
  }
}
</style>