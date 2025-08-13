<template>
  <div class="users-page">
    <div class="card-container">
      <!-- Header -->
      <div class="page-header">
        <div>
          <h2>Quản lý người dùng</h2>
          <p>Quản lý thông tin người dùng trong hệ thống</p>
        </div>
        <a-button type="primary" @click="showCreateModal">
          <template #icon>
            <PlusOutlined />
          </template>
          Thêm người dùng
        </a-button>
      </div>

      <!-- Filters -->
      <div class="filters">
        <a-row :gutter="16">
          <a-col :span="6">
            <a-input-search
                v-model:value="searchText"
                placeholder="Tìm kiếm theo tên, email..."
                @search="handleSearch"
                allow-clear
            />
          </a-col>
          <a-col :span="4">
            <a-select
                v-model:value="roleFilter"
                placeholder="Lọc theo vai trò"
                style="width: 100%"
                allow-clear
                @change="handleFilter"
            >
              <a-select-option value="admin">Admin</a-select-option>
              <a-select-option value="user">User</a-select-option>
              <a-select-option value="editor">Editor</a-select-option>
            </a-select>
          </a-col>
          <a-col :span="4">
            <a-select
                v-model:value="statusFilter"
                placeholder="Trạng thái"
                style="width: 100%"
                allow-clear
                @change="handleFilter"
            >
              <a-select-option value="active">Hoạt động</a-select-option>
              <a-select-option value="inactive">Không hoạt động</a-select-option>
            </a-select>
          </a-col>
          <a-col :span="10">
            <div style="text-align: right;">
              <a-button @click="handleRefresh" style="margin-right: 8px;">
                <template #icon>
                  <ReloadOutlined />
                </template>
                Làm mới
              </a-button>
              <a-button
                  danger
                  :disabled="selectedRowKeys.length === 0"
                  @click="handleBatchDelete"
              >
                <template #icon>
                  <DeleteOutlined />
                </template>
                Xóa đã chọn
              </a-button>
            </div>
          </a-col>
        </a-row>
      </div>

      <!-- Table -->
      <a-table
          :columns="columns"
          :data-source="usersStore.users"
          :loading="usersStore.isLoading"
          :pagination="pagination"
          :row-selection="rowSelection"
          :scroll="{ x: 1000 }"
          @change="handleTableChange"
          row-key="id"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'avatar'">
            <a-avatar :src="record.avatar" :size="40">
              {{ record.name?.charAt(0)?.toUpperCase() }}
            </a-avatar>
          </template>

          <template v-else-if="column.key === 'name'">
            <div>
              <div style="font-weight: 500;">{{ record.name }}</div>
              <div style="font-size: 12px; color: #999;">{{ record.email }}</div>
            </div>
          </template>

          <template v-else-if="column.key === 'role'">
            <a-tag :color="getRoleColor(record.role)">
              {{ getRoleText(record.role) }}
            </a-tag>
          </template>

          <template v-else-if="column.key === 'status'">
            <a-tag :color="record.status === 'active' ? 'green' : 'red'">
              {{ record.status === 'active' ? 'Hoạt động' : 'Không hoạt động' }}
            </a-tag>
          </template>

          <template v-else-if="column.key === 'createdAt'">
            {{ formatDate(record.createdAt) }}
          </template>

          <template v-else-if="column.key === 'actions'">
            <a-space>
              <a-button type="link" size="small" @click="showEditModal(record)">
                <template #icon>
                  <EditOutlined />
                </template>
                Sửa
              </a-button>
              <a-popconfirm
                  title="Bạn có chắc chắn muốn xóa người dùng này?"
                  @confirm="handleDelete(record.id)"
              >
                <a-button type="link" danger size="small">
                  <template #icon>
                    <DeleteOutlined />
                  </template>
                  Xóa
                </a-button>
              </a-popconfirm>
            </a-space>
          </template>
        </template>
      </a-table>
    </div>

    <!-- Create/Edit Modal -->
    <a-modal
        v-model:open="modalVisible"
        :title="isEditing ? 'Chỉnh sửa người dùng' : 'Thêm người dùng mới'"
        :width="600"
        @ok="handleModalOk"
        @cancel="handleModalCancel"
        :confirm-loading="modalLoading"
    >
      <a-form
          ref="formRef"
          :model="formData"
          :rules="formRules"
          layout="vertical"
      >
        <a-row :gutter="16">
          <a-col :span="12">
            <a-form-item label="Tên" name="name">
              <a-input v-model:value="formData.name" placeholder="Nhập tên" />
            </a-form-item>
          </a-col>
          <a-col :span="12">
            <a-form-item label="Email" name="email">
              <a-input v-model:value="formData.email" placeholder="Nhập email" />
            </a-form-item>
          </a-col>
        </a-row>

        <a-row :gutter="16">
          <a-col :span="12">
            <a-form-item label="Vai trò" name="role">
              <a-select v-model:value="formData.role" placeholder="Chọn vai trò">
                <a-select-option value="admin">Admin</a-select-option>
                <a-select-option value="user">User</a-select-option>
                <a-select-option value="editor">Editor</a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
          <a-col :span="12">
            <a-form-item label="Trạng thái" name="status">
              <a-select v-model:value="formData.status" placeholder="Chọn trạng thái">
                <a-select-option value="active">Hoạt động</a-select-option>
                <a-select-option value="inactive">Không hoạt động</a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
        </a-row>

        <a-form-item v-if="!isEditing" label="Mật khẩu" name="password">
          <a-input-password v-model:value="formData.password" placeholder="Nhập mật khẩu" />
        </a-form-item>
      </a-form>
    </a-modal>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useUsersStore } from '@/stores/users.ts'
