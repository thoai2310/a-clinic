<template>
  <div class="users-page">
    <div class="card-container">
      <div class="page-header">
        <div>
          <h2>Quản lý các message</h2>
          <p>Danh sách các mesages đã được tạo</p>
        </div>
        <a-button type="primary" @click="showCreateTagModal">
          <template #icon>
            <PlusOutlined/>
          </template>
          Tạo mới Message
        </a-button>
      </div>
    </div>

    <a-table
        :columns="columns"
        :data-source="messageStore.messages"
        :loading="messageStore.isLoading"
        :pagination="pagination"
        :row-selection="rowSelection"
        :scroll="{ x: '100%' }"
        row-key="id"
    >
      <template #bodyCell="{ column, record }">
        <template v-if="column.key === 'title'">
          <div>
            <div style="font-weight: 500;">{{ record.title }}</div>
          </div>
        </template>
        <template v-else-if="column.key === 'type'">
          <div>
            <div v-if="record.type === 1" style="font-weight: 500;">Ngay lập tức</div>
            <div v-if="record.type === 2" style="font-weight: 500;">Gửi theo lịch</div>
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
  </div>
</template>
<script setup lang="ts">
import {PlusOutlined, EditOutlined} from "@ant-design/icons-vue";
import {useTagStore} from "@/stores/tags.ts";
import {computed, onMounted, reactive, ref} from "vue";
import {useCustomerStore} from "@/stores/customers.ts";
import router from "@/router";
import {useMessageStore} from "@/stores/messages.ts";

const selectedRowKeys = ref<number[]>([]);
const currentPage = ref(1);
const pageSize = ref(10);


const messageStore = useMessageStore();
const customerStore = useCustomerStore();

const columns = [
  {
    title: 'Tên',
    key: 'title',
    width: 200,
    align: 'center',
  },
  {
    title: 'Loại',
    key: 'type',
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
  total: messageStore.total,
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
  fetchMessages();
});

const fetchMessages = () => {
  messageStore.fetchMessages({
    page: currentPage.value,
    pageSize: pageSize.value,
  })
}




const showCreateTagModal = () => {
  router.push(`/messages/create`);
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