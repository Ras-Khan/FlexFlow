<template>
  <div class="skills-page">
    <div class="page-header">
      <h1>Skills Management</h1>
      <button @click="toggleForm" class="btn-primary">
        {{ showAddForm ? (isEditing ? 'Cancel Edit' : 'Cancel') : (isEditing ? 'Edit Skill' : 'Add Skill') }}
      </button>
    </div>

    <div v-if="showAddForm" class="form-section">
      <h2>{{ isEditing ? 'Edit Skill' : 'Add New Skill' }}</h2>
      <form @submit.prevent="saveSkill" class="skill-form">
        <div class="form-group">
          <label for="name">Skill Name</label>
          <input id="name" v-model="newSkill.name" placeholder="e.g., JavaScript, Project Management" required />
        </div>

        <div class="form-group">
          <label for="description">Description</label>
          <textarea
            id="description"
            v-model="newSkill.description"
            placeholder="Describe this skill..."
            rows="4"
          ></textarea>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-primary">{{ isEditing ? 'Update' : 'Save' }}</button>
          <button type="button" @click="cancelAdd" class="btn-secondary">Cancel</button>
        </div>
      </form>
    </div>

    <div class="skills-section">
      <h2>Skills ({{ skills.length }})</h2>
      <div v-if="skills.length === 0" class="empty-state">
        <p>No skills yet. Create one above.</p>
      </div>

      <div v-else>
        <div v-if="isDesktop" class="skills-table-wrapper">
          <table class="skills-table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="skill in skills" :key="skill.id">
                <td class="skill-name">{{ skill.name }}</td>
                <td class="skill-description">{{ skill.description || '-' }}</td>
                <td>{{ formatDate(skill.created_at) }}</td>
                <td>
                  <button @click.stop="editSkill(skill)" class="btn-edit">Edit</button>
                  <button @click.stop="deleteSkill(skill.id)" class="btn-delete">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="skills-grid">
          <div v-for="skill in skills" :key="skill.id" class="skill-card">
            <div class="skill-header">
              <h3>{{ skill.name }}</h3>
              <div class="skill-actions" @click.stop>
                <button @click="editSkill(skill)" class="btn-edit">Edit</button>
                <button @click="deleteSkill(skill.id)" class="btn-delete">Delete</button>
              </div>
            </div>

            <p class="skill-description">{{ skill.description || 'No description' }}</p>
            <div class="skill-date">Created: {{ formatDate(skill.created_at) }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="assignment-section" v-if="skills.length > 0">
      <h2>Assign Skills to Workers</h2>
      <div class="assignment-form">
        <div class="form-group">
          <label for="worker-select">Select Worker</label>
          <select id="worker-select" v-model.number="selectedWorker" @change="updateWorkerSkills">
            <option value="">Choose a worker...</option>
            <option v-for="worker in workers" :key="worker.id" :value="worker.id">{{ worker.name }}</option>
          </select>
        </div>

        <div v-if="selectedWorker" class="skills-assignment">
          <label>Worker Skills</label>
          <div class="skills-checkboxes">
            <label v-for="skill in skills" :key="skill.id" class="skill-checkbox">
              <input
                type="checkbox"
                :value="skill.id"
                :checked="workerSkills.includes(skill.id)"
                @change="toggleWorkerSkill(skill.id)"
              />
              {{ skill.name }}
            </label>
          </div>
          <button @click="saveWorkerSkills" class="btn-primary" style="margin-top: 15px">Save Skills</button>
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
const skills = ref([])
const workers = ref([])
const jobs = ref([])
const isDesktop = ref(window.innerWidth >= 1024)
const showAddForm = ref(false)
const isEditing = ref(false)
const editingSkillId = ref(null)
const newSkill = ref({ name: '', description: '' })
const selectedWorker = ref('')
const workerSkills = ref([])

function updateDesktop() {
  isDesktop.value = window.innerWidth >= 1024
}

function formatDate(dateString) {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString()
}

onMounted(() => {
  window.addEventListener('resize', updateDesktop)
  fetchSkills()
  fetchWorkers()
})

onUnmounted(() => {
  window.removeEventListener('resize', updateDesktop)
})

const getToken = () => localStorage.getItem('token')
const getAuthHeaders = () => ({ Authorization: `Bearer ${getToken()}` })

const fetchSkills = async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/skills', {
      headers: getAuthHeaders(),
    })
    skills.value = response.data
  } catch (error) {
    if (error.response?.status === 401) {
      router.push('/login')
    } else {
      alert('Error fetching skills. Check if Laravel is running!')
    }
  }
}

