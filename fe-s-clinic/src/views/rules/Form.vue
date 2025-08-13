<script setup lang="ts">
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { PlusOutlined, DeleteOutlined, QuestionCircleOutlined } from '@ant-design/icons-vue';
import type {
  AutoTagRuleGroup,
  AutoTagRuleCondition,
  Tag,
  Question,
  QuestionOption
} from '@/types';
import {useQuestionFormStore} from "@/stores/forms.ts";
import {useTagStore} from "@/stores/tags.ts";
import {useRuleStore} from "@/stores/rules.ts";
import router from "@/router";

// Form refs
const formRef = ref();
const loading = ref(false);
const isEdit = ref(false);

// Form data
const formData = reactive<AutoTagRuleGroup>({
  name: '',
  form_id: null,
  tag_id: null,
  logic_operator: 'AND',
  description: '',
  conditions: []
});

// State
const tags = ref<Tag[]>();
const questions = ref<Question[]>([]);
const selectedFormQuestions = ref<Question[]>([]);

const formStore = useQuestionFormStore();
const tagStore = useTagStore();
const ruleStore = useRuleStore();

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

const conditionTypeOptions = [
  { label: 'Bằng', value: 'equals' },
  { label: 'Chứa', value: 'contains' },
  { label: 'Bắt đầu bằng', value: 'starts_with' },
  { label: 'Kết thúc bằng', value: 'ends_with' },
  { label: 'Trong khoảng', value: 'in_range' },
  { label: 'Trong danh sách', value: 'in_list' }
];

// Methods
onMounted(() => {
  fetchAllForms();
  fetchAllTags();
  addCondition();
  // if (props.ruleGroup) {
  //   Object.assign(formData, props.ruleGroup);
  //   if (formData.form_id) {
  //     loadFormQuestions(formData.form_id);
  //   }
  // } else {
  //   // Add initial condition for new rule
  //   addCondition();
  // }
});

watch(() => formData.form_id, (newFormId) => {
  if (newFormId) {
    loadFormQuestions(newFormId);
    formData.conditions = [];
    addCondition();
  }
});

const loadFormQuestions = async (formId: number) => {
  try {
    const selectedForm = formStore.all.find((item) => item.id === formId);
    console.log('formId', selectedForm);
    selectedFormQuestions.value = selectedForm ? selectedForm.questions : [];
  } catch (error) {
    selectedFormQuestions.value = [];
  }
};

const getQuestionById = (questionId: string): Question | undefined => {
  return selectedFormQuestions.value.find(q => q.id === questionId);
};

const getQuestionOptions = (questionId: string): QuestionOption[] => {
  const question = getQuestionById(questionId);
  return question?.options || [];
};

const isTextQuestion = (questionId: string): boolean => {
  const question = getQuestionById(questionId);
  return question?.type === 'text';
};

const addCondition = () => {
  formData.conditions.push({
    question_id: '',
    question_option_id: null,
    condition_type: 'equals',
    condition_value: null
  });
};

const removeCondition = (index: number) => {
  if (formData.conditions.length > 1) {
    formData.conditions.splice(index, 1);
  }
};

const onQuestionChange = (conditionIndex: number, questionId: string) => {
  const condition = formData.conditions[conditionIndex];
  condition.question_id = questionId;
  condition.question_option_id = null;
  condition.condition_value = null;

  // Set default condition type based on question type
  if (isTextQuestion(questionId)) {
    condition.condition_type = 'equals';
  } else {
    condition.condition_type = 'equals';
  }
};

const onConditionTypeChange = (conditionIndex: number, type: string) => {
  const condition = formData.conditions[conditionIndex];
  condition.condition_type = type as AutoTagRuleCondition['condition_type'];
  condition.condition_value = null;

  // Initialize condition value based on type
  if (type === 'in_range') {
    condition.condition_value = { min: null, max: null };
  } else if (type === 'in_list') {
    condition.condition_value = { values: [] };
  } else if (['equals', 'contains', 'starts_with', 'ends_with'].includes(type)) {
    condition.condition_value = { value: '' };
  }
};

const handleSubmit = async () => {
  try {
    loading.value = true;
    await formRef.value?.validate();

    // Clean up conditions
    const cleanedData = {
      ...formData,
      conditions: formData.conditions.filter(condition =>
          condition.question_id &&
          (condition.question_option_id || condition.condition_value)
      )
    };

    await ruleStore.create(cleanedData);
    await router.push('/rules');

  } catch (error) {
    console.error('Form validation failed:', error);
  } finally {
    loading.value = false;
  }
};

