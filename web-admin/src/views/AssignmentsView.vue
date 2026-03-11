<template>
  <div class="assignments-page">
    <div class="page-header">
      <h1>Assignments</h1>
      <button @click="toggleForm" class="btn-primary">
        {{ showAddForm ? (isEditing ? 'Cancel Edit' : 'Cancel') : (isEditing ? 'Edit Assignment' : 'Add Assignment') }}
      </button>
    </div>

    <div v-if="showAddForm" class="form-section">
      <h2>{{ isEditing ? 'Edit Assignment' : 'Add Assignment' }}</h2>
      <form @submit.prevent="saveAssignment" class="assignment-form">
        <div class="form-group">
          <label for="job">Job</label>
          <select id="job" v-model.number="newAssignment.job_id" required>
            <option value="" disabled>Select job</option>
            <option v-for="job in jobs" :key="job.id" :value="job.id">{{ job.title }}</option>
          </select>
        </div>

        <div class="form-group">
          <label for="worker">Worker</label>
          <select id="worker" v-model.number="newAssignment.worker_id" required>
            <option value="" disabled>Select worker</option>
            <option v-for="worker in workers" :key="worker.id" :value="worker.id">{{ worker.name }}</option>
          </select>
        </div>

        <div class="form-group">
          <label for="start_date">Start Date</label>
          <input id="start_date" type="date" v-model="newAssignment.start_date" required />
        </div>

        <div class="form-group">
          <label for="end_date">End Date (optional)</label>
          <input id="end_date" type="date" v-model="newAssignment.end_date" />
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-primary">{{ isEditing ? 'Update' : 'Save' }}</button>
          <button type="button" @click="cancelAdd" class="btn-secondary">Cancel</button>
        </div>
      </form>
    </div>

    <div class="assignments-section">
      <h2>Current Assignments ({{ assignments.length }})</h2>
      <div v-if="assignments.length === 0" class="empty-state">
        <p>No assignments yet. Create one above.</p>
      </div>
      <div v-else>
        <div v-if="isDesktop" class="assignments-table-wrapper">
          <table class="assignments-table">
            <thead>
              <tr>
                <th>Job</th>
                <th>Worker</th>
                <th>Start</th>
                <th>End</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="a in assignments" :key="a.id">
                <td>{{ a.job?.title || 'n/a' }}</td>
                <td>{{ a.worker?.name || 'n/a' }}</td>
                <td>{{ formatDate(a.start_date) }}</td>
                <td>{{ a.end_date ? formatDate(a.end_date) : '-' }}</td>
                <td>
                  <button @click.stop="editAssignment(a)" class="btn-edit">Edit</button>
                  <button @click.stop="deleteAssignment(a.id)" class="btn-delete">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="assignments-grid">
          <div v-for="a in assignments" :key="a.id" class="assignment-card">
            <div class="assignment-header">
              <h3>{{ a.job?.title || 'n/a' }}</h3>
              <div class="assignment-actions" @click.stop>
                <button @click="editAssignment(a)" class="btn-edit">Edit</button>
                <button @click="deleteAssignment(a.id)" class="btn-delete">Delete</button>
              </div>
            </div>
            <p>Worker: {{ a.worker?.name || 'n/a' }}</p>
            <p>Start: {{ formatDate(a.start_date) }}</p>
            <p>End: {{ a.end_date ? formatDate(a.end_date) : '-' }}</p>
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
const assignments = ref([])
const jobs = ref([])
const workers = ref([])
const isDesktop = ref(window.innerWidth >= 1024)
const showAddForm = ref(false)
const isEditing = ref(false)
const editingId = ref(null)
const newAssignment = ref({ job_id: '', worker_id: '', start_date: '', end_date: '' })

function updateDesktop() {
  isDesktop.value = window.innerWidth >= 1024
}

onMounted(() => {
  window.addEventListener('resize', updateDesktop)
  fetchJobs()
  fetchUsers()
  fetchAssignments()
})

onUnmounted(() => {
  window.removeEventListener('resize', updateDesktop)
})

const fetchJobs = async () => {
  try {
    const res = await axios.get('http://localhost:8000/api/jobs')
    jobs.value = res.data
  } catch (e) {
    console.error(e)
  }
}

const fetchUsers = async () => {
  try {
    const res = await axios.get('http://localhost:8000/api/users')
    workers.value = res.data.filter(u => u.role === 'worker')
  } catch (e) {
    console.error(e)
  }
}

const fetchAssignments = async () => {
  try {
    const res = await axios.get('http://localhost:8000/api/assignments')
    assignments.value = res.data
  } catch (e) {
    console.error(e)
  }
}

const saveAssignment = async () => {
  try {
    if (isEditing.value && editingId.value) {
      await axios.put(`http://localhost:8000/api/assignments/${editingId.value}`, newAssignment.value)
    } else {
      await axios.post('http://localhost:8000/api/assignments', newAssignment.value)
    }
    resetForm()
    fetchAssignments()
  } catch (e) {
    alert('Error saving assignment')
  }
}

const resetForm = () => {
  newAssignment.value = { job_id: '', worker_id: '', start_date: '', end_date: '' }
  showAddForm.value = false
  isEditing.value = false
  editingId.value = null
}

const cancelAdd = () => {
  resetForm()
}

const toggleForm = () => {
  if (showAddForm.value) cancelAdd()
  else showAddForm.value = true
}

const editAssignment = (a) => {
  showAddForm.value = true
  isEditing.value = true
  editingId.value = a.id
  newAssignment.value = {
    job_id: a.job_id,
    worker_id: a.worker_id,
    start_date: a.start_date,
    end_date: a.end_date || ''
  }
}

const deleteAssignment = async (id) => {
  if (confirm('Delete this assignment?')) {
    try {
      await axios.delete(`http://localhost:8000/api/assignments/${id}`)
      fetchAssignments()
    } catch (e) {
      alert('Error deleting')
    }
  }
}

const formatDate = (d) => {
  return new Date(d).toLocaleDateString()
}
</script>

<style scoped>
.assignments-page { padding: 1rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; }
.form-section { margin-top: 1rem; }
.form-group { margin-bottom: 0.75rem; }
.form-actions { margin-top: 1rem; }
.assignments-table { width: 100%; border-collapse: collapse; }
.assignments-table th, .assignments-table td { padding: 0.5rem; border: 1px solid #ccc; }
.assignments-grid { display: grid; grid-template-columns: 1fr; gap: 1rem; }
.assignment-card { border: 1px solid #ccc; padding: 0.75rem; border-radius: 4px; }
.assignment-header { display: flex; justify-content: space-between; align-items: center; }
.assignment-actions button { margin-left: 0.5rem; }
</style>