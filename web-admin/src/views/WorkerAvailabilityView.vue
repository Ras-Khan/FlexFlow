<template>
  <div class="availability-page">
    <div class="page-header">
      <h1>Worker Availability Management</h1>
    </div>

    <div class="worker-selector">
      <label for="worker-select">Select Worker:</label>
      <select id="worker-select" v-model.number="selectedWorker" @change="loadAvailability">
        <option value="">Choose a worker...</option>
        <option v-for="worker in workers" :key="worker.id" :value="worker.id">{{ worker.name }}</option>
      </select>
    </div>

    <div v-if="selectedWorker" class="availability-content">
      <div class="controls">
        <div class="date-picker">
          <label for="month-picker">Select Month:</label>
          <input id="month-picker" type="month" v-model="selectedMonth" @change="generateCalendar" />
        </div>

        <button @click="toggleBulkEdit" class="btn-primary">{{ showBulkEdit ? 'Done Editing' : 'Bulk Edit' }}</button>
      </div>

      <div class="calendar-view">
        <h2>{{ monthYear }}</h2>

        <div class="calendar-header">
          <div v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="day" class="day-name">
            {{ day }}
          </div>
        </div>

        <div class="calendar-grid">
          <div
            v-for="(day, index) in calendarDays"
            :key="index"
            :class="['calendar-day', { 'other-month': !day.isCurrentMonth, 'today': day.isToday }]"
          >
            <div class="day-number">{{ day.date }}</div>

            <div v-if="day.isCurrentMonth" class="availability-toggle">
              <span v-if="showBulkEdit" class="edit-mode">
                <input
                  type="checkbox"
                  :checked="isAvailable(day.fullDate)"
                  @change="toggleAvailability(day.fullDate)"
                />
              </span>
              <span v-else :class="['status', { available: isAvailable(day.fullDate), unavailable: !isAvailable(day.fullDate) }]">
                {{ isAvailable(day.fullDate) ? '✓ Available' : '✗ Unavailable' }}
              </span>
            </div>
          </div>
        </div>

        <div v-if="showBulkEdit" class="bulk-actions">
          <button @click="saveChanges" class="btn-primary">Save Changes</button>
          <button @click="fillMonth" class="btn-secondary">Mark All Available</button>
          <button @click="clearMonth" class="btn-secondary">Mark All Unavailable</button>
        </div>
      </div>

      <div class="quick-actions">
        <h3>Quick Actions</h3>
        <button @click="markNextWeekAvailable" class="btn-secondary">Mark Next 7 Days Available</button>
        <button @click="markNextMonth" class="btn-secondary">Mark Next Month Available</button>
      </div>
    </div>

    <div v-else class="empty-state">
      <p>Select a worker to manage their availability</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const workers = ref([])
const selectedWorker = ref('')
const selectedMonth = ref(new Date().toISOString().slice(0, 7))
const availabilities = ref({})
const showBulkEdit = ref(false)
const changedDates = ref(new Set())
const calendarDays = ref([])

const getToken = () => localStorage.getItem('token')
const getAuthHeaders = () => ({ Authorization: `Bearer ${getToken()}` })

const monthYear = computed(() => {
  if (!selectedMonth.value) return ''
  const [year, month] = selectedMonth.value.split('-')
  const date = new Date(year, parseInt(month) - 1)
  return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
})

onMounted(() => {
  fetchWorkers()
})

const fetchWorkers = async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/users', {
      headers: getAuthHeaders(),
    })
    workers.value = response.data.filter((u) => u.role === 'worker')
  } catch (error) {
    if (error.response?.status === 401) {
      router.push('/login')
    } else {
      console.error('Error fetching workers:', error)
    }
  }
}

const loadAvailability = async () => {
  if (!selectedWorker.value) return

  try {
    const response = await axios.get('http://localhost:8000/api/availabilities', {
      headers: getAuthHeaders(),
    })

    availabilities.value = {}
    response.data.forEach((av) => {
      if (av.worker_id === selectedWorker.value) {
        availabilities.value[av.date] = av.available
      }
    })

    changedDates.value.clear()
    generateCalendar()
  } catch (error) {
    console.error('Error loading availability:', error)
  }
}

