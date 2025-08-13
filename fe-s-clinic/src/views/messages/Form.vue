<script setup lang="ts">
import {computed, onMounted, reactive, ref, watch} from "vue";
import {useQuestionFormStore} from "@/stores/forms.ts";
import {useTagStore} from "@/stores/tags.ts";
import {useMessageStore} from "@/stores/messages.ts";
import router from "@/router";

const formStore = useQuestionFormStore();
const tagStore = useTagStore();
const formRef = ref();
const messageStore = useMessageStore();

const formData = reactive({
  title: '',
  content: '',
  type: 1,
  forms: '',
  tags: [],
  customerIds: []
});

onMounted(() => {
  fetchAllForm();
  fetchAllTags();
});

const fetchAllForm = () => {
  formStore.getAll();
}

const fetchAllTags = () => {
  tagStore.getAll();
}

const formOptions = computed(() => {
  return formStore.all.map((item) => ({
    label: item.title,
    value: item.id
  }));
});

const tagOptions = computed(() => {
  return tagStore.all.map((item) => ({
    label: item.name,
    value: item.id
  }));
});

const selectedCustomers = computed(() => {
  if (!formData.tags || formData.tags.length === 0) {
    return [];
  }

  const selectedTags = tagStore.all.filter(tag => formData.tags.includes(tag.id));
  const customerMap = new Map();

  selectedTags.forEach(tag => {
    if (tag.customers && Array.isArray(tag.customers)) {
      tag.customers.forEach(customer => {
        if (!customerMap.has(customer.id)) {
          customerMap.set(customer.id, {
            id: customer.id,
            name: customer.name,
            phone: customer.phone,
            tags: [tag.name] // Track which tags this customer belongs to
          });
        } else {
          const existingCustomer = customerMap.get(customer.id);
          if (!existingCustomer.tags.includes(tag.name)) {
            existingCustomer.tags.push(tag.name);
          }
        }
      });
    }
  });

  return Array.from(customerMap.values());
});

const customerCount = computed(() => selectedCustomers.value.length);

watch(selectedCustomers, (newCustomers) => {
  formData.customerIds = newCustomers.map(c => c.id);
}, { immediate: true });

const onCancel = () => {
  router.push('/messages');
}

const onSubmit = async () => {
  await messageStore.createMessage(formData);
  await router.push('/messages');
}
</script>

<template>
  <div class="form-page">
    <a-card :bordered="false" title="Create a new message" class="form-card">
      <div class="form-content">
        <a-form
            :model="formData"
            ref="formRef"
            layout="vertical">
          <a-form-item label="Title" name="title">
            <a-input v-model:value="formData.title"/>
          </a-form-item>

          <a-form-item label="Content" name="content">
            <a-textarea v-model:value="formData.content" :auto-size="{ minRows: 3 }"/>
          </a-form-item>

          <a-form-item label="Hành động gửi" name="type">
            <a-radio-group v-model:value="formData.type">
              <a-radio :value="1">Gửi luôn và ngay</a-radio>
              <a-radio :value="2">Gửi theo lịch</a-radio>
            </a-radio-group>
          </a-form-item>

          <a-card v-if="+formData.type === 2" title="Đặt lịch">
            <!-- Schedule form content here -->
          </a-card>

          <a-form-item label="Chọn biểu mẫu" name="forms">
            <a-select
                v-model:value="formData.forms"
                show-search
                :options="formOptions"
                placeholder="Tìm kiếm và chọn biểu mẫu"
                :filter-option="(input: any, option: any) => option?.label?.toLowerCase().includes(input.toLowerCase())"
            />
          </a-form-item>

          <a-row :gutter="15">
            <a-col :span="12">
              <a-form-item label="Chọn gửi theo tag Khách Hàng" name="tags">
                <a-select
                    v-model:value="formData.tags"
                    show-search
                    :options="tagOptions"
                    mode="multiple"
                    :filter-option="(input: any, option: any) => option?.label?.toLowerCase().includes(input.toLowerCase())"
                />
              </a-form-item>
            </a-col>
            <a-col :span="12">
              <div class="customer-list-section">
                <label class="customer-list-title">
                  Danh sách Khách Hàng sẽ nhận Message
                  <span v-if="customerCount > 0" class="customer-count">
                    ({{ customerCount }} khách hàng)
                  </span>
                </label>

                <div class="customer-list-container">
                  <div v-if="selectedCustomers.length === 0" class="empty-state">
                    Chọn tags để xem danh sách khách hàng
                  </div>

                  <div v-else class="customer-list">
                    <a-row :gutter="[8, 8]">
                      <a-col
                          v-for="customer in selectedCustomers"
                          :key="customer.id"
                          :xs="24"
                          :sm="12"
                          :md="12"
                          :lg="8"
                          :xl="8"
                      >
                        <div class="customer-item">
                          <div class="customer-info">
                            <div class="customer-name">{{ customer.name }}</div>
                            <div class="customer-phone">{{ customer.phone }}</div>
                          </div>
                          <div class="customer-tags">
                            <a-tag
                                v-for="tag in customer.tags"
                                :key="tag"
                                size="small"
                                color="blue"
                            >
                              {{ tag }}
                            </a-tag>
                          </div>
                        </div>
                      </a-col>
                    </a-row>
                  </div>
                </div>
              </div>
            </a-col>
          </a-row>
        </a-form>
      </div>

      <div class="form-footer">
        <a-space>
          <a-button @click="onCancel">Cancel</a-button>
          <a-button type="primary" @click="onSubmit">Submit</a-button>
        </a-space>
      </div>
    </a-card>
  </div>
</template>

<style scoped>
.form-card {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.form-content {
  flex: 1;
  overflow-y: auto;
  padding-right: 12px;
}

.form-footer {
  position: sticky;
  bottom: 0;
  background: #fff;
  padding: 16px;
  border-top: 1px solid #f0f0f0;
  text-align: right;
  z-index: 10;
  margin-top: 1rem;
}

.customer-list-section {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.customer-list-title {
  font-weight: 500;
  margin-bottom: 8px;
  display: block;
}

.customer-count {
  color: #1890ff;
  font-size: 12px;
}

.customer-list-container {
  flex: 1;
  border: 1px solid #d9d9d9;
  border-radius: 6px;
  min-height: 200px;
  max-height: 300px;
  overflow-y: auto;
}

.empty-state {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100px;
  color: #999;
  font-style: italic;
}

.customer-list {
  padding: 8px;
}

.customer-item {
  display: flex;
  flex-direction: column;
  padding: 12px;
  border: 1px solid #f0f0f0;
  border-radius: 4px;
  background: #fafafa;
  transition: all 0.2s;
  height: 100%;
  min-height: 80px;
}

.customer-item:hover {
  background: #f0f8ff;
  border-color: #1890ff;
}

.customer-item:last-child {
  margin-bottom: 0;
}

.customer-info {
  flex: 1;
  margin-bottom: 8px;
}

.customer-name {
  font-weight: 500;
  color: #333;
  margin-bottom: 4px;
}

.customer-phone {
  font-size: 12px;
  color: #666;
}

.customer-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
}

.customer-tags .ant-tag {
  margin: 0;
}
</style>