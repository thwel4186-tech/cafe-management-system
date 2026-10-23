<script setup>
import { onMounted, ref } from 'vue'
import { user } from '../session'
import api, { errorText } from '../api'
const staff=ref([]),editing=ref(null),error=ref(''),notice=ref(''),busy=ref(false)
const empty=()=>({name:'',email:'',password:'',role:'cashier',is_active:true})
const form=ref(empty())
async function load(){try{staff.value=(await api.get('/api/staff')).data}catch(e){error.value=errorText(e)}}
onMounted(load)
function edit(s){editing.value=s.id;form.value={name:s.name,email:s.email,password:'',role:s.role,is_active:s.is_active}}
function reset(){editing.value=null;form.value=empty()}
async function save(){error.value='';notice.value='';busy.value=true;try{if(editing.value)await api.put(`/api/staff/${editing.value}`,form.value);else await api.post('/api/staff',form.value);reset();notice.value='Staff account saved.';await load()}catch(e){error.value=errorText(e)}finally{busy.value=false}}
</script>
<template>
  <div v-if="error" class="alert alert-danger">{{ error }}</div><div v-if="notice" class="alert alert-success">{{ notice }}</div>
  <div class="split-layout"><div class="panel"><h3 class="mb-3">{{ editing?'Edit staff member':'Add staff member' }}</h3><form class="form-stack" @submit.prevent="save"><div><label class="field-label">Full name</label><input v-model="form.name" class="form-control" required/></div><div><label class="field-label">Email</label><input v-model="form.email" class="form-control" type="email" required/></div><div><label class="field-label">{{ editing?'New password (leave empty to keep old)':'Password' }}</label><input v-model="form.password" class="form-control" type="password" :required="!editing" minlength="10" autocomplete="new-password"/></div><div><label class="field-label">Role</label><select v-model="form.role" class="form-select"><option value="cashier">Cashier</option><option value="admin">Admin</option></select></div><label v-if="editing" class="form-check"><input v-model="form.is_active" class="form-check-input" type="checkbox" :disabled="editing===user?.id"/> Account enabled</label><div class="small-actions"><button class="btn btn-primary" :disabled="busy">Save staff</button><button v-if="editing" type="button" class="btn btn-light" @click="reset">Cancel</button></div></form></div>
    <div class="panel"><h3 class="mb-3">Team members</h3><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Active</th><th></th></tr></thead><tbody><tr v-for="s in staff" :key="s.id"><td><strong>{{ s.name }}</strong></td><td>{{ s.email }}</td><td class="text-capitalize">{{ s.role }}</td><td>{{ s.is_active?'Yes':'No' }}</td><td><button class="btn btn-sm btn-outline-secondary" @click="edit(s)">Edit</button></td></tr><tr v-if="!staff.length"><td colspan="5" class="empty-state">No staff members.</td></tr></tbody></table></div></div>
  </div>
</template>
