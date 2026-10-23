<script setup>
import { onMounted, ref } from 'vue'
import api, { errorText, kyat, localDate } from '../api'
const expenses=ref([]),form=ref({title:'',amount:0,spent_on:localDate(),notes:''}),error=ref(''),notice=ref(''),busy=ref(false)
async function load(){try{expenses.value=(await api.get('/api/expenses')).data}catch(e){error.value=errorText(e)}}
onMounted(load)
async function save(){error.value='';notice.value='';busy.value=true;try{await api.post('/api/expenses',form.value);form.value={title:'',amount:0,spent_on:localDate(),notes:''};notice.value='Expense recorded.';await load()}catch(e){error.value=errorText(e)}finally{busy.value=false}}
async function remove(e){if(!confirm(`Delete expense "${e.title}"?`))return;try{await api.delete(`/api/expenses/${e.id}`);await load()}catch(err){error.value=errorText(err)}}
</script>
<template>
  <div v-if="error" class="alert alert-danger">{{ error }}</div><div v-if="notice" class="alert alert-success">{{ notice }}</div>
  <div class="split-layout"><div class="panel"><h3 class="mb-3">Record expense</h3><form class="form-stack" @submit.prevent="save"><div><label class="field-label">Expense description</label><input v-model="form.title" class="form-control" placeholder="Electricity bill" required maxlength="150"/></div><div><label class="field-label">Amount (MMK)</label><input v-model.number="form.amount" type="number" min="1" required class="form-control"/></div><div><label class="field-label">Date</label><input v-model="form.spent_on" type="date" required class="form-control"/></div><div><label class="field-label">Notes (optional)</label><textarea v-model="form.notes" rows="2" maxlength="1000" class="form-control"/></div><button class="btn btn-primary" :disabled="busy">Save expense</button></form></div>
    <div class="panel"><div class="panel-header"><h3>Expenses</h3><span class="muted small">Latest 200</span></div><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Date</th><th>Description</th><th>Amount</th><th></th></tr></thead><tbody><tr v-for="e in expenses" :key="e.id"><td>{{ String(e.spent_on).slice(0,10) }}</td><td>{{ e.title }}</td><td><strong>{{ kyat(e.amount) }}</strong></td><td><button class="btn btn-sm btn-outline-danger" @click="remove(e)">Delete</button></td></tr><tr v-if="!expenses.length"><td class="empty-state" colspan="4">No expenses recorded.</td></tr></tbody></table></div></div>
  </div>
</template>
