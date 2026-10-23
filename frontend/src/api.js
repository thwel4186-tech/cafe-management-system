import axios from 'axios'

// Automatically detect the browser's hostname.
const hostname = window.location.hostname

// Local laptop: localhost:8000
// Other laptop: 192.168.88.78:8000
const API_URL = `http://${hostname}:8000`

const api = axios.create({
  baseURL: API_URL,
  withCredentials: true,
  withXSRFToken: true,
  headers: {
    Accept: 'application/json',
  },
})

export default api

export function errorText(error) {
  const data = error?.response?.data
  const first = data?.errors &&
    Object.values(data.errors).flat()[0]

  return first ||
    data?.message ||
    'Something went wrong. Check the Laravel server.'
}

export function kyat(value) {
  return `${Number(value || 0).toLocaleString('en-US')} MMK`
}

export function localDate() {
  const d = new Date()

  return new Date(
    d.getTime() - d.getTimezoneOffset() * 60000
  ).toISOString().slice(0, 10)
}