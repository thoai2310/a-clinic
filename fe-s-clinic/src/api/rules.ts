import http from "@/utils/request.ts";
import type {AutoTagRuleGroup} from "@/types";

export const rulesApi = {
    create(params: AutoTagRuleGroup) {
        return http.post("/auto-tag-rule-groups", params);
    }
}