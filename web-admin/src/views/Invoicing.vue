<template>
  <div class="invoicing-page">
    <div class="page-header">
      <h1>Invoicing &amp; Payroll</h1>
    </div>
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
.invoicing-page {
  width: 100%;
  padding: 0 30px;
  box-sizing: border-box;
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 40px;
  flex-wrap: wrap;
  gap: 20px;
  padding: 20px 0;
  border-bottom: 1px solid var(--glass-border);
}

.page-header h1 {
  color: var(--color-heading);
  margin: 0;
  font-size: 2.5rem;
  font-weight: 600;
}

.controls {
  display: flex;
  gap: 1rem;
  align-items: center;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

.btn-primary {
  background: #3498db;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1rem;
  transition: background 0.3s;
}

.btn-primary:hover {
  background: #2980b9;
}
</style>