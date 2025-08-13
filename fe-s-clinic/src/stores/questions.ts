import {defineStore} from 'pinia'
import {questionsApi} from '@/api/questions'
import type {QuestionForm, Question, QuestionOption, CreateQuestionRequest} from '@/types'
import {message} from 'ant-design-vue'

// Generate UUID function (simple implementation)
function generateId(): string {
    return Date.now().toString(36) + Math.random().toString(36).substr(2)
}

interface QuestionsState {
    currentForm: QuestionForm | null
    isLoading: boolean
    isSaving: boolean
}

const STORAGE_KEY = 'question_form_draft';

export const useQuestionsStore = defineStore('questions', {
    state: (): QuestionsState => ({
        currentForm: null,
        isLoading: false,
        isSaving: false
    }),

    actions: {
        // init a new form
        initNewForm() {
            const draft = this.loadDraft();
            if (draft) {
                this.currentForm = draft;
                message.info('Recover draft form');
            } else {
                this.currentForm = {
                    title: '',
                    description: '',
                    questions: [this.createDefaultQuestion()]
                }
            }

            this.startAutoSave();
        },

        // Tạo question mặc định
        createDefaultQuestion(): Question {
            return {
                id: generateId(),
                title: '',
                description: '',
                type: 'text',
                required: false,
                options: [],
                hasOtherOption: false,
            }
        },

        // init a default option
        createDefaultOption(): QuestionOption {
            return {
                id: generateId(),
                text: '',
                value: '',
                isOther: false,
            }
        },

        // create an "Other" option
        createOtherOption(): QuestionOption {
            return {
                id: generateId(),
                text: 'Other...',
                value: 'other',
                isOther: true,
            }
        },

        // add a new question
        addQuestion() {
            if (this.currentForm) {
                this.currentForm.questions.push(this.createDefaultQuestion());
                this.saveDraft();
            }
        },

        // Xóa question
        removeQuestion(questionId: string) {
            if (this.currentForm) {
                this.currentForm.questions = this.currentForm.questions.filter(q => q.id !== questionId)

                // Đảm bảo luôn có ít nhất 1 question
                if (this.currentForm.questions.length === 0) {
                    this.currentForm.questions.push(this.createDefaultQuestion())
                }
                this.saveDraft();
            }
        },

        // Duplicate question
        duplicateQuestion(questionId: string) {
            if (this.currentForm) {
                const question = this.currentForm.questions.find(q => q.id === questionId)
                if (question) {
                    const duplicated: Question = {
                        ...question,
                        id: generateId(),
                        title: question.title + ' (Copy)',
                        options: question.options.map(opt => ({
                            ...opt,
                            id: generateId()
                        }))
                    }
                    const index = this.currentForm.questions.findIndex(q => q.id === questionId)
                    this.currentForm.questions.splice(index + 1, 0, duplicated);
                    this.saveDraft();
                }
            }
        },

        // add an option into a question
        addOption(questionId: string) {
            if (this.currentForm) {
                const question = this.currentForm.questions.find(q => q.id === questionId)
                if (question) {
                    question.options.push(this.createDefaultOption());
                    this.saveDraft();
                }
            }
        },

        // add an "Other" option
        addOtherOption(questionId: string) {
            if (this.currentForm) {
                const question = this.currentForm.questions.find(q => q.id === questionId);
                if (question && !question.hasOtherOption) {
                    question.options.push(this.createOtherOption());
                    question.hasOtherOption = true;
                    this.saveDraft();
                }
            }
        },

        // delete an option
        removeOption(questionId: string, optionId: string) {
            if (this.currentForm) {
                const question = this.currentForm.questions.find(q => q.id === questionId)
                if (question) {
                    // check the deleted option is an "Other" option?
                    const optionToRemove = question.options.find(opt => opt.id === optionId);
                    if (optionToRemove?.isOther) {
                        question.hasOtherOption = false;
                    }
                    question.options = question.options.filter(opt => opt.id !== optionId);
                    this.saveDraft();
                }
            }
        },

        // update question
        updateQuestion(questionId: string, updates: Partial<Question>) {
            if (this.currentForm) {
                const questionIndex = this.currentForm.questions.findIndex(q => q.id === questionId)
                if (questionIndex !== -1) {
                    this.currentForm.questions[questionIndex] = {
                        ...this.currentForm.questions[questionIndex],
                        ...updates
                    }

                    // Nếu thay đổi type, xử lý options
                    if (updates.type) {
                        if (updates.type === 'text') {
                            this.currentForm.questions[questionIndex].options = [];
                            this.currentForm.questions[questionIndex].hasOtherOption = false;
                        } else if ((updates.type === 'radio' || updates.type === 'checkbox')) {
                            if (this.currentForm.questions[questionIndex].options.length === 0) {
                                this.currentForm.questions[questionIndex].options = [
                                    this.createDefaultOption(),
                                    this.createDefaultOption()
                                ];
                                this.currentForm.questions[questionIndex].hasOtherOption = false;
                            }
                        }
                    }
                    this.saveDraft();
                }
            }
        },

        // Cập nhật option
        updateOption(questionId: string, optionId: string, updates: Partial<QuestionOption>) {
            if (this.currentForm) {
                const question = this.currentForm.questions.find(q => q.id === questionId)
                if (question) {
                    const optionIndex = question.options.findIndex(opt => opt.id === optionId)
                    if (optionIndex !== -1) {
                        question.options[optionIndex] = {
                            ...question.options[optionIndex],
                            ...updates
                        }
                        this.saveDraft();
                    }
                }
            }
        },

        updateFormMeta(updates: { title?: string, description?: string }[]) {
            if (this.currentForm) {
                Object.assign(this.currentForm, updates);
                this.saveDraft();
            }
        },

        // save a form
        async saveForm() {
            if (!this.currentForm) return

            try {
                this.isSaving = true

                const requestData: CreateQuestionRequest = {
                    title: this.currentForm.title,
                    description: this.currentForm.description,
                    questions: this.currentForm.questions.map(q => ({
                        title: q.title,
                        description: q.description,
                        type: q.type,
                        required: q.required,
                        options: q.options,
                        hasOtherOption: q.hasOtherOption,
                    }))
                }


                if (this.currentForm.id) {
                    await questionsApi.update(this.currentForm.id, requestData)
                    message.success('Cập nhật form thành công!')
                } else {
                    const result = await questionsApi.create(requestData)
                    this.currentForm.id = result.id
                    message.success('Tạo form thành công!')
                }

                this.clearDraft();

                return {success: true}
            } catch (error: any) {
                message.error(error.message || 'Lưu form thất bại')
                return {success: false, error: error.message}
            } finally {
                this.isSaving = false
            }
        },

        // Load form để edit
        async loadForm(id: number) {
            try {
                this.isLoading = true
                this.currentForm = await questionsApi.getById(id);
                this.clearDraft();
            } catch (error: any) {
                message.error(error.message || 'Lấy thông tin form thất bại')
                throw error
            } finally {
                this.isLoading = false
            }
        },

        // Reset form
        resetForm() {
            this.currentForm = null;
            this.stopAutoSave();
        },

        // auto save
        autoSaveInterval: null as number | null,

        startAutoSave() {
            if (this.autoSaveInterval) return;

            this.autoSaveInterval = window.setInterval(() => {
                if (this.currentForm && !this.currentForm.id) {
                    this.saveDraft();
                }
            }, 10 * 1000)
        },

        stopAutoSave() {
            if (this.autoSaveInterval) {
                window.clearInterval(this.autoSaveInterval);
                this.autoSaveInterval = null;
            }
        },

        saveDraft() {
            if (this.currentForm && !this.currentForm.id) {
                try {
                    const draftData = {
                        ...this.currentForm,
                        lastSaved: new Date().toISOString(),
                    }
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(draftData))
                } catch (e) {
                    console.error('Failed to save draft:', e);
                }
            }
        },

        loadDraft() {
            try {
                const draftStr = localStorage.getItem(STORAGE_KEY) ? localStorage.getItem(STORAGE_KEY) : '';
                if (draftStr) {
                    const draft = JSON.parse(draftStr);
                    const lastSaved = new Date();
                    const now = new Date();
                    const daysDiff = (now.getTime() - lastSaved.getTime()) / (1000 * 3600 * 24)
                    if (daysDiff > 7) {
                        this.clearDraft();
                        return null;
                    }

                    delete draft.lastSaved;
                    return draft;
                }
                return null;
            } catch (e) {
                return null;
            }
        },

        clearDraft() {
            try {
                localStorage.removeItem(STORAGE_KEY);
            } catch (e) {
                console.error('Failed to clear draft:', e);
            }
        },

        hasDraft() {
            try {
                const draftStf = localStorage.getItem(STORAGE_KEY);
                return !!draftStf;
            } catch (e) {
                return false;
            }
        }


    }
})