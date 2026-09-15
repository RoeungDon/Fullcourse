import { computed, ref } from 'vue'
import { defineStore } from 'pinia'

export const useAuthStore = defineStore(
  'auth',
  () => {
    const token = ref(null)
    const user = ref(null)

    const isAuthenticated = computed(() => !!token.value)

    function setAuth(payload) {
      // payload from signin: { user, token }
      token.value = payload.token ?? null
      user.value = payload.user ?? null
    }

    function clearAuth() {
      token.value = null
      user.value = null
    }

    return {
      token,
      user,
      isAuthenticated,
      setAuth,
      clearAuth,
    }
  },
  {
    persist: {
      // only keep what you need after refresh
      pick: ['token', 'user'],
    },
  },
)