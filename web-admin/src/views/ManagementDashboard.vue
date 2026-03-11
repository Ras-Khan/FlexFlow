<template>
  <div class="management-dashboard">
    <h1>Management Analytics</h1>
    <!--
      Displays two summary charts:
      1. Bar chart of active workers (workers.labels vs workers.data)
      2. Pie chart of client distribution

      The current backend controller (AnalyticsController@index) returns hard‑coded
      values, e.g. labels ['Acme','Globex','Initech'] with corresponding counts.
      Those strings are just sample client names – replace or compute them in the API
      to show real data.
    -->
    <div class="charts">
      <!-- wrap each canvas to let Chart.js size it correctly -->
      <div class="chart-container">
        <canvas ref="workersChartRef"></canvas>
      </div>
      <div class="chart-container">
        <canvas ref="clientsChartRef"></canvas>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, nextTick } from 'vue'
import Chart from 'chart.js/auto'
import axios from 'axios'

const workersChartRef = ref(null)
const clientsChartRef = ref(null)
let workersChartInstance = null
let clientsChartInstance = null

const renderCharts = async () => {
  try {
    const res = await axios.get('http://localhost:8000/api/analytics')
    const { workers, clients } = res.data

    await nextTick()

    const wCtx = workersChartRef.value?.getContext('2d')
    if (wCtx) {
      if (workersChartInstance) workersChartInstance.destroy()
      workersChartInstance = new Chart(wCtx, {
        type: 'bar',
        data: {
          labels: workers.labels,
          datasets: [{ label: 'Active workers', data: workers.data, backgroundColor: '#3498db' }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
        }
      })
    }

    const cCtx = clientsChartRef.value?.getContext('2d')
    if (cCtx) {
      if (clientsChartInstance) clientsChartInstance.destroy()
      clientsChartInstance = new Chart(cCtx, {
        type: 'pie',
        data: {
          labels: clients.labels,
          datasets: [{ data: clients.data, backgroundColor: ['#3498db', '#2ecc71', '#e74c3c'] }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
        }
      })
    }
  } catch (e) {
    console.error('analytics error', e)
  }
}

onMounted(renderCharts)
</script>

<style scoped>
.charts {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
}

.chart-container {
  max-width: 600px;
  height: 300px;
}

.chart-container canvas {
  width: 100%;
  height: 100%;
  display: block;
}
</style>