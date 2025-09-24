/**
 * API请求工具类
 */
const BASE_URL = 'http://localhost:3344/api/'

class ApiService {
  constructor() {
    this.baseURL = BASE_URL
  }

  // 获取token
  getToken() {
    return uni.getStorageSync('token')
  }

  // 设置token
  setToken(token) {
    uni.setStorageSync('token', token)
  }

  // 清除token
  clearToken() {
    uni.removeStorageSync('token')
  }

  // 通用请求方法
  request(options) {
    return new Promise((resolve, reject) => {
      const token = this.getToken()
      
      uni.request({
        url: this.baseURL + options.url,
        method: options.method || 'GET',
        data: options.data || {},
        header: {
          'Content-Type': 'application/json',
          'Authorization': token ? `Bearer ${token}` : '',
          ...options.header
        },
        success: (res) => {
          if (res.statusCode === 200) {
            if (res.data.code === 200) {
              resolve(res.data)
            } else {
              // 处理业务错误
              if (res.data.code === 401) {
                // token过期，清除本地存储
                this.clearToken()
                uni.navigateTo({
                  url: '/pages/login/login'
                })
              }
              reject(res.data)
            }
          } else {
            reject({
              code: res.statusCode,
              message: '网络请求失败'
            })
          }
        },
        fail: (err) => {
          reject({
            code: -1,
            message: '网络连接失败'
          })
        }
      })
    })
  }

  // GET请求
  get(url, data = {}) {
    return this.request({
      url,
      method: 'GET',
      data
    })
  }

  // POST请求
  post(url, data = {}) {
    return this.request({
      url,
      method: 'POST',
      data
    })
  }

  // PUT请求
  put(url, data = {}) {
    return this.request({
      url,
      method: 'PUT',
      data
    })
  }

  // DELETE请求
  delete(url, data = {}) {
    return this.request({
      url,
      method: 'DELETE',
      data
    })
  }

  // 文件上传
  upload(url, filePath, formData = {}) {
    return new Promise((resolve, reject) => {
      const token = this.getToken()
      
      uni.uploadFile({
        url: this.baseURL + url,
        filePath: filePath,
        name: 'image',
        formData: formData,
        header: {
          'Authorization': token ? `Bearer ${token}` : ''
        },
        success: (res) => {
          const data = JSON.parse(res.data)
          if (data.code === 200) {
            resolve(data)
          } else {
            reject(data)
          }
        },
        fail: (err) => {
          reject({
            code: -1,
            message: '上传失败'
          })
        }
      })
    })
  }
}

// 创建API实例
const api = new ApiService()

// 用户相关API
export const userApi = {
  // 注册
  register(data) {
    return api.post('auth/register.php', data)
  },
  
  // 登录
  login(data) {
    return api.post('auth/login.php', data)
  },
  
  // 获取用户信息
  getProfile() {
    return api.get('auth/profile.php')
  },
  
  // 更新用户信息
  updateProfile(data) {
    return api.put('auth/profile.php', data)
  }
}

// 模型相关API
export const modelApi = {
  // 生成模型
  generate(data) {
    return api.post('model/generate.php', data)
  },
  
  // 获取模型列表
  getList(params = {}) {
    return api.get('model/list.php', params)
  },
  
  // 获取模型详情
  getDetail(id) {
    return api.get(`model/detail.php?id=${id}`)
  },
  
  // 删除模型
  delete(id) {
    return api.delete(`model/detail.php?id=${id}`)
  },

  // 上传图片（供生成页使用）
  uploadImage(filePath) {
    return api.upload('upload/image.php', filePath)
  }
}

// 积分相关API
export const pointsApi = {
  // 获取积分余额
  getBalance(params = {}) {
    return api.get('points/balance.php', params)
  },
  
  // 购买积分
  purchase(data) {
    return api.post('points/purchase.php', data)
  }
}

// 上传相关API
export const uploadApi = {
  // 上传图片
  uploadImage(filePath) {
    return api.upload('upload/image.php', filePath)
  }
}

export default api