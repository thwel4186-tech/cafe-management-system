<script setup>
import { ref, onMounted, computed } from 'vue'
import { user } from '../session'
import api, { errorText } from '../api'
const isAdmin=computed(()=>user.value?.role==='admin')
const tables=ref([]),form=ref({name:'',seats:2}),editing=ref(null),error=ref(''),notice=ref(''),saving=ref(false)
async function load(){try{tables.value=(await api.get('/api/tables')).data}catch(e){error.value=errorText(e)}}
onMounted(load)
function edit(t){editing.value=t.id;form.value={name:t.name,seats:t.seats}}
function reset(){editing.value=null;form.value={name:'',seats:2}}
async function save(){error.value='';notice.value='';saving.value=true;try{if(editing.value)await api.put(`/api/tables/${editing.value}`,form.value);else await api.post('/api/tables',form.value);reset();notice.value='Table saved.';await load()}catch(e){error.value=errorText(e)}finally{saving.value=false}}
async function remove(t){if(!confirm(`Delete ${t.name}?`))return;try{await api.delete(`/api/tables/${t.id}`);await load()}catch(e){error.value=errorText(e)}}
</script>
<template>
  <div v-if="error" class="alert alert-danger">{{ error }}</div><div v-if="notice" class="alert alert-success">{{ notice }}</div>
  <div :class="isAdmin ? 'split-layout' : ''"><div v-if="isAdmin" class="panel"><h3 class="mb-3">{{ editing ? 'Edit table' : 'New table' }}</h3><form class="form-stack" @submit.prevent="save"><div><label class="field-label">Table name</label><input v-model="form.name" class="form-control" placeholder="Table 9" required maxlength="80"/></div><div><label class="field-label">Seats</label><input v-model.number="form.seats" class="form-control" type="number" required min="1" max="30"/></div><div class="small-actions"><button class="btn btn-primary" :disabled="saving">Save table</button><button v-if="editing" type="button" class="btn btn-light" @click="reset">Cancel</button></div></form></div>
    <div class="cards-grid"><div v-for="t in tables" :key="t.id" class="table-card"><div class="d-flex justify-content-between align-items-center"><span style="font-size:25px">▤</span><span :class="t.open_orders_count ? 'badge-busy' : 'badge-available'">{{ t.open_orders_count ? 'Occupied' : 'Available' }}</span></div><h4 class="mt-3 mb-1">{{ t.name }}</h4><p class="muted mb-3" style="font-size:12px">{{ t.seats }} seats</p><div v-if="isAdmin" class="small-actions"><button class="btn btn-outline-secondary" @click="edit(t)">Edit</button><button class="btn btn-outline-danger" @click="remove(t)">Delete</button></div></div><p v-if="!tables.length" class="muted">No tables yet.</p></div>
  </div>
</template>
