export interface User {
    id: number
    email: string
    name: string
    avatar?: string
    role: string
    status?: string
    createdAt?: string
}

export interface Customer {
    id: number,
    name: string,
    phone: string,
}

export interface LoginForm {
    email: string
    password: string
}

export interface ApiResponse<T = any> {
    code: number
    message: string
    data: T
}

export interface RouteItem {
    path: string
    name: string
    component?: any
    meta?: RouteMeta
    children?: RouteItem[]
}

export interface RouteMeta {
    title: string
    icon?: string
    requiresAuth?: boolean
    roles?: string[]
    layout?: string
}

export interface DashboardStats {
    users: {
        total: number
        active: number
    }
    visits: {
        total: number
        today: number
    }
    downloads: {
        total: number
        today: number
    }
    usage: {
        total: number
        today: number
    }
}

export interface QuestionOption {
    id: string;
    text: string;
    value: string;
    isOther?: boolean;
    question_id?: string,
    order?: number
}

export interface Question {
    id: string;
    title: string;
    description: string;
    type: 'text' | 'radio' | 'checkbox';
    required: boolean;
    options: QuestionOption[];
    hasOtherOption?: boolean;
    code: string
}

export interface QuestionForm {
    id?: number;
    title: string;
    description?: string;
    questions: Question[];
    assignCustomerIds?: Array<number>;
}

export interface FormSimple {
    id: number;
    title: string;
}

export interface CreateQuestionRequest {
    title: string;
    description?: string;
    questions: Omit<Question, 'id'>[]
}

export interface Tag {
    id?: number;
    name: string;
    key: string;
    customerIds?: Array<number>;
}

export interface CreateTagRequest {
    name: string;
    key: string;
    customerIds?: Array<number>;
}

export interface TagWithCustomer {
    id: number;
    name: string;
    key: string;
    customers?: Customer[];
}

export interface CreateMessageRequest {
    title: string;
    content: string;
    forms?: string,
    tags?: Array<number>,
    customerIds?: Array<number>
}

export interface Message {
    id: number;
    title: string;
    content: string;
    forms?: string;
    tags?: Array<number>;
    customerIds?: Array<number>;
}

export interface AutoTagRuleCondition {
    question_id: string;
    question_option_id?: number | null;
    condition_type: 'equals' | 'contains' | 'starts_with' | 'ends_with' | 'in_range' | 'in_list';
    condition_value?: {
        value?: string;
        min?: number | null;
        max?: number | null;
        values?: string[];
    } | null;
}

export interface AutoTagRuleGroup {
    id?: number;
    name: string;
    form_id?: number | null;
    tag_id?: number | null;
    logic_operator: 'AND' | 'OR';
    status?: number;
    description?: string;
    conditions: AutoTagRuleCondition[];
}

// export interface Form {
//     id: number;
//     title: string;
//     code: string;
//     status: string;
//     created_at: string;
// }

export interface AutoTagRuleGroupResponse extends AutoTagRuleGroup {
    id: number;
    created_at: string;
    updated_at: string;
    form: QuestionForm;
    tag: Tag;
    conditions: AutoTagRuleConditionResponse[];
}

export interface AutoTagRuleConditionResponse extends AutoTagRuleCondition {
    id: number;
    rule_group_id: number;
    order: number;
    question: Question;
    question_option?: QuestionOption;
}

export interface Step {
    id?: number;
    title: string;
    description?: string;
    completion_message?: string;
}

export interface Workflow {
    id: number;
    name: string;
    description?: string;
    steps?: Step[];
}



// Extend Vue Router types
declare module 'vue-router' {
    interface RouteMeta {
        title?: string
        icon?: string
        requiresAuth?: boolean
        roles?: string[]
        layout?: string
    }
}