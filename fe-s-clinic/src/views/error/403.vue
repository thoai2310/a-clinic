<template>
  <div class="error-page">
    <div class="error-content">
      <div class="error-code">403</div>
      <div class="error-title">Không có quyền truy cập</div>
      <div class="error-description">
        Xin lỗi, bạn không có quyền truy cập vào trang này.
      </div>
      <a-button type="primary" size="large" @click="goBack">
        Quay lại
      </a-button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'

const router = useRouter()

const goBack = () => {
  router.go(-1)
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
  color: #f5222d;
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