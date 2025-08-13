import type { NavigationGuardNext, RouteLocationNormalized } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

export function authMiddleware(
    to: RouteLocationNormalized,
    from: RouteLocationNormalized,
    next: NavigationGuardNext
) {
    const authStore = useAuthStore()

    // Nếu route yêu cầu authentication
    if (to.meta.requiresAuth) {
        if (!authStore.isAuthenticated) {
            next({
                path: '/login',
                query: { redirect: to.fullPath }
            })
            return
        }

        // Kiểm tra quyền truy cập nếu có
        console.log('roles', authStore, authStore.userRole);
        // if (to.meta.roles && Array.isArray(to.meta.roles) && to.meta.roles.length > 0) {
        //     const userRole = authStore.userRole
        //     if (!to.meta.roles.includes(userRole)) {
        //         // Không có quyền truy cập
        //         next({ path: '/403' });
        //         return
        //     }
        // }
    }

    // Nếu đã đăng nhập và truy cập trang login, chuyển về dashboard
    if (to.path === '/login' && authStore.isAuthenticated) {
        next('/dashboard')
        return
    }

    next()
}