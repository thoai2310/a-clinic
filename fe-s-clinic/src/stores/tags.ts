import type {CreateTagRequest, Tag, TagWithCustomer} from "@/types";
import {defineStore} from "pinia";
import {type TagListParams, tagsApi} from "@/api/tags.ts";
import {message} from "ant-design-vue";

interface TagState {
    tags: Tag[],
    total: number,
    isLoading: boolean,
    currentTag: Tag | null,
    all: TagWithCustomer[]
}

export const useTagStore = defineStore('tags', {
    state: (): TagState => ({
        tags: [],
        total: 0,
        isLoading: false,
        currentTag: null,
        all: []
    }),
    actions: {
        async fetchTags(params: TagListParams) {
            try {
                this.isLoading = true;
                const response = await tagsApi.list(params);
                this.tags = response.list;
                this.total = response.total;
            } catch (e) {
                message.error('Get tags error');
            } finally {
                this.isLoading = false;
            }
        },
        async createTag(params: CreateTagRequest) {
            try {
                const response = await tagsApi.createTag(params);
                message.success("Created new tag");
                return response;
            } catch (e) {
                message.error('Create the tag error');
            }
        },
        async getAll() {
            this.all = await tagsApi.all();
        }

    }
});