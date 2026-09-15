<script setup>
import { ref, onMounted } from 'vue'

// holds products from Laravel
const products = ref([])
const error = ref('')
const loading = ref(false)

// Laravel API base URL (port 8000)
const API = 'http://localhost:8000/api'

async function loadProducts() {
  loading.value = true
  error.value = ''
  try {
    // Step: browser calls Laravel
    const res = await fetch(`${API}/products`, {
      headers: { Accept: 'application/json' },
    })

    if (!res.ok) {
      throw new Error(`HTTP ${res.status}`)
    }

    // JSON from ProductController@index
    products.value = await res.json()
  } catch (e) {
    error.value = e.message || 'Failed to load products'
    console.error(e)
  } finally {
    loading.value = false
  }
}

// run once when page opens
onMounted(() => {
  loadProducts()
})
</script>

<template>
  <main style="font-family: system-ui; max-width: 640px; margin: 2rem auto; padding: 0 1rem">
    <h1>Products (from Laravel API)</h1>

    <button type="button" @click="loadProducts" :disabled="loading">
      {{ loading ? 'Loading…' : 'Reload' }}
    </button>

    <p v-if="error" style="color: crimson">Error: {{ error }}</p>

    <ul v-if="products.length">
      <li v-for="p in products" :key="p.id">
        <strong>{{ p.name }}</strong>
        — ${{ p.price }}
        <span v-if="p.description"> ({{ p.description }})</span>
      </li>
    </ul>

    <p v-else-if="!loading && !error">No products yet. Create one with curl, then Reload.</p>
  </main>
</template>