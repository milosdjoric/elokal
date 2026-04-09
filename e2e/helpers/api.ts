const API_BASE = 'http://localhost:8000/api'

interface ApiOptions {
  token?: string
  method?: string
  body?: Record<string, unknown>
}

export async function apiRequest<T = unknown>(
  path: string,
  options: ApiOptions = {}
): Promise<T> {
  const { token, method = 'GET', body } = options

  const headers: Record<string, string> = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  }

  if (token) {
    headers['Authorization'] = `Bearer ${token}`
  }

  const response = await fetch(`${API_BASE}${path}`, {
    method,
    headers,
    body: body ? JSON.stringify(body) : undefined,
  })

  return response.json() as Promise<T>
}

export async function loginStorefrontUser(email: string, password: string): Promise<string> {
  const data = await apiRequest<{ data: { token: string } }>('/v1/login', {
    method: 'POST',
    body: { email, password },
  })
  return data.data.token
}

export async function registerStorefrontUser(user: {
  name: string
  email: string
  password: string
  password_confirmation: string
  phone?: string
}): Promise<string> {
  const data = await apiRequest<{ data: { token: string } }>('/v1/register', {
    method: 'POST',
    body: user,
  })
  return data.data.token
}

export async function loginAdmin(email: string, password: string): Promise<string> {
  const data = await apiRequest<{ data: { token: string } }>('/admin/login', {
    method: 'POST',
    body: { email, password },
  })
  return data.data.token
}

export async function getProducts(params?: Record<string, string>): Promise<unknown> {
  const query = params ? '?' + new URLSearchParams(params).toString() : ''
  return apiRequest(`/v1/products${query}`)
}

export async function getProductBySlug(slug: string): Promise<unknown> {
  return apiRequest(`/v1/products/${slug}`)
}
