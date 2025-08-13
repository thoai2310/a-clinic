import type {Workflow} from "@/types";
import http from '@/utils/request.ts';

export const workflowApi = {
    create(params: Workflow) {
        return http.post('/workflows', params);
    }
}