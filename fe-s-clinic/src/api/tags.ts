import http from '@/utils/request'
import type {CreateTagRequest, FormSimple, Tag, TagWithCustomer} from "@/types";

export interface TagListParams {
    page: number
    pageSize: number
}

export interface TagResponse {
    list: Tag[]
    total: number
    page: number
    pageSize: number
}

export const tagsApi = {
    list(params: TagListParams): Promise<TagResponse> {
        return http.get('/tags', { params })
    },
    createTag(params: CreateTagRequest): Promise<Tag> {
        return http.post('/tags', params)
    },
    all(): Promise<TagWithCustomer[]> {
        return http.get('/tags/all')
    }
}