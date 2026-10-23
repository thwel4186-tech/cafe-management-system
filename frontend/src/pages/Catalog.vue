<script setup>
import { computed, onMounted, ref } from 'vue'
import { user } from '../session'
import api, { errorText, kyat } from '../api'
const isAdmin = computed(() => user.value?.role === 'admin')
const categories = ref([]), products = ref([]), error = ref(''), notice = ref(''), saving = ref(false)
const categoryName = ref(''), editCategoryId = ref(null)
const defaultProduct = () => ({ name: '', category_id: '', price: 0, cost: 0, stock: 0, track_stock: false, is_active: true })
const form = ref(defaultProduct()), editProductId = ref(null)
async function load() {
  try { const [c,p] = await Promise.all([api.get('/api/categories'),api.get('/api/products')]); categories.value=c.data;products.value=p.data }
  catch (e) { error.value=errorText(e) }
}
onMounted(load)
async function saveCategory() {
  error.value=''; notice.value=''; saving.value=true
  try {
    if (editCategoryId.value) await api.put(`/api/categories/${editCategoryId.value}`, {name:categoryName.value})
    else await api.post('/api/categories', {name:categoryName.value})
    categoryName.value=''; editCategoryId.value=null; notice.value='Category saved.'; await load()
  } catch (e) { error.value=errorText(e) }
  finally { saving.value=false }
}
function editCategory(c) { editCategoryId.value=c.id; categoryName.value=c.name }
async function removeCategory(c) {
  if (!confirm(`Delete category "${c.name}"?`)) return
  try { await api.delete(`/api/categories/${c.id}`); await load() } catch(e) { error.value=errorText(e) }
}
function editProduct(p) { editProductId.value=p.id; form.value={name:p.name,category_id:p.category_id,price:p.price,cost:p.cost,stock:p.stock,track_stock:p.track_stock,is_active:p.is_active}; window.scrollTo({top:0,behavior:'smooth'}) }
function clearProduct() { editProductId.value=null;form.value=defaultProduct() }
async function saveProduct() {
  error.value=''; notice.value=''; saving.value=true
  try {
    if (editProductId.value) await api.put(`/api/products/${editProductId.value}`,form.value)
    else await api.post('/api/products',form.value)
    clearProduct();notice.value='Menu item saved.';await load()
  } catch (e) { error.value=errorText(e) }
  finally { saving.value=false }
}
async function removeProduct(p) {
  if (!confirm(`Delete menu item "${p.name}"?`)) return
  try { await api.delete(`/api/products/${p.id}`); await load() } catch(e) { error.value=errorText(e) }
}
</script>
<template>
  <div v-if="error" class="alert alert-danger message">{{ error }}</div><div v-if="notice" class="alert alert-success message">{{ notice }}</div>
  <div :class="isAdmin ? 'split-layout' : ''">
    <div>
      <div v-if="isAdmin" class="panel"><h3 class="mb-3">{{ editCategoryId ? 'Edit category' : 'Add category' }}</h3>
        <form class="form-stack" @submit.prevent="saveCategory"><div><label class="field-label">Category name</label><input v-model="categoryName" class="form-control" placeholder="Coffee" required maxlength="100"/></div><div class="small-actions"><button class="btn btn-primary" :disabled="saving">Save category</button><button v-if="editCategoryId" type="button" class="btn btn-light" @click="editCategoryId=null;categoryName=''">Cancel</button></div></form>
        <hr/><div v-for="c in categories" :key="c.id" class="cart-line"><strong>{{ c.name }}</strong><div class="small-actions"><button class="btn btn-outline-secondary" @click="editCategory(c)">Edit</button><button class="btn btn-outline-danger" @click="removeCategory(c)">Delete</button></div></div>
      </div>
      <div v-if="isAdmin" class="panel"><h3 class="mb-3">{{ editProductId ? 'Edit menu item' : 'Add menu item' }}</h3>
        <form class="form-stack" @submit.prevent="saveProduct"><div><label class="field-label">Item name</label><input v-model="form.name" class="form-control" required maxlength="150"/></div>
          <div><label class="field-label">Category</label><select v-model.number="form.category_id" class="form-select" required><option value="" disabled>Choose category</option><option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option></select></div>
          <div class="row g-2"><div class="col-6"><label class="field-label">Sell price (MMK)</label><input v-model.number="form.price" class="form-control" type="number" min="0" required/></div><div class="col-6"><label class="field-label">Cost (MMK)</label><input v-model.number="form.cost" class="form-control" type="number" min="0" required/></div></div>
          <div><label class="field-label">Stock quantity</label><input v-model.number="form.stock" class="form-control" type="number" min="0" required/></div>
          <label class="form-check"><input v-model="form.track_stock" type="checkbox" class="form-check-input"/> Track stock for this item</label>
          <label class="form-check"><input v-model="form.is_active" type="checkbox" class="form-check-input"/> Available to order</label>
          <div class="small-actions"><button :disabled="saving" class="btn btn-primary">{{ editProductId ? 'Update item' : 'Add item' }}</button><button v-if="editProductId" type="button" class="btn btn-light" @click="clearProduct">Cancel</button></div>
        </form>
      </div>
    </div>
    <div class="panel"><div class="panel-header"><h3>Menu items <span class="muted">({{ products.length }})</span></h3></div><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Actions</th></tr></thead><tbody><tr v-for="p in products" :key="p.id"><td><strong>{{ p.name }}</strong><br/><small class="muted">{{ p.is_active ? 'Active' : 'Hidden' }}</small></td><td>{{ p.category?.name }}</td><td>{{ kyat(p.price) }}</td><td>{{ p.track_stock ? p.stock : 'Not tracked' }}</td><td><div v-if="isAdmin" class="small-actions"><button class="btn btn-outline-secondary" @click="editProduct(p)">Edit</button><button class="btn btn-outline-danger" @click="removeProduct(p)">Delete</button></div></td></tr><tr v-if="!products.length"><td colspan="5" class="empty-state">No menu items yet.</td></tr></tbody></table></div></div>
  </div>
</template>
