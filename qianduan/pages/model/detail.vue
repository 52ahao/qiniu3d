<template>
  <view class="container">
    <view class="card" v-if="loading">
      <text>加载中...</text>
    </view>

    <view class="card" v-else-if="!model">
      <text>未找到模型</text>
    </view>

    <view class="viewer-page" v-else>
      <view class="viewer-wrap">
        <view v-if="!model.model_file" class="card placeholder">
          <image v-if="model.original_image" :src="fullUrl(model.original_image)" class="cover" mode="aspectFit"></image>
          <text class="meta">暂无 .glb 文件可预览</text>
        </view>
        <view v-else class="viewer-card">
          <model-viewer
            ref="mv"
            :src="fullUrl(model.model_file)"
            :poster="model.original_image ? fullUrl(model.original_image) : ''"
            crossorigin="anonymous"
            camera-controls
            autoplay
            exposure="1"
            shadow-intensity="0.8"
            environment-image="neutral"
            ar
            @load="onViewerLoad"
            class="model-viewer">
          </model-viewer>

          <!-- 底部材质/变体切换条 -->
          <view class="variants-bar" v-if="variants.length || true">
            <scroll-view class="variants-scroll" scroll-x>
              <view v-for="item in variants" :key="item" class="variant-chip" :class="{active: selectedVariant===item}" @click="switchVariant(item)">
                <text>{{ item }}</text>
              </view>
              <view v-if="!variants.length" class="mode-buttons">
                <view class="variant-chip" :class="{active: viewMode==='white'}" @click="setViewMode('white')"><text>白模</text></view>
                <view class="variant-chip" :class="{active: viewMode==='albedo'}" @click="setViewMode('albedo')"><text>反照率</text></view>
                <view class="variant-chip" :class="{active: viewMode==='normal'}" @click="setViewMode('normal')"><text>法线</text></view>
              </view>
            </scroll-view>
          </view>
        </view>
      </view>

      <!-- 右侧信息栏 -->
      <view class="sidebar">
        <view class="card info">
          <text class="name">{{ model.name }}</text>
          <text class="desc">{{ model.description || '暂无描述' }}</text>
          <text class="meta">类型：{{ typeText(model.model_type) }} | 积分：{{ model.points_cost }}</text>
          <text class="meta">状态：{{ statusText(model.status) }}</text>
          <view v-if="model.model_file" class="actions">
            <button class="btn-primary" @click="downloadModel">下载</button>
          </view>
        </view>
        <view class="card reference" v-if="model.original_image">
          <text class="side-title">参考图</text>
          <image :src="fullUrl(model.original_image)" class="ref-image" mode="aspectFit"></image>
        </view>
      </view>
    </view>
  </view>
</template>

<script>
import { modelApi } from '@/utils/api.js'

