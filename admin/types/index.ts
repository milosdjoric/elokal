// --- API ---

export interface ApiResponse<T> {
  data: T
  message?: string
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number | null
    to: number | null
  }
  links: {
    first: string | null
    last: string | null
    prev: string | null
    next: string | null
  }
}

export interface ApiError {
  message: string
  errors?: Record<string, string[]>
}

// --- Auth ---

export interface Admin {
  id: number
  name: string
  email: string
  role: 'super_admin' | 'admin' | 'editor'
  is_active: boolean
  created_at: string
  updated_at: string
}

export interface AuthResponse {
  token: string
  admin: Admin
}

export interface LoginCredentials {
  email: string
  password: string
}

// --- Product ---

export interface Product {
  id: number
  name: string
  slug: string
  short_description: string | null
  description: string | null
  price: string
  sale_price: string | null
  sale_price_from: string | null
  sale_price_to: string | null
  cost_price: string | null
  unit_price: string | null
  unit_label: string | null
  sku: string | null
  stock_quantity: number
  is_active: boolean
  featured: boolean
  sort_order: number
  meta_title: string | null
  meta_description: string | null
  effective_price: string
  sale_percentage: number | null
  formatted_unit_price: string | null
  is_on_sale: boolean
  categories: number[]
  images: ProductImage[]
  created_at: string
  updated_at: string
}

export interface ProductImage {
  id: number
  product_id: number
  image_path: string
  alt_text: string | null
  sort_order: number
  is_primary: boolean
  created_at: string
  updated_at: string
}

// --- Category ---

export interface Category {
  id: number
  parent_id: number | null
  name: string
  slug: string
  description: string | null
  image_path: string | null
  sort_order: number
  is_active: boolean
  meta_title: string | null
  meta_description: string | null
  children?: Category[]
  products_count?: number
  created_at: string
  updated_at: string
}

// --- Dashboard ---

export interface DashboardStats {
  total_products: number
  active_products: number
  total_categories: number
  featured_products: number
  out_of_stock: number
}
