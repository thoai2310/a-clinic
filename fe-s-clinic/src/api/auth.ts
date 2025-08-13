import http from '@/utils/request'
import type { LoginForm, User, ApiResponse } from '@/types';

export interface LoginResponse {
    user: User,
    token: string,
    token_type: string,
    expires_in: number
}

export const authApi = {
    login(data: LoginForm): Promise<LoginResponse> {
        return http.post('/auth/login', data)
    },

    // get current user login info
    getCurrentUser(): Promise<User> {
        return http.get('/auth/me')
    },

    // user logout
    logout(): Promise<void> {
        return http.post('/auth/logout')
    }
}