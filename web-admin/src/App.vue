<template>
  <div id="app">
    <h1>FlexFlow Admin Dashboard</h1>
    <hr />
    <div v-if="jobs.length > 0">
      <div v-for="job in jobs" :key="job.id" class="job-card">
        <h3>{{ job.title }}</h3>
        <p>{{ job.description }}</p>
        <strong>Rate: €{{ job.hourly_rate }}</strong>
      </div>
    </div>
    <p v-else>Loading jobs or no jobs found...</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const jobs = ref([])

onMounted(async () => {
  try {
    // This calls your Laravel API
    const response = await axios.get('http://localhost:8000/api/jobs')
    jobs.value = response.data
  } catch (error) {
    console.error("Could not connect to Laravel:", error)
  }
})
</script>

<style>
.job-card {
  border: 1px solid #ccc;
  padding: 10px;
  margin: 10px 0;
  border-radius: 8px;
}
</style>