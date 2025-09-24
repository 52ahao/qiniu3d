
// #ifndef VUE3
import Vue from 'vue'
import App from './App'
import store from './store/index.js'

Vue.config.productionTip = false
// 允许使用自定义元素 model-viewer（H5 预览 3D 模型）
Vue.config.ignoredElements = ['model-viewer']

App.mpType = 'app'

// 挂载简易 store（非 Vuex 实例）
Vue.prototype.$store = store

const app = new Vue({
    ...App
})
app.$mount()
// #endif

// #ifdef VUE3
import { createSSRApp } from 'vue'
import App from './App.vue'
import store from './store/index.js'
export function createApp() {
  const app = createSSRApp(App)
  // 提供到全局（兼容 mapGetters 访问 this.$store）
  app.config.globalProperties.$store = store
  app.provide('$store', store)
  return {
    app
  }
}
// #endif