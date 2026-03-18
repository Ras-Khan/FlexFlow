<template>
  <div class="profile-page">
    <div class="profile-container">
      <div class="profile-card">
        <div class="profile-header">
          <h1>My Profile</h1>
          <button v-if="!isEditing" @click="enableEdit" class="btn-primary">Edit Profile</button>
          <button v-else @click="cancelEdit" class="btn-secondary">Cancel</button>
        </div>

        <div v-if="!isEditing" class="profile-view">
          <div class="profile-section">
            <div class="profile-field">
              <label>Name</label>
              <p>{{ profile.name }}</p>
            </div>

            <div class="profile-field">
              <label>Email</label>
              <p>{{ profile.email }}</p>
            </div>

            <div class="profile-field">
              <label>Role</label>
              <p>
                <span :class="['role-badge', `role-${profile.role}`]">
                  {{ capitalizeRole(profile.role) }}
                </span>
              </p>
            </div>

            <div class="profile-field">
              <label>Member Since</label>
              <p>{{ formatDate(profile.created_at) }}</p>
            </div>
          </div>
        </div>

        <div v-else class="profile-edit">
          <form @submit.prevent="saveProfile">
            <div class="form-group">
              <label for="name">Name</label>
              <input
                id="name"
                v-model="editedProfile.name"
                type="text"
                placeholder="Your name"
                required
              />
            </div>

            <div class="form-group">
              <label for="email">Email</label>
              <input
                id="email"
                v-model="editedProfile.email"
                type="email"
                placeholder="your@email.com"
                required
              />
            </div>

            <div class="form-actions">
              <button type="submit" class="btn-primary">Save Changes</button>
              <button type="button" @click="cancelEdit" class="btn-secondary">Cancel</button>
            </div>
          </form>
        </div>
      </div>

      <div class="password-card">
        <div class="card-header">
          <h2>Change Password</h2>
        </div>

        <div v-if="!showPasswordForm" class="card-content">
          <p>Update your password to keep your account secure.</p>
          <button @click="showPasswordForm = true" class="btn-secondary">Change Password</button>
        </div>

        <div v-else class="card-content">
          <form @submit.prevent="changePassword">
            <div class="form-group">
              <label for="current-password">Current Password</label>
              <input
                id="current-password"
                v-model="passwordForm.currentPassword"
                type="password"
                placeholder="Current password"
                required
              />
            </div>

            <div class="form-group">
              <label for="new-password">New Password</label>
              <input
                id="new-password"
                v-model="passwordForm.newPassword"
                type="password"
                placeholder="New password"
                required
                minlength="8"
              />
            </div>

            <div class="form-group">
              <label for="confirm-password">Confirm Password</label>
              <input
                id="confirm-password"
                v-model="passwordForm.confirmPassword"
                type="password"
                placeholder="Confirm new password"
                required
              />
            </div>

            <div class="password-requirements">
              <p><strong>Password Requirements:</strong></p>
              <ul>
                <li>At least 8 characters long</li>
                <li>Should contain mixed case letters</li>
                <li>Consider including numbers and symbols</li>
              </ul>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn-primary">Update Password</button>
              <button type="button" @click="cancelPasswordForm" class="btn-secondary">Cancel</button>
            </div>
          </form>
        </div>
      </div>

      <div class="account-info-card">
        <div class="card-header">
          <h2>Account Information</h2>
        </div>

        <div class="card-content">
          <table class="info-table">
            <tbody>
              <tr>
                <td><strong>Account ID:</strong></td>
                <td>{{ profile.id }}</td>
              </tr>
              <tr>
                <td><strong>Account Created:</strong></td>
                <td>{{ formatDateTime(profile.created_at) }}</td>
              </tr>
              <tr>
                <td><strong>Last Updated:</strong></td>
                <td>{{ formatDateTime(profile.updated_at) }}</td>
              </tr>
              <tr>
                <td><strong>Account Role:</strong></td>
                <td>
                  <span :class="['role-badge', `role-${profile.role}`]">
                    {{ capitalizeRole(profile.role) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const profile = ref({
  id: null,
  name: '',
  email: '',
  role: '',
  created_at: '',
  updated_at: '',
})
const editedProfile = ref({
  name: '',
  email: '',
})
const isEditing = ref(false)
const showPasswordForm = ref(false)
const passwordForm = ref({
  currentPassword: '',
  newPassword: '',
  confirmPassword: '',
})

const getToken = () => localStorage.getItem('token')
const getAuthHeaders = () => ({ Authorization: `Bearer ${getToken()}` })

function formatDate(dateString) {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString()
}

function formatDateTime(dateString) {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleString()
}

function capitalizeRole(role) {
  if (!role) return ''
  return role.charAt(0).toUpperCase() + role.slice(1)
}

onMounted(() => {
  loadProfile()
})

const loadProfile = async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/profile', {
      headers: getAuthHeaders(),
    })
    profile.value = response.data
    editedProfile.value = {
      name: response.data.name,
      email: response.data.email,
    }
  } catch (error) {
    if (error.response?.status === 401) {
      router.push('/login')
    } else {
      alert('Error loading profile. Check if Laravel is running!')
    }
  }
}

