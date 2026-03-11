<template>
  <div class="users-page">
    <div class="page-header">
      <h1>User Management</h1>
      <button @click="toggleFormUser" class="btn-primary">
        {{ showAddForm ? (isEditingUser ? 'Cancel Edit' : 'Cancel') : (isEditingUser ? 'Edit User' : 'Add New User') }}
      </button>
    </div>

    <div v-if="showAddForm" class="form-section">
      <h2>{{ isEditingUser ? 'Edit User' : 'Add New User' }}</h2>
      <form @submit.prevent="saveUser" class="user-form">
        <div class="form-group">
          <label for="name">Name</label>
          <input
            id="name"
            v-model="newUser.name"
            placeholder="Enter full name"
            required
          />
        </div>

        <div class="form-group">
          <label for="email">Email</label>
          <input
            id="email"
            v-model="newUser.email"
            type="email"
            placeholder="Enter email address"
            required
          />
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input
            id="password"
            v-model="newUser.password"
            type="password"
            placeholder="Enter password"
            required
          />
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-primary">{{ isEditingUser ? 'Update User' : 'Create User' }}</button>
          <button type="button" @click="cancelAdd" class="btn-secondary">Cancel</button>
        </div>
      </form>
    </div>

    <div class="users-section">
      <h2>Current Users ({{ users.length }})</h2>

      <div v-if="users.length === 0" class="empty-state">
        <p>No users found. Add new user</p>
      </div>

      <div v-else>
        <div v-if="isDesktop" class="users-table-wrapper">
          <table class="users-table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Joined</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in users" :key="user.id">
                <td class="link-cell" @click="router.push(`/users/${user.id}`)">{{ user.name }}</td>
                <td>{{ user.email }}</td>
                <td>{{ formatDate(user.created_at) }}</td>
                <td>
                  <span v-if="user.email_verified_at">Verified</span>
                  <span v-else>Unverified</span>
                </td>
                <td>
                  <button @click.stop="editUser(user)" class="btn-edit">Edit</button>
                  <button @click.stop="deleteUser(user.id)" class="btn-delete">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="users-grid">
          <div v-for="user in users" :key="user.id" class="user-card" @click="router.push(`/users/${user.id}`)">
            <div class="user-header">
              <h3>{{ user.name }}</h3>
              <div class="user-actions" @click.stop>
                <button @click="editUser(user)" class="btn-edit">Edit</button>
                <button @click="deleteUser(user.id)" class="btn-delete">Delete</button>
              </div>
            </div>

            <p class="user-email">{{ user.email }}</p>
            <div class="user-date">Joined: {{ formatDate(user.created_at) }}</div>
            <div v-if="user.email_verified_at" class="verified-badge">
              Email Verified
            </div>
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
const users = ref([])
const isDesktop = ref(window.innerWidth >= 1024)
const showAddForm = ref(false)
const isEditingUser = ref(false)
const editingUserId = ref(null)
const newUser = ref({ name: '', email: '', password: '' })

function updateDesktop() {
  isDesktop.value = window.innerWidth >= 1024
}

onMounted(() => {
  window.addEventListener('resize', updateDesktop)
})

onUnmounted(() => {
  window.removeEventListener('resize', updateDesktop)
})

const fetchUsers = async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/users')
    users.value = response.data
  } catch (error) {
    alert("Error fetching users")
  }
}

const saveUser = async () => {
  try {
    if (isEditingUser.value && editingUserId.value) {
      await axios.put(`http://localhost:8000/api/users/${editingUserId.value}`, newUser.value)
    } else {
      await axios.post('http://localhost:8000/api/users', newUser.value)
    }

    newUser.value = { name: '', email: '', password: '' }
    showAddForm.value = false
    isEditingUser.value = false
    editingUserId.value = null
    fetchUsers()
  } catch (error) {
    alert("Error creating user")
  }
}

const cancelAdd = () => {
  newUser.value = { name: '', email: '', password: '' }
  showAddForm.value = false
  if (isEditingUser.value) {
    isEditingUser.value = false
    editingUserId.value = null
  }
}

const toggleFormUser = () => {
  if (showAddForm.value) {
    cancelAdd()
  } else {
    showAddForm.value = true
  }
}

const editUser = (user) => {
  showAddForm.value = true
  isEditingUser.value = true
  editingUserId.value = user.id
  newUser.value = {
    name: user.name || '',
    email: user.email || '',
    password: '', 
  }
}

const deleteUser = async (userId) => {
  if (confirm('Are you sure you want to delete this user?')) {
    try {
      await axios.delete(`http://localhost:8000/api/users/${userId}`)
      fetchUsers()
    } catch (error) {
      alert("Error deleting user. Check if Laravel is running!")
    }
  }
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString()
}

