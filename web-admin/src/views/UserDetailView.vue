<template>
  <div class="user-detail-page">
    <div class="detail-container">
      <router-link to="/users" class="back-button">← Back to Users</router-link>

      <div v-if="loading" class="loading">Loading user details...</div>
      <div v-else-if="user" class="user-detail-card">
        <div class="detail-header">
          <h1>{{ user.name }}</h1>
          <div class="detail-actions">
            <button @click="editUser" class="btn-primary">Edit</button>
            <button @click="deleteUser" class="btn-danger">Delete</button>
          </div>
        </div>

        <div class="detail-grid">
          <div class="detail-section">
            <h3>Email</h3>
            <p class="email-text">{{ user.email }}</p>
          </div>

          <div class="detail-section">
            <h3>Status</h3>
            <p v-if="user.email_verified_at" class="verified">
              ✓ Email Verified
            </p>
            <p v-else class="unverified">
              ○ Email Not Verified
            </p>
          </div>

          <div class="detail-section">
            <h3>Joined</h3>
            <p>{{ formatDate(user.created_at) }}</p>
          </div>

          <div class="detail-section">
            <h3>Last Updated</h3>
            <p>{{ formatDate(user.updated_at) }}</p>
          </div>
        </div>

        <div class="edit-section" v-if="isEditing">
          <h2>Edit User</h2>
          <form @submit.prevent="saveUser" class="edit-form">
            <div class="form-group">
              <label for="name">Name</label>
              <input id="name" v-model="editedUser.name" required />
            </div>

            <div class="form-group">
              <label for="email">Email</label>
              <input id="email" v-model="editedUser.email" type="email" required />
            </div>

            <div class="form-group">
              <label for="password">Password (leave blank to keep current)</label>
              <input id="password" v-model="editedUser.password" type="password" />
            </div>

            <div class="form-actions">
              <button type="submit" class="btn-primary">Save Changes</button>
              <button type="button" @click="cancelEdit" class="btn-secondary">Cancel</button>
            </div>
          </form>
        </div>
      </div>
      <div v-else class="not-found">
        <p>User not found</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const router = useRouter()
const user = ref(null)
const editedUser = ref({})
const isEditing = ref(false)
const loading = ref(true)

const userId = route.params.id

const fetchUser = async () => {
  try {
    const response = await axios.get(`http://localhost:8000/api/users/${userId}`)
    user.value = response.data
    editedUser.value = { ...user.value }
    loading.value = false
  } catch (error) {
    console.error('Error fetching user:', error)
    loading.value = false
  }
}

const editUser = () => {
  isEditing.value = true
}

const cancelEdit = () => {
  isEditing.value = false
  editedUser.value = { ...user.value }
}

const saveUser = async () => {
  try {
    const updateData = { ...editedUser.value }
    if (!updateData.password) {
      delete updateData.password
    }
    await axios.put(`http://localhost:8000/api/users/${userId}`, updateData)
    user.value = { ...editedUser.value }
    isEditing.value = false
    alert('User updated successfully!')
  } catch (error) {
    alert('Error updating user')
  }
}

const deleteUser = async () => {
  if (confirm('Are you sure you want to delete this user?')) {
    try {
      await axios.delete(`http://localhost:8000/api/users/${userId}`)
      alert('User deleted successfully!')
      router.push('/users')
    } catch (error) {
      alert('Error deleting user')
    }
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

onMounted(fetchUser)
</script>

<style scoped>
.user-detail-page {
  width: 100%;
  padding: 20px;
}

.detail-container {
  max-width: 900px;
  margin: 0 auto;
}

.back-button {
  display: inline-block;
  margin-bottom: 20px;
  padding: 10px 15px;
  background: #ecf0f1;
  color: #2c3e50;
  text-decoration: none;
  border-radius: 4px;
  transition: background 0.3s;
}

.back-button:hover {
  background: #d5dbdb;
}

.loading,
.not-found {
  text-align: center;
  padding: 40px;
  font-size: 1.1rem;
  color: #7f8c8d;
}

.user-detail-card {
  background: var(--glass-bg);
  backdrop-filter: var(--glass-backdrop);
  border: 1px solid var(--glass-border);
  box-shadow: var(--glass-shadow);
  border-radius: 12px;
  padding: 30px;
}

.detail-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 30px;
  padding-bottom: 20px;
  border-bottom: 1px solid var(--glass-border);
}

.detail-header h1 {
  margin: 0;
  color: var(--color-heading);
  font-size: 2rem;
}

.detail-actions {
  display: flex;
  gap: 10px;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 25px;
  margin-bottom: 30px;
}

.detail-section {
  padding: 20px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid var(--glass-border);
  border-radius: 8px;
  backdrop-filter: blur(5px);
}

.detail-section h3 {
  margin: 0 0 10px 0;
  color: var(--color-heading);
  font-size: 0.95rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
  opacity: 0.9;
}

.detail-section p {
  margin: 0;
  color: var(--color-text);
  line-height: 1.6;
  opacity: 0.9;
}

.email-text {
  font-size: 1rem;
  word-break: break-all;
}

.verified {
  color: #27ae60;
  font-weight: 600;
}

.unverified {
  color: #e67e22;
  font-weight: 600;
}

.edit-section {
  margin-top: 30px;
  padding-top: 30px;
  border-top: 2px solid #ecf0f1;
}

.edit-section h2 {
  color: #2c3e50;
  margin-bottom: 20px;
}

.edit-form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  margin-bottom: 5px;
  color: #2c3e50;
  font-weight: 500;
}

.form-group input {
  padding: 10px;
  border: 1px solid #bdc3c7;
  border-radius: 4px;
  font-family: inherit;
  font-size: 1rem;
}

.form-group input:focus {
  outline: none;
  border-color: #3498db;
  box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.form-actions {
  display: flex;
  gap: 10px;
  margin-top: 20px;
}

.btn-primary,
.btn-secondary,
.btn-danger {
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s;
}

.btn-primary {
  background: #3498db;
  color: white;
}

.btn-primary:hover {
  background: #2980b9;
}

.btn-secondary {
  background: #bdc3c7;
  color: #2c3e50;
}

.btn-secondary:hover {
  background: #95a5a6;
}

.btn-danger {
  background: #e74c3c;
  color: white;
}

.btn-danger:hover {
  background: #c0392b;
}

@media (max-width: 768px) {
  .user-detail-page {
    padding: 10px;
  }

  .user-detail-card {
    padding: 20px;
  }

  .detail-header {
    flex-direction: column;
    gap: 15px;
  }

  .detail-header h1 {
    font-size: 1.5rem;
  }

  .detail-actions {
    width: 100%;
  }

  .detail-actions button {
    flex: 1;
  }

  .detail-grid {
    grid-template-columns: 1fr;
    gap: 15px;
  }

  .detail-section {
    padding: 15px;
  }

  .form-actions {
    flex-direction: column;
  }

  .form-actions button {
    width: 100%;
  }
}
</style>