const fetchWorkers = async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/users', {
      headers: getAuthHeaders(),
    })
    workers.value = response.data.filter((u) => u.role === 'worker')
  } catch (error) {
    console.error('Error fetching workers:', error)
  }
}

const updateWorkerSkills = async () => {
  if (!selectedWorker.value) return

  try {
    const worker = workers.value.find((w) => w.id === selectedWorker.value)
    if (worker && worker.skills) {
      workerSkills.value = worker.skills.map((s) => s.id)
    } else {
      workerSkills.value = []
    }
  } catch (error) {
    console.error('Error updating worker skills:', error)
  }
}

const toggleWorkerSkill = (skillId) => {
  const index = workerSkills.value.indexOf(skillId)
  if (index > -1) {
    workerSkills.value.splice(index, 1)
  } else {
    workerSkills.value.push(skillId)
  }
}

const saveWorkerSkills = async () => {
  if (!selectedWorker.value) return

  try {
    // Get current skills
    const response = await axios.get(`http://localhost:8000/api/users/${selectedWorker.value}`, {
      headers: getAuthHeaders(),
    })
    const currentSkills = response.data.skills?.map((s) => s.id) || []

    // Detach skills that were unchecked
    for (const skillId of currentSkills) {
      if (!workerSkills.value.includes(skillId)) {
        await axios.delete(`http://localhost:8000/api/users/${selectedWorker.value}/skills/${skillId}`, {
          headers: getAuthHeaders(),
        })
      }
    }

    // Attach skills that were checked and aren't already attached
    for (const skillId of workerSkills.value) {
      if (!currentSkills.includes(skillId)) {
        await axios.post(
          `http://localhost:8000/api/users/${selectedWorker.value}/skills`,
          { skill_id: skillId },
          { headers: getAuthHeaders() }
        )
      }
    }

    alert('Worker skills updated successfully!')
    fetchWorkers()
  } catch (error) {
    alert('Error saving worker skills.')
    console.error(error)
  }
}

const saveSkill = async () => {
  try {
    if (isEditing.value && editingSkillId.value) {
      await axios.put(`http://localhost:8000/api/skills/${editingSkillId.value}`, newSkill.value, {
        headers: getAuthHeaders(),
      })
    } else {
      await axios.post('http://localhost:8000/api/skills', newSkill.value, {
        headers: getAuthHeaders(),
      })
    }

    resetForm()
    fetchSkills()
  } catch (error) {
    alert('Error saving skill. Check if Laravel is running!')
  }
}

const resetForm = () => {
  newSkill.value = { name: '', description: '' }
  showAddForm.value = false
  isEditing.value = false
  editingSkillId.value = null
}

const cancelAdd = () => {
  resetForm()
}

const toggleForm = () => {
  if (showAddForm.value) {
    cancelAdd()
  } else {
    showAddForm.value = true
  }
}

const editSkill = (skill) => {
  showAddForm.value = true
  isEditing.value = true
  editingSkillId.value = skill.id
  newSkill.value = {
    name: skill.name,
    description: skill.description || '',
  }
}

const deleteSkill = async (skillId) => {
  if (confirm('Are you sure you want to delete this skill?')) {
    try {
      await axios.delete(`http://localhost:8000/api/skills/${skillId}`, {
        headers: getAuthHeaders(),
      })
      fetchSkills()
    } catch (error) {
      alert('Error deleting skill.')
    }
  }
}
</script>

