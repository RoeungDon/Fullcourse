<template>
    <div class="login-box">
        <div class="login-logo">
            <a href="#"><b>Admin</b>LTE</a>
        </div>
        <div class="card">
            <div class="card-body login-card-body">
                <p class="login-box-msg">Email verification</p>

                <div
                    v-if="status"
                    :class="{
                        'alert alert-success': status === 'success',
                        'alert alert-danger': status === 'error',
                    }"
                    role="alert"
                >
                    {{ message }}
                </div>

                <p v-else class="text-muted text-center mb-3">
                    Verifying your email…
                </p>

                <p class="mb-1">
                    <RouterLink :to="{ name: 'auth.signin' }">
                        Go back to login
                    </RouterLink>
                </p>
                <p class="mb-0">
                    <RouterLink :to="{ name: 'auth.signup' }">
                        Register a new membership
                    </RouterLink>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import axios from 'axios'
import { onMounted, onUnmounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import Swal from 'sweetalert2'

const route = useRoute()

const status = ref(null) // 'success' | 'error' | null
const message = ref('')

onMounted(async () => {
    document.body.classList.remove('sidebar-mini', 'layout-fixed')
    document.body.classList.add('hold-transition', 'login-page')

    // Laravel puts the signed backend URL here (see EmailVerificationNotification)
    const forwardedUrl = route.query['forwarded-url']

    if (!forwardedUrl) {
        status.value = 'error'
        message.value =
            'Missing verification link. Open the link from your email, or request a new one from the sign-up page.'
        return
    }

    try {
        Swal.fire({
            title: 'Verifying email…',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        })

        // Full absolute URL with expires + signature — do not strip query params
        const response = await axios.get(new URL(forwardedUrl).toString())

        status.value = 'success'
        message.value =
            response.data?.message || 'Your email has been verified successfully.'
    } catch (error) {
        status.value = 'error'
        message.value =
            error.response?.data?.message ||
            error.message ||
            'An error occurred during email verification.'
    } finally {
        Swal.close()
    }
})

onUnmounted(() => {
    document.body.classList.remove('hold-transition', 'login-page')
    document.body.classList.add('sidebar-mini', 'layout-fixed')
})
</script>