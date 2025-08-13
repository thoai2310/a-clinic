import { createRouter, createWebHistory } from 'vue-router'
import { routes } from './routes'
import { authMiddleware } from '@/middleware/auth'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  }
})

// Global navigation guard
router.beforeEach(async (to, from, next) => {
  // Khởi tạo auth store nếu chưa có
  const authStore = useAuthStore()
  if (!authStore.user && authStore.token) {
    await authStore.getCurrentUser()
  }

  // Áp dụng auth middleware
  authMiddleware(to, from, next)
})

export default router