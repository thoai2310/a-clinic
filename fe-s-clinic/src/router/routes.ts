import type { RouteRecordRaw } from 'vue-router'

export const routes: RouteRecordRaw[] = [
    {
        path: '/login',
        name: 'Login',
        component: () => import('@/views/Login.vue'),
        meta: {
            title: 'Đăng nhập',
            layout: 'blank'
        }
    },
    {
        path: '/',
        redirect: '/dashboard',
        component: () => import('@/layouts/MainLayout.vue'),
        meta: {
            requiresAuth: true
        },
        children: [
            {
                path: '/dashboard',
                name: 'Dashboard',
                component: () => import('@/views/Dashboard.vue'),
                meta: {
                    title: 'Dashboard',
                    icon: 'dashboard',
                    requiresAuth: true
                }
            },
            {
                path: '/users',
                name: 'Users',
                component: () => import('@/views/users/Users.vue'),
                meta: {
                    title: 'Quản lý người dùng',
                    icon: 'user',
                    requiresAuth: true,
                    roles: ['admin']
                }
            },
            {
                path: '/questions',
                name: 'Questions',
                component: () => import('@/views/questions/Questions.vue'),
                meta: {
                    title: 'Questions Management',
                    icon: 'question',
                    requiresAuth: true,
                    roles: ['admin']
                }
            },
            {
                path: '/questions/create',
                name: 'Creating Question',
                component: () => import('@/views/questions/Form.vue'),
                meta: {
                    title: 'Question Form',
                    icon: 'question',
                    requiresAuth: true,
                    roles: ['admin']
                }
            },
            {
                path: '/questions/:id/edit',
                name: 'EditQuestion',
                component: () => import('@/views/questions/Form.vue'),
                meta: {
                    title: 'Edit Question Form',
                    requiresAuth: true
                }
            },
            {
                path: '/tags',
                name: 'Tags',
                component: () => import('@/views/tags/Tags.vue'),
                meta: {
                    title: 'Edit Question Form',
                    requiresAuth: true
                }
            },
            {
                path: '/messages/create',
                name: 'CreateMessage',
                component: () => import('@/views/messages/Form.vue'),
                meta: {
                    title: 'Edit Question Form',
                    requiresAuth: true
                }
            },
            {
                path: '/messages',
                name: 'Messages',
                component: () => import('@/views/messages/Messages.vue'),
                meta: {
                    title: 'Questions',
                    requiresAuth: true
                }
            },
            {
                path: '/rules',
                name: 'Rules',
                component: () => import('@/views/rules/Rules.vue'),
                meta: {
                    title: 'Rules',
                    requiresAuth: true
                }
            },
            {
                path: '/rules/create',
                name: 'CreateRule',
                component: () => import('@/views/rules/Form.vue'),
                meta: {
                    title: 'Rules',
                    requiresAuth: true
                }
            }
        ]
    },
    {
        path: '/403',
        name: 'Forbidden',
        component: () => import('@/views/error/403.vue'),
        meta: {
            title: 'Không có quyền truy cập'
        }
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'NotFound',
        component: () => import('@/views/error/404.vue'),
        meta: {
            title: 'Không tìm thấy trang'
        }
    }
]