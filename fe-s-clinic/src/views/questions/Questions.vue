<template>
  <div class="users-page">
    <div class="card-container">
      <!-- Header -->
      <div class="page-header">
        <div>
          <h2>Quản lý Form</h2>
          <p>Danh sách các form đã tạo</p>
        </div>
        <a-button type="primary" @click="createQuestionForm">
          <template #icon>
            <PlusOutlined/>
          </template>
          Tạo mới Form
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
                  <ReloadOutlined/>
                </template>
                Làm mới
              </a-button>
              <a-button
                  danger
                  :disabled="selectedRowKeys.length === 0"
                  @click="handleBatchDelete"
              >
                <template #icon>
                  <DeleteOutlined/>
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
          :data-source="formsStore.forms"
          :loading="formsStore.isLoading"
          :pagination="pagination"
          :row-selection="rowSelection"
          :scroll="{ x: 1000 }"
          @change="handleTableChange"
          row-key="id"
      >
        <template #bodyCell="{ column, record }">


          <template v-if="column.key === 'title'">
            <div>
              <div style="font-weight: 500;">{{ record.title }}</div>
            </div>
          </template>

          <template v-else-if="column.key === 'role'">
            <a-tag :color="getRoleColor(record.role)">
              {{ getRoleText(record.role) }}
            </a-tag>
          </template>


          <template v-else-if="column.key === 'actions'">
            <a-space>
              <a-button type="link" size="small" @click="editQuestionForm(record)">
                <template #icon>
                  <EditOutlined/>
                </template>
                Sửa
              </a-button>
              <a-button size="small" type="link" @click="openAssignModal(record)">
                <template #icon>
                  <TagOutlined/>
                </template>
                Gửi
              </a-button>
              <a-popconfirm
                  title="Bạn có chắc chắn muốn xóa người dùng này?"
                  @confirm="handleDelete(record.id)"
              >
                <a-button type="link" danger size="small">
                  <template #icon>
                    <DeleteOutlined/>
                  </template>
                  Xóa
                </a-button>
              </a-popconfirm>


            </a-space>
          </template>
        </template>
      </a-table>
    </div>
    <a-modal
        v-model:open="assignModalVisible"
        :width="'50%'"
        title="Gửi form đến cho Khách Hàng"
        @ok="handleAssignFormToCustomer"
    >
      <label style="display: block; margin-bottom: 12px;">
        {{ formsStore.currentQuestionForm?.title }}
      </label>

      <a-checkbox-group v-model:value="formsStore.currentQuestionForm.assignCustomerIds">
        <a-row :gutter="[16, 16]">
          <a-col
              v-for="item in customersStore.all"
              :key="item.id"
              :span="6"
          >
            <a-checkbox :value="item.id">
              <div>
                <div style="font-weight: 500;">{{ item.name }}</div>
                <div style="color: #888; font-size: 12px;">{{ item.phone }}</div>
              </div>
            </a-checkbox>
          </a-col>
        </a-row>
      </a-checkbox-group>
    </a-modal>


  </div>
</template>

<script setup lang="ts">
import {ref, reactive, computed, onMounted} from 'vue'
import {useUsersStore} from '@/stores/users.ts'
import type {QuestionForm, User} from '@/types'
import {
  PlusOutlined,
  EditOutlined,
  DeleteOutlined,
  ReloadOutlined,
  TagOutlined
} from '@ant-design/icons-vue'
import {message} from 'ant-design-vue'
import dayjs from 'dayjs';
import router from '@/router';
import {useQuestionFormStore} from "@/stores/forms.ts";
import {useCustomerStore} from "@/stores/customers.ts";

const usersStore = useUsersStore();
const customersStore = useCustomerStore();

const formsStore = useQuestionFormStore();

const searchText = ref('')
const roleFilter = ref<string>()
const statusFilter = ref<string>()
const selectedRowKeys = ref<number[]>([])
const currentPage = ref(1)
const pageSize = ref(10);

const assignModalVisible = ref(false);
const selectedCustomerIds = ref<number[]>([])

const columns = [
  {
    title: 'Tên form',
    key: 'title',
    width: 200,
    align: 'left'
  },
  {
    title: 'Thao tác',
    key: 'actions',
    width: 150,
    fixed: 'right'
  }
]


// Computed
const pagination = computed(() => ({
  current: currentPage.value,
  pageSize: pageSize.value,
  total: formsStore.total,
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
const fetchForms = () => {
  formsStore.fetchQuestionForms({
    page: currentPage.value,
    pageSize: pageSize.value,
    search: searchText.value,
    role: roleFilter.value,
    status: statusFilter.value
  })
}

const handleSearch = () => {
  currentPage.value = 1
  fetchForms()
}

const handleFilter = () => {
  currentPage.value = 1
  fetchForms()
}

const handleRefresh = () => {
  fetchForms()
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

  fetchForms()
}

const createQuestionForm = () => {
  router.push('/questions/create');
}

const editQuestionForm = (questionForm: QuestionForm) => {
  router.push({
    name: 'EditQuestion',
    params: {
      id: questionForm.id,
    }
  })
}

const handleDelete = async (id: number) => {
  // await usersStore.deleteUser(id)
  // fetchForms()
}

const handleBatchDelete = () => {
  // if (selectedRowKeys.value && selectedRowKeys.value.length > 0) {
  //   usersStore.deleteUsers(selectedRowKeys.value)
  //   selectedRowKeys.value = []
  //   fetchForms()
  // }
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
  fetchForms();
  fetchAllCustomer();
});

const fetchAllCustomer = () => {
  customersStore.getAll();
}

const openAssignModal = (questionForm: QuestionForm) => {
  assignModalVisible.value = true;
  formsStore.currentQuestionForm = {
    ...questionForm,
    assignCustomerIds: questionForm.assignCustomerIds || []
  };
}

const handleAssignFormToCustomer = () => {
  if (formsStore.currentQuestionForm && formsStore.currentQuestionForm.id
  && Array.isArray(formsStore.currentQuestionForm.assignCustomerIds)) {
    formsStore.assignToCustomers({
      form_id: +formsStore.currentQuestionForm.id,
      customer_ids: formsStore.currentQuestionForm.assignCustomerIds
    })
  }
  assignModalVisible.value = false;
  fetchForms();
}


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