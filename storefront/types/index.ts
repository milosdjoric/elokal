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
  times_sold?: number
  meta_title: string | null
  meta_description: string | null
  categories: number[]
  images: ProductImage[]
  variants?: ProductVariant[]
  related_products?: Product[]
  cross_sell_products?: Product[]
  up_sell_products?: Product[]
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

// --- Variant ---

export interface VariantAttribute {
  attribute_id: number
  attribute_name: string
  attribute_slug: string
  attribute_type: 'select' | 'color' | 'image'
  value_id: number
  value: string
  color_hex: string | null
  image_path: string | null
}

export interface ProductVariant {
  id: number
  sku: string | null
  price: string | null
  sale_price: string | null
  effective_price: string
  weight: string | null
  stock_quantity: number
  is_active: boolean
  attributes: VariantAttribute[]
  images?: ProductImage[]
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

// --- Order ---

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
  tracking: {
    number: string | null
    carrier: string | null
    url: string | null
  }
  notes: string | null
  items: OrderItem[]
  created_at: string
  updated_at: string
}

// --- Review ---

export interface ReviewUser {
  id: number
  name: string
}

export interface Review {
  id: number
  product_id: number
  user: ReviewUser
  rating: number
  title: string | null
  content: string
  is_verified_purchase: boolean
  admin_reply: string | null
  admin_replied_at: string | null
  helpful_count: number
  not_helpful_count: number
  created_at: string
}

export interface ReviewStats {
  average_rating: number
  total_reviews: number
  distribution: Record<number, number>
}

// --- Blog ---

export interface PostAuthor {
  id: number
  name: string
}

export interface PostCategory {
  id: number
  name: string
  slug: string
}

export interface PostTag {
  id: number
  name: string
  slug: string
}

export interface Post {
  id: number
  title: string
  slug: string
  excerpt: string | null
  content?: string
  featured_image: string | null
  status: string
  published_at: string | null
  reading_time: number
  author?: PostAuthor
  categories?: PostCategory[]
  tags?: PostTag[]
  meta_title: string | null
  meta_description: string | null
  created_at: string
}

export interface BlogSidebar {
  recent_posts: Array<{ id: number; title: string; slug: string; featured_image: string | null; published_at: string }>
  categories: Array<{ id: number; name: string; slug: string; posts_count: number }>
  tags: Array<{ id: number; name: string; slug: string; posts_count: number }>
}

export type OrderStatus =
  | 'pending'
  | 'confirmed'
  | 'processing'
  | 'shipped'
  | 'delivered'
  | 'completed'
  | 'cancelled'
  | 'refunded'
