import http from '@/utils/request'
import type {FormSimple, QuestionForm, User} from '@/types'

export interface FormListParams {
    page: number
    pageSize: number
    search?: string
    role?: string
    status?: string
}

export interface AssignToCustomerParams {
    form_id: number,
    customer_ids: Array<number>,
}

export interface FormListResponse {
    list: QuestionForm[]
    total: number
    page: number
    pageSize: number
}

export interface AllFromResponse {
    all: FormSimple[]
}

export const formsApi = {
    getForms(params: FormListParams): Promise<FormListResponse> {
        return http.get('/forms', { params })
    },
    assignToCustomers(params: AssignToCustomerParams) {
        return http.post('/forms/assign-to-customers',  params)
    },
    all(): Promise<FormSimple[]> {
        return http.get('/forms/all')
    }
}