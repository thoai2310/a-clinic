<template>
  <a-layout class="main-layout">
    <!-- Sidebar -->
    <a-layout-sider
        v-model:collapsed="collapsed"
        :trigger="null"
        collapsible
        theme="light"
        width="256"
        class="sidebar"
    >
      <!-- Logo -->
      <div class="logo">
        <div class="logo-icon">
          <div class="logo-triangle"></div>
        </div>
        <span v-show="!collapsed" class="logo-text">S-Clinic Admin</span>
      </div>

      <!-- Menu -->
      <a-menu
          v-model:selectedKeys="selectedKeys"
          mode="inline"
          theme="light"
          :inline-collapsed="collapsed"
          class="sidebar-menu"
      >
        <a-menu-item key="dashboard" @click="$router.push('/dashboard')">
          <template #icon>
            <DashboardOutlined/>
          </template>
          <span>Analytics</span>
        </a-menu-item>

        <a-menu-item key="users" @click="$router.push('/users')">
          <template #icon>
            <UserOutlined/>
          </template>
          <span>Customer Management</span>
        </a-menu-item>
        <a-menu-item key="messages" @click="$router.push('/messages')">
          <template #icon>
            <MessageOutlined/>
          </template>
          <span>Message Management</span>
        </a-menu-item>
        <a-menu-item key="rules" @click="$router.push('/rules')">
          <template #icon>
            <GoldOutlined />
          </template>
          <span>Rule Management</span>
        </a-menu-item>
        <a-menu-item key="tags" @click="$router.push('/tags')">
          <template #icon>
            <TagOutlined/>
          </template>
          <span>Tag Management</span>
        </a-menu-item>

        <a-menu-item key="questions" @click="$router.push('/questions')">
          <template #icon>
            <QuestionCircleOutlined/>
          </template>
          <span>Form Management</span>
        </a-menu-item>

        <a-sub-menu key="workspace" v-if="false">
          <template #icon>
            <FolderOutlined/>
          </template>
          <template #title>Workspace</template>
          <a-menu-item key="workspace1">Workspace 1</a-menu-item>
          <a-menu-item key="workspace2">Workspace 2</a-menu-item>
        </a-sub-menu>

        <a-sub-menu key="demos" v-if="false">
          <template #icon>
            <AppstoreOutlined/>
          </template>
          <template #title>Demos</template>
          <a-menu-item key="demo1">Demo 1</a-menu-item>
          <a-menu-item key="demo2">Demo 2</a-menu-item>
        </a-sub-menu>

        <a-sub-menu key="examples" v-if="false">
          <template #icon>
            <CodeOutlined/>
          </template>
          <template #title>Examples</template>
          <a-menu-item key="example1">Example 1</a-menu-item>
          <a-menu-item key="example2">Example 2</a-menu-item>
        </a-sub-menu>

        <a-sub-menu key="system" v-if="false">
          <template #icon>
            <SettingOutlined/>
          </template>
          <template #title>System Management</template>
          <a-menu-item key="system1">System 1</a-menu-item>
          <a-menu-item key="system2">System 2</a-menu-item>
        </a-sub-menu>

        <a-menu-item key="about" v-if="false">
          <template #icon>
            <InfoCircleOutlined/>
          </template>
          <span>About</span>
        </a-menu-item>
      </a-menu>
    </a-layout-sider>

    <!-- Main Content -->
    <a-layout>
      <!-- Header -->
      <a-layout-header class="header">
        <div class="header-left">
          <a-button
              type="text"
              @click="collapsed = !collapsed"
              class="trigger"
          >
            <template #icon>
              <MenuUnfoldOutlined v-if="collapsed"/>
              <MenuFoldOutlined v-else/>
            </template>
          </a-button>

          <!-- Breadcrumb -->
          <!--          <a-breadcrumb class="breadcrumb">-->
          <!--            <a-breadcrumb-item>-->
          <!--              <DashboardOutlined />-->
          <!--              <span>Dashboard</span>-->
          <!--            </a-breadcrumb-item>-->
          <!--            <a-breadcrumb-item>Analytics</a-breadcrumb-item>-->
          <!--          </a-breadcrumb>-->
        </div>

        <div class="header-right">
          <!-- Search -->
          <a-input-search
              placeholder="Search..."
              style="width: 300px; margin-right: 16px"
              @search="onSearch"
          />

          <!-- Icons -->
          <a-space size="middle">
            <a-badge :count="5" size="small">
              <a-button type="text" shape="circle" size="large">
                <template #icon>
                  <BellOutlined/>
                </template>
              </a-button>
            </a-badge>

            <a-button type="text" shape="circle" size="large">
              <template #icon>
                <QuestionCircleOutlined/>
              </template>
            </a-button>

            <a-button type="text" shape="circle" size="large">
              <template #icon>
                <SettingOutlined/>
              </template>
            </a-button>

            <a-button type="text" shape="circle" size="large">
              <template #icon>
                <ExpandOutlined/>
              </template>
            </a-button>
          </a-space>

          <!-- User Dropdown -->
          <a-dropdown placement="bottomRight">
            <a-button type="text" class="user-info">
              <a-avatar
                  size="small"
                  src="https://avatars.githubusercontent.com/u/1?v=4"
                  style="margin-right: 8px"
              />
              <span>{{ authStore.user?.name || 'User' }}</span>
              <DownOutlined style="margin-left: 8px"/>
            </a-button>
            <template #overlay>
              <a-menu>
                <a-menu-item key="profile">
                  <UserOutlined/>
                  <span>Profile</span>
                </a-menu-item>
                <a-menu-item key="settings">
                  <SettingOutlined/>
                  <span>Settings</span>
                </a-menu-item>
                <a-menu-divider/>
                <a-menu-item key="logout" @click="handleLogout">
                  <LogoutOutlined/>
                  <span>Logout</span>
                </a-menu-item>
              </a-menu>
            </template>
          </a-dropdown>
        </div>
      </a-layout-header>

      <!-- Content -->
      <a-layout-content class="content">
        <router-view/>
      </a-layout-content>
    </a-layout>
  </a-layout>
