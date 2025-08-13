<template>
  <div class="users-page">
    <div class="card-container">
      <div class="page-header">
        <div>
          <h2>Quản lý các tags</h2>
          <p>Danh sách các tags đã được tạo</p>
        </div>
        <a-button type="primary" @click="showCreateTagModal">
          <template #icon>
            <PlusOutlined/>
          </template>
          Tạo mới Tag
        </a-button>
      </div>
    </div>

    <a-table
        :columns="columns"
        :data-source="tagStore.tags"
        :loading="tagStore.isLoading"
        :pagination="pagination"
        :row-selection="rowSelection"
        :scroll="{ x: '100%' }"
        row-key="id"
    >
      <template #bodyCell="{ column, record }">
        <template v-if="column.key === 'name'">
          <div>
            <div style="font-weight: 500;">{{ record.name }}</div>
          </div>
        </template>
        <template v-else-if="column.key === 'key'">
          <div>
            <div style="font-weight: 500;">{{ record.key }}</div>
          </div>
        </template>

        <template v-else-if="column.key === 'actions'">
          <a-button @click="openEditTagModal(record)" size="small" type="primary">
            <template #icon>
              <EditOutlined/>
            </template>
          </a-button>
        </template>
      </template>
    </a-table>

    <a-modal
        v-model:open="tagModalVisible"
        :title="isEdit ? 'Chỉnh sửa' : 'Tạo mới'"
        :width="'50%'"
        @ok="handleModalOK"
        @cancel="handleModalCancel"
        :confirm-loading="modalLoading"
    >
      <a-form
          ref="formRef"
          :model="tagData"
          :rules="formRules"
          layout="vertical"
      >
        <a-row :gutter="16">
          <a-col :span="12">
            <a-form-item label="Tên" name="name">
              <a-input
                  v-model:value="tagData.name"
                  placeholder="Nhập tên"
                  @blur="generateKeyFromName"
              />
            </a-form-item>
          </a-col>
          <a-col :span="12">
            <a-form-item label="Key" name="key">
              <a-input v-model:value="tagData.key" placeholder="Key sẽ tự động tạo từ tên" />
            </a-form-item>
          </a-col>
        </a-row>

        <h3>Gắn Tag cho Khách Hàng</h3>

        <a-checkbox-group v-model:value="tagData.customerIds">
          <a-row :gutter="[16, 16]">
            <a-col
                v-for="item in customerStore.all"
                :key="item.id"
                :span="6">
              <a-checkbox :value="item.id">
                <div>
                  <div style="font-weight: 500;">{{ item.name }}</div>
                  <div style="color: #888; font-size: 12px;">{{ item.phone }}</div>
                </div>
              </a-checkbox>
            </a-col>
          </a-row>
        </a-checkbox-group>

      </a-form>
    </a-modal>

  </div>

</template>
<script setup lang="ts">
import {PlusOutlined, EditOutlined} from "@ant-design/icons-vue";
import {useTagStore} from "@/stores/tags.ts";
import {computed, onMounted, reactive, ref} from "vue";
import {useCustomerStore} from "@/stores/customers.ts";
import type {Tag} from "@/types";

const selectedRowKeys = ref<number[]>([]);
const currentPage = ref(1);
const pageSize = ref(10);

const formRef = ref();

const tagModalVisible = ref(false);
const isEdit = ref(false);
const modalLoading = ref(false);

const tagData = reactive({
  name: "",
  key: "",
  customerIds: []
});

const formRules = {
  name: [
    {required: true, message: 'Vui lòng nhập tên'}
  ],
  key: [
    {required: true, message: 'Vui lòng nhập key'},
    {
      pattern: /^[^\s]+$/,
      message: 'Key không được chứa khoảng trắng'
    }
  ]
}

const tagStore = useTagStore();
const customerStore = useCustomerStore();

const columns = [
  {
    title: 'Tên',
    key: 'name',
    width: 200,
    align: 'center',
  },
  {
    title: 'Key',
    key: 'key',
    width: 200,
    align: 'center',
  },
  {
    title: 'Actions',
    key: 'actions',
    width: 200,
    fixed: 'right',
  }
];

const pagination = computed(() => ({
  current: currentPage.value,
  pageSize: pageSize.value,
  total: tagStore.total,
  showSizeChanger: true,
  showQuickJumper: true,
  showTotal: (total: number, range: [number, number]) =>
      `${range[0]}-${range[1]} của ${total} mục`
}));

const rowSelection = computed(() => ({
  selectedRowKeys: selectedRowKeys.value,
  onChange: (keys: number[]) => {
    selectedRowKeys.value = keys
  }
}));

onMounted(() => {
  fetchTags();
  fetchAllCustomers();
});

const fetchTags = () => {
  tagStore.fetchTags({
    page: currentPage.value,
    pageSize: pageSize.value
  });
}

const generateKeyFromName = () => {
  if (tagData.name.trim() && !isEdit.value) {
    tagData.key = tagData.name
        .trim()
        .toUpperCase()
        .replace(/\s+/g, '_');
  }
}

const handleModalOK = async () => {
  try {
    await formRef.value.validateFields();
    modalLoading.value = true;

    await tagStore.createTag(tagData);

    tagModalVisible.value = false;
    resetForm();
    fetchTags();

  } catch (e) {
  } finally {
    modalLoading.value = false;
  }
}

const handleModalCancel = () => {
  tagModalVisible.value = false;
}

const showCreateTagModal = () => {
  tagModalVisible.value = true;
  isEdit.value = false;
  resetForm();
}

const resetForm = () => {
  Object.assign(tagData, {
    name: '',
    key: '',
    customerIds: []
  });
  formRef.value?.resetFields();
}

const fetchAllCustomers = () => {
  customerStore.getAll();
}

const openEditTagModal = (tag: Tag) => {
  tagModalVisible.value = true;
  isEdit.value = true;
  Object.assign(tagData, {
    name: tag.name,
    key: tag.key,
    customerIds: tag.customerIds
  });
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