import type { User } from '@/types'
import {
  PlusOutlined,
  EditOutlined,
  DeleteOutlined,
  ReloadOutlined
} from '@ant-design/icons-vue'
import { message } from 'ant-design-vue'
import dayjs from 'dayjs'

const usersStore = useUsersStore()

// Table state
const searchText = ref('')
const roleFilter = ref<string>()
const statusFilter = ref<string>()
const selectedRowKeys = ref<number[]>([])
const currentPage = ref(1)
const pageSize = ref(10)

// Modal state
const modalVisible = ref(false)
const modalLoading = ref(false)
const isEditing = ref(false)
const formRef = ref()
const editingUserId = ref<number>()

// Form data
const formData = reactive({
  name: '',
  email: '',
  role: '',
  status: 'active',
  password: ''
})

// Table columns
const columns = [
  {
    title: 'Avatar',
    key: 'avatar',
    width: 80,
    align: 'center'
  },
  {
    title: 'Thông tin',
    key: 'name',
    sorter: true
  },
  {
    title: 'Vai trò',
    key: 'role',
    width: 120,
    filters: [
      { text: 'Admin', value: 'admin' },
      { text: 'User', value: 'user' },
      { text: 'Editor', value: 'editor' }
    ]
  },
  {
    title: 'Trạng thái',
    key: 'status',
    width: 120,
    filters: [
      { text: 'Hoạt động', value: 'active' },
      { text: 'Không hoạt động', value: 'inactive' }
    ]
  },
  {
    title: 'Ngày tạo',
    key: 'createdAt',
    width: 150,
    sorter: true
  },
  {
    title: 'Thao tác',
    key: 'actions',
    width: 150,
    fixed: 'right'
  }
]

// Form validation rules
const formRules = {
  name: [
    { required: true, message: 'Vui lòng nhập tên!' }
  ],
  email: [
    { required: true, message: 'Vui lòng nhập email!' },
    { type: 'email', message: 'Email không hợp lệ!' }
  ],
  role: [
    { required: true, message: 'Vui lòng chọn vai trò!' }
  ],
  password: [
    { required: true, message: 'Vui lòng nhập mật khẩu!' },
    { min: 6, message: 'Mật khẩu phải có ít nhất 6 ký tự!' }
  ]
}

