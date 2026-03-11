<template>
  <div class="invoicing-page">
    <h1>Invoicing &amp; Payroll</h1>
    <p>Select a period and press generate to create invoices/payroll.</p>
    <div class="controls">
      <label>From: <input type="date" v-model="from" /></label>
      <label>To: <input type="date" v-model="to" /></label>
      <button @click="generate" class="btn-primary">Generate</button>
    </div>
    <div v-if="loading">Generating...</div>
    <div v-if="result">
      <p>Generated file: <a :href="result.url" target="_blank">{{ result.filename }}</a></p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const from = ref('')
const to = ref('')
const loading = ref(false)
const result = ref(null)

const generate = async () => {
  loading.value = true
  try {
    const res = await axios.post('http://localhost:8000/api/invoices/generate', {
      from: from.value,
      to: to.value,
    })
    result.value = res.data
  } catch (e) {
    console.error('invoice error', e)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.controls {
  display: flex;
  gap: 1rem;
  align-items: center;
  margin-bottom: 1rem;
}
</style>