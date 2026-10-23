import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import 'bootstrap/dist/css/bootstrap.min.css'
import './style.css'
import App from './App.vue'
import Login from './pages/Login.vue'
import Dashboard from './pages/Dashboard.vue'
import Catalog from './pages/Catalog.vue'
import Tables from './pages/Tables.vue'
import NewOrder from './pages/NewOrder.vue'
import Orders from './pages/Orders.vue'
import Expenses from './pages/Expenses.vue'
import Reports from './pages/Reports.vue'
import Staff from './pages/Staff.vue'
import { restoreUser, user } from './session'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/login', component: Login, meta: { title: 'Sign in' } },
    { path: '/', component: Dashboard, meta: { title: 'Dashboard' } },
    { path: '/orders/new', component: NewOrder, meta: { title: 'New order' } },
    { path: '/orders', component: Orders, meta: { title: 'Orders' } },
    { path: '/menu', component: Catalog, meta: { title: 'Menu' } },
    { path: '/tables', component: Tables, meta: { title: 'Tables' } },
    { path: '/expenses', component: Expenses, meta: { title: 'Expenses', admin: true } },
    { path: '/reports', component: Reports, meta: { title: 'Reports', admin: true } },
    { path: '/staff', component: Staff, meta: { title: 'Staff', admin: true } },
  ],
})
router.beforeEach(async (to) => {
  await restoreUser()
  if (to.path !== '/login' && !user.value) return '/login'
  if (to.path === '/login' && user.value) return '/'
  if (to.meta.admin && user.value?.role !== 'admin') return '/'
})

createApp(App).use(router).mount('#app')
