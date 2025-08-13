<template>
  <div class="dashboard-container">
    <!-- Stats Cards -->
    <div class="dashboard-stats">
      <div class="stat-card">
        <div class="stat-header">
          <div>
            <div class="stat-title">Users</div>
            <div class="stat-value">{{ formatNumber(stats?.users?.total || 2000) }}</div>
            <div class="stat-desc">Total {{ formatNumber(stats?.users?.active || 120000) }}</div>
          </div>
          <div class="stat-icon users">
            <UserOutlined />
          </div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-header">
          <div>
            <div class="stat-title">Visits</div>
            <div class="stat-value">{{ formatNumber(stats?.visits.total || 20000) }}</div>
            <div class="stat-desc">Today {{ formatNumber(stats?.visits.today || 500000) }}</div>
          </div>
          <div class="stat-icon visits">
            <EyeOutlined />
          </div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-header">
          <div>
            <div class="stat-title">Clicks</div>
            <div class="stat-value">{{ formatNumber(stats?.downloads.total || 8000) }}</div>
            <div class="stat-desc">Today {{ formatNumber(stats?.downloads.today || 120000) }}</div>
          </div>
          <div class="stat-icon downloads">
            <DownloadOutlined />
          </div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-header">
          <div>
            <div class="stat-title">Usage</div>
            <div class="stat-value">{{ formatNumber(stats?.usage.total || 5000) }}</div>
            <div class="stat-desc">Today {{ formatNumber(stats?.usage.today || 50000) }}</div>
          </div>
          <div class="stat-icon usage">
            <ThunderboltOutlined />
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="charts-row">
      <!-- Traffic Trends Chart -->
      <div class="chart-container" style="flex: 2;">
        <div class="chart-header">
          <div class="chart-title">Biểu đồ 1</div>
          <a-radio-group v-model:value="chartPeriod" @change="updateChartData">
            <a-radio-button value="7d">Tháng</a-radio-button>
          </a-radio-group>
        </div>
        <div ref="trafficChartRef" style="height: 300px;"></div>
      </div>

      <!-- Pie Charts -->
      <div style="flex: 1; display: flex; flex-direction: column; gap: 24px;">
        <!-- Visit Count -->
        <div class="chart-container">
          <div class="chart-header">
            <div class="chart-title">访问数量</div>
          </div>
          <div ref="visitCountChartRef" style="height: 140px;"></div>
        </div>

        <!-- Visit Source -->
        <div class="chart-container">
          <div class="chart-header">
            <div class="chart-title">Nguồn</div>
          </div>
          <div ref="visitSourceChartRef" style="height: 140px;"></div>
        </div>
      </div>
    </div>

    <!-- Additional Charts Row -->
    <div class="charts-row">
      <!-- Visit Source (Large) -->
      <div class="chart-container" style="flex: 1;">
        <div class="chart-header">
          <div class="chart-title">Nguồn</div>
        </div>
        <div ref="visitSourceLargeRef" style="height: 200px;"></div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue'
import { UserOutlined, EyeOutlined, DownloadOutlined, ThunderboltOutlined } from '@ant-design/icons-vue'
import { useDashboardStore } from '@/stores/dashboard'
import * as echarts from 'echarts'

const dashboardStore = useDashboardStore()
const stats = computed(() => dashboardStore.stats);

watch(stats, (newStats: any) => {
  console.log('Stats updated:', newStats)
}, { immediate: true })

const chartPeriod = ref('7d')
const trafficChartRef = ref<HTMLElement>()
const visitCountChartRef = ref<HTMLElement>()
const visitSourceChartRef = ref<HTMLElement>()
const visitSourceLargeRef = ref<HTMLElement>()

let trafficChart: echarts.ECharts | null = null
let visitCountChart: echarts.ECharts | null = null
let visitSourceChart: echarts.ECharts | null = null
let visitSourceLargeChart: echarts.ECharts | null = null

const formatNumber = (num: number) => {
  return num.toLocaleString()
}

// Generate mock chart data
const generateTrafficData = () => {
  const hours = Array.from({ length: 24 }, (_, i) => `${i}:00`)
  const data1 = Array.from({ length: 24 }, () => Math.floor(Math.random() * 60000) + 10000)
  const data2 = Array.from({ length: 24 }, () => Math.floor(Math.random() * 20000) + 5000)

  return { hours, data1, data2 }
}

