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

      <div v-else class="jobs-grid">
        <div v-for="job in jobs" :key="job.id" class="job-card">
          <div class="job-header">
            <h3>{{ job.title }}</h3>
            <div class="job-actions">
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
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const jobs = ref([])
const showAddForm = ref(false)
const isEditing = ref(false)
const editingJobId = ref(null)
const newJob = ref({ title: '', description: '', hourly_rate: 0 })

const fetchJobs = async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/jobs')
    jobs.value = response.data
  } catch (error) {
    alert("Error fetching jobs. Check if Laravel is running!")
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
  padding: 0 20px;
  box-sizing: border-box;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  flex-wrap: wrap;
  gap: 15px;
}

.page-header h1 {
  color: #2c3e50;
  margin: 0;
  font-size: 2rem;
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
  background: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  margin-bottom: 30px;
}

.form-section h2 {
  margin-top: 0;
  color: #2c3e50;
  border-bottom: 2px solid #ecf0f1;
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
  background: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.jobs-section h2 {
  margin-top: 0;
  color: #2c3e50;
  border-bottom: 2px solid #ecf0f1;
  padding-bottom: 10px;
  font-size: 1.3rem;
}

.empty-state {
  text-align: center;
  padding: 40px;
  color: #7f8c8d;
}

.jobs-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.job-card {
  border: 1px solid #ecf0f1;
  border-radius: 8px;
  padding: 20px;
  background: #f8f9fa;
  transition: box-shadow 0.3s, transform 0.2s ease;
}

.job-card:hover {
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  transform: translateY(-2px);
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
  color: #2c3e50;
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
  padding: 5px 10px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.8rem;
}

.btn-edit {
  background: #f39c12;
  color: white;
}

.btn-edit:hover {
  background: #e67e22;
}

.btn-delete {
  background: #e74c3c;
  color: white;
}

.btn-delete:hover {
  background: #c0392b;
}

.job-description {
  color: #7f8c8d;
  margin-bottom: 10px;
  line-height: 1.4;
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
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 15px;
  }

  .job-card {
    padding: 15px;
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
  }

  .page-header h1 {
    font-size: 1.5rem;
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