<script setup>
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { user, signOut } from './session'
const route = useRoute()
const router = useRouter()
const busy = ref(false)
const isAdmin = computed(() => user.value?.role === 'admin')
const items = [
  { to: '/', icon: '▦', label: 'Dashboard' },
  { to: '/orders/new', icon: '＋', label: 'New order' },
  { to: '/orders', icon: '☷', label: 'Orders' },
  { to: '/menu', icon: '☕', label: 'Menu management' },
  { to: '/tables', icon: '▤', label: 'Tables' },
  { to: '/expenses', icon: '↘', label: 'Expenses', admin: true },
  { to: '/reports', icon: '▥', label: 'Reports', admin: true },
  { to: '/staff', icon: '♙', label: 'Staff', admin: true },
]
async function logout() {
  busy.value = true
  try { await signOut() } catch { /* UI signs out even if the session expired */ }
  busy.value = false
  router.push('/login')
}
</script>

<template>
  <router-view v-if="route.path === '/login'" />
  <div v-else class="app-shell">
    <aside class="sidebar">
      <router-link class="brand" to="/"><span class="brand-icon">☕</span><span><strong>Cafe Control</strong><small>MANAGEMENT SYSTEM</small></span></router-link>
      <div class="nav-label">WORKSPACE</div>
      <nav class="nav-menu">
        <template v-for="item in items" :key="item.to">
          <router-link v-if="!item.admin || isAdmin" :to="item.to" class="nav-entry" :class="{ active: route.path === item.to }"><span class="nav-symbol">{{ item.icon }}</span>{{ item.label }}</router-link>
        </template>
      </nav>
      <div class="sidebar-bottom"><span class="avatar">{{ user?.name?.charAt(0) }}</span><div><strong>{{ user?.name }}</strong><small>{{ user?.role }}</small></div><button :disabled="busy" class="logout" title="Sign out" @click="logout">↪</button></div>
    </aside>
    <main class="main-area">
      <header class="topbar"><div><small>CAFE OPERATIONS</small><h1>{{ route.meta.title || 'Cafe Management' }}</h1></div><div class="topbar-right"><span class="status-dot"></span> Local system</div></header>
      <section class="workspace"><router-view /></section>
    </main>
  </div>
</template>
