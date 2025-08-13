import http from '@/utils/request'
import type { User } from '@/types'

export interface UserListParams {
    page: number
    pageSize: number
    search?: string
    role?: string
    status?: string
}

export interface UserListResponse {
    list: User[]
    total: number
    page: number
    pageSize: number
}

export const usersApi = {
    // Lấy danh sách users
    getUsers(params: UserListParams): Promise<UserListResponse> {
        return http.get('/users', { params })
    },

    // Lấy chi tiết user
    getUser(id: number): Promise<User> {
        return http.get(`/users/${id}`)
    },

    // Tạo user mới
    createUser(data: Partial<User>): Promise<User> {
        return http.post('/users', data)
    },

    // Cập nhật user
    updateUser(id: number, data: Partial<User>): Promise<User> {
        return http.put(`/users/${id}`, data)
    },

    // Xóa user
    deleteUser(id: number): Promise<void> {
        return http.delete(`/users/${id}`)
    },

    // Xóa nhiều users
    deleteUsers(ids: number[]): Promise<void> {
        return http.delete('/users/batch', { data: { ids } })
    }
}