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
.assignments-page {
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

.assignment-form {
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
.form-group select {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
  box-sizing: border-box;
}

.form-actions {
  margin-top: 20px;
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.assignments-section {
  background: var(--glass-bg);
  backdrop-filter: var(--glass-backdrop);
  border: 1px solid var(--glass-border);
  box-shadow: var(--glass-shadow);
  padding: 30px;
  border-radius: 12px;
}

.assignments-section h2 {
  margin-top: 0;
  color: var(--color-heading);
  border-bottom: 1px solid var(--glass-border);
  padding-bottom: 15px;
  font-size: 1.5rem;
  font-weight: 600;
}

.assignments-table-wrapper {
  overflow-x: auto;
  margin-top: 20px;
  border-radius: 16px;
  backdrop-filter: var(--glass-backdrop);
  background: var(--glass-bg);
  border: 1px solid var(--glass-border);
  box-shadow: var(--glass-shadow);
}

.assignments-table {
  width: 100%;
  border-collapse: collapse;
  background: transparent;
}

.assignments-table th,
.assignments-table td {
  padding: 18px 20px;
  text-align: left;
  border-bottom: 1px solid var(--glass-border);
}

.assignments-table th {
  background: rgba(255, 255, 255, 0.05);
  font-weight: 700;
  color: var(--color-heading);
  font-size: 0.95rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  backdrop-filter: blur(5px);
}

.assignments-table tbody tr {
  transition: all 0.3s ease;
  backdrop-filter: blur(2px);
}

.assignments-table tbody tr:hover {
  background: rgba(255, 255, 255, 0.08);
  transform: translateY(-1px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.assignments-table tbody tr:nth-child(odd) {
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
  margin: 0 2px;
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

.form-section { margin-top: 1rem; }
.form-group { margin-bottom: 0.75rem; }
.form-actions { margin-top: 1rem; }
.assignments-table { width: 100%; border-collapse: collapse; }
.assignments-table th, .assignments-table td { padding: 0.5rem; border: 1px solid #ccc; }
.assignments-grid { display: grid; grid-template-columns: 1fr; gap: 1rem; }
.assignment-card { border: 1px solid #ccc; padding: 0.75rem; border-radius: 4px; }
.assignment-header { display: flex; justify-content: space-between; align-items: center; }
.assignment-actions button { margin-left: 0.5rem; }

.assignment-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 24px;
  margin-top: 20px;
}

.assignment-card {
  background: var(--glass-bg);
  backdrop-filter: var(--glass-backdrop);
  border: 1px solid var(--glass-border);
  box-shadow: var(--glass-shadow);
  border-radius: 12px;
  padding: 24px;
  transition: all 0.3s ease;
  cursor: pointer;
}

.assignment-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--glass-shadow-hover);
  background: var(--glass-bg-hover);
}

.assignment-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 10px;
  flex-wrap: wrap;
  gap: 10px;
}

.assignment-header h3 {
  margin: 0;
  color: var(--color-heading);
  flex: 1;
  font-size: 1.2rem;
}

.assignment-actions {
  display: flex;
  gap: 5px;
  flex-shrink: 0;
}

@media (max-width: 1024px) {
  .assignment-grid {
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 18px;
  }

  .assignment-card {
    padding: 20px;
    border-radius: 10px;
  }
}

@media (min-width: 1200px) {
  .assignment-grid {
    grid-template-columns: repeat(auto-fill, minmax(450px, 1fr));
    gap: 30px;
  }

  .assignment-card {
    padding: 28px;
  }

  .assignment-header h3 {
    font-size: 1.4rem;
  }
}

@media (max-width: 768px) {
  .assignments-page {
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

  .assignments-section {
    padding: 15px;
  }

  .assignments-section h2 {
    font-size: 1.1rem;
  }

  .assignment-grid {
    grid-template-columns: 1fr;
    gap: 15px;
  }

  .assignment-card {
    padding: 15px;
  }

  .assignment-header {
    flex-direction: column;
    align-items: stretch;
  }

  .assignment-actions {
    align-self: flex-end;
    margin-top: 10px;
  }
}

@media (max-width: 480px) {
  .assignments-page {
    padding: 0 5px;
  }

  .page-header h1 {
    font-size: 1.3rem;
  }

  .form-section,
  .assignments-section {
    padding: 12px;
  }

  .assignment-card {
    padding: 12px;
  }

  .assignment-header h3 {
    font-size: 1rem;
  }
}
</style>