</template>

<script setup lang="ts">
import {ref, computed, watch} from 'vue'
import {useRoute} from 'vue-router'
import {useAuthStore} from '@/stores/auth'
import {
  DashboardOutlined,
  UserOutlined,
  FolderOutlined,
  AppstoreOutlined,
  CodeOutlined,
  SettingOutlined,
  InfoCircleOutlined,
  MenuUnfoldOutlined,
  MenuFoldOutlined,
  BellOutlined,
  QuestionCircleOutlined,
  ExpandOutlined,
  DownOutlined,
  LogoutOutlined,
  TagOutlined,
  MessageOutlined,
  GoldOutlined
} from '@ant-design/icons-vue'

const authStore = useAuthStore()
const route = useRoute()

const collapsed = ref(false)
const selectedKeys = ref<string[]>([])

// Watch route changes to update selected menu
watch(
    () => route.path,
    (newPath) => {
      if (newPath.includes('/dashboard')) {
        selectedKeys.value = ['dashboard']
      } else if (newPath.includes('/users')) {
        selectedKeys.value = ['users']
      } else if (newPath.includes('/questions')) {
        selectedKeys.value = ['questions']
      } else if (newPath.includes('/tags')) {
        selectedKeys.value = ['tags']
      } else if (newPath.includes('/messages')) {
        selectedKeys.value = ['messages']
      } else if (newPath.includes('/rules')) {
        selectedKeys.value = ['rules']
      }
    },
    {immediate: true}
)

const onSearch = (value: string) => {
  console.log('Search:', value)
}

const handleLogout = () => {
  authStore.logout()
}
</script>

<style scoped>
.main-layout {
  min-height: 100vh;
}

.sidebar {
  box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
  z-index: 100;
  position: relative;
}

.logo {
  height: 64px;
  display: flex;
  align-items: center;
  padding: 0 16px;
  border-bottom: 1px solid #f0f0f0;
}

.logo-icon {
  width: 32px;
  height: 32px;
  margin-right: 12px;
  position: relative;
}

.logo-triangle {
  width: 0;
  height: 0;
  border-left: 16px solid #1890ff;
  border-right: 16px solid #722ed1;
  border-bottom: 16px solid #52c41a;
  border-top: 16px solid transparent;
  transform: rotate(45deg);
}

.logo-text {
  font-size: 18px;
  font-weight: 600;
  color: #1a1a1a;
}

.sidebar-menu {
  border-right: none;
  padding: 8px 0;
}

.sidebar-menu .ant-menu-item {
  margin: 4px 8px;
  border-radius: 6px;
  height: 40px;
  line-height: 40px;
}

.sidebar-menu .ant-menu-submenu {
  margin: 4px 8px;
}

.sidebar-menu .ant-menu-submenu .ant-menu-submenu-title {
  border-radius: 6px;
  height: 40px;
  line-height: 40px;
}

.header {
  background: #fff;
  padding: 0 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
  z-index: 99;
}

.header-left {
  display: flex;
  align-items: center;
}

.trigger {
  font-size: 18px;
  margin-right: 24px;
}

.breadcrumb {
  margin: 0;
}

.breadcrumb .ant-breadcrumb-link {
  display: flex;
  align-items: center;
  gap: 4px;
}

.header-right {
  display: flex;
  align-items: center;
}

.user-info {
  display: flex;
  align-items: center;
  padding: 0 12px;
  height: 40px;
  border-radius: 6px;
  margin-left: 16px;
}

.user-info:hover {
  background: rgba(0, 0, 0, 0.04);
}

.content {
  margin: 12px;
  padding: 12px;
  background: #f0f2f5;
  border-radius: 8px;
  min-height: calc(100vh - 112px);
  overflow: auto;
}

/* Responsive */
@media (max-width: 768px) {
  .header {
    padding: 0 16px;
  }

  .header-right .ant-input-search {
    display: none;
  }

  .content {
    margin: 16px;
    padding: 16px;
  }
}
</style>