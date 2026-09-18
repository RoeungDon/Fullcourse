<template>
    <div class="login-box">
        <div class="login-logo">
            <a href="#"><b>Admin</b>LTE</a>
        </div>
        <div class="card">
            <div class="card-body login-card-body">
                <p class="login-box-msg">Sign in to start your session</p>

                <form @submit.prevent="signIn">
                    <div class="input-group mb-3">
                        <input
                            type="email"
                            class="form-control"
                            placeholder="Email"
                            v-model="form.email"
                        />
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input
                            type="password"
                            class="form-control"
                            placeholder="Password"
                            v-model="form.password"
                        />
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-8">
                            <div class="icheck-primary">
                                <input type="checkbox" id="remember" />
                                <label for="remember">Remember Me</label>
                            </div>
                        </div>
                        <div class="col-4">
                            <button
                                type="submit"
                                class="btn btn-primary btn-block"
                                :disabled="loading"
                            >
                                {{ loading ? 'Signing in...' : 'Sign In' }}
                            </button>
                        </div>
                    </div>
                </form>

                <div class="social-auth-links text-center mb-3">
                    <p>- OR -</p>
                    <a href="#" class="btn btn-block btn-primary">
                        <i class="fab fa-facebook mr-2"></i> Sign in using Facebook
                    </a>
                    <a href="#" class="btn btn-block btn-danger">
                        <i class="fab fa-google-plus mr-2"></i> Sign in using Google+
                    </a>
                </div>

                <p class="mb-1">
                    <RouterLink :to="{ name: 'auth.reset-password' }">
                         I forgot my password
                    </RouterLink>
                </p>
                <p class="mb-0">
                    <RouterLink to="/signup" class="text-center">
                        Register a new membership
                    </RouterLink>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, onUnmounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import Swal from 'sweetalert2'
import { signin } from '@/functions/api/auth'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth = useAuthStore()

const loading = ref(false)

const form = reactive({
    email: '',
    password: '',
})

async function signIn() {
    loading.value = true

    try {
        const { data } = await signin({
            email: form.email,
            password: form.password,
        })

        auth.setAuth({
            user: data.user,
            token: data.token,
        })

        await Swal.fire({
            icon: 'success',
            title: 'Signed in',
            text: data.message || 'Welcome back.',
            timer: 1500,
            showConfirmButton: false,
        })

        router.push({ name: 'dashboard' })
    } catch (error) {
        const status = error.response?.status
        const payload = error.response?.data

        let message = 'Could not sign in. Please try again.'

        if (status === 422 && payload?.errors) {
            message = Object.values(payload.errors).flat().join('\n')
        } else if (payload?.message) {
            message = payload.message
        }

        await Swal.fire({
            icon: 'error',
            title: 'Sign in failed',
            text: message,
        })
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    document.body.classList.remove('sidebar-mini', 'layout-fixed')
    document.body.classList.add('hold-transition', 'login-page')
})

onUnmounted(() => {
    document.body.classList.remove('hold-transition', 'login-page')
    document.body.classList.add('sidebar-mini', 'layout-fixed')
})
</script>