onMounted(fetchUsers)
</script>

<style scoped>
.users-page {
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

.user-form {
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

.form-group input {
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

.users-section {
  background: var(--glass-bg);
  backdrop-filter: var(--glass-backdrop);
  border: 1px solid var(--glass-border);
  box-shadow: var(--glass-shadow);
  padding: 30px;
  border-radius: 12px;
}

.users-section h2 {
  margin-top: 0;
  color: var(--color-heading);
  border-bottom: 1px solid var(--glass-border);
  padding-bottom: 15px;
  font-size: 1.5rem;
  font-weight: 600;
}

.users-table-wrapper {
  overflow-x: auto;
  margin-top: 20px;
  border-radius: 16px;
  backdrop-filter: var(--glass-backdrop);
  background: var(--glass-bg);
  border: 1px solid var(--glass-border);
  box-shadow: var(--glass-shadow);
}

.users-table {
  width: 100%;
  border-collapse: collapse;
  background: transparent;
}

.users-table th,
.users-table td {
  padding: 18px 20px;
  text-align: left;
  border-bottom: 1px solid var(--glass-border);
}

.users-table th {
  background: rgba(255, 255, 255, 0.05);
  font-weight: 700;
  color: var(--color-heading);
  font-size: 0.95rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  backdrop-filter: blur(5px);
}

.users-table tbody tr {
  transition: all 0.3s ease;
  backdrop-filter: blur(2px);
}

.users-table tbody tr:hover {
  background: rgba(255, 255, 255, 0.08);
  transform: translateY(-1px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.users-table tbody tr:nth-child(odd) {
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

.users-table td:nth-child(2),
.users-table td:nth-child(3),
.users-table td:nth-child(4) {
  color: var(--color-text);
  font-size: 0.95rem;
  opacity: 0.9;
}

.users-table td:last-child {
  display: flex;
  gap: 10px;
}

.empty-state {
  text-align: center;
  padding: 40px;
  color: #7f8c8d;
}

.users-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 24px;
  margin-top: 20px;
}

.user-card {
  background: var(--glass-bg);
  backdrop-filter: var(--glass-backdrop);
  border: 1px solid var(--glass-border);
  box-shadow: var(--glass-shadow);
  border-radius: 12px;
  padding: 24px;
  transition: all 0.3s ease;
  cursor: pointer;
}

.user-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--glass-shadow-hover);
  background: var(--glass-bg-hover);
}

.user-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 10px;
  flex-wrap: wrap;
  gap: 10px;
}

.user-header h3 {
  margin: 0;
  color: var(--color-heading);
  flex: 1;
  font-size: 1.2rem;
}

.user-actions {
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

.user-email {
  color: #7f8c8d;
  margin-bottom: 10px;
  font-size: 0.9rem;
}

.user-date {
  font-size: 0.8rem;
  color: #95a5a6;
  margin-bottom: 10px;
}

.verified-badge {
  display: inline-block;
  background: #27ae60;
  color: white;
  padding: 3px 8px;
  border-radius: 12px;
  font-size: 0.7rem;
  font-weight: bold;
}

/* Responsive styles */
@media (max-width: 1024px) {
  .users-grid {
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 18px;
  }

  .user-card {
    padding: 20px;
    border-radius: 10px;
  }
}

@media (min-width: 1200px) {
  .users-grid {
    grid-template-columns: repeat(auto-fill, minmax(450px, 1fr));
    gap: 30px;
  }

  .user-card {
    padding: 28px;
  }

  .user-header h3 {
    font-size: 1.4rem;
  }

  .user-email {
    font-size: 1rem;
  }
}

@media (max-width: 768px) {
  .users-page {
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

  .users-section {
    padding: 15px;
  }

  .users-section h2 {
    font-size: 1.1rem;
  }

  .users-grid {
    grid-template-columns: 1fr;
    gap: 15px;
  }

  .user-card {
    padding: 15px;
  }

  .user-header {
    flex-direction: column;
    align-items: stretch;
  }

  .user-header h3 {
    font-size: 1.1rem;
  }

  .user-actions {
    align-self: flex-end;
    margin-top: 10px;
  }
}

@media (max-width: 480px) {
  .users-page {
    padding: 0 5px;
  }

  .page-header h1 {
    font-size: 1.3rem;
  }

  .form-section,
  .users-section {
    padding: 12px;
  }

  .user-card {
    padding: 12px;
  }

  .user-header h3 {
    font-size: 1rem;
  }
}
</style>