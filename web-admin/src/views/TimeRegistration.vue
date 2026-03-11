<template>
  <div class="time-registration-page">
    <h1>Time Registration</h1>
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
.time-table {
  width: 100%;
  border-collapse: collapse;
}
.time-table th,
.time-table td {
  border: 1px solid var(--color-border);
  padding: 8px;
  text-align: center;
}
</style>