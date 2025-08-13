<script setup lang="ts">
import {CopyOutlined, DeleteOutlined, CloseOutlined, PlusOutlined} from "@ant-design/icons-vue";
import type {Question} from "@/types";
import {onUnmounted, reactive, watch} from "vue";

interface Props {
  question: Question,
  index: number,
  isOnlyQuestion: boolean,
}

interface Emits {
  (e: 'update', questionId: string, updates: Partial<Question>): void;

  (e: 'updateOption', questionId: string, optionId: string, updates: any): void;

  (e: 'addOption', questionId: string): void;

  (e: 'removeOption', questionId: string, optionId: string): void;

  (e: 'remove'): void;

  (e: 'duplicate'): void;

  (e: 'addOtherOption', questionId: string): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const localQuestion = reactive({...props.question});

let titleDebounceTimer: number | null = null;
let descriptionDebounceTimer: number | null = null;
let optionDebounceTimers: { [key: string]: number } = {}


watch(() => props.question, (newQuestion: Question) => {
  Object.assign(localQuestion, newQuestion);
}, {deep: true});

const updateQuestion = (updates: Partial<Question>) => {
  emit('update', props.question.id, updates);
}

const updateOption = (optionId: string, updates: any) => {
  emit('updateOption', props.question.id, optionId, updates)
}

const addOption = () => {
  emit('addOption', props.question.id)
}

const addOtherOption = () => {
  emit('addOtherOption', props.question.id)
}

const removeOption = (optionId: string) => {
  emit('removeOption', props.question.id, optionId)
}

const handleTypeChange = (newType: string) => {
  updateQuestion({type: newType as any})
}

const handleTitleInput = () => {
  if (titleDebounceTimer) {
    window.clearTimeout(titleDebounceTimer);
  }
  titleDebounceTimer = window.setTimeout(() => {
    updateQuestion({title: localQuestion.title})
  }, 1000); // save after 1 second of no typing
}

const handleDescriptionInput = () => {
  if (descriptionDebounceTimer) {
    window.clearTimeout(descriptionDebounceTimer);
  }
  descriptionDebounceTimer = window.setTimeout(() => {
    updateQuestion({description: localQuestion.description})
  }, 1000)
}

const handleOptionInput = (optionId: string) => {
  if (optionDebounceTimers[optionId]) {
    clearTimeout(optionDebounceTimers[optionId])
  }
  optionDebounceTimers[optionId] = setTimeout(() => {
    const option = localQuestion.options.find(opt => opt.id === optionId)
    if (option) {
      updateOption(optionId, {text: option.text, value: option.text})
    }
  }, 1000)
}

// Cleanup timers
onUnmounted(() => {
  if (titleDebounceTimer) clearTimeout(titleDebounceTimer)
  if (descriptionDebounceTimer) clearTimeout(descriptionDebounceTimer)
  Object.values(optionDebounceTimers).forEach(timer => clearTimeout(timer))
})


</script>

<template>
  <div class="question-item">
    <div class="question-header">
      <div class="question-number">{{ index + 1 }}</div>
      <div class="question-actions">
        <a-button
            type="text"
            size="small"
            @click="$emit('duplicate')"
            title="Duplicate"
        >
          <template #icon>
            <CopyOutlined/>
          </template>
        </a-button>
        <a-button
            type="text"
            size="small"
            danger
            @click="$emit('remove')"
            :disabled="isOnlyQuestion"
            title="Delete"
        >
          <template #icon>
            <DeleteOutlined/>
          </template>
        </a-button>
      </div>
    </div>
    <div class="question-content">
      <a-form-item>
        <a-input
            v-model:value="localQuestion.title"
            placeholder="Untitled Question"
            size="large"
            class="question-title-input"
            @blur="updateQuestion({ title: localQuestion.title })"
            @input="handleTitleInput"
        />
      </a-form-item>

      <!-- Question Description -->
      <a-form-item>
        <a-textarea
            v-model:value="localQuestion.description"
            placeholder="Question description (optional)"
            :rows="2"
            @blur="updateQuestion({ description: localQuestion.description })"
            @input="handleDescriptionInput"
        />
      </a-form-item>

      <!-- Question Type & Required -->
      <div class="question-settings">
        <a-form-item label="Answer Type" style="margin-bottom: 0;">
          <a-select
              v-model:value="localQuestion.type"
              style="width: 150px"
              @change="handleTypeChange"
          >
            <a-select-option value="text">Text</a-select-option>
            <a-select-option value="radio">Single Choice</a-select-option>
            <a-select-option value="checkbox">Multi Choice</a-select-option>
          </a-select>
        </a-form-item>

        <a-form-item style="margin-bottom: 0;">
          <a-switch
              v-model:checked="localQuestion.required"
              @change="updateQuestion({ required: localQuestion.required })"
          />
          <span style="margin-left: 8px">Required</span>
        </a-form-item>
      </div>

