import http from '@/utils/request';
import type {CreateMessageRequest, Message} from "@/types";

export interface MessageListParams {
    page: number;
    pageSize: number;
}

export interface MessageListResponse {
    list: Message[],
    total: number;
    page: number;
    pageSize: number;
}



export const messageApi = {
    list(params: MessageListParams): Promise<MessageListResponse> {
        return http.get('/messages', {params});
    },
    createMessage(params: CreateMessageRequest): Promise<Message> {
        return http.post('/messages', params)
    }
}