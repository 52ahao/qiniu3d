/**
 * 状态管理
 */
import { userApi } from '@/utils/api.js'

const state = {
  // 用户信息
  user: null,
  // 是否已登录
  isLogin: false,
  // 积分余额
  points: 0
}

const mutations = {
  // 设置用户信息
  SET_USER(state, user) {
    state.user = user
    state.isLogin = !!user
    if (user) {
      state.points = user.points || 0
    }
  },
  
  // 清除用户信息
  CLEAR_USER(state) {
    state.user = null
    state.isLogin = false
    state.points = 0
  },
  
  // 更新积分
  UPDATE_POINTS(state, points) {
    state.points = points
    if (state.user) {
      state.user.points = points
    }
  }
}

const actions = {
  // 登录
  async login({ commit }, { username, password }) {
    try {
      const res = await userApi.login({ username, password })
      
      // 保存token
      uni.setStorageSync('token', res.data.token)
      
      // 保存用户信息
      commit('SET_USER', res.data.user)
      
      return res
    } catch (error) {
      throw error
    }
  },
  
  // 注册
  async register({ commit }, userData) {
    try {
      const res = await userApi.register(userData)
      
      // 保存token
      uni.setStorageSync('token', res.data.token)
      
      // 保存用户信息
      commit('SET_USER', res.data.user)
      
      return res
    } catch (error) {
      throw error
    }
  },
  
  // 获取用户信息
  async getUserInfo({ commit }) {
    try {
      const res = await userApi.getProfile()
      commit('SET_USER', res.data)
      return res
    } catch (error) {
      throw error
    }
  },
  
  // 登出
  logout({ commit }) {
    uni.removeStorageSync('token')
    commit('CLEAR_USER')
  },
  
  // 更新积分
  updatePoints({ commit }, points) {
    commit('UPDATE_POINTS', points)
  }
}

const getters = {
  // 获取用户信息
  user: state => state.user,
  // 是否已登录
  isLogin: state => state.isLogin,
  // 积分余额
  points: state => state.points
}

export default {
  state,
  mutations,
  actions,
  getters
}