// Computed
const pagination = computed(() => ({
  current: currentPage.value,
  pageSize: pageSize.value,
  total: usersStore.total,
  showSizeChanger: true,
  showQuickJumper: true,
  showTotal: (total: number, range: [number, number]) =>
      `${range[0]}-${range[1]} của ${total} mục`
}))

const rowSelection = computed(() => ({
  selectedRowKeys: selectedRowKeys.value,
  onChange: (keys: number[]) => {
    selectedRowKeys.value = keys
  }
}))

// Methods
const fetchUsers = () => {
  usersStore.fetchUsers({
    page: currentPage.value,
    pageSize: pageSize.value,
    search: searchText.value,
    role: roleFilter.value,
    status: statusFilter.value
  })
}

const handleSearch = () => {
  currentPage.value = 1
  fetchUsers()
}

const handleFilter = () => {
  currentPage.value = 1
  fetchUsers()
}

const handleRefresh = () => {
  fetchUsers()
}

const handleTableChange = (pagination: any, filters: any, sorter: any) => {
  currentPage.value = pagination.current
  pageSize.value = pagination.pageSize

  // Handle filters
  if (filters.role) {
    roleFilter.value = filters.role[0]
  }
  if (filters.status) {
    statusFilter.value = filters.status[0]
  }

  fetchUsers()
}

const showCreateModal = () => {
  isEditing.value = false
  modalVisible.value = true
  resetForm()
}

const showEditModal = (user: User) => {
  isEditing.value = true
  editingUserId.value = user.id
  modalVisible.value = true

  Object.assign(formData, {
    name: user.name,
    email: user.email,
    role: user.role,
    status: user.status || 'active',
    password: ''
  })
}

const handleModalOk = async () => {
  try {
    await formRef.value.validate()
    modalLoading.value = true

    if (isEditing.value && editingUserId.value) {
      await usersStore.updateUser(editingUserId.value, {
        name: formData.name,
        email: formData.email,
        role: formData.role,
        status: formData.status
      })
    } else {
      await usersStore.createUser(formData)
    }

    modalVisible.value = false
    resetForm()
    fetchUsers()
  } catch (error) {
    console.error('Modal error:', error)
  } finally {
    modalLoading.value = false
  }
}

const handleModalCancel = () => {
  modalVisible.value = false
  resetForm()
}

const resetForm = () => {
  Object.assign(formData, {
    name: '',
    email: '',
    role: '',
    status: 'active',
    password: ''
  })
  formRef.value?.resetFields()
}

const handleDelete = async (id: number) => {
  await usersStore.deleteUser(id)
  fetchUsers()
}

const handleBatchDelete = () => {
  if (selectedRowKeys.value && selectedRowKeys.value.length > 0) {
    usersStore.deleteUsers(selectedRowKeys.value)
    selectedRowKeys.value = []
    fetchUsers()
  }
}

const getRoleColor = (role: string) => {
  const colors = {
    admin: 'red',
    editor: 'orange',
    user: 'blue'
  }
  return colors[role as keyof typeof colors] || 'default'
}

const getRoleText = (role: string) => {
  const texts = {
    admin: 'Quản trị viên',
    editor: 'Biên tập viên',
    user: 'Người dùng'
  }
  return texts[role as keyof typeof texts] || role
}

const formatDate = (date: string) => {
  return dayjs(date).format('DD/MM/YYYY HH:mm')
}

// Lifecycle
onMounted(() => {
  fetchUsers()
})
</script>

<style scoped>
.users-page {
  background: #f0f2f5;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid #f0f0f0;
}

.page-header h2 {
  margin: 0 0 4px 0;
  font-size: 20px;
  font-weight: 600;
}

.page-header p {
  margin: 0;
  color: #666;
  font-size: 14px;
}

.filters {
  margin-bottom: 16px;
  padding: 16px;
  background: #fafafa;
  border-radius: 6px;
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    gap: 16px;
    align-items: stretch;
  }

  .filters .ant-row {
    flex-direction: column;
  }

  .filters .ant-col {
    width: 100% !important;
    margin-bottom: 8px;
  }
}
</style>