import {defineStore} from "pinia";
import type {AutoTagRuleGroup} from "@/types";
import {message} from "ant-design-vue";
import {rulesApi} from "@/api/rules.ts";

interface RuleStage {
    rules: AutoTagRuleGroup[]
}

export const useRuleStore = defineStore('rules', {
    state: () : RuleStage => ({
        rules: []
    }),
    actions: {
        async create(params: AutoTagRuleGroup) {
            try {
              await rulesApi.create(params);
              message.success('Create Rule Success');
            } catch (e) {
                message.error('Create Rule Fail');
            }
        }
    }
})