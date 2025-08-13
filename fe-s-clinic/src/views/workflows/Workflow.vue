<script setup lang="ts">

import FormModal from "@/views/workflows/FormModal.vue";
import {PlusOutlined} from "@ant-design/icons-vue";
</script>

<template>
  <div class="form-page">
    <div class="card-container">
      <div class="page-header">
        <div>
          <h2>Quản lý các Quy trình</h2>
        </div>
      </div>
      <a-button type="primary">
        <template #icon>
          <PlusOutlined />
        </template>
        Tạo mới quy trình
      </a-button>

    </div>




    <form-modal

    >

    </form-modal>
  </div>

</template>

<style scoped>

</style>