const generateCalendar = () => {
  const [year, month] = selectedMonth.value.split('-')
  const firstDay = new Date(year, parseInt(month) - 1, 1)
  const lastDay = new Date(year, parseInt(month), 0)
  const startDate = new Date(firstDay)
  startDate.setDate(startDate.getDate() - firstDay.getDay())

  calendarDays.value = []
  const today = new Date().toISOString().split('T')[0]

  for (let i = 0; i < 42; i++) {
    const date = new Date(startDate)
    date.setDate(date.getDate() + i)
    const fullDate = date.toISOString().split('T')[0]
    const isCurrentMonth = date.getMonth() === firstDay.getMonth()

    calendarDays.value.push({
      date: date.getDate(),
      fullDate,
      isCurrentMonth,
      isToday: fullDate === today,
    })
  }
}

const isAvailable = (date) => {
  // If we have data for this date, use it
  if (date in availabilities.value) {
    return availabilities.value[date]
  }
  // Default to available if no record exists
  return true
}

const toggleAvailability = (date) => {
  const currentValue = availabilities.value[date] !== undefined ? availabilities.value[date] : true
  availabilities.value[date] = !currentValue
  changedDates.value.add(date)
}

const toggleBulkEdit = () => {
  showBulkEdit.value = !showBulkEdit.value
}

const fillMonth = () => {
  const [year, month] = selectedMonth.value.split('-')
  const firstDay = new Date(year, parseInt(month) - 1, 1)
  const lastDay = new Date(year, parseInt(month), 0)

  for (let d = 1; d <= lastDay.getDate(); d++) {
    const date = new Date(year, parseInt(month) - 1, d)
    const fullDate = date.toISOString().split('T')[0]
    availabilities.value[fullDate] = true
    changedDates.value.add(fullDate)
  }
}

const clearMonth = () => {
  const [year, month] = selectedMonth.value.split('-')
  const lastDay = new Date(year, parseInt(month), 0)

  for (let d = 1; d <= lastDay.getDate(); d++) {
    const date = new Date(year, parseInt(month) - 1, d)
    const fullDate = date.toISOString().split('T')[0]
    availabilities.value[fullDate] = false
    changedDates.value.add(fullDate)
  }
}

const markNextWeekAvailable = () => {
  const today = new Date()
  for (let i = 0; i < 7; i++) {
    const date = new Date(today)
    date.setDate(date.getDate() + i)
    const fullDate = date.toISOString().split('T')[0]
    availabilities.value[fullDate] = true
    changedDates.value.add(fullDate)
  }
  generateCalendar()
}

const markNextMonth = () => {
  const today = new Date()
  const nextMonth = new Date(today.getFullYear(), today.getMonth() + 1, 1)
  const lastDay = new Date(nextMonth.getFullYear(), nextMonth.getMonth() + 1, 0)

  for (let d = 1; d <= lastDay.getDate(); d++) {
    const date = new Date(nextMonth.getFullYear(), nextMonth.getMonth(), d)
    const fullDate = date.toISOString().split('T')[0]
    availabilities.value[fullDate] = true
    changedDates.value.add(fullDate)
  }
  generateCalendar()
}

const saveChanges = async () => {
  if (changedDates.value.size === 0) {
    alert('No changes to save')
    return
  }

  try {
    for (const date of changedDates.value) {
      const available = availabilities.value[date]
      const existingAV = Object.values(availabilities.value).find((av) => av.date === date)

      if (existingAV && existingAV.id) {
        // Update existing
        await axios.put(
          `http://localhost:8000/api/availabilities/${existingAV.id}`,
          { worker_id: selectedWorker.value, date, available },
          { headers: getAuthHeaders() }
        )
      } else {
        // Create new
        await axios.post(
          'http://localhost:8000/api/availabilities',
          { worker_id: selectedWorker.value, date, available },
          { headers: getAuthHeaders() }
        )
      }
    }

    alert('Changes saved successfully!')
    changedDates.value.clear()
    showBulkEdit.value = false
    loadAvailability()
  } catch (error) {
    alert('Error saving availability changes')
    console.error(error)
  }
}
</script>

