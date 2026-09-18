<template>
    <div class="register-box">
        <div class="register-logo">
            <a href="#"><b>Admin</b>LTE</a>
        </div>
        <div class="card">
            <div class="card-body register-card-body">
                <p class="login-box-msg">Register a new membership</p>

                <form @submit.prevent="signUp">
                    <div class="input-group mb-3">
                        <input
                            type="text"
                            class="form-control"
                            placeholder="Full name"
                            v-model="form.name"
                            :class="{ 'is-invalid': !!formError.name }"
                        />
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                        <div class="invalid-feedback">{{ formError.name }}</div>
                    </div>

                    <div class="input-group mb-3">
                        <input
                            type="email"
                            class="form-control"
                            placeholder="Email"
                            v-model="form.email"
                            :class="{ 'is-invalid': !!formError.email }"
                        />
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                        <div class="invalid-feedback">{{ formError.email }}</div>
                    </div>

                    <div class="input-group mb-3">
                        <input
                            type="password"
                            class="form-control"
                            placeholder="Password"
                            v-model="form.password"
                            autocomplete="new-password"
                            :class="{ 'is-invalid': !!formError.password }"
                        />
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                        <div class="invalid-feedback">{{ formError.password }}</div>
                    </div>

                    <div class="input-group mb-3">
                        <input
                            type="password"
                            class="form-control"
                            placeholder="Retype password"
                            v-model="form.password_confirmation"
                            autocomplete="new-password"
                        />
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-8"></div>
                        <div class="col-4">
                            <button
                                type="submit"
                                class="btn btn-primary btn-block"
                                :disabled="loading"
                            >
                                {{ loading ? 'Registering...' : 'Register' }}
                            </button>
                        </div>
                    </div>
                </form>

                <p class="mt-3 mb-1">
                    <RouterLink :to="{ name: 'auth.signin' }" class="text-center">
                        I already have a membership
                    </RouterLink>
                </p>

                <div v-if="signedUpEmail" class="mt-3">
                    <hr />
                    <p>
                        Signed up with <strong>{{ signedUpEmail }}</strong>
                    </p>
                    <p class="mb-3">Didn't receive the verification email?</p>
                    <button
                        type="button"
                        class="btn btn-secondary btn-block"
                        :disabled="resending"
                        @click="resendVerificationEmail"
                    >
                        {{ resending ? 'Sending...' : 'Resend Verification Email' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, onUnmounted, reactive, ref } from 'vue'
import Swal from 'sweetalert2'
import { signup, sendVerificationEmail } from '@/functions/api/auth'

const loading = ref(false)
const resending = ref(false)
const signedUpEmail = ref('')

const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

const formError = reactive({
    name: '',
    email: '',
    password: '',
})

function clearFormErrors() {
    formError.name = ''
    formError.email = ''
    formError.password = ''
}

function resetForm() {
    form.name = ''
    form.email = ''
    form.password = ''
    form.password_confirmation = ''
    clearFormErrors()
}

async function signUp() {
    loading.value = true
    signedUpEmail.value = ''
    clearFormErrors()

    try {
        const { data } = await signup({
            name: form.name,
            email: form.email,
            password: form.password,
            password_confirmation: form.password_confirmation,
        })

        // Keep email for resend; clear password fields
        signedUpEmail.value = form.email
        resetForm()

        await Swal.fire({
            icon: 'success',
            title: 'Account created',
            text:
                data.message ||
                'Check your inbox for a verification email before signing in.',
        })
    } catch (error) {
        const status = error.response?.status
        const payload = error.response?.data

        if (status === 422 && payload?.errors) {
            formError.name = payload.errors.name?.[0] || ''
            formError.email = payload.errors.email?.[0] || ''
            formError.password = payload.errors.password?.[0] || ''
            return
        }

        await Swal.fire({
            icon: 'error',
            title: 'Sign up failed',
            text: payload?.message || error.message || 'Could not create account.',
        })
    } finally {
        loading.value = false
    }
}

async function resendVerificationEmail() {
    if (!signedUpEmail.value) return

    resending.value = true
    try {
        const { data } = await sendVerificationEmail(signedUpEmail.value)
        await Swal.fire({
            icon: 'success',
            title: 'Email sent',
            text: data.message || 'Verification email has been resent.',
        })
    } catch (error) {
        const payload = error.response?.data
        await Swal.fire({
            icon: 'error',
            title: 'Could not resend',
            text: payload?.message || error.message || 'Please try again later.',
        })
    } finally {
        resending.value = false
    }
}

onMounted(() => {
    document.body.classList.remove('sidebar-mini', 'layout-fixed')
    document.body.classList.add('hold-transition', 'register-page')
})

onUnmounted(() => {
    document.body.classList.remove('hold-transition', 'register-page')
    document.body.classList.add('sidebar-mini', 'layout-fixed')
})
</script>