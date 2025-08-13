<template>
  <div class="login-container">
    <!-- Left side with illustration -->
    <div class="login-left">
      <div class="login-illustration">
        <!-- SVG illustration similar to the uploaded image -->
        <svg viewBox="0 0 500 400" xmlns="http://www.w3.org/2000/svg">
          <!-- Isometric platform -->
          <g transform="translate(150, 200)">
            <!-- Base platform -->
            <polygon
                points="0,50 100,0 200,50 100,100"
                fill="url(#platformGradient)"
                stroke="#4a90e2"
                stroke-width="2"
            />

            <!-- Central cylinder -->
            <ellipse cx="100" cy="50" rx="30" ry="15" fill="url(#cylinderGradient)" />
            <rect x="70" y="35" width="60" height="30" fill="url(#cylinderSideGradient)" />
            <ellipse cx="100" cy="35" rx="30" ry="15" fill="url(#cylinderTopGradient)" />

            <!-- Floating elements -->
            <g transform="translate(20, -20)">
              <rect width="25" height="25" rx="5" fill="#4a90e2" opacity="0.8" />
              <rect x="5" y="5" width="15" height="15" rx="3" fill="#fff" opacity="0.9" />
            </g>

            <g transform="translate(140, -30)">
              <rect width="30" height="20" rx="4" fill="#7b68ee" opacity="0.8" />
              <rect x="5" y="3" width="20" height="14" rx="2" fill="#fff" opacity="0.9" />
            </g>

            <!-- Data visualization elements -->
            <g transform="translate(80, -10)">
              <rect x="0" y="15" width="4" height="15" fill="#4a90e2" />
              <rect x="6" y="10" width="4" height="20" fill="#7b68ee" />
              <rect x="12" y="5" width="4" height="25" fill="#4a90e2" />
              <rect x="18" y="12" width="4" height="18" fill="#7b68ee" />
            </g>
          </g>

          <!-- Gradient definitions -->
          <defs>
            <linearGradient id="platformGradient" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" style="stop-color:#667eea;stop-opacity:1" />
              <stop offset="100%" style="stop-color:#764ba2;stop-opacity:1" />
            </linearGradient>

            <linearGradient id="cylinderGradient" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" style="stop-color:#4a90e2;stop-opacity:1" />
              <stop offset="100%" style="stop-color:#357abd;stop-opacity:1" />
            </linearGradient>

            <linearGradient id="cylinderSideGradient" x1="0%" y1="0%" x2="100%" y2="0%">
              <stop offset="0%" style="stop-color:#357abd;stop-opacity:1" />
              <stop offset="100%" style="stop-color:#4a90e2;stop-opacity:1" />
            </linearGradient>

            <linearGradient id="cylinderTopGradient" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" style="stop-color:#5ba3f5;stop-opacity:1" />
              <stop offset="100%" style="stop-color:#4a90e2;stop-opacity:1" />
            </linearGradient>
          </defs>
        </svg>
      </div>
    </div>

    <!-- Right side with login form -->
    <div class="login-right">
      <div class="login-form-container">
        <div class="login-header">
          <div class="login-title">Welcome Back 👋</div>
          <div class="login-subtitle">Enter your account details to manage your projects</div>
        </div>

        <a-form
            :model="loginForm"
            :rules="rules"
            @finish="handleLogin"
            class="login-form"
            layout="vertical"
        >
          <a-form-item name="email" label="">
            <a-input
                v-model:value="loginForm.email"
                placeholder="Email"
                size="large"
                :prefix="h(UserOutlined)"
            />
          </a-form-item>

          <a-form-item name="password" label="">
            <a-input-password
                v-model:value="loginForm.password"
                placeholder="Password"
                size="large"
                :prefix="h(LockOutlined)"
            />
          </a-form-item>

          <div class="login-options">
            <a-checkbox v-model:checked="rememberMe">Remember Me</a-checkbox>
            <a href="#" style="color: #1890ff;">Forgot Password?</a>
          </div>

          <a-form-item>
            <a-button
                type="primary"
                html-type="submit"
                size="large"
                :loading="authStore.isLoading"
                block
            >
              Login
            </a-button>
          </a-form-item>
        </a-form>

        <div class="create-account">
          Don't have an account? <a href="#">Create Account</a>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, h } from 'vue'
import { UserOutlined, LockOutlined } from '@ant-design/icons-vue'
import { useAuthStore } from '@/stores/auth'
import type { LoginForm } from '@/types'

const authStore = useAuthStore()

const loginForm = reactive<LoginForm>({
  email: '',
  password: ''
})

const rememberMe = ref(false)

const rules = {
  email: [
    { required: true, message: 'Vui lòng nhập email!' },
    { type: 'email', message: 'Email không hợp lệ!' }
  ],
  password: [
    { required: true, message: 'Vui lòng nhập mật khẩu!' },
    { min: 6, message: 'Mật khẩu phải có ít nhất 6 ký tự!' }
  ]
}

const handleLogin = async () => {
  await authStore.login(loginForm)
}
</script>