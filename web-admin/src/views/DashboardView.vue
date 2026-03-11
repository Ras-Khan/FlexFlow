<template>
  <div class="dashboard">
    <h1>Dashboard Overview</h1>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">J</div>
        <div class="stat-content">
          <h3>{{ totalJobs }}</h3>
          <p>Total Jobs</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">U</div>
        <div class="stat-content">
          <h3>{{ totalUsers }}</h3>
          <p>Total Users</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">R</div>
        <div class="stat-content">
          <h3>€{{ averageRate }}/hr</h3>
          <p>Average Rate</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">M</div>
        <div class="stat-content">
          <h3>{{ recentJobs }}</h3>
          <p>Jobs This Month</p>
        </div>
      </div>
    </div>

    <div class="dashboard-sections">
      <div class="section">
        <h2>Recent Jobs</h2>
        <div class="jobs-list">
          <div v-for="job in recentJobsList" :key="job.id" class="job-item" @click="router.push(`/jobs/${job.id}`)">
            <h4>{{ job.title }}</h4>
            <p>{{ job.description }}</p>
            <span class="rate">€{{ job.hourly_rate }}/hr</span>
          </div>
        </div>
      </div>

      <div class="section">
        <h2>Quick Actions</h2>
        <div class="actions-grid">
          <router-link to="/jobs" class="action-card">
            <span class="action-icon">+</span>
            <span>Add New Job</span>
          </router-link>
          <router-link to="/users" class="action-card">
            <span class="action-icon">U</span>
            <span>Manage Users</span>
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const jobs = ref([])
const users = ref([])

const fetchData = async () => {
  try {
    const [jobsResponse, usersResponse] = await Promise.all([
      axios.get('http://localhost:8000/api/jobs'),
      axios.get('http://localhost:8000/api/users') 
    ])
    jobs.value = jobsResponse.data
    users.value = usersResponse.data
  } catch (error) {
    console.error('Error fetching data:', error)
  }
}

const totalJobs = computed(() => jobs.value.length)
const totalUsers = computed(() => users.value.length)
const averageRate = computed(() => {
  if (jobs.value.length === 0) return 0
  const sum = jobs.value.reduce((acc, job) => acc + parseFloat(job.hourly_rate || 0), 0)
  return (sum / jobs.value.length).toFixed(2)
})
const recentJobs = computed(() => {
  return jobs.value.length
})
const recentJobsList = computed(() => jobs.value.slice(0, 5))

onMounted(fetchData)
</script>

<style scoped>
.dashboard {
  width: 100%;
  padding: 0 30px;
  box-sizing: border-box;
  max-width: 1400px;
  margin: 0 auto;
}

.dashboard h1 {
  color: var(--color-heading);
  margin-bottom: 40px;
  font-size: 2.5rem;
  font-weight: 600;
  padding: 20px 0;
  border-bottom: 1px solid var(--glass-border);
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 30px;
  margin-bottom: 50px;
}

.stat-card {
  background: var(--glass-bg);
  backdrop-filter: var(--glass-backdrop);
  border: 1px solid var(--glass-border);
  box-shadow: var(--glass-shadow);
  padding: 30px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  transition: all 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
  background: rgba(255, 255, 255, 0.15);
}

.stat-icon {
  font-size: 2rem;
  margin-right: 15px;
  flex-shrink: 0;
}

.stat-content h3 {
  margin: 0;
  font-size: 2rem;
  color: var(--color-heading);
}

.stat-content p {
  margin: 5px 0 0 0;
  color: var(--color-text);
  font-size: 0.9rem;
  opacity: 0.8;
}

.dashboard-sections {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 30px;
}

.section {
  background: var(--glass-bg);
  backdrop-filter: var(--glass-backdrop);
  border: 1px solid var(--glass-border);
  box-shadow: var(--glass-shadow);
  padding: 30px;
  border-radius: 16px;
}

.section h2 {
  margin-top: 0;
  color: var(--color-heading);
  border-bottom: 1px solid var(--glass-border);
  padding-bottom: 15px;
  font-size: 1.5rem;
  font-weight: 600;
}

.jobs-list {
  margin-top: 15px;
}

.job-item {
  padding: 15px;
  margin-bottom: 10px;
  background: var(--glass-bg);
  backdrop-filter: var(--glass-blur);
  border: var(--glass-border);
  border-radius: 12px;
  box-shadow: var(--glass-shadow);
  transition: all 0.3s ease;
  cursor: pointer;
}

.job-item:hover {
  transform: translateY(-2px);
  box-shadow: var(--glass-shadow-hover);
  background: var(--glass-bg-hover);
}

.job-item:last-child {
  margin-bottom: 0;
}

.job-item h4 {
  margin: 0 0 5px 0;
  color: var(--color-heading);
  font-size: 1.1rem;
}

.job-item p {
  margin: 0 0 5px 0;
  color: var(--color-text);
  font-size: 0.9rem;
  line-height: 1.4;
  opacity: 0.9;
}

.rate {
  font-weight: bold;
  color: var(--color-success);
  font-size: 0.95rem;
}

.actions-grid {
  display: grid;
  gap: 15px;
}

.action-card {
  display: flex;
  align-items: center;
  padding: 15px;
  background: var(--glass-bg);
  backdrop-filter: var(--glass-backdrop);
  border: 1px solid var(--glass-border);
  border-radius: 8px;
  text-decoration: none;
  color: var(--color-heading);
  transition: all 0.3s ease;
  box-shadow: var(--glass-shadow);
}

.action-card:hover {
  background: var(--glass-bg-hover);
  transform: translateY(-2px);
  box-shadow: var(--glass-shadow-hover);
}

.action-icon {
  font-size: 1.5rem;
  margin-right: 10px;
  flex-shrink: 0;
}

/* Responsive styles */
@media (max-width: 1024px) {
  .dashboard-sections {
    grid-template-columns: 1fr;
    gap: 20px;
  }

  .stats-grid {
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
  }

  .stat-card {
    padding: 15px;
  }

  .stat-content h3 {
    font-size: 1.5rem;
  }
}

@media (max-width: 768px) {
  .dashboard {
    padding: 0 10px;
  }

  .dashboard h1 {
    font-size: 1.8rem;
    margin-bottom: 30px;
    padding: 15px 0;
  }

  .stats-grid {
    grid-template-columns: 1fr;
    gap: 20px;
    margin-bottom: 40px;
  }

  .stat-card {
    padding: 20px;
  }

  .stat-content h3 {
    font-size: 1.8rem;
  }

  .dashboard-sections {
    gap: 20px;
  }

  .section {
    padding: 20px;
  }

  .section h2 {
    font-size: 1.2rem;
  }

  .job-item {
    padding: 12px 0;
  }

  .job-item h4 {
    font-size: 1rem;
  }

  .actions-grid {
    grid-template-columns: 1fr;
    gap: 10px;
  }

  .action-card {
    padding: 12px;
    justify-content: center;
    text-align: center;
  }

  .action-icon {
    margin-right: 8px;
    margin-bottom: 0;
  }
}

@media (max-width: 480px) {
  .dashboard h1 {
    font-size: 1.3rem;
  }

  .stat-card {
    padding: 12px;
  }

  .stat-content h3 {
    font-size: 1.5rem;
  }

  .section {
    padding: 12px;
  }
}
</style>