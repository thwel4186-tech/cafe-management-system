<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api, { kyat, errorText } from '../api'
const router=useRouter(),products=ref([]),categories=ref([]),tables=ref([]),error=ref(''),saving=ref(false)
const type=ref('dine_in'),tableId=ref(''),notes=ref(''),filter=ref(''),cart=ref([])
const availableTables=computed(()=>tables.value.filter(t=>!t.open_orders_count))
const filtered=computed(()=>products.value.filter(p=>p.is_active&&(!p.track_stock||p.stock>0)&&(!filter.value||p.category_id===Number(filter.value))))
const total=computed(()=>cart.value.reduce((s,i)=>s+i.qty*i.price,0))
async function load(){try{const [p,c,t]=await Promise.all([api.get('/api/products'),api.get('/api/categories'),api.get('/api/tables')]);products.value=p.data;categories.value=c.data;tables.value=t.data}catch(e){error.value=errorText(e)}}
onMounted(load)
function add(p){const line=cart.value.find(i=>i.product_id===p.id);if(line){if(p.track_stock&&line.qty>=p.stock)return;line.qty++}else cart.value.push({product_id:p.id,name:p.name,price:p.price,qty:1,track_stock:p.track_stock,stock:p.stock})}
function increase(line){if(line.track_stock&&line.qty>=line.stock)return;line.qty++}
function decrease(line){if(line.qty===1)cart.value=cart.value.filter(i=>i!==line);else line.qty--}
async function submit(){error.value='';saving.value=true;try{const payload={type:type.value,cafe_table_id:type.value==='dine_in'?Number(tableId.value):null,notes:notes.value||null,items:cart.value.map(i=>({product_id:i.product_id,qty:i.qty}))};await api.post('/api/orders',payload);router.push('/orders')}catch(e){error.value=errorText(e)}finally{saving.value=false}}
</script>
<template>
  <div v-if="error" class="alert alert-danger">{{ error }}</div>
  <div class="split-layout order-layout">
    <div class="panel"><div class="panel-header"><h3>Select menu items</h3><span class="muted" style="font-size:12px">Click to add</span></div><div class="mb-3"><select v-model="filter" class="form-select" style="max-width:250px"><option value="">All categories</option><option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option></select></div><div class="cards-grid"><button v-for="p in filtered" :key="p.id" class="product-card" @click="add(p)"><strong>{{ p.name }}</strong><small>{{ p.category?.name }}{{ p.track_stock ? ' · Stock '+p.stock : '' }}</small><span>{{ kyat(p.price) }}</span></button></div><div v-if="!filtered.length" class="empty-state">No products available.</div></div>
    <div class="panel"><h3 class="mb-3">Current order</h3><div class="mb-3"><label class="field-label">Order type</label><select v-model="type" class="form-select"><option value="dine_in">Dine in</option><option value="takeaway">Takeaway</option></select></div><div v-if="type==='dine_in'" class="mb-3"><label class="field-label">Available table</label><select v-model="tableId" class="form-select" required><option value="" disabled>Select a table</option><option v-for="t in availableTables" :key="t.id" :value="t.id">{{ t.name }} · {{ t.seats }} seats</option></select></div>
      <div v-if="!cart.length" class="empty-state">Your cart is empty.<br/>Choose products from the menu.</div><div v-for="line in cart" :key="line.product_id" class="cart-line"><div><strong>{{ line.name }}</strong><small>{{ kyat(line.price*line.qty) }}</small></div><div class="cart-controls"><button @click="decrease(line)">−</button><strong>{{ line.qty }}</strong><button @click="increase(line)">+</button></div></div>
      <div class="mt-3"><label class="field-label">Order note (optional)</label><textarea v-model="notes" class="form-control" rows="2" maxlength="1000" placeholder="Less sugar, extra ice…"></textarea></div><div class="order-summary"><span>Total</span><strong>{{ kyat(total) }}</strong></div><button class="btn btn-primary w-100 py-2" :disabled="saving||!cart.length||(type==='dine_in'&&!tableId)" @click="submit">{{ saving?'Saving order…':'Place order →' }}</button><p class="inline-note mt-3 mb-0">Prices and stock are rechecked on the server at checkout.</p>
    </div>
  </div>
</template>
