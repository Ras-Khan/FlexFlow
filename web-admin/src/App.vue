<template>
  <div id="app">
    <div v-if="isMobileMenuOpen" class="mobile-overlay" @click="closeMobileMenu"></div>

    <nav class="sidebar" :class="{ 'sidebar-open': isMobileMenuOpen }">
      <div class="sidebar-header">
        <h2>FlexFlow Admin</h2>
        <button class="mobile-close-btn" @click="closeMobileMenu" v-if="isMobile">
          x
        </button>
      </div>
      <ul class="sidebar-menu">
        <li>
          <router-link to="/" class="sidebar-link" @click="closeMobileMenu">
            <span class="icon">D</span>
            Dashboard
          </router-link>
        </li>
        <li>
          <router-link to="/jobs" class="sidebar-link" @click="closeMobileMenu">
            <span class="icon">J</span>
            Jobs
          </router-link>
        </li>
        <li>
          <router-link to="/users" class="sidebar-link" @click="closeMobileMenu">
            <span class="icon">U</span>
            Users
          </router-link>
        </li>
      </ul>
    </nav>

    <main class="main-content" :class="{ 'main-content-mobile': isMobile }">
      <div class="mobile-header" v-if="isMobile">
        <button class="mobile-menu-btn" @click="toggleMobileMenu">
          M
        </button>
        <h1 class="mobile-title">FlexFlow Admin</h1>
      </div>

      <router-view />
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { RouterView } from 'vue-router'

const isMobileMenuOpen = ref(false)
const isMobile = ref(false)

const checkMobile = () => {
  isMobile.value = window.innerWidth <= 768
}

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
  background: #f8f9fa;
  min-height: 100vh;
  transition: margin-left 0.3s ease;
}

.main-content-mobile {
  margin-left: 0;
}

.mobile-header {
  display: flex;
  align-items: center;
  padding: 15px 20px;
  background: white;
  border-bottom: 1px solid #ecf0f1;
  margin: -20px -20px 20px -20px;
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
    display: none;
  }
}

/* Responsive styles */
@media (max-width: 768px) {
  .main-content {
    padding: 10px;
  }

  .mobile-header {
    margin: -10px -10px 20px -10px;
    padding: 15px 10px;
  }
}
</style>


<style>
.form-section { background: #f4f4f4; padding: 20px; border-radius: 8px; }
input, textarea { margin-bottom: 10px; width: 100%; max-width: 300px; }
.job-card { border: 1px solid #ddd; padding: 10px; margin: 10px 0; }
</style>