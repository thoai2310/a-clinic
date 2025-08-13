<template>
  <div class="error-page">
    <div class="error-content">
      <div class="error-code">404</div>
      <div class="error-title">Trang không tìm thấy</div>
      <div class="error-description">
        Xin lỗi, trang bạn đang tìm kiếm không tồn tại.
      </div>
      <a-button type="primary" size="large" @click="goHome">
        Về trang chủ
      </a-button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'

const router = useRouter()

const goHome = () => {
  router.push('/dashboard')
}
</script>

<style scoped>
.error-page {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background: #f0f2f5;
}

.error-content {
  text-align: center;
  padding: 48px;
}

.error-code {
  font-size: 120px;
  font-weight: 700;
  color: #1890ff;
  line-height: 1;
  margin-bottom: 24px;
}

.error-title {
  font-size: 32px;
  font-weight: 600;
  color: #1a1a1a;
  margin-bottom: 16px;
}

.error-description {
  font-size: 16px;
  color: #666;
  margin-bottom: 32px;
  max-width: 400px;
}
</style>