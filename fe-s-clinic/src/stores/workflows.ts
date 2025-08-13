import type {Workflow} from "@/types";
import {defineStore} from "pinia";
import {workflowApi} from "@/api/workflows.ts";
import {message} from "ant-design-vue";

interface WorkflowState {
    workflows: Workflow[];
    currentWorkflow: Workflow | null;
}

export const useWorkflowStore = defineStore('workflows', {
    state: (): WorkflowState => ({
        workflows: [],
        currentWorkflow: null,
    }),
    actions: {
        async create(params: Workflow) {
            try {
                await workflowApi.create(params);
                message.success('Create workflow Success');
            } catch (e) {
                message.error('Create workflow Failed');
            }
        }
    }
})