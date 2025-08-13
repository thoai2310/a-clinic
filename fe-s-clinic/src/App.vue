<template>
  <a-config-provider :locale="locale">
    <router-view />
  </a-config-provider>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import viVN from 'ant-design-vue/es/locale/vi_VN'

const locale = viVN
const authStore = useAuthStore()

onMounted(() => {
  // Khởi tạo auth state khi app load
  authStore.initAuth()
})
</script>

<style>
#app {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}
</style>