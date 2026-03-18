<template>
  <div class="time-registration-page">
    <div class="page-header">
      <h1>Time Registration</h1>
    </div>
    <table class="time-table">
      <thead>
        <tr>
          <th>Worker</th>
          <th v-for="day in weekDays" :key="day">{{ day }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="entry in entries" :key="entry.worker_id">
          <td>{{ entry.worker_name }}</td>
          <td v-for="day in weekDays" :key="day">
            <input
              type="text"
              v-model="entry.hours[day]"
              placeholder="0.0"
              @blur="saveEntry(entry, day)"
            />
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const weekDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
const entries = ref([])

function startOfWeek() {
  const d = new Date()
  const day = d.getDay() || 7;
  if (day !== 1) d.setHours(-24 * (day - 1));
  return d.toISOString().substr(0,10);
}
function endOfWeek() {
  const d = new Date(startOfWeek())
  d.setDate(d.getDate() + 6)
  return d.toISOString().substr(0,10);
}

const fetchEntries = async () => {
  try {
    const res = await axios.get('http://localhost:8000/api/time-entries', { params: { format: 'week', start: startOfWeek(), end: endOfWeek() } })
    entries.value = res.data
  } catch (e) {
    console.error('could not load entries', e)
  }
}

const saveEntry = async (entry, day) => {
  try {
    await axios.post('http://localhost:8000/api/time-entries', {
      worker_id: entry.worker_id,
      date: entry.dates[day],
      hours: entry.hours[day],
    })
  } catch (e) {
    console.error('save failed', e)
  }
}

onMounted(fetchEntries)
</script>

<style scoped>
.time-registration-page {
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

.time-table {
  width: 100%;
  border-collapse: collapse;
  background: var(--glass-bg);
  border: 1px solid var(--glass-border);
  box-shadow: var(--glass-shadow);
  border-radius: 16px;
  overflow: hidden;
}

.time-table th,
.time-table td {
  border: 1px solid var(--glass-border);
  padding: 12px;
  text-align: center;
}

.time-table th {
  background: rgba(255, 255, 255, 0.05);
  color: var(--color-heading);
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  backdrop-filter: blur(5px);
}

.time-table tbody tr {
  transition: all 0.3s ease;
  backdrop-filter: blur(2px);
}

.time-table tbody tr:hover {
  background: rgba(255, 255, 255, 0.08);
  transform: translateY(-1px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

@media (max-width: 768px) {
  .time-registration-page {
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

  .time-table th,
  .time-table td {
    padding: 10px;
  }
}

@media (max-width: 480px) {
  .time-registration-page {
    padding: 0 5px;
  }

  .page-header h1 {
    font-size: 1.3rem;
  }

  .time-table th,
  .time-table td {
    padding: 8px;
  }
}
</style>