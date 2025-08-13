import { defineStore } from 'pinia'
import {authApi, type LoginResponse} from '@/api/auth'
import type { User, LoginForm } from '@/types'
import { message } from 'ant-design-vue'
import router from '@/router'

interface AuthState {
    user: User | null
    token: string | null
    isLoading: boolean
}

export const useAuthStore = defineStore('auth', {
    state: (): AuthState => ({
        user: null,
        token: localStorage.getItem('token'),
        isLoading: false
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
        userRole: (state) => state.user?.role || ''
    },

    actions: {
        async login(loginForm: LoginForm) {
            try {
                this.isLoading = true
                const loginData: LoginResponse = await authApi.login(loginForm)

                this.token = loginData.token
                this.user = loginData.user

                localStorage.setItem('token', loginData.token)

                message.success('Đăng nhập thành công!');
                await router.push('/dashboard');

                return { success: true }
            } catch (error: any) {
                console.error('Login error:', error)
                message.error(error.message || 'Đăng nhập thất bại')
                return { success: false, error: error.message }
            } finally {
                this.isLoading = false
            }
        },

        async logout() {
            try {
                console.log('111111')
                await authApi.logout()
            } catch (error) {
                console.error('Logout error:', error)
            } finally {
                this.user = null
                this.token = null
                localStorage.removeItem('token')
                await router.push('/login')
            }
        },

        async getCurrentUser() {
            if (!this.token) return null

            try {
                const user = await authApi.getCurrentUser()
                this.user = user
                return user
            } catch (error) {
                console.log('222222')
                await this.logout()
                return null
            }
        },

        // init auth state from localStorage
        async initAuth() {
            if (this.token) {
              await  this.getCurrentUser()
            }
        }
    }
})