<style scoped>
.availability-page {
  padding: 20px;
  max-width: 1000px;
  margin: 0 auto;
  background: #0d0d0d;
  min-height: 100vh;
}

.page-header {
  margin-bottom: 30px;
}

.page-header h1 {
  margin: 0;
  font-size: 28px;
  color: #e0e0e0;
}

.worker-selector {
  margin-bottom: 30px;
  padding: 20px;
  background: #2a2a2a;
  border-radius: 8px;
  border: 1px solid #444;
}

.worker-selector label {
  display: block;
  font-weight: 600;
  color: #b0b0b0;
  margin-bottom: 8px;
}

.worker-selector select {
  width: 100%;
  max-width: 400px;
  padding: 10px;
  border: 1px solid #444;
  border-radius: 4px;
  font-size: 14px;
  font-family: inherit;
  background-color: #1a1a1a;
  color: #e0e0e0;
}

.availability-content {
  background: #1a1a1a;
  border: 1px solid #444;
  border-radius: 8px;
  padding: 20px;
}

.controls {
  display: flex;
  gap: 20px;
  margin-bottom: 30px;
  align-items: center;
}

.date-picker {
  display: flex;
  align-items: center;
  gap: 10px;
}

.date-picker label {
  font-weight: 600;
  color: #b0b0b0;
}

.date-picker input {
  padding: 8px;
  border: 1px solid #444;
  border-radius: 4px;
  font-size: 14px;
  background-color: #2a2a2a;
  color: #e0e0e0;
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

.calendar-view h2 {
  margin: 0 0 20px 0;
  font-size: 20px;
  color: #e0e0e0;
  text-align: center;
}

.calendar-header {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 8px;
  margin-bottom: 10px;
}

.day-name {
  text-align: center;
  font-weight: 600;
  color: #b0b0b0;
  padding: 10px 0;
  border-bottom: 2px solid #444;
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 8px;
  margin-bottom: 30px;
}

.calendar-day {
  border: 1px solid #444;
  border-radius: 4px;
  padding: 10px;
  min-height: 100px;
  background: #2a2a2a;
  display: flex;
  flex-direction: column;
}

.calendar-day.other-month {
  background-color: #1a1a1a;
  color: #666;
}

.calendar-day.today {
  background-color: #1a3a4a;
  border: 2px solid #007bff;
}

.day-number {
  font-weight: 600;
  margin-bottom: 8px;
  color: #e0e0e0;
}

.calendar-day.other-month .day-number {
  color: #666;
}

.availability-toggle {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  color: #e0e0e0;
}

.availability-toggle.edit-mode {
  cursor: pointer;
}

.status {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 3px;
  font-weight: 600;
  font-size: 11px;
}

.status.available {
  background-color: #1a4a2a;
  color: #40c95c;
}

.status.unavailable {
  background-color: #4a1a1a;
  color: #ff6b6b;
}

.edit-mode input {
  width: 20px;
  height: 20px;
  cursor: pointer;
  accent-color: #007bff;
}

.bulk-actions {
  display: flex;
  gap: 10px;
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid #444;
}

.bulk-actions button {
  flex: 1;
}

.quick-actions {
  margin-top: 30px;
  padding-top: 20px;
  border-top: 1px solid #444;
}

.quick-actions h3 {
  margin: 0 0 15px 0;
  color: #e0e0e0;
}

.quick-actions {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.quick-actions button {
  max-width: 250px;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #888;
}

@media (max-width: 768px) {
  .calendar-grid {
    gap: 4px;
  }

  .calendar-day {
    padding: 5px;
    min-height: 80px;
  }

  .day-number {
    font-size: 12px;
  }

  .availability-toggle {
    font-size: 10px;
  }

  .controls {
    flex-direction: column;
    align-items: flex-start;
  }

  .bulk-actions {
    flex-direction: column;
  }

  .quick-actions button {
    max-width: 100%;
  }
}
</style>
