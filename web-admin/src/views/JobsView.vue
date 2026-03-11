<template>
  <div class="jobs-page">
    <div class="page-header">
      <h1>Job Management</h1>
      <button @click="toggleForm" class="btn-primary">
        {{ showAddForm ? (isEditing ? 'Cancel Edit' : 'Cancel') : (isEditing ? 'Edit Job' : 'Add New Job') }}
      </button>
    </div>

    <div v-if="showAddForm" class="form-section">
      <h2>{{ isEditing ? 'Edit Job' : 'Add New Job' }}</h2>
      <form @submit.prevent="saveJob" class="job-form">
        <div class="form-group">
          <label for="title">Job Title</label>
          <input
            id="title"
            v-model="newJob.title"
            placeholder="Enter job title"
            required
          />
        </div>

        <div class="form-group">
          <label for="description">Description</label>
          <textarea
            id="description"
            v-model="newJob.description"
            placeholder="Enter job description"
            rows="4"
          ></textarea>
        </div>

        <div class="form-group">
          <label for="hourly_rate">Hourly Rate (€)</label>
          <input
            id="hourly_rate"
            v-model.number="newJob.hourly_rate"
            type="number"
            step="0.01"
            placeholder="0.00"
            min="0"
          />
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-primary">{{ isEditing ? 'Update Job' : 'Save Job' }}</button>
          <button type="button" @click="cancelAdd" class="btn-secondary">Cancel</button>
        </div>
      </form>
    </div>

    <div class="jobs-section">
      <h2>Current Jobs ({{ jobs.length }})</h2>

      <div v-if="jobs.length === 0" class="empty-state">
        <p>No jobs found. Create a new job</p>
      </div>

      <div v-else>
        <div v-if="isDesktop" class="jobs-table-wrapper">
          <table class="jobs-table">
            <thead>
              <tr>
                <th>Title</th>
                <th>Rate</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="job in jobs" :key="job.id">
                <td class="link-cell" @click="router.push(`/jobs/${job.id}`)">{{ job.title }}</td>
                <td>€{{ job.hourly_rate }}/hr</td>
                <td>{{ formatDate(job.created_at) }}</td>
                <td>
                  <button @click.stop="editJob(job)" class="btn-edit">Edit</button>
                  <button @click.stop="deleteJob(job.id)" class="btn-delete">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="jobs-grid">
          <div v-for="job in jobs" :key="job.id" class="job-card" @click="router.push(`/jobs/${job.id}`)">
            <div class="job-header">
              <h3>{{ job.title }}</h3>
              <div class="job-actions" @click.stop>
                <button @click="editJob(job)" class="btn-edit">Edit</button>
                <button @click="deleteJob(job.id)" class="btn-delete">Delete</button>
              </div>
            </div>

            <p class="job-description">{{ job.description }}</p>
            <div class="job-rate">€{{ job.hourly_rate }}/hr</div>
            <div class="job-date">Created: {{ formatDate(job.created_at) }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const jobs = ref([])
const isDesktop = ref(window.innerWidth >= 1024)
const showAddForm = ref(false)
const isEditing = ref(false)
const editingJobId = ref(null)
const newJob = ref({ title: '', description: '', hourly_rate: 0 })

function updateDesktop() {
  isDesktop.value = window.innerWidth >= 1024
}

onMounted(() => {
  window.addEventListener('resize', updateDesktop)
})

onUnmounted(() => {
  window.removeEventListener('resize', updateDesktop)
})

const fetchJobs = async () => {
  try {
    // send optional role filter via server
    const user = JSON.parse(localStorage.getItem('user') || 'null')
    const params = {}
    if (user && user.role === 'client') {
      params.client = user.id
    }
    const response = await axios.get('http://localhost:8000/api/jobs', { params })
    jobs.value = response.data
  } catch (error) {
    // redirect to login if unauthorized, otherwise generic alert
    if (error.response && error.response.status === 401) {
      router.push('/login')
    } else {
      alert("Error fetching jobs. Check if Laravel is running!")
    }
  }
}

const saveJob = async () => {
  try {
    if (isEditing.value && editingJobId.value) {
      await axios.put(`http://localhost:8000/api/jobs/${editingJobId.value}`, newJob.value)
    } else {
      await axios.post('http://localhost:8000/api/jobs', newJob.value)
    }

    newJob.value = { title: '', description: '', hourly_rate: 0 }
    showAddForm.value = false
    isEditing.value = false
    editingJobId.value = null
    fetchJobs()
  } catch (error) {
    alert("Error saving job. Check if Laravel is running!")
  }
}

const cancelAdd = () => {
  newJob.value = { title: '', description: '', hourly_rate: 0 }
  showAddForm.value = false
  if (isEditing.value) {
    isEditing.value = false
    editingJobId.value = null
  }
}

const toggleForm = () => {
  if (showAddForm.value) {
    cancelAdd()
  } else {
    showAddForm.value = true
  }
}

const editJob = (job) => {
  showAddForm.value = true
  isEditing.value = true
  editingJobId.value = job.id
  newJob.value = {
    title: job.title || '',
    description: job.description || '',
    hourly_rate: job.hourly_rate || 0,
  }
}

const deleteJob = async (jobId) => {
  if (confirm('Are you sure you want to delete this job?')) {
    try {
      await axios.delete(`http://localhost:8000/api/jobs/${jobId}`)
      fetchJobs()
    } catch (error) {
      alert("Error deleting job.")
    }
  }
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString()
}

onMounted(fetchJobs)
</script>

<style scoped>
.jobs-page {
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

.btn-secondary {
  background: #95a5a6;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1rem;
  margin-left: 10px;
  transition: background 0.3s;
}

.btn-secondary:hover {
  background: #7f8c8d;
}

.form-section {
  background: var(--glass-bg);
  backdrop-filter: var(--glass-backdrop);
  border: 1px solid var(--glass-border);
  box-shadow: var(--glass-shadow);
  padding: 20px;
  border-radius: 12px;
  margin-bottom: 30px;
}

.form-section h2 {
  margin-top: 0;
  color: var(--color-heading);
  border-bottom: 1px solid var(--glass-border);
  padding-bottom: 10px;
  font-size: 1.3rem;
}

.job-form {
  margin-top: 20px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
  color: #2c3e50;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
  box-sizing: border-box;
}

.form-group textarea {
  resize: vertical;
}

.form-actions {
  margin-top: 20px;
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.jobs-section {
  background: var(--glass-bg);
  backdrop-filter: var(--glass-backdrop);
  border: 1px solid var(--glass-border);
  box-shadow: var(--glass-shadow);
  padding: 30px;
  border-radius: 12px;
}

.jobs-section h2 {
  margin-top: 0;
  color: var(--color-heading);
  border-bottom: 1px solid var(--glass-border);
  padding-bottom: 15px;
  font-size: 1.5rem;
  font-weight: 600;
}

.jobs-table-wrapper {
  overflow-x: auto;
  margin-top: 20px;
  border-radius: 16px;
  backdrop-filter: var(--glass-backdrop);
  background: var(--glass-bg);
  border: 1px solid var(--glass-border);
  box-shadow: var(--glass-shadow);
}

.jobs-table {
  width: 100%;
  border-collapse: collapse;
  background: transparent;
}

.jobs-table th,
.jobs-table td {
  padding: 18px 20px;
  text-align: left;
  border-bottom: 1px solid var(--glass-border);
}

.jobs-table th {
  background: rgba(255, 255, 255, 0.05);
  font-weight: 700;
  color: var(--color-heading);
  font-size: 0.95rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  backdrop-filter: blur(5px);
}

.jobs-table tbody tr {
  transition: all 0.3s ease;
  backdrop-filter: blur(2px);
}

.jobs-table tbody tr:hover {
  background: rgba(255, 255, 255, 0.08);
  transform: translateY(-1px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.jobs-table tbody tr:nth-child(odd) {
  background: rgba(255, 255, 255, 0.02);
}

.link-cell {
  cursor: pointer;
  color: #00d4ff;
  font-weight: 600;
  transition: all 0.3s ease;
  text-shadow: 0 0 10px rgba(0, 212, 255, 0.3);
}

.link-cell:hover {
  color: #00a8cc;
  text-decoration: none;
  text-shadow: 0 0 15px rgba(0, 212, 255, 0.5);
}

.jobs-table td:nth-child(2),
.jobs-table td:nth-child(3) {
  color: var(--color-text);
  font-size: 0.95rem;
  opacity: 0.9;
}

.jobs-table td:last-child {
  display: flex;
  gap: 10px;
}

.empty-state {
  text-align: center;
  padding: 40px;
  color: #7f8c8d;
}

.jobs-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 24px;
  margin-top: 20px;
}

.job-card {
  background: var(--glass-bg);
  backdrop-filter: var(--glass-backdrop);
  border: 1px solid var(--glass-border);
  box-shadow: var(--glass-shadow);
  border-radius: 12px;
  padding: 24px;
  transition: all 0.3s ease;
  cursor: pointer;
}

.job-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--glass-shadow-hover);
  background: var(--glass-bg-hover);
}

.job-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 10px;
  flex-wrap: wrap;
  gap: 10px;
}

.job-header h3 {
  margin: 0;
  color: var(--color-heading);
  flex: 1;
  font-size: 1.2rem;
}

.job-actions {
  display: flex;
  gap: 5px;
  flex-shrink: 0;
}

.btn-edit,
.btn-delete {
  padding: 8px 14px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.85rem;
  font-weight: 600;
  transition: all 0.2s ease;
  text-transform: capitalize;
}

.btn-edit {
  background: #f39c12;
  color: white;
  box-shadow: 0 2px 4px rgba(243, 156, 18, 0.3);
}

.btn-edit:hover {
  background: #e67e22;
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(243, 156, 18, 0.4);
}

.btn-delete {
  background: #e74c3c;
  color: white;
  box-shadow: 0 2px 4px rgba(231, 76, 60, 0.3);
}

.btn-delete:hover {
  background: #c0392b;
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(231, 76, 60, 0.4);
}

.job-description {
  color: var(--color-text);
  margin-bottom: 10px;
  line-height: 1.4;
  opacity: 0.9;
}

.job-rate {
  font-weight: bold;
  font-size: 1.1rem;
  color: #27ae60;
  margin-bottom: 5px;
}

.job-date {
  font-size: 0.8rem;
  color: #95a5a6;
}

/* Responsive */
@media (max-width: 1024px) {
  .jobs-grid {
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 18px;
  }

  .job-card {
    padding: 20px;
    border-radius: 10px;
  }
}

@media (min-width: 1200px) {
  .jobs-grid {
    grid-template-columns: repeat(auto-fill, minmax(450px, 1fr));
    gap: 30px;
  }

  .job-card {
    padding: 28px;
  }

  .job-header h3 {
    font-size: 1.4rem;
  }

  .job-description {
    font-size: 1rem;
    line-height: 1.6;
  }
}

@media (max-width: 768px) {
  .jobs-page {
    padding: 0 10px;
  }

  .page-header {
    flex-direction: column;
    align-items: stretch;
    gap: 15px;
    margin-bottom: 30px;
    padding: 15px 0;
  }

  .page-header h1 {
    font-size: 1.8rem;
    text-align: center;
  }

  .btn-primary {
    align-self: center;
    padding: 12px 24px;
  }

  .form-section {
    padding: 15px;
    margin-bottom: 20px;
  }

  .form-section h2 {
    font-size: 1.1rem;
  }

  .form-actions {
    flex-direction: column;
  }

  .btn-secondary {
    margin-left: 0;
    align-self: stretch;
  }

  .jobs-section {
    padding: 15px;
  }

  .jobs-section h2 {
    font-size: 1.1rem;
  }

  .jobs-grid {
    grid-template-columns: 1fr;
    gap: 15px;
  }

  .job-card {
    padding: 15px;
  }

  .job-header {
    flex-direction: column;
    align-items: stretch;
  }

  .job-header h3 {
    font-size: 1.1rem;
  }

  .job-actions {
    align-self: flex-end;
    margin-top: 10px;
  }
}

@media (max-width: 480px) {
  .jobs-page {
    padding: 0 5px;
  }

  .page-header h1 {
    font-size: 1.3rem;
  }

  .form-section,
  .jobs-section {
    padding: 12px;
  }

  .job-card {
    padding: 12px;
  }

  .job-header h3 {
    font-size: 1rem;
  }
}
</style>