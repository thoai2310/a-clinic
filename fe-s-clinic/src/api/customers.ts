import type {Customer} from "@/types";
import http from "@/utils/request.ts";



export const customerApi = {
    getAll(): Promise<Customer[]> {
        return http.get('/customers/all')
    }
}