      <!-- Options for radio/checkbox -->
      <div v-if="localQuestion.type !== 'text'" class="question-options">
        <h4>Answer Options</h4>
        <div
            v-for="(option, optionIndex) in localQuestion.options"
            :key="option.id"
            class="option-item"
        >
          <div class="option-indicator">
            <input
                v-if="localQuestion.type === 'radio'"
                type="radio"
                disabled
                :name="`preview-${localQuestion.id}`"
            />
            <input
                v-else-if="localQuestion.type === 'checkbox'"
                type="checkbox"
                disabled
            />
          </div>
          <a-input
              v-model:value="option.text"
              :placeholder="option.isOther ? 'Câu trả lời khác...' : 'Option text'"
              :disabled="option.isOther"
              @blur="updateOption(option.id, { text: option.text, value: option.text })"
              @input="handleOptionInput(option.id)"
          />

          <a-button
              type="text"
              size="small"
              danger
              @click="removeOption(option.id)"
              :disabled="localQuestion.options.length <= 1"
          >
            <template #icon>
              <CloseOutlined/>
            </template>
          </a-button>
        </div>

        <div class="add-option-buttons">
          <a-button
              type="dashed"
              @click="addOption"
              style="margin-right: 8px;"
          >
            <template #icon>
              <PlusOutlined />
            </template>
            Add Option
          </a-button>

          <a-button
              v-if="!localQuestion.hasOtherOption"
              type="dashed"
              @click="addOtherOption"
              style="color: #1890ff;"
          >
            <template #icon>
              <PlusOutlined />
            </template>
            Thêm tùy chọn <span style="color: #1890ff;"> hoặc "Câu trả lời khác"</span>
          </a-button>
        </div>

      </div>

      <!-- Preview -->
      <div class="question-preview" v-if="false">
        <h4>Preview:</h4>
        <div class="preview-content">
          <div class="preview-title">
            {{ localQuestion.title || 'Untitled Question' }}
            <span v-if="localQuestion.required" class="required-mark">*</span>
          </div>
          <div v-if="localQuestion.description" class="preview-description">
            {{ localQuestion.description }}
          </div>

          <!-- Text preview -->
          <a-input
              v-if="localQuestion.type === 'text'"
              placeholder="Text answer"
              disabled
          />

          <!-- Radio preview -->
          <a-radio-group v-else-if="localQuestion.type === 'radio'" disabled>
            <div v-for="option in localQuestion.options" :key="option.id" class="preview-option">
              <a-radio :value="option.value">{{ option.text || 'Option' }}</a-radio>
            </div>
          </a-radio-group>

          <!-- Checkbox preview -->
          <a-checkbox-group v-else-if="localQuestion.type === 'checkbox'" disabled>
            <div v-for="option in localQuestion.options" :key="option.id" class="preview-option">
              <a-checkbox :value="option.value">{{ option.text || 'Option' }}</a-checkbox>
            </div>
          </a-checkbox-group>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.question-item {
  border: 1px solid #d9d9d9;
  border-radius: 8px;
  margin-bottom: 24px;
  background: #fff;
  transition: all 0.3s ease;
}

.question-item:hover {
  border-color: #1890ff;
  box-shadow: 0 2px 8px rgba(24, 144, 255, 0.1);
}

.question-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  border-bottom: 1px solid #f0f0f0;
  background: #fafafa;
  border-radius: 8px 8px 0 0;
}

.question-number {
  font-weight: 600;
  color: #1890ff;
  font-size: 16px;
}

.question-actions {
  display: flex;
  gap: 4px;
}

.question-content {
  padding: 20px;
}

.question-title-input {
  font-size: 16px;
  font-weight: 500;
}

.question-settings {
  display: flex;
  gap: 24px;
  align-items: center;
  margin-bottom: 16px;
  padding: 12px;
  background: #f8f9fa;
  border-radius: 6px;
}

.question-options {
  margin-bottom: 20px;
}

.option-item {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
}

.option-item.other-option {
  background: #f0f8ff;
  padding: 8px;
  border-radius: 6px;
  border: 1px solid #d6f7ff;
}

.option-indicator {
  width: 20px;
  display: flex;
  justify-content: center;
}

.question-preview {
  border-top: 1px solid #f0f0f0;
  padding-top: 16px;
}

.preview-content {
  padding: 16px;
  background: #f8f9fa;
  border-radius: 6px;
  border-left: 4px solid #1890ff;
}

.preview-title {
  font-weight: 500;
  margin-bottom: 8px;
}

.required-mark {
  color: #ff4d4f;
  margin-left: 4px;
}

.preview-description {
  color: #666;
  margin-bottom: 12px;
  font-size: 14px;
}

.preview-option {
  margin-bottom: 8px;
}

.add-option-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 12px;
}
</style>