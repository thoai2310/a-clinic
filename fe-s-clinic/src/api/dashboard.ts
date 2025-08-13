import http from '@/utils/request'
import type { DashboardStats } from '@/types'

export const dashboardApi = {
    // Lấy thống kê dashboard
    getStats(): Promise<DashboardStats> {
        return http.get('/dashboard/stats')
    },

    // Lấy dữ liệu biểu đồ
    getChartData(type: string, period: string = '7d'): Promise<any> {
        return http.get(`/dashboard/chart/${type}`, {
            params: { period }
        })
    }
}