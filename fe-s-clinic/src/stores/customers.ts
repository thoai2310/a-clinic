import type {Customer} from "@/types";
import {defineStore} from "pinia";
import {customerApi} from "@/api/customers.ts";

interface CustomersState {
    all: Customer[]
}

export const useCustomerStore = defineStore('customers', {
    state: () : CustomersState => ({
        all: []
    }),
    actions: {
        async getAll() {
            const allCustomer = await customerApi.getAll();
            this.all = allCustomer;
        }
    }
})