import type {FormSimple, QuestionForm} from "@/types";
import {defineStore} from "pinia";
import {type AssignToCustomerParams, type FormListParams, formsApi} from "@/api/forms.ts";
import {message} from "ant-design-vue";

interface FormsState {
    forms: QuestionForm[],
    total: number,
    isLoading: boolean,
    currentQuestionForm: QuestionForm | null,
    all: FormSimple[],
}

export const useQuestionFormStore = defineStore('forms', {
    state: (): FormsState => ({
        forms: [],
        total: 0,
        isLoading: false,
        currentQuestionForm: null,
        all: []
    }),
    actions: {
        async fetchQuestionForms(params: FormListParams) {
            try {
                this.isLoading = true;
                const response = await formsApi.getForms(params);
                this.forms = response.list;
                this.total = response.total;
            } catch (e: any) {
                message.error(e.message || 'Getting forms failed');
            } finally {
                this.isLoading = false;
            }
        },
        async assignToCustomers(params: AssignToCustomerParams) {
            try {
                await formsApi.assignToCustomers(params);
                message.success("Successfully Assigned To Customer");
            } catch (e) {
                message.error('AssignToCustomers failed');
            }
        },
        async getAll() {
            this.all = await formsApi.all();
        }


    }
})