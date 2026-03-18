<template>
  <div class="payroll-page">
    <div class="page-header">
      <h1>Payroll Management</h1>
      <button @click="toggleForm" class="btn-primary">
        {{ showAddForm ? (isEditing ? 'Cancel Edit' : 'Cancel') : (isEditing ? 'Edit Payroll' : 'Add Payroll') }}
      </button>
    </div>

    <div v-if="showAddForm" class="form-section">
      <h2>{{ isEditing ? 'Edit Payroll' : 'Add Payroll' }}</h2>
      <form @submit.prevent="savePayroll" class="payroll-form">
        <div class="form-group">
          <label for="worker">Worker</label>
          <select id="worker" v-model.number="newPayroll.worker_id" required>
            <option value="" disabled>Select worker</option>
            <option v-for="worker in workers" :key="worker.id" :value="worker.id">{{ worker.name }}</option>
          </select>
        </div>

        <div class="form-group">
          <label for="period_start">Period Start</label>
          <input id="period_start" type="date" v-model="newPayroll.period_start" required />
        </div>

        <div class="form-group">
          <label for="period_end">Period End</label>
          <input id="period_end" type="date" v-model="newPayroll.period_end" required />
        </div>

        <div class="form-group">
          <label for="gross_amount">Gross Amount (€)</label>
          <input
            id="gross_amount"
            type="number"
            v-model.number="newPayroll.gross_amount"
            step="0.01"
            min="0"
            required
          />
        </div>

        <div class="form-group">
          <label for="taxes">Taxes (€)</label>
          <input
            id="taxes"
            type="number"
            v-model.number="newPayroll.taxes"
            step="0.01"
            min="0"
            required
          />
        </div>

        <div class="form-group">
          <label for="net_amount">Net Amount (€)</label>
          <input
            id="net_amount"
            type="number"
            v-model.number="newPayroll.net_amount"
            step="0.01"
            min="0"
            readonly
          />
        </div>

        <div class="form-group">
          <label for="status">Status</label>
          <select id="status" v-model="newPayroll.status" required>
            <option value="pending">Pending</option>
            <option value="processed">Processed</option>
            <option value="paid">Paid</option>
          </select>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-primary">{{ isEditing ? 'Update' : 'Save' }}</button>
          <button type="button" @click="cancelAdd" class="btn-secondary">Cancel</button>
        </div>
      </form>
    </div>

    <div class="payroll-section">
      <h2>Payroll Records ({{ payrolls.length }})</h2>
      <div v-if="payrolls.length === 0" class="empty-state">
        <p>No payroll records yet. Create one above.</p>
      </div>

      <div v-else>
        <div v-if="isDesktop" class="payroll-table-wrapper">
          <table class="payroll-table">
            <thead>
              <tr>
                <th>Worker</th>
                <th>Period</th>
                <th>Gross</th>
                <th>Taxes</th>
                <th>Net</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="payroll in payrolls" :key="payroll.id">
                <td>{{ payroll.worker?.name || '-' }}</td>
                <td>{{ formatDate(payroll.period_start) }} - {{ formatDate(payroll.period_end) }}</td>
                <td class="amount">€{{ Number(payroll.gross_amount).toFixed(2) }}</td>
                <td class="amount">€{{ Number(payroll.taxes).toFixed(2) }}</td>
                <td class="amount highlight">€{{ Number(payroll.net_amount).toFixed(2) }}</td>
                <td>
                  <span :class="['status-badge', `status-${payroll.status}`]">
                    {{ capitalize(payroll.status) }}
                  </span>
                </td>
                <td>
                  <button @click.stop="editPayroll(payroll)" class="btn-edit">Edit</button>
                  <button @click.stop="deletePayroll(payroll.id)" class="btn-delete">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="payroll-grid">
          <div v-for="payroll in payrolls" :key="payroll.id" class="payroll-card">
            <div class="payroll-header">
              <h3>{{ payroll.worker?.name || 'Unknown Worker' }}</h3>
              <span :class="['status-badge', `status-${payroll.status}`]">
                {{ capitalize(payroll.status) }}
              </span>
            </div>

            <div class="payroll-details">
              <p>
                <strong>Period:</strong> {{ formatDate(payroll.period_start) }} - {{ formatDate(payroll.period_end) }}
              </p>
              <p><strong>Gross:</strong> €{{ Number(payroll.gross_amount).toFixed(2) }}</p>
              <p><strong>Taxes:</strong> €{{ Number(payroll.taxes).toFixed(2) }}</p>
              <p class="net"><strong>Net:</strong> €{{ Number(payroll.net_amount).toFixed(2) }}</p>
            </div>

            <div class="payroll-actions" @click.stop>
              <button @click="editPayroll(payroll)" class="btn-edit">Edit</button>
              <button @click="deletePayroll(payroll.id)" class="btn-delete">Delete</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const payrolls = ref([])
const workers = ref([])
const isDesktop = ref(window.innerWidth >= 1024)
const showAddForm = ref(false)
const isEditing = ref(false)
const editingPayrollId = ref(null)
const newPayroll = ref({
  worker_id: '',
  period_start: '',
  period_end: '',
  gross_amount: 0,
  taxes: 0,
  net_amount: 0,
  status: 'pending',
})

function updateDesktop() {
  isDesktop.value = window.innerWidth >= 1024
}