<style scoped>
.skills-page {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
  background: #0d0d0d;
  min-height: 100vh;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

.page-header h1 {
  margin: 0;
  font-size: 28px;
  color: #e0e0e0;
}

.form-section {
  background: #2a2a2a;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 30px;
  border: 1px solid #444;
}

.form-section h2 {
  margin-top: 0;
  margin-bottom: 20px;
  color: #e0e0e0;
}

.skill-form {
  max-width: 600px;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 600;
  color: #b0b0b0;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 8px;
  border: 1px solid #444;
  border-radius: 4px;
  font-size: 14px;
  font-family: inherit;
  box-sizing: border-box;
  background-color: #1a1a1a;
  color: #e0e0e0;
}

.form-group textarea {
  resize: vertical;
}

.form-actions {
  display: flex;
  gap: 10px;
  margin-top: 20px;
}

.btn-primary,
.btn-secondary,
.btn-edit,
.btn-delete {
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
}

.btn-primary {
  background-color: #007bff;
  color: white;
}

.btn-primary:hover {
  background-color: #0056b3;
}

.btn-secondary {
  background-color: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background-color: #5a6268;
}

.btn-edit {
  background-color: #28a745;
  color: white;
}

.btn-edit:hover {
  background-color: #218838;
}

.btn-delete {
  background-color: #dc3545;
  color: white;
}

.btn-delete:hover {
  background-color: #c82333;
}

.skills-section,
.assignment-section {
  margin-top: 30px;
}

.skills-section h2,
.assignment-section h2 {
  margin-bottom: 20px;
  font-size: 22px;
  color: #e0e0e0;
}

.empty-state {
  text-align: center;
  padding: 40px 20px;
  color: #888;
}

.skills-table-wrapper {
  overflow-x: auto;
}

.skills-table {
  width: 100%;
  border-collapse: collapse;
  background: #1a1a1a;
  border: 1px solid #444;
  border-radius: 4px;
}

.skills-table thead {
  background-color: #2a2a2a;
}

.skills-table th {
  padding: 12px;
  text-align: left;
  font-weight: 600;
  border-bottom: 2px solid #444;
  color: #e0e0e0;
}

.skills-table td {
  padding: 12px;
  border-bottom: 1px solid #444;
  color: #e0e0e0;
}

.skills-table tbody tr:hover {
  background-color: #2a2a2a;
}

.skill-name {
  font-weight: 600;
  color: #e0e0e0;
}

.skill-description {
  color: #b0b0b0;
  max-width: 300px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.skills-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.skill-card {
  background: #1a1a1a;
  border: 1px solid #444;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.skill-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  margin-bottom: 15px;
}

.skill-header h3 {
  margin: 0;
  font-size: 18px;
  color: #e0e0e0;
  flex: 1;
}

.skill-actions {
  display: flex;
  gap: 5px;
}

.skill-actions button {
  padding: 6px 12px;
  font-size: 12px;
}

.skill-description {
  color: #b0b0b0;
  margin: 10px 0;
  font-size: 14px;
}

.skill-date {
  color: #888;
  font-size: 12px;
}

.assignment-section {
  background: #2a2a2a;
  padding: 20px;
  border-radius: 8px;
  border: 1px solid #444;
}

.assignment-form {
  max-width: 600px;
}

.skills-assignment {
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid #444;
}

.skills-checkboxes {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 15px;
  margin-top: 10px;
}

.skill-checkbox {
  display: flex;
  align-items: center;
  cursor: pointer;
  font-size: 14px;
  color: #e0e0e0;
}

.skill-checkbox input {
  margin-right: 8px;
  cursor: pointer;
  accent-color: #007bff;
}

@media (max-width: 1023px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }

  .skills-grid {
    grid-template-columns: 1fr;
  }

  .skills-checkboxes {
    grid-template-columns: 1fr;
  }
}
</style>