const enableEdit = () => {
  isEditing.value = true
}

const cancelEdit = () => {
  isEditing.value = false
  editedProfile.value = {
    name: profile.value.name,
    email: profile.value.email,
  }
}

const saveProfile = async () => {
  try {
    const response = await axios.put('http://localhost:8000/api/profile', editedProfile.value, {
      headers: getAuthHeaders(),
    })

    profile.value = response.data
    isEditing.value = false
    alert('Profile updated successfully!')

    // Update localStorage user info
    const user = JSON.parse(localStorage.getItem('user') || '{}')
    user.name = response.data.name
    user.email = response.data.email
    localStorage.setItem('user', JSON.stringify(user))
  } catch (error) {
    alert('Error updating profile. ' + (error.response?.data?.message || ''))
  }
}

const changePassword = async () => {
  if (passwordForm.value.newPassword !== passwordForm.value.confirmPassword) {
    alert('Passwords do not match!')
    return
  }

  if (passwordForm.value.newPassword.length < 8) {
    alert('Password must be at least 8 characters long!')
    return
  }

  try {
    await axios.put(
      'http://localhost:8000/api/profile',
      { password: passwordForm.value.newPassword },
      { headers: getAuthHeaders() }
    )

    alert('Password changed successfully!')
    passwordForm.value = {
      currentPassword: '',
      newPassword: '',
      confirmPassword: '',
    }
    showPasswordForm.value = false
  } catch (error) {
    alert('Error changing password. ' + (error.response?.data?.message || ''))
  }
}

const cancelPasswordForm = () => {
  showPasswordForm.value = false
  passwordForm.value = {
    currentPassword: '',
    newPassword: '',
    confirmPassword: '',
  }
}
</script>

<style scoped>
.profile-page {
  padding: 20px;
  max-width: 800px;
  margin: 0 auto;
  background: #0d0d0d;
  min-height: 100vh;
}

.profile-container {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.profile-card,
.password-card,
.account-info-card {
  background: #1a1a1a;
  border: 1px solid #444;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.profile-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #444;
  background-color: #2a2a2a;
}

.profile-header h1 {
  margin: 0;
  font-size: 24px;
  color: #e0e0e0;
}

.card-header {
  padding: 20px;
  border-bottom: 1px solid #444;
  background-color: #2a2a2a;
}

.card-header h2 {
  margin: 0;
  font-size: 20px;
  color: #e0e0e0;
}

.profile-view,
.card-content {
  padding: 20px;
  color: #e0e0e0;
}

.profile-section {
  display: grid;
  gap: 20px;
}

.profile-field {
  padding: 15px;
  border: 1px solid #444;
  border-radius: 4px;
  background-color: #2a2a2a;
}

.profile-field label {
  display: block;
  font-weight: 600;
  color: #b0b0b0;
  margin-bottom: 8px;
  font-size: 12px;
  text-transform: uppercase;
}

.profile-field p {
  margin: 0;
  font-size: 16px;
  color: #e0e0e0;
}

.role-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.role-admin {
  background-color: #d1ecf1;
  color: #0c5460;
}

.role-worker {
  background-color: #d4edda;
  color: #155724;
}

.role-client {
  background-color: #fff3cd;
  color: #856404;
}

.profile-edit,
.card-content form {
  padding: 20px;
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

.form-group input {
  width: 100%;
  padding: 10px;
  border: 1px solid #444;
  border-radius: 4px;
  font-size: 14px;
  font-family: inherit;
  background-color: #1a1a1a;
  color: #e0e0e0;
}

.form-group input:focus {
  outline: none;
  border-color: #007bff;
  box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.form-actions {
  display: flex;
  gap: 10px;
  margin-top: 20px;
}

.btn-primary,
.btn-secondary {
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
  flex: 1;
}

.btn-primary:hover {
  background-color: #0056b3;
}

.btn-secondary {
  background-color: #6c757d;
  color: white;
  flex: 1;
}

.btn-secondary:hover {
  background-color: #5a6268;
}

.password-requirements {
  background-color: #2a2a2a;
  padding: 15px;
  border-radius: 4px;
  margin: 20px 0;
  border-left: 4px solid #007bff;
}

.password-requirements p {
  margin: 0 0 8px 0;
  font-size: 13px;
  color: #e0e0e0;
}

.password-requirements ul {
  margin: 0;
  padding-left: 20px;
  color: #b0b0b0;
  font-size: 13px;
}

.password-requirements li {
  margin: 4px 0;
}

.info-table {
  width: 100%;
  border-collapse: collapse;
}

.info-table tr {
  border-bottom: 1px solid #444;
}

.info-table tr:hover {
  background-color: #2a2a2a;
}

.info-table td {
  padding: 12px;
  color: #e0e0e0;
}

.info-table td:first-child {
  width: 40%;
  color: #b0b0b0;
}

.info-table td:last-child {
  color: #e0e0e0;
}

@media (max-width: 768px) {
  .profile-page {
    padding: 15px;
  }

  .profile-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }

  .profile-header h1 {
    font-size: 20px;
  }

  .form-actions {
    flex-direction: column;
  }

  .form-actions button {
    width: 100%;
  }
}
</style>
