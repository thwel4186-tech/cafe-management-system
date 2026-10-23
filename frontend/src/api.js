import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000',
  withCredentials: true,
  withXSRFToken: true,
  headers: { Accept: 'application/json' },
})

export default api

export function errorText(error) {
  const data = error?.response?.data
  const first = data?.errors && Object.values(data.errors).flat()[0]
  return first || data?.message || 'Something went wrong. Check the Laravel server and try again.'
}

export function kyat(value) {
  return `${Number(value || 0).toLocaleString('en-US')} MMK`
}

export function localDate() {
  const d = new Date()
  return new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 10)
}
