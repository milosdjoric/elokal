// --- API ---

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
  unit_price: string | null
  unit_label: string | null
  sku: string | null
  stock_quantity: number
  is_active: boolean
  featured: boolean
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
}

// --- Category ---

export interface Category {
  id: number
  parent_id: number | null
  name: string
  slug: string
  description: string | null
  image_path: string | null
  children?: Category[]
  products_count?: number
}

// --- Cart ---

export interface CartItem {
  product: Product
  quantity: number
}

export interface Cart {
  items: CartItem[]
  total: number
  count: number
}
