import axios from 'axios'
import type { AxiosInstance, AxiosRequestConfig, AxiosResponse } from 'axios'
import { message } from 'ant-design-vue'
import { useAuthStore } from '@/stores/auth'
import router from '@/router'

class HttpRequest {
    private instance: AxiosInstance

    constructor() {
        this.instance = axios.create({
            baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
            timeout: 10000,
            headers: {
                'Content-Type': 'application/json'
            }
        })

        this.setupInterceptors()
    }

    private setupInterceptors() {
        // Request interceptor
        this.instance.interceptors.request.use(
            (config) => {
                const authStore = useAuthStore()
                const token = authStore.token

                if (token) {
                    config.headers.Authorization = `Bearer ${token}`
                }

                return config
            },
            (error) => {
                return Promise.reject(error)
            }
        )

        // Response interceptor
        this.instance.interceptors.response.use(
            async (response: AxiosResponse) => {
                const responseData = response.data;

                // If API response a structure { success, message, data }
                if (responseData.success !== undefined) {
                    if (responseData.success) {
                        return responseData.data // Trả về data bên trong
                    } else {
                        message.error(responseData.message || 'Có lỗi xảy ra')
                        return Promise.reject(new Error(responseData.message || 'Request failed'))
                    }
                }

                // If API response a structure { code, message, data }
                if (responseData.code !== undefined) {
                    if (responseData.code === 200) {
                        return responseData.data
                    } else if (responseData.code === 401) {
                        const authStore = useAuthStore();
                        console.log('33333')
                        await authStore.logout()
                        await router.push('/login')
                        message.error('Token đã hết hạn, vui lòng đăng nhập lại')
                    } else {
                        message.error(responseData.message || 'Có lỗi xảy ra')
                    }
                    return Promise.reject(new Error(responseData.message || 'Request failed'))
                }

                return responseData
            },
            async (error) => {
                if (error.response?.status === 401) {
                    const authStore = useAuthStore();
                    console.log('4444444')
                    await authStore.logout()
                    localStorage.removeItem('token')
                    localStorage.removeItem('user')
                    await router.push('/login')
                    message.error('Token đã hết hạn, vui lòng đăng nhập lại')
                } else {
                    message.error(error.response?.data?.message || error.message || 'Network error')
                }

                return Promise.reject(error)
            }
        )
    }

    public get<T = any>(url: string, config?: AxiosRequestConfig): Promise<T> {
        return this.instance.get(url, config)
    }

    public post<T = any>(url: string, data?: any, config?: AxiosRequestConfig): Promise<T> {
        return this.instance.post(url, data, config)
    }

    public put<T = any>(url: string, data?: any, config?: AxiosRequestConfig): Promise<T> {
        return this.instance.put(url, data, config)
    }

    public delete<T = any>(url: string, config?: AxiosRequestConfig): Promise<T> {
        return this.instance.delete(url, config)
    }
}

export const http = new HttpRequest()
export default http