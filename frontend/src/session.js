import { ref } from 'vue'
import api from './api'

export const user = ref(null)
let initialized = false

export async function restoreUser() {
  if (initialized) return
  initialized = true
  try { user.value = (await api.get('/api/me')).data.user }
  catch { user.value = null }
}

export async function signIn(email, password) {
  await api.get('/sanctum/csrf-cookie')
  user.value = (await api.post('/api/login', { email, password })).data.user
}

export async function signOut() {
  try { await api.post('/api/logout') }
  finally { user.value = null }
}