const initTrafficChart = () => {
  if (!trafficChartRef.value) return

  trafficChart = echarts.init(trafficChartRef.value)
  const { hours, data1, data2 } = generateTrafficData()

  const option = {
    tooltip: {
      trigger: 'axis',
      axisPointer: {
        type: 'cross'
      }
    },
    legend: {
      data: ['Trend', 'Monthly'],
      right: 0
    },
    grid: {
      left: '3%',
      right: '4%',
      bottom: '3%',
      top: '15%',
      containLabel: true
    },
    xAxis: {
      type: 'category',
      boundaryGap: false,
      data: hours,
      axisLine: {
        show: false
      },
      axisTick: {
        show: false
      }
    },
    yAxis: {
      type: 'value',
      axisLine: {
        show: false
      },
      axisTick: {
        show: false
      },
      splitLine: {
        lineStyle: {
          color: '#f0f0f0'
        }
      }
    },
    series: [
      {
        name: '流量趋势',
        type: 'line',
        data: data1,
        smooth: true,
        areaStyle: {
          color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
            { offset: 0, color: 'rgba(24, 144, 255, 0.6)' },
            { offset: 1, color: 'rgba(24, 144, 255, 0.1)' }
          ])
        },
        lineStyle: {
          color: '#1890ff',
          width: 3
        },
        itemStyle: {
          color: '#1890ff'
        }
      },
      {
        name: 'Number of access',
        type: 'line',
        data: data2,
        smooth: true,
        areaStyle: {
          color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
            { offset: 0, color: 'rgba(82, 196, 26, 0.6)' },
            { offset: 1, color: 'rgba(82, 196, 26, 0.1)' }
          ])
        },
        lineStyle: {
          color: '#52c41a',
          width: 3
        },
        itemStyle: {
          color: '#52c41a'
        }
      }
    ]
  }

  trafficChart.setOption(option)
}

const initVisitCountChart = () => {
  if (!visitCountChartRef.value) return

  visitCountChart = echarts.init(visitCountChartRef.value)

  const option = {
    tooltip: {
      trigger: 'item'
    },
    series: [
      {
        type: 'pie',
        radius: ['40%', '70%'],
        center: ['50%', '50%'],
        data: [
          { value: 4000, name: '网站', itemStyle: { color: '#1890ff' } },
          { value: 198, name: '其他', itemStyle: { color: '#52c41a' } }
        ],
        label: {
          show: false
        },
        labelLine: {
          show: false
        }
      }
    ]
  }

  visitCountChart.setOption(option)
}

const initVisitSourceChart = () => {
  if (!visitSourceChartRef.value) return

  visitSourceChart = echarts.init(visitSourceChartRef.value)

  const option = {
    tooltip: {
      trigger: 'item'
    },
    series: [
      {
        type: 'pie',
        radius: ['40%', '70%'],
        center: ['50%', '50%'],
        data: [
          { value: 60, name: '直接访问', itemStyle: { color: '#1890ff' } },
          { value: 25, name: '搜索引擎', itemStyle: { color: '#52c41a' } },
          { value: 15, name: '社交媒体', itemStyle: { color: '#faad14' } }
        ],
        label: {
          show: false
        },
        labelLine: {
          show: false
        }
      }
    ]
  }

  visitSourceChart.setOption(option)
}

const initVisitSourceLargeChart = () => {
  if (!visitSourceLargeRef.value) return

  visitSourceLargeChart = echarts.init(visitSourceLargeRef.value)

  const option = {
    tooltip: {
      trigger: 'item',
      formatter: '{a} <br/>{b}: {c} ({d}%)'
    },
    legend: {
      orient: 'horizontal',
      bottom: 10,
      data: ['技术支持', '外部']
    },
    series: [
      {
        name: 'Nguồn',
        type: 'pie',
        radius: ['30%', '60%'],
        center: ['50%', '45%'],
        data: [
          { value: 75, name: 'Zalo', itemStyle: { color: '#52c41a' } },
          { value: 25, name: 'Telegram', itemStyle: { color: '#1890ff' } }
        ],
        emphasis: {
          itemStyle: {
            shadowBlur: 10,
            shadowOffsetX: 0,
            shadowColor: 'rgba(0, 0, 0, 0.5)'
          }
        }
      }
    ]
  }

  visitSourceLargeChart.setOption(option)
}

const updateChartData = () => {
  // Refresh chart data when period changes
  initTrafficChart()
}

const resizeCharts = () => {
  trafficChart?.resize()
  visitCountChart?.resize()
  visitSourceChart?.resize()
  visitSourceLargeChart?.resize()
}

onMounted(async () => {
  await dashboardStore.fetchStats();

  await nextTick()

  initTrafficChart()
  initVisitCountChart()
  initVisitSourceChart()
  initVisitSourceLargeChart()

  // Handle window resize
  window.addEventListener('resize', resizeCharts)
})

onUnmounted(() => {
  window.removeEventListener('resize', resizeCharts)
  trafficChart?.dispose()
  visitCountChart?.dispose()
  visitSourceChart?.dispose()
  visitSourceLargeChart?.dispose()
})
</script>

<style scoped>
.dashboard-container {
  padding: 0;
}

.charts-row {
  display: flex;
  gap: 24px;
  margin-bottom: 24px;
}

@media (max-width: 1200px) {
  .charts-row {
    flex-direction: column;
  }
}
</style>