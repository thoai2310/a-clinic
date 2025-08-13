import http from "@/utils/request.ts";
import type { QuestionForm, CreateQuestionRequest } from "@/types";

export const questionsApi = {
    create(data: CreateQuestionRequest) {
        return http.post("/forms", data);
    },

    update(id: number, data: CreateQuestionRequest): Promise<QuestionForm> {
        return http.put(`/forms/${id}`, data)
    },

    getById(id: number): Promise<QuestionForm> {
        return http.get(`/forms/${id}`)
    },

    delete(id: number): Promise<void> {
        return http.delete(`/forms/${id}`)
    }
}