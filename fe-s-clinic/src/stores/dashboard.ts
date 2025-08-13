import { defineStore } from 'pinia'
import { dashboardApi } from '@/api/dashboard'
import type { DashboardStats } from '@/types'

interface DashboardState {
    stats: DashboardStats | null
    chartData: any
    isLoading: boolean
}

export const useDashboardStore = defineStore('dashboard', {
    state: (): DashboardState => ({
        stats: null,
        chartData: null,
        isLoading: false
    }),

    actions: {
        async fetchStats() {
            try {
                this.isLoading = true
                this.stats = await dashboardApi.getStats()
            } catch (error) {
                console.error('Failed to fetch dashboard stats:', error)
            } finally {
                this.isLoading = false
            }
        },

        async fetchChartData(type: string, period: string = '7d') {
            try {
                this.chartData = await dashboardApi.getChartData(type, period)
            } catch (error) {
                console.error('Failed to fetch chart data:', error)
            }
        }
    }
})