export default {
  data() {
    return {
      id: null,
      model: null,
      loading: true,
      variants: [],
      selectedVariant: '',
      viewMode: 'albedo',
      _materialSnapshot: null
    }
  },
  onLoad(query) {
    this.id = query && query.id
    this.fetchDetail()
  },
  methods: {
    async fetchDetail() {
      this.loading = true
      try {
        const res = await modelApi.getDetail(this.id)
        this.model = res.data
      } catch (e) {
        this.model = null
        uni.showToast({ title: e.message || '加载失败', icon: 'none' })
      } finally {
        this.loading = false
      }
    },
    statusText(status) {
      const map = { pending: '等待中', processing: '生成中', completed: '已完成', failed: '生成失败' }
      return map[status] || status
    },
    typeText(t) {
      const map = { character: '角色', object: '物体', scene: '场景' }
      return map[t] || t
    },
    fullUrl(path) {
      if (!path) return ''
      if (/^https?:\/\//.test(path)) return path
      // 兼容后端返回的相对路径
      const base = `${getApp().globalData?.BASE_URL || 'http://localhost:3344/api/'}`.replace(/\/$/, '/')
      // 通过后端静态文件代理输出，附带CORS与正确MIME
      if (/^(uploads\/.+|public\/.+)/.test(path)) {
        return base + 'upload/file.php?path=' + encodeURIComponent(path)
      }
      return base + path
    },
    onViewerLoad() {
      try {
        const mv = this.$refs.mv
        if (!mv) return
        // 读取 glTF 变体列表（如果模型包含 KHR_materials_variants）
        const list = (mv.availableVariants || []).slice()
        this.variants = list
        if (this.variants.length) {
          this.selectedVariant = this.variants[0]
          mv.variantName = this.selectedVariant
        } else {
          // 无变体时，默认进入反照率模式
          this.snapshotMaterials()
          this.setViewMode('albedo')
        }
      } catch (e) {
        // 忽略
      }
    },
    switchVariant(name) {
      const mv = this.$refs.mv
      if (!mv) return
      this.selectedVariant = name
      try { mv.variantName = name } catch (e) {}
    },
    snapshotMaterials() {
      const mv = this.$refs.mv
      if (!mv) return
      const materials = mv.model && mv.model.materials ? mv.model.materials : []
      this._materialSnapshot = materials.map(m => ({
        m,
        baseColorFactor: m?.pbrMetallicRoughness?.baseColorFactor?.slice?.() || [1,1,1,1],
        baseColorTexture: m?.pbrMetallicRoughness?.baseColorTexture?.texture || null,
        metallicFactor: m?.pbrMetallicRoughness?.metallicFactor ?? 0,
        roughnessFactor: m?.pbrMetallicRoughness?.roughnessFactor ?? 1,
        normalTexture: m?.normalTexture?.texture || null
      }))
    },
    restoreMaterials() {
      if (!this._materialSnapshot) return
      this._materialSnapshot.forEach(s => {
        try {
          const pbr = s.m.pbrMetallicRoughness
          if (!pbr) return
          if (pbr.setBaseColorFactor) pbr.setBaseColorFactor(s.baseColorFactor)
          if (pbr.setBaseColorTexture) pbr.setBaseColorTexture(s.baseColorTexture)
          if (pbr.setMetallicFactor) pbr.setMetallicFactor(s.metallicFactor)
          if (pbr.setRoughnessFactor) pbr.setRoughnessFactor(s.roughnessFactor)
          if (s.m.normalTexture && s.m.normalTexture.setTexture) {
            s.m.normalTexture.setTexture(s.normalTexture)
          } else if (s.m.setNormalTexture) {
            s.m.setNormalTexture(s.normalTexture)
          }
        } catch (e) {}
      })
    },
    async setViewMode(mode) {
      this.viewMode = mode
      const mv = this.$refs.mv
      if (!mv || this.variants.length) return
      await mv.updateComplete
      if (!this._materialSnapshot) this.snapshotMaterials()
      // 先恢复，再施加模式
      this.restoreMaterials()
      const materials = mv.model && mv.model.materials ? mv.model.materials : []
      if (mode === 'white') {
        materials.forEach(m => {
          const pbr = m.pbrMetallicRoughness
          if (!pbr) return
          if (pbr.setBaseColorFactor) pbr.setBaseColorFactor([1,1,1,1])
          if (pbr.setBaseColorTexture) pbr.setBaseColorTexture(null)
          if (pbr.setMetallicFactor) pbr.setMetallicFactor(0)
          if (pbr.setRoughnessFactor) pbr.setRoughnessFactor(0.8)
          if (m.normalTexture && m.normalTexture.setTexture) m.normalTexture.setTexture(null)
        })
      } else if (mode === 'albedo') {
        materials.forEach(m => {
          const pbr = m.pbrMetallicRoughness
          if (!pbr) return
          // 保留底色纹理，只看颜色
          if (pbr.setBaseColorFactor) pbr.setBaseColorFactor([1,1,1,1])
          if (pbr.setMetallicFactor) pbr.setMetallicFactor(0)
          if (pbr.setRoughnessFactor) pbr.setRoughnessFactor(1)
          if (m.normalTexture && m.normalTexture.setTexture) m.normalTexture.setTexture(null)
        })
      } else if (mode === 'normal') {
        materials.forEach(m => {
          const pbr = m.pbrMetallicRoughness
          if (!pbr) return
          // 移除底色，仅通过法线产生阴影效果
          if (pbr.setBaseColorTexture) pbr.setBaseColorTexture(null)
          if (pbr.setBaseColorFactor) pbr.setBaseColorFactor([1,1,1,1])
          if (pbr.setMetallicFactor) pbr.setMetallicFactor(0)
          if (pbr.setRoughnessFactor) pbr.setRoughnessFactor(1)
          // 保留原 normalTexture（快照里已恢复）
        })
      }
    },
    downloadModel() {
      if (!this.model || !this.model.model_file) return
      const url = this.fullUrl(this.model.model_file)
      // H5 直接跳转下载
      if (typeof window !== 'undefined') {
        window.open(url, '_blank')
      } else {
        uni.showToast({ title: '请在H5端下载', icon: 'none' })
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.container { padding: 16rpx; }

// 页面两栏布局
.viewer-page { display: flex; gap: 16rpx; }
.viewer-wrap { flex: 1; min-height: 600rpx; }
.sidebar { width: 380rpx; display: flex; flex-direction: column; gap: 16rpx; }

.card { background: #0f1115; border-radius: 12rpx; padding: 20rpx; border: 1rpx solid rgba(255,255,255,0.06); color: #e6e8eb; }
.placeholder { display: flex; flex-direction: column; align-items: center; }
.cover { width: 100%; height: 360rpx; border-radius: 8rpx; margin-bottom: 12rpx; }

.viewer-card { position: relative; background: #0b0d12; border-radius: 12rpx; overflow: hidden; border: 1rpx solid rgba(255,255,255,0.06); }
.model-viewer { width: 100%; height: 720rpx; background: linear-gradient(#0b0d12, #141821); }

.variants-bar { position: absolute; left: 0; right: 0; bottom: 0; padding: 12rpx; background: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(10,12,16,0.9) 40%, rgba(10,12,16,0.95) 100%); }
.variants-scroll { white-space: nowrap; display: flex; gap: 12rpx; }
.variant-chip { display: inline-flex; padding: 10rpx 16rpx; border-radius: 999rpx; background: rgba(255,255,255,0.06); color: #e6e8eb; border: 1rpx solid transparent; }
.variant-chip.active { border-color: #8ab4ff; background: rgba(138,180,255,0.15); }
.mode-buttons { display: inline-flex; gap: 12rpx; align-items: center; padding: 0 8rpx; }

.info { display: flex; flex-direction: column; gap: 12rpx; }
.name { font-size: 34rpx; font-weight: 600; }
.desc { color: #9aa4b2; font-size: 26rpx; }
.meta { color: #9aa4b2; font-size: 24rpx; }
.actions { margin-top: 12rpx; }
.btn-primary { background-color: #2b74ff; color: #fff; border: none; border-radius: 10rpx; padding: 14rpx 20rpx; }

.reference { display: flex; flex-direction: column; gap: 12rpx; }
.side-title { font-size: 26rpx; color: #9aa4b2; }
.ref-image { width: 100%; height: 280rpx; background: #0b0d12; border-radius: 10rpx; }
</style>

