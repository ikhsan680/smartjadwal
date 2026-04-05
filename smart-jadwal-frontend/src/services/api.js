import axios from "axios"

const TOKEN_STORAGE_KEY = "token"
const DEFAULT_API_BASE_URL = "http://127.0.0.1:8000"
const rawBaseUrl = import.meta.env.VITE_API_BASE_URL || DEFAULT_API_BASE_URL
const normalizedBaseUrl = rawBaseUrl.replace(/\/+$/, "")

const api = axios.create({
    baseURL: normalizedBaseUrl,
    withCredentials: true,
    headers: {
        "X-Requested-With": "XMLHttpRequest"
    }
})

export const setAuthToken = (token) => {
    if (token) {
        api.defaults.headers.common.Authorization = `Bearer ${token}`
        return
    }

    delete api.defaults.headers.common.Authorization
}

export const ensureCsrfCookie = () => api.get("/sanctum/csrf-cookie")

api.interceptors.request.use((config) => {
    const token = localStorage.getItem(TOKEN_STORAGE_KEY)

    if (!config.headers) {
        config.headers = {}
    }

    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    } else {
        delete config.headers.Authorization
    }

    return config
})

setAuthToken(localStorage.getItem(TOKEN_STORAGE_KEY))

export default api