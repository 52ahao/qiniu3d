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
              :placeholder-style="'color:#9aa4b2'"
              v-model="form.username"
            />
          </view>

          <view class="form-item code-row">
            <input
              class="input"
              type="text"
              placeholder="请输入邮箱验证码"
              :placeholder-style="'color:#9aa4b2'"
              v-model="form.password"
            />
            <button class="code-btn" @click="getEmailCode">获取验证码</button>
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
</style>