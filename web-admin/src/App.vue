<template>
  <div id="app">
    <div v-if="isMobileMenuOpen" class="mobile-overlay" @click="closeMobileMenu"></div>

    <nav class="sidebar" :class="{ 'sidebar-open': isMobileMenuOpen }">
      <div class="sidebar-header">
        <h2>FlexFlow Admin</h2>
        <button class="mobile-close-btn" @click="closeMobileMenu" v-if="isMobile" aria-label="Close menu">
          ✕
        </button>
      </div>
      <ul class="sidebar-menu" v-if="user">
        <li>
          <router-link to="/" class="sidebar-link" @click="closeMobileMenu">
            <span class="icon">D</span>
            Dashboard
          </router-link>
        </li>
        <li v-if="['admin','worker'].includes(user.role)">
          <router-link to="/time" class="sidebar-link" @click="closeMobileMenu">
            <span class="icon">W</span>
            Work
          </router-link>
        </li>
        <li v-if="user.role === 'admin'">
          <router-link to="/invoicing" class="sidebar-link" @click="closeMobileMenu">
            <span class="icon">$</span>
            Invoicing
          </router-link>
        </li>
        <li v-if="user.role === 'admin'">
          <router-link to="/analytics" class="sidebar-link" @click="closeMobileMenu">
            <span class="icon">📊</span>
            Analytics
          </router-link>
        </li>
        <li>
          <router-link to="/jobs" class="sidebar-link" @click="closeMobileMenu">
            <span class="icon">J</span>
            Jobs
          </router-link>
        </li>
        <li v-if="user.role === 'admin'">
          <router-link to="/assignments" class="sidebar-link" @click="closeMobileMenu">
            <span class="icon">A</span>
            Assignments
          </router-link>
        </li>
        <li v-if="user.role === 'admin'">
          <router-link to="/users" class="sidebar-link" @click="closeMobileMenu">
            <span class="icon">U</span>
            Users
          </router-link>
        </li>
      </ul>
    </nav>

    <main class="main-content" :class="{ 'main-content-mobile': isMobile }">
      <div class="mobile-header" v-if="isMobile">
        <button class="mobile-menu-btn" @click="toggleMobileMenu" aria-label="Open menu">
          ☰
        </button>
        <h1 class="mobile-title">FlexFlow Admin</h1>
      </div>

      <router-view />
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { RouterView } from 'vue-router'

const isMobileMenuOpen = ref(false)
const isMobile = ref(false)
const user = ref(JSON.parse(localStorage.getItem('user') || 'null'))

// watch user logout/login
watch(user, (val) => {
  if (!val) {
    router.push('/login')
  }
})

function checkMobile() {
  const w = window.innerWidth
  isMobile.value = w <= 768
  console.debug('[App] window width', w, 'isMobile?', isMobile.value)
}

watch(isMobile, (newVal) => {
  if (!newVal) {
    isMobileMenuOpen.value = false
  }
})

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
}

const closeMobileMenu = () => {
  isMobileMenuOpen.value = false
}

onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile)
})
</script>

<style scoped>
#app {
  display: flex;
  min-height: 100vh;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.sidebar {
  width: 250px;
  background: #2c3e50;
  color: white;
  padding: 20px 0;
  position: fixed;
  height: 100vh;
  overflow-y: auto;
  z-index: 1000;
  transform: translateX(-100%);
  transition: transform 0.3s ease;
}

.sidebar-open {
  transform: translateX(0);
}

.mobile-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  z-index: 999;
}

.sidebar-header {
  padding: 0 20px 20px;
  border-bottom: 1px solid #34495e;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.sidebar-header h2 {
  margin: 0;
  font-size: 1.5rem;
}

.mobile-close-btn {
  background: none;
  border: none;
  color: white;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 5px;
}

.sidebar-menu {
  list-style: none;
  padding: 0;
  margin: 20px 0 0 0;
}

.sidebar-menu li {
  margin: 0;
}

.sidebar-link {
  display: flex;
  align-items: center;
  padding: 12px 20px;
  color: #ecf0f1;
  text-decoration: none;
  transition: background 0.3s;
}

.sidebar-link:hover,
.sidebar-link.router-link-active {
  background: #34495e;
}

.icon {
  margin-right: 10px;
  font-size: 1.2rem;
}

.main-content {
  flex: 1;
  margin-left: 0;
  padding: 20px;
  background: var(--color-background-soft);
  min-height: 100vh;
  transition: margin-left 0.3s ease;
}

.main-content-mobile {
  margin-left: 0;
}

.mobile-header {
  display: none;
  align-items: center;
  padding: 15px 20px;
  background: var(--color-background);
  border-bottom: 1px solid var(--color-border);
  width: 100%;
}

@media (max-width: 768px) {
  .mobile-header {
    display: flex !important;
  }
}

.mobile-menu-btn {
  background: #3498db;
  color: white;
  border: none;
  padding: 10px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1.2rem;
  margin-right: 15px;
}

.mobile-title {
  margin: 0;
  font-size: 1.3rem;
  color: #2c3e50;
}

/* Desktop styles */
@media (min-width: 769px) {
  .sidebar {
    transform: translateX(0);
  }

  .main-content {
    margin-left: 250px;
  }

  .mobile-overlay,
  .mobile-header,
  .mobile-close-btn {
    display: none !important;
  }
}

/* Responsive styles */
@media (max-width: 768px) {
  .sidebar {
    width: 80%;
    max-width: 280px;
  }

  .main-content {
    padding: 10px;
    padding-top: 60px;       
  }

  .mobile-header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    margin: 0;           
    padding: 15px 10px;
    z-index: 1001;
  }
}

#app {
  background: var(--color-background);
}

</style>


<style>
.form-section { background: #f4f4f4; padding: 20px; border-radius: 8px; }
input, textarea { margin-bottom: 10px; width: 100%; max-width: 300px; }
.job-card { border: 1px solid #ddd; padding: 10px; margin: 10px 0; }
</style>