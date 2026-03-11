<template>
  <div class="login-page">
    <h1>Login</h1>
    <form @submit.prevent="login" class="login-form">
      <div class="form-group">
        <label for="email">Email</label>
        <input id="email" v-model="email" type="email" required />
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input id="password" v-model="password" type="password" required />
      </div>
      <button type="submit" class="btn-primary">Login</button>
    </form>
    <p v-if="error" class="error">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import router from '../router'

const email = ref('')
const password = ref('')
const error = ref(null)

const login = async () => {
  try {
    const res = await axios.post('http://localhost:8000/api/login', { email: email.value, password: password.value })
    const u = res.data.user || {}
    u.token = res.data.token
    localStorage.setItem('user', JSON.stringify(u))
    router.push('/')
  } catch (e) {
    console.error('login error', e.response ? e.response.data : e)
    error.value = e.response?.data?.message || 'Login failed'
  }
}
</script>

<style scoped>
.login-page { max-width: 400px; margin: 4rem auto; }
.login-form { display: flex; flex-direction: column; gap: 1rem; }
.error { color: red; margin-top: 1rem; }
</style>