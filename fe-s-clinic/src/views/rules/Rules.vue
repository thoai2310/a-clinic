<script setup lang="ts">
import {PlusOutlined} from "@ant-design/icons-vue";
import router from "@/router";


const createRule = () => {
  router.push('/rules/create');
}



</script>

<template>
  <div class="users-page">
    <div class="card-container">
      <div class="page-header">
        <div>
          <h2>Quản lý các rule</h2>
          <p>Danh sách các rules đã được tạo</p>
        </div>
        <a-button type="primary" @click="createRule">
          <template #icon>
            <PlusOutlined/>
          </template>
          Tạo mới Rule
        </a-button>
      </div>
    </div>
  </div>

</template>

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