const handleCancel = () => {
};

// Form validation rules
const rules = {
  name: [{ required: true, message: 'Vui lòng nhập tên rule', trigger: 'blur' }],
  form_id: [{ required: true, message: 'Vui lòng chọn form', trigger: 'change' }],
  tag_id: [{ required: true, message: 'Vui lòng chọn tag', trigger: 'change' }],
  logic_operator: [{ required: true, message: 'Vui lòng chọn logic operator', trigger: 'change' }]
};

const fetchAllForms = () => {
  formStore.getAll();
}

const fetchAllTags = () => {
  tagStore.getAll();
}
</script>

<template>
  <div class="auto-tag-rule-form">
    <a-card :bordered="false" :title="isEdit ? 'Chỉnh sửa Auto Tag Rule' : 'Tạo Auto Tag Rule mới'">
      <a-form
          ref="formRef"
          :model="formData"
          :rules="rules"
          layout="vertical"
          class="form-content"
      >
        <!-- Basic Information -->
        <a-card title="Thông tin cơ bản" size="small" class="section-card">
          <a-row :gutter="16">
            <a-col :span="12">
              <a-form-item label="Tên Rule" name="name">
                <a-input v-model:value="formData.name" placeholder="VD: Nam trung niên" />
              </a-form-item>
            </a-col>
            <a-col :span="12">
              <a-form-item label="Logic Operator" name="logic_operator">
                <a-radio-group v-model:value="formData.logic_operator">
                  <a-radio value="AND">
                    <span>AND</span>
                    <a-tooltip title="Tất cả điều kiện phải đúng">
                      <QuestionCircleOutlined style="margin-left: 4px; color: #999;" />
                    </a-tooltip>
                  </a-radio>
                  <a-radio value="OR">
                    <span>OR</span>
                    <a-tooltip title="Chỉ cần 1 điều kiện đúng">
                      <QuestionCircleOutlined style="margin-left: 4px; color: #999;" />
                    </a-tooltip>
                  </a-radio>
                </a-radio-group>
              </a-form-item>
            </a-col>
          </a-row>

          <a-row :gutter="16">
            <a-col :span="12">
              <a-form-item label="Form áp dụng" name="form_id">
                <a-select
                    v-model:value="formData.form_id"
                    :options="formOptions"
                    placeholder="Chọn form"
                    show-search
                    :filter-option="(input: any, option: any) => option.label.toLowerCase().includes(input.toLowerCase())"
                />
              </a-form-item>
            </a-col>
            <a-col :span="12">
              <a-form-item label="Tag sẽ được gán" name="tag_id">
                <a-select
                    v-model:value="formData.tag_id"
                    :options="tagOptions"
                    placeholder="Chọn tag"
                    show-search
                    :filter-option="(input: any, option: any) => option.label.toLowerCase().includes(input.toLowerCase())"
                />
              </a-form-item>
            </a-col>
          </a-row>

          <a-form-item label="Mô tả" name="description">
            <a-textarea
                v-model:value="formData.description"
                placeholder="Mô tả về rule này..."
                :rows="3"
            />
          </a-form-item>
        </a-card>

        <!-- Conditions -->
        <a-card title="Điều kiện" size="small" class="section-card">
          <div class="logic-operator-info">
            <a-tag :color="formData.logic_operator === 'AND' ? 'blue' : 'orange'">
              {{ formData.logic_operator === 'AND' ? 'Tất cả điều kiện phải đúng' : 'Chỉ cần 1 điều kiện đúng' }}
            </a-tag>
          </div>

          <div
              v-for="(condition, index) in formData.conditions"
              :key="index"
              class="condition-item"
          >
            <a-card size="small" class="condition-card">
              <template #title>
                <span>Điều kiện {{ index + 1 }}</span>
              </template>
              <template #extra>
                <a-button
                    v-if="formData.conditions.length > 1"
                    type="text"
                    danger
                    size="small"
                    @click="removeCondition(index)"
                >
                  <DeleteOutlined />
                </a-button>
              </template>

              <a-row :gutter="16">
                <a-col :span="12">
                  <a-form-item label="Câu hỏi">
                    <a-select
                        :value="condition.question_id || undefined"
                        placeholder="Chọn câu hỏi"
                        @change="(value: any) => onQuestionChange(index, value)"
                    >
                      <a-select-option
                          v-for="question in selectedFormQuestions"
                          :key="question.id"
                          :value="question.id"
                      >
                        {{ question.title }}
                      </a-select-option>
                    </a-select>
                  </a-form-item>
                </a-col>
                <a-col :span="12">
                  <a-form-item
                      v-if="!isTextQuestion(condition.question_id)"
                      label="Lựa chọn"
                  >
                    <a-select
                        v-model:value="condition.question_option_id"
                        placeholder="Chọn option"
                    >
                      <a-select-option
                          v-for="option in getQuestionOptions(condition.question_id)"
                          :key="option.id"
                          :value="option.id"
                      >
                        {{ option.text }}
                      </a-select-option>
                    </a-select>
                  </a-form-item>
                  <a-form-item
                      v-else
                      label="Loại điều kiện"
                  >
                    <a-select
                        :value="condition.condition_type"
                        :options="conditionTypeOptions"
                        @change="(value) => onConditionTypeChange(index, value)"
                    />
                  </a-form-item>
                </a-col>
              </a-row>

              <!-- Condition Value for Text Questions -->
              <div v-if="isTextQuestion(condition.question_id) && condition.condition_value">
                <a-row :gutter="16">
                  <!-- Simple text conditions -->
                  <a-col
                      v-if="['equals', 'contains', 'starts_with', 'ends_with'].includes(condition.condition_type)"
                      :span="24"
                  >
                    <a-form-item label="Giá trị">
                      <a-input
                          v-model:value="condition.condition_value.value"
                          placeholder="Nhập giá trị để so sánh"
                      />
                    </a-form-item>
                  </a-col>

                  <!-- Range condition -->
                  <template v-if="condition.condition_type === 'in_range'">
                    <a-col :span="12">
                      <a-form-item label="Giá trị tối thiểu">
                        <a-input-number
                            v-model:value="condition.condition_value.min"
                            placeholder="Min"
                            style="width: 100%"
                        />
                      </a-form-item>
                    </a-col>
                    <a-col :span="12">
                      <a-form-item label="Giá trị tối đa">
                        <a-input-number
                            v-model:value="condition.condition_value.max"
                            placeholder="Max"
                            style="width: 100%"
                        />
                      </a-form-item>
                    </a-col>
                  </template>

                  <!-- List condition -->
                  <a-col v-if="condition.condition_type === 'in_list'" :span="24">
                    <a-form-item label="Danh sách giá trị">
                      <a-select
                          v-model:value="condition.condition_value.values"
                          mode="tags"
                          placeholder="Nhập và nhấn Enter để thêm giá trị"
                          style="width: 100%"
                      />
                    </a-form-item>
                  </a-col>
                </a-row>
              </div>
            </a-card>
          </div>

          <div class="add-condition">
            <a-button type="dashed" @click="addCondition" block>
              <PlusOutlined />
              Thêm điều kiện
            </a-button>
          </div>
        </a-card>
      </a-form>

      <!-- Footer Actions -->
      <div class="form-footer">
        <a-space>
          <a-button @click="handleCancel">Hủy bỏ</a-button>
          <a-button type="primary" :loading="loading" @click="handleSubmit">
            {{ isEdit ? 'Cập nhật' : 'Tạo mới' }}
          </a-button>
        </a-space>
      </div>
    </a-card>
  </div>
</template>

<style scoped>
.auto-tag-rule-form {
  padding: 16px;
}

.section-card {
  margin-bottom: 16px;
}

.section-card :deep(.ant-card-head) {
  background: #fafafa;
}

.logic-operator-info {
  margin-bottom: 16px;
  text-align: center;
}

.condition-item {
  margin-bottom: 16px;
}

.condition-item:last-child {
  margin-bottom: 0;
}

.condition-card {
  border: 1px solid #e8e8e8;
}

.condition-card :deep(.ant-card-head) {
  background: #f9f9f9;
  min-height: 40px;
  padding: 0 16px;
}

.condition-card :deep(.ant-card-head-title) {
  font-size: 14px;
  font-weight: 500;
}

.add-condition {
  margin-top: 16px;
}

.form-footer {
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid #f0f0f0;
  text-align: right;
}

.form-content {
  max-height: 70vh;
  overflow-y: auto;
  padding-right: 8px;
}

/* Custom scrollbar */
.form-content::-webkit-scrollbar {
  width: 6px;
}

.form-content::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.form-content::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.form-content::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>