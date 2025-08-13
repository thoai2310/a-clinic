<template>
  <div class="form-preview">
    <div class="preview-header">
      <h2>{{ form.title || 'Untitled Form' }}</h2>
      <p v-if="form.description">{{ form.description }}</p>
    </div>

    <a-form layout="vertical" class="preview-form">
      <div
          v-for="(question, index) in form.questions"
          :key="question.id"
          class="preview-question"
      >
        <a-form-item
            :label="getQuestionLabel(question, index)"
            :required="question.required"
        >
          <p v-if="question.description" class="question-description">
            {{ question.description }}
          </p>

          <!-- Text input -->
          <a-input
              v-if="question.type === 'text'"
              placeholder="Your answer"
              size="large"
          />

          <!-- Radio options -->
          <a-radio-group
              v-else-if="question.type === 'radio'"
              v-model:value="answers[question.id]"
              @change="handleRadioChange(question.id, $event)"
          >
            <div
                v-for="option in question.options"
                :key="option.id"
                class="option-item"
            >
              <a-radio :value="option.value">
                {{ option.text }}
              </a-radio>
              <!-- Show text input for "Other" option when selected -->
              <div v-if="option.isOther && answers[question.id] === option.value" class="other-input-container">
                <a-input
                    v-model:value="otherAnswers[question.id]"
                    placeholder="Nhập câu trả lời của bạn"
                    size="small"
                    style="width: 250px; margin-top: 8px; margin-left: 24px;"
                />
              </div>
            </div>
          </a-radio-group>

          <!-- Checkbox options -->
          <a-checkbox-group
              v-else-if="question.type === 'checkbox'"
              v-model:value="answers[question.id]"
              @change="handleCheckboxChange(question.id, $event)"
          >
            <div
                v-for="option in question.options"
                :key="option.id"
                class="option-item"
            >
              <a-checkbox :value="option.value">
                {{ option.text }}
              </a-checkbox>
              <!-- Show text input for "Other" option when checked -->
              <div v-if="option.isOther && isOtherSelected(question.id, option.value)" class="other-input-container">
                <a-input
                    v-model:value="otherAnswers[question.id]"
                    placeholder="Nhập câu trả lời của bạn"
                    size="small"
                    style="width: 250px; margin-top: 8px; margin-left: 24px;"
                />
              </div>
            </div>
          </a-checkbox-group>
        </a-form-item>
      </div>

      <a-form-item>
        <a-button type="primary" size="large">
          Submit
        </a-button>
      </a-form-item>
    </a-form>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import type { QuestionForm, Question } from '@/types'

interface Props {
  form: QuestionForm
}

const props = defineProps<Props>()

const answers = ref<Record<string, any>>({})
const otherAnswers = ref<Record<string, string>>({})

const getQuestionLabel = (question: Question, index: number) => {
  return `${index + 1}. ${question.title || 'Untitled Question'}`
}

const handleRadioChange = (questionId: string, event: any) => {
  // Clear other answer when selecting different option
  const selectedValue = event.target.value
  const question = props.form.questions.find(q => q.id === questionId)
  const selectedOption = question?.options.find(opt => opt.value === selectedValue)

  if (!selectedOption?.isOther) {
    delete otherAnswers.value[questionId]
  }
}

const handleCheckboxChange = (questionId: string, checkedValues: string[]) => {
  // Clear other answer if "other" option is unchecked
  const question = props.form.questions.find(q => q.id === questionId)
  const otherOption = question?.options.find(opt => opt.isOther)

  if (otherOption && !checkedValues.includes(otherOption.value)) {
    delete otherAnswers.value[questionId]
  }
}

const isOtherSelected = (questionId: string, optionValue: string) => {
  const selectedValues = answers.value[questionId] || []
  return Array.isArray(selectedValues) && selectedValues.includes(optionValue)
}
</script>

<style scoped>
.form-preview {
  max-height: 70vh;
  overflow-y: auto;
  padding: 8px;
}

.preview-header {
  margin-bottom: 32px;
  text-align: center;
  border-bottom: 1px solid #f0f0f0;
  padding-bottom: 24px;
}

.preview-header h2 {
  margin: 0 0 8px 0;
  font-size: 28px;
  font-weight: 600;
}

.preview-header p {
  margin: 0;
  color: #666;
  font-size: 16px;
}

.preview-question {
  margin-bottom: 32px;
  padding: 24px;
  border: 1px solid #f0f0f0;
  border-radius: 8px;
  background: #fafafa;
}

.question-description {
  color: #666;
  margin-bottom: 16px;
  font-size: 14px;
}

.option-item {
  margin-bottom: 12px;
}

.other-input-container {
  margin-left: 24px;
}
</style>