<script setup>
import { onMounted, ref } from 'vue'
import api, { kyat, errorText } from '../api'
const data = ref(null)
const error = ref('')
async function refresh() { try { data.value = (await api.get('/api/dashboard')).data } catch (e) { error.value = errorText(e) } }
onMounted(refresh)
</script>
<template>
  <div v-if="error" class="alert alert-danger">{{ error }}</div>
  <template v-if="data">
    <div class="welcome-banner"><div><small>YOUR CAFE AT A GLANCE</small><h2>Good day! ☕</h2><p>Here’s what’s happening with your business today.</p></div><router-link to="/orders/new" class="btn btn-light">＋ Create order</router-link></div>
    <div class="metric-grid"><article class="metric"><span>Today's Revenue</span><strong>{{ kyat(data.revenue_today) }}</strong><small>Payments received today</small></article><article class="metric"><span>Today's Orders</span><strong>{{ data.orders_today }}</strong><small>All new orders today</small></article><article class="metric"><span>Unpaid Orders</span><strong>{{ data.unpaid_orders }}</strong><small>Open balances</small></article><article class="metric"><span>Active Menu Items</span><strong>{{ data.products }}</strong><small>Available to sell</small></article></div>
    <div class="panel"><div class="panel-header"><h3>Recent orders</h3><router-link to="/orders" class="text-link">View all →</router-link></div><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Order</th><th>Type / Table</th><th>Total</th><th>Status</th><th>Payment</th></tr></thead><tbody><tr v-for="o in data.recent_orders" :key="o.id"><td class="fw-semibold">{{ o.order_number }}</td><td>{{ o.table?.name || 'Takeaway' }}</td><td>{{ kyat(o.total_amount) }}</td><td><span class="status-pill">{{ o.status }}</span></td><td>{{ o.payment ? 'Paid' : 'Unpaid' }}</td></tr><tr v-if="!data.recent_orders.length"><td colspan="5" class="muted text-center p-4">No orders yet. Start by creating an order.</td></tr></tbody></table></div></div>
  </template><p v-else-if="!error" class="muted">Loading dashboard…</p>
</template>
