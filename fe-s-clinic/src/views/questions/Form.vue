<template>
  <div class="question-form-page">
    <div class="form-header">
      <div class="header-content">
        <a-button type="text" @click="goBack" class="back-button">
          <template #icon>
            <ArrowLeftOutlined/>
          </template>
          Back
        </a-button>

        <div v-if="questionsStore.currentForm" class="form-title-section">
          <a-input
              v-model:value="questionsStore.currentForm.title"
              placeholder="Untitled Form"
              size="large"
              class="form-title-input"
              @blur="handleFormMetaUpdate"
          />
          <a-textarea
              v-model:value="questionsStore.currentForm.description"
              placeholder="Form description (optional)"
              :rows="2"
              class="form-description-input"
              @blur="handleFormMetaUpdate"
          />
        </div>

        <div class="header-actions">
          <!-- Draft indicator -->
          <div v-if="showDraftIndicator" class="draft-indicator">
            <a-tag color="orange">
              <template #icon>
                <SaveOutlined/>
              </template>
              Draft saved
            </a-tag>
          </div>

          <a-button
              @click="previewForm"
              style="margin-right: 8px;"
              :disabled="!questionsStore.currentForm"
          >
            <template #icon>
              <EyeOutlined/>
            </template>
            Preview
          </a-button>
          <a-button
              type="primary"
              @click="saveForm"
              :loading="questionsStore.isSaving"
              :disabled="!questionsStore.currentForm"
          >
            <template #icon>
              <SaveOutlined/>
            </template>
            {{ isEditing ? 'Update' : 'Save' }}
          </a-button>
        </div>
      </div>
    </div>

    <div class="form-content">
      <a-spin :spinning="questionsStore.isLoading">
        <div v-if="questionsStore.currentForm" class="questions-container">
          <QuestionItem
              v-for="(question, index) in questionsStore.currentForm.questions"
              :key="question.id"
              :question="question"
              :index="index"
              :is-only-question="questionsStore.currentForm.questions.length <= 1"
              @update="questionsStore.updateQuestion"
              @update-option="questionsStore.updateOption"
              @add-option="questionsStore.addOption"
              @add-other-option="questionsStore.addOtherOption"
              @remove-option="questionsStore.removeOption"
              @remove="questionsStore.removeQuestion(question.id)"
              @duplicate="questionsStore.duplicateQuestion(question.id)"
          />

          <div class="add-question-section">
            <a-button
                type="dashed"
                size="large"
                @click="questionsStore.addQuestion"
                class="add-question-button"
            >
              <template #icon>
                <PlusOutlined/>
              </template>
              Add Question
            </a-button>
          </div>
        </div>

        <!-- Loading state -->
        <div v-else-if="questionsStore.isLoading" class="loading-container">
          <a-spin size="large"/>
        </div>

        <!-- Empty state -->
        <div v-else class="empty-container">
          <a-empty description="Initializing form..."/>
        </div>
      </a-spin>
    </div>

    <!-- Preview Modal -->
    <a-modal
        v-model:open="previewVisible"
        title="Form Preview"
        width="800px"
        :footer="null"
    >
      <FormPreview
          v-if="questionsStore.currentForm"
          :form="questionsStore.currentForm"
      />
    </a-modal>

    <!-- Draft Recovery Modal -->
    <a-modal
        v-model:open="draftRecoveryVisible"
        title="Recover Draft"
        width="400px"
        :closable="false"
        :mask-closable="false"
    >
      <div style="text-align: center; padding: 20px 0;">
        <div style="color: #1890ff; font-size: 48px; margin-bottom: 16px;">
          <InfoCircleOutlined />
        </div>
        <h3>Found unsaved draft</h3>
        <p>We found an unsaved draft from your previous session. Would you like to recover it?</p>
      </div>

      <template #footer>
        <a-button @click="discardDraft">
          Discard Draft
        </a-button>
        <a-button type="primary" @click="recoverDraft">
          Recover Draft
        </a-button>
      </template>
    </a-modal>
  </div>
</template>

<script setup lang="ts">
import {ref, computed, onMounted, onUnmounted, onBeforeUnmount} from 'vue'
import {useRoute, useRouter} from 'vue-router'
import {useQuestionsStore} from '@/stores/questions'
import QuestionItem from '@/components/QuestionItem.vue'
import FormPreview from '@/components/FormPreview.vue'
import {
  ArrowLeftOutlined,
  PlusOutlined,
  SaveOutlined,
  EyeOutlined,
  InfoCircleOutlined
} from '@ant-design/icons-vue'

const route = useRoute()
const router = useRouter()
const questionsStore = useQuestionsStore()

const previewVisible = ref(false)
const draftRecoveryVisible = ref(false)
const showDraftIndicator = ref(false)
let draftIndicatorTimer: number | null = null

const isEditing = computed(() => !!route.params.id)

