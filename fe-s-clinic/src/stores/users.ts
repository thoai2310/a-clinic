import { defineStore } from 'pinia'
import { usersApi, type UserListParams, type UserListResponse } from '@/api/users'
import type { User } from '@/types'
import { message } from 'ant-design-vue'

interface UsersState {
    users: User[]
    total: number
    isLoading: boolean
    currentUser: User | null
}

export const useUsersStore = defineStore('users', {
    state: (): UsersState => ({
        users: [],
        total: 0,
        isLoading: false,
        currentUser: null
    }),

    actions: {
        async fetchUsers(params: UserListParams) {
            try {
                this.isLoading = true
                const response = await usersApi.getUsers(params)
                this.users = response.list
                this.total = response.total
            } catch (error: any) {
                message.error(error.message || 'Lấy danh sách người dùng thất bại')
            } finally {
                this.isLoading = false
            }
        },

        async createUser(userData: Partial<User>) {
            try {
                const newUser = await usersApi.createUser(userData)
                this.users.unshift(newUser)
                this.total += 1
                message.success('Tạo người dùng thành công')
                return newUser
            } catch (error: any) {
                message.error(error.message || 'Tạo người dùng thất bại')
                throw error
            }
        },

        async updateUser(id: number, userData: Partial<User>) {
            try {
                const updatedUser = await usersApi.updateUser(id, userData)
                const index = this.users.findIndex(user => user.id === id)
                if (index !== -1) {
                    this.users[index] = updatedUser
                }
                message.success('Cập nhật người dùng thành công')
                return updatedUser
            } catch (error: any) {
                message.error(error.message || 'Cập nhật người dùng thất bại')
                throw error
            }
        },

        async deleteUser(id: number) {
            try {
                await usersApi.deleteUser(id)
                this.users = this.users.filter(user => user.id !== id)
                this.total -= 1
                message.success('Xóa người dùng thành công')
            } catch (error: any) {
                message.error(error.message || 'Xóa người dùng thất bại')
                throw error
            }
        },

        async deleteUsers(ids: number[]) {
            try {
                await usersApi.deleteUsers(ids)
                this.users = this.users.filter(user => !ids.includes(user.id))
                this.total -= ids.length
                message.success(`Xóa ${ids.length} người dùng thành công`)
            } catch (error: any) {
                message.error(error.message || 'Xóa người dùng thất bại')
                throw error
            }
        },

        async fetchUser(id: number) {
            try {
                const user = await usersApi.getUser(id)
                this.currentUser = user
                return user
            } catch (error: any) {
                message.error(error.message || 'Lấy thông tin người dùng thất bại')
                throw error
            }
        },

        // Reset current user
        resetCurrentUser() {
            this.currentUser = null
        },

        // Update user in list without API call (for optimistic updates)
        updateUserInList(updatedUser: User) {
            const index = this.users.findIndex(user => user.id === updatedUser.id)
            if (index !== -1) {
                this.users[index] = updatedUser
            }
        },

        // Add user to list without API call
        addUserToList(newUser: User) {
            this.users.unshift(newUser)
            this.total += 1
        },

        // Remove user from list without API call
        removeUserFromList(userId: number) {
            this.users = this.users.filter(user => user.id !== userId)
            this.total -= 1
        }
    }
})