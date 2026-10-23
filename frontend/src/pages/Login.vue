<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { signIn } from '../session'
import { errorText } from '../api'
const router = useRouter()
const email = ref('admin@cafe.test')
const password = ref('')
const busy = ref(false)
const error = ref('')
async function login() {
  busy.value = true; error.value = ''
  try { await signIn(email.value, password.value); router.push('/') }
  catch (e) { error.value = errorText(e) }
  finally { busy.value = false }
}
</script>
<template>
  <div class="login-screen"><div class="login-card"><div class="login-symbol">☕</div><small class="eyebrow">WELCOME BACK</small><h1>Cafe Control</h1><p class="muted">Sign in to manage your café.</p>
    <div v-if="error" class="alert alert-danger">{{ error }}</div>
    <form @submit.prevent="login"><label class="field-label">Email address</label><input v-model="email" class="form-control mb-3" type="email" required autocomplete="username"/><label class="field-label">Password</label><input v-model="password" class="form-control mb-4" type="password" required autocomplete="current-password" placeholder="Your password"/><button class="btn btn-primary w-100 py-2" :disabled="busy">{{ busy ? 'Signing in…' : 'Sign in →' }}</button></form>
    <p class="login-hint">Demo email: <strong>admin@cafe.test</strong><br/>Change demo passwords before real use.</p>
  </div></div>
</template>