const goBack = () => {
  // Confirm if there are unsaved changes
  if (!isEditing.value && questionsStore.currentForm && !questionsStore.currentForm.id) {
    const hasContent = questionsStore.currentForm.title ||
        questionsStore.currentForm.description ||
        questionsStore.currentForm.questions.some(q => q.title || q.description)

    if (hasContent) {
      if (confirm('You have unsaved changes. Are you sure you want to leave?')) {
        router.go(-1)
      }
    } else {
      router.go(-1)
    }
  } else {
    router.go(-1)
  }
}

const saveForm = async () => {
  if (!questionsStore.currentForm) return

  const result = await questionsStore.saveForm();
  await router.push('/questions');
  if (result && result.success && !isEditing.value) {
    // Redirect to edit mode after successful creation
    // router.replace(`/questions/${questionsStore.currentForm?.id}/edit`)
  }
}

const previewForm = () => {
  if (!questionsStore.currentForm) return
  previewVisible.value = true
}

const handleFormMetaUpdate = () => {
  if (questionsStore.currentForm) {
    questionsStore.updateFormMeta({
      title: questionsStore.currentForm.title,
      description: questionsStore.currentForm.description
    })
    showDraftSaved()
  }
}

const showDraftSaved = () => {
  showDraftIndicator.value = true

  if (draftIndicatorTimer) {
    window.clearTimeout(draftIndicatorTimer)
  }

  draftIndicatorTimer = window.setTimeout(() => {
    showDraftIndicator.value = false
  }, 2000)
}

const recoverDraft = () => {
  // Draft đã được load trong initNewForm
  draftRecoveryVisible.value = false
}

const discardDraft = () => {
  questionsStore.clearDraft()
  questionsStore.initNewForm()
  draftRecoveryVisible.value = false
}

// Handle browser beforeunload
const handleBeforeUnload = (e: BeforeUnloadEvent) => {
  if (!isEditing.value && questionsStore.currentForm && !questionsStore.currentForm.id) {
    const hasContent = questionsStore.currentForm.title ||
        questionsStore.currentForm.description ||
        questionsStore.currentForm.questions.some(q => q.title || q.description)

    if (hasContent) {
      e.preventDefault()
      // e.returnValue = ''
    }
  }
}

onMounted(async () => {
  try {
    if (isEditing.value) {
      const id = Number(route.params.id)
      if (isNaN(id)) {
        router.push('/questions/create')
        return
      }
      await questionsStore.loadForm(id)
    } else {
      // Check for existing draft
      if (questionsStore.hasDraft()) {
        draftRecoveryVisible.value = true
      }
      questionsStore.initNewForm()
    }

    // Add beforeunload listener
    window.addEventListener('beforeunload', handleBeforeUnload)
  } catch (error) {
    console.error('Error initializing form:', error)
    router.push('/questions/create')
  }
})

onBeforeUnmount(() => {
  if (draftIndicatorTimer) {
    window.clearTimeout(draftIndicatorTimer)
  }
})

onUnmounted(() => {
  questionsStore.resetForm()
  window.removeEventListener('beforeunload', handleBeforeUnload)
})
</script>

<style scoped>
.question-form-page {
  min-height: 100vh;
  background: #f0f2f5;
}

.form-header {
  background: #fff;
  border-bottom: 1px solid #e8e8e8;
  position: sticky;
  top: 0;
  z-index: 100;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.header-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 16px 24px;
  display: flex;
  align-items: flex-start;
  gap: 24px;
}

.back-button {
  margin-top: 8px;
}

.form-title-section {
  flex: 1;
}

.form-title-input {
  font-size: 24px;
  font-weight: 600;
  border: none;
  box-shadow: none;
  padding: 0;
  margin-bottom: 8px;
}

.form-title-input:focus {
  border: none;
  box-shadow: 0 2px 0 #1890ff;
}

.form-description-input {
  border: none;
  box-shadow: none;
  padding: 0;
  resize: none;
}

.form-description-input:focus {
  border: none;
  box-shadow: 0 2px 0 #1890ff;
}

.header-actions {
  margin-top: 8px;
  display: flex;
  align-items: center;
  gap: 12px;
}

.draft-indicator {
  animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.form-content {
  max-width: 800px;
  margin: 0 auto;
  padding: 32px 24px;
}

.questions-container {
  background: transparent;
}

.loading-container,
.empty-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 200px;
}

.add-question-section {
  text-align: center;
  margin-top: 32px;
}

.add-question-button {
  width: 200px;
  height: 48px;
  border: 2px dashed #d9d9d9;
  border-radius: 8px;
  font-size: 16px;
}

.add-question-button:hover {
  border-color: #1890ff;
  color: #1890ff;
}

@media (max-width: 768px) {
  .header-content {
    flex-direction: column;
    gap: 16px;
  }

  .form-content {
    padding: 24px 16px;
  }
}
</style>