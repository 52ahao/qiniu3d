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

// 原始 getter 定义（函数形式）
const rawGetters = {
  // 获取用户信息
  user: state => state.user,
  // 是否已登录
  isLogin: state => state.isLogin,
  // 积分余额
  points: state => state.points
}

// 轻量 store 适配：提供 commit/dispatch，并把 getters 暴露为可直接取值的属性
const store = {
  state,
  mutations,
  actions,
  getters: {}
}

// 提供 commit
store.commit = function(type, payload) {
  const mutation = store.mutations[type]
  if (typeof mutation === 'function') {
    mutation(store.state, payload)
  } else {
    console.warn('[store] unknown mutation:', type)
  }
}

// 提供 dispatch（与 mapActions 兼容）
store.dispatch = function(type, payload) {
  const action = store.actions[type]
  if (typeof action === 'function') {
    return action({ commit: store.commit, state: store.state }, payload)
  } else {
    console.warn('[store] unknown action:', type)
    return Promise.reject(new Error('unknown action'))
  }
}

// 将 getters 暴露为取值属性（保持与 Vuex 相同使用方式）
Object.keys(rawGetters).forEach((key) => {
  Object.defineProperty(store.getters, key, {
    enumerable: true,
    get() {
      return rawGetters[key](store.state)
    }
  })
})

export default store