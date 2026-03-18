<template>
  <div class="companies-page">
    <div class="page-header">
      <h1>Companies Management</h1>
      <button @click="toggleForm" class="btn-primary">
        {{ showAddForm ? (isEditing ? 'Cancel Edit' : 'Cancel') : (isEditing ? 'Edit Company' : 'Add Company') }}
      </button>
    </div>

    <div v-if="showAddForm" class="form-section">
      <h2>{{ isEditing ? 'Edit Company' : 'Add Company' }}</h2>
      <form @submit.prevent="saveCompany" class="company-form">
        <div class="form-group">
          <label for="name">Company Name</label>
          <input id="name" v-model="newCompany.name" placeholder="Enter company name" required />
        </div>

        <div class="form-group">
          <label for="value">Value / Details</label>
          <textarea
            id="value"
            v-model="newCompany.value"
            placeholder="Enter company value or additional details"
            rows="4"
          ></textarea>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-primary">{{ isEditing ? 'Update' : 'Save' }}</button>
          <button type="button" @click="cancelAdd" class="btn-secondary">Cancel</button>
        </div>
      </form>
    </div>

    <div class="companies-section">
      <h2>Companies ({{ companies.length }})</h2>
      <div v-if="companies.length === 0" class="empty-state">
        <p>No companies yet. Create one above.</p>
      </div>

      <div v-else>
        <div v-if="isDesktop" class="companies-table-wrapper">
          <table class="companies-table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Value</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="company in companies" :key="company.id">
                <td>{{ company.name }}</td>
                <td>{{ company.value || '-' }}</td>
                <td>{{ formatDate(company.created_at) }}</td>
                <td>
                  <button @click.stop="editCompany(company)" class="btn-edit">Edit</button>
                  <button @click.stop="deleteCompany(company.id)" class="btn-delete">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="companies-grid">
          <div v-for="company in companies" :key="company.id" class="company-card">
            <div class="company-header">
              <h3>{{ company.name }}</h3>
              <div class="company-actions" @click.stop>
                <button @click="editCompany(company)" class="btn-edit">Edit</button>
                <button @click="deleteCompany(company.id)" class="btn-delete">Delete</button>
              </div>
            </div>

            <p class="company-value">{{ company.value || 'No details' }}</p>
            <div class="company-date">Created: {{ formatDate(company.created_at) }}</div>
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
const companies = ref([])
const isDesktop = ref(window.innerWidth >= 1024)
const showAddForm = ref(false)
const isEditing = ref(false)
const editingCompanyId = ref(null)
const newCompany = ref({ name: '', value: '' })

function updateDesktop() {
  isDesktop.value = window.innerWidth >= 1024
}

function formatDate(dateString) {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString()
}

onMounted(() => {
  window.addEventListener('resize', updateDesktop)
  fetchCompanies()
})

onUnmounted(() => {
  window.removeEventListener('resize', updateDesktop)
})

const fetchCompanies = async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('http://localhost:8000/api/companies', {
      headers: { Authorization: `Bearer ${token}` },
    })
    companies.value = response.data
  } catch (error) {
    if (error.response && error.response.status === 401) {
      router.push('/login')
    } else {
      alert('Error fetching companies. Check if Laravel is running!')
    }
  }
}

const saveCompany = async () => {
  try {
    const dataToSave = {
      name: newCompany.value.name,
      value: newCompany.value.value ? newCompany.value.value : null,
    }

    if (isEditing.value && editingCompanyId.value) {
      const token = localStorage.getItem('token')
      await axios.put(`http://localhost:8000/api/companies/${editingCompanyId.value}`, dataToSave, {
        headers: { Authorization: `Bearer ${token}` },
      })
    } else {
      const token = localStorage.getItem('token')
      await axios.post('http://localhost:8000/api/companies', dataToSave, {
        headers: { Authorization: `Bearer ${token}` },
      })
    }

    newCompany.value = { name: '', value: '' }
    showAddForm.value = false
    isEditing.value = false
    editingCompanyId.value = null
    fetchCompanies()
  } catch (error) {
    const errorMsg = error.response?.data?.message || 'Error saving company. Check if Laravel is running!'
    alert(errorMsg)
    console.error(error)
  }
}

const cancelAdd = () => {
  newCompany.value = { name: '', value: '' }
  showAddForm.value = false
  if (isEditing.value) {
    isEditing.value = false
    editingCompanyId.value = null
  }
}

const toggleForm = () => {
  if (showAddForm.value) {
    cancelAdd()
  } else {
    showAddForm.value = true
  }
}

const editCompany = (company) => {
  showAddForm.value = true
  isEditing.value = true
  editingCompanyId.value = company.id
  newCompany.value = {
    name: company.name || '',
    value: company.value || '',
  }
}

const deleteCompany = async (companyId) => {
  if (confirm('Are you sure you want to delete this company?')) {
    try {
      const token = localStorage.getItem('token')
      await axios.delete(`http://localhost:8000/api/companies/${companyId}`, {
        headers: { Authorization: `Bearer ${token}` },
      })
      fetchCompanies()
    } catch (error) {
      alert('Error deleting company.')
    }
  }
}
</script>

<style scoped>
.companies-page {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
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

.company-form {
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
.form-group textarea {
  width: 100%;
  padding: 8px;
  border: 1px solid #444;
  border-radius: 4px;
  font-size: 14px;
  font-family: inherit;
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

.companies-section {
  margin-top: 30px;
}

.companies-section h2 {
  margin-bottom: 20px;
  font-size: 22px;
  color: #333;
}

.empty-state {
  text-align: center;
  padding: 40px 20px;
  color: #666;
}

.companies-table-wrapper {
  overflow-x: auto;
}

.companies-table {
  width: 100%;
  border-collapse: collapse;
  background: #1a1a1a;
  border: 1px solid #444;
  border-radius: 4px;
}

.companies-table thead {
  background-color: #2a2a2a;
}

.companies-table th {
  padding: 12px;
  text-align: left;
  font-weight: 600;
  border-bottom: 2px solid #444;
  color: #e0e0e0;
}

.companies-table td {
  padding: 12px;
  border-bottom: 1px solid #333;
  color: #b0b0b0;
}

.companies-table tbody tr:hover {
  background-color: #222;
}

.companies-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.company-card {
  background: #1a1a1a;
  border: 1px solid #444;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.company-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  margin-bottom: 15px;
}

.company-header h3 {
  margin: 0;
  font-size: 18px;
  color: #e0e0e0;
  flex: 1;
}

.company-actions {
  display: flex;
  gap: 5px;
}

.company-actions button {
  padding: 6px 12px;
  font-size: 12px;
}

.company-value {
  color: #b0b0b0;
  margin: 10px 0;
  font-size: 14px;
}

.company-date {
  color: #666;
  font-size: 12px;
}

@media (max-width: 1023px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }

  .companies-grid {
    grid-template-columns: 1fr;
  }
}
</style>
