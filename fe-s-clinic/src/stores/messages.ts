import type {CreateMessageRequest, Message} from "@/types";
import {defineStore} from "pinia";
import {message} from "ant-design-vue";
import {messageApi, type MessageListParams} from "@/api/message.ts";

interface MessageState {
    messages: Message[],
    total: number,
    isLoading: boolean,
    currentMessage: Message | null
}

export const useMessageStore = defineStore('messages', {
    state: (): MessageState => ({
        messages: [],
        total: 0,
        isLoading: false,
        currentMessage: null,
    }),
    actions: {
        async fetchMessages(params: MessageListParams) {
            try {
                this.isLoading = true;
                const response = await messageApi.list(params);
                this.total = response.total;
                this.messages = response.list;
            } catch (e) {
                message.error('Get Message error');
            } finally {
                this.isLoading = false;
            }
        },
        async createMessage(params: CreateMessageRequest) {
            try {
                const response = await messageApi.createMessage(params);
                message.success('Create Message Success');
                return response;
            } catch (e) {
                message.error('Create Message Failed');
            }
        }
    }
})