function formatDate(dateString) {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString()
}

function capitalize(str) {
  if (!str) return ''
  return str.charAt(0).toUpperCase() + str.slice(1)
}

onMounted(() => {
  window.addEventListener('resize', updateDesktop)
  fetchWorkers()
  fetchPayrolls()
})

onUnmounted(() => {
  window.removeEventListener('resize', updateDesktop)
})

// Auto-calculate net amount when gross or taxes change
watch(() => newPayroll.value.gross_amount, calculateNetAmount)
watch(() => newPayroll.value.taxes, calculateNetAmount)

const getToken = () => localStorage.getItem('token')
const getAuthHeaders = () => ({ Authorization: `Bearer ${getToken()}` })

const calculateNetAmount = () => {
  const gross = Number(newPayroll.value.gross_amount) || 0
  const taxes = Number(newPayroll.value.taxes) || 0
  newPayroll.value.net_amount = Math.max(0, gross - taxes)
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

const fetchPayrolls = async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/payrolls', {
      headers: getAuthHeaders(),
    })
    payrolls.value = response.data
  } catch (error) {
    if (error.response?.status === 401) {
      router.push('/login')
    } else {
      alert('Error fetching payroll records. Check if Laravel is running!')
    }
  }
}

const savePayroll = async () => {
  try {
    if (isEditing.value && editingPayrollId.value) {
      await axios.put(`http://localhost:8000/api/payrolls/${editingPayrollId.value}`, newPayroll.value, {
        headers: getAuthHeaders(),
      })
    } else {
      await axios.post('http://localhost:8000/api/payrolls', newPayroll.value, {
        headers: getAuthHeaders(),
      })
    }

    resetForm()
    fetchPayrolls()
  } catch (error) {
    alert('Error saving payroll record. Check if Laravel is running!')
  }
}

const resetForm = () => {
  newPayroll.value = {
    worker_id: '',
    period_start: '',
    period_end: '',
    gross_amount: 0,
    taxes: 0,
    net_amount: 0,
    status: 'pending',
  }
  showAddForm.value = false
  isEditing.value = false
  editingPayrollId.value = null
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

const editPayroll = (payroll) => {
  showAddForm.value = true
  isEditing.value = true
  editingPayrollId.value = payroll.id
  newPayroll.value = {
    worker_id: payroll.worker_id,
    period_start: payroll.period_start,
    period_end: payroll.period_end,
    gross_amount: payroll.gross_amount,
    taxes: payroll.taxes,
    net_amount: payroll.net_amount,
    status: payroll.status,
  }
}

const deletePayroll = async (payrollId) => {
  if (confirm('Are you sure you want to delete this payroll record?')) {
    try {
      await axios.delete(`http://localhost:8000/api/payrolls/${payrollId}`, {
        headers: getAuthHeaders(),
      })
      fetchPayrolls()
    } catch (error) {
      alert('Error deleting payroll record.')
    }
  }
}
</script>

<style scoped>
.payroll-page {
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

.payroll-form {
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
.form-group select {
  width: 100%;
  padding: 8px;
  border: 1px solid #444;
  border-radius: 4px;
  font-size: 14px;
  font-family: inherit;
  background-color: #1a1a1a;
  color: #e0e0e0;
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

.payroll-section {
  margin-top: 30px;
}

.payroll-section h2 {
  margin-bottom: 20px;
  font-size: 22px;
  color: #333;
}

.empty-state {
  text-align: center;
  padding: 40px 20px;
  color: #666;
}

.payroll-table-wrapper {
  overflow-x: auto;
}

.payroll-table {
  width: 100%;
  border-collapse: collapse;
  background: #1a1a1a;
  border: 1px solid #444;
  border-radius: 4px;
}

.payroll-table thead {
  background-color: #2a2a2a;
}

.payroll-table th {
  padding: 12px;
  text-align: left;
  font-weight: 600;
  border-bottom: 2px solid #444;
  color: #e0e0e0;
}

.payroll-table td {
  padding: 12px;
  border-bottom: 1px solid #333;
  color: #b0b0b0;
}

.payroll-table tbody tr:hover {
  background-color: #222;
}

.amount {
  text-align: right;
  font-weight: 500;
}

.amount.highlight {
  color: #28a745;
  font-weight: 600;
}

.status-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.status-pending {
  background-color: #fff3cd;
  color: #856404;
}

.status-processed {
  background-color: #d1ecf1;
  color: #0c5460;
}

.status-paid {
  background-color: #d4edda;
  color: #155724;
}

.payroll-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.payroll-card {
  background: #1a1a1a;
  border: 1px solid #444;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.payroll-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  margin-bottom: 15px;
}

.payroll-header h3 {
  margin: 0;
  font-size: 18px;
  color: #e0e0e0;
  flex: 1;
}

.payroll-details {
  margin: 15px 0;
  font-size: 14px;
}

.payroll-details p {
  margin: 8px 0;
  color: #b0b0b0;
}

.payroll-details .net {
  color: #28a745;
  font-weight: 600;
}

.payroll-actions {
  display: flex;
  gap: 5px;
  margin-top: 15px;
}

.payroll-actions button {
  flex: 1;
  padding: 8px 12px;
  font-size: 12px;
}

@media (max-width: 1023px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }

  .payroll-grid {
    grid-template-columns: 1fr;
  }
}
</style>
