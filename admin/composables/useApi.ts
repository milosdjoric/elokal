import type { ApiError } from '~/types'

const API_BASE = 'http://localhost:8000/api'

export function useApi() {
  function getToken(): string | null {
    if (import.meta.server) return null
    return localStorage.getItem('admin_token')
  }

  function authHeaders(): Record<string, string> {
    const h: Record<string, string> = { Accept: 'application/json' }
    const token = getToken()
    if (token) h.Authorization = `Bearer ${token}`
    return h
  }

  async function get<T>(path: string, params?: Record<string, string | number | boolean>): Promise<T> {
    const query = params
      ? '?' + new URLSearchParams(
          Object.entries(params)
            .filter(([, v]) => v !== undefined && v !== null && v !== '')
            .map(([k, v]) => [k, String(v)]),
        ).toString()
      : ''

    return $fetch<T>(`${API_BASE}${path}${query}`, {
      method: 'GET',
      headers: authHeaders(),
    })
  }

  async function post<T>(path: string, body?: unknown): Promise<T> {
    return $fetch<T>(`${API_BASE}${path}`, {
      method: 'POST',
      headers: { ...authHeaders(), 'Content-Type': 'application/json' },
      body: body ? JSON.stringify(body) : undefined,
    })
  }

  async function put<T>(path: string, body?: unknown): Promise<T> {
    return $fetch<T>(`${API_BASE}${path}`, {
      method: 'PUT',
      headers: { ...authHeaders(), 'Content-Type': 'application/json' },
      body: body ? JSON.stringify(body) : undefined,
    })
  }

  async function patch<T>(path: string, body?: unknown): Promise<T> {
    return $fetch<T>(`${API_BASE}${path}`, {
      method: 'PATCH',
      headers: { ...authHeaders(), 'Content-Type': 'application/json' },
      body: body ? JSON.stringify(body) : undefined,
    })
  }

  async function del<T>(path: string): Promise<T> {
    return $fetch<T>(`${API_BASE}${path}`, {
      method: 'DELETE',
      headers: authHeaders(),
    })
  }

  async function upload<T>(path: string, formData: FormData): Promise<T> {
    return $fetch<T>(`${API_BASE}${path}`, {
      method: 'POST',
      headers: authHeaders(),
      body: formData,
    })
  }

  function isApiError(error: unknown): error is { data: ApiError } {
    return typeof error === 'object' && error !== null && 'data' in error
  }

  function getErrorMessage(error: unknown): string {
    if (isApiError(error)) {
      return error.data.message || 'Došlo je do greške.'
    }
    return 'Došlo je do greške.'
  }

  function getValidationErrors(error: unknown): Record<string, string[]> {
    if (isApiError(error) && error.data.errors) {
      return error.data.errors
    }
    return {}
  }

  return { get, post, put, patch, del, upload, getErrorMessage, getValidationErrors, isApiError }
}
