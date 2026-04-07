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

// --- Order ---

export type OrderStatus = 'pending' | 'confirmed' | 'processing' | 'shipped' | 'delivered' | 'completed' | 'cancelled' | 'refunded'

export interface OrderItem {
  id: number
  product_id: number
  product_name: string
  product_sku: string | null
  product_slug: string | null
  product_image: string | null
  price: string
  quantity: number
  line_total: string
}

export interface OrderTimelineEntry {
  id: number
  status: string
  old_status: string | null
  note: string | null
  actor_type: string | null
  created_at: string
}

export interface Order {
  id: number
  order_number: string
  status: OrderStatus
  email: string
  phone: string | null
  shipping: {
    first_name: string
    last_name: string
    company: string | null
    address_line_1: string
    address_line_2: string | null
    city: string
    postal_code: string
    country: string
  }
  subtotal: string
  shipping_cost: string
  tax: string
  discount: string
  total: string
  notes: string | null
  admin_notes: string | null
  items: OrderItem[]
  timeline?: OrderTimelineEntry[]
  created_at: string
  updated_at: string
}

// --- Customer ---

export interface Customer {
  id: number
  name: string
  email: string
  phone: string | null
  orders_count: number
  total_spent: string
  created_at: string
}
