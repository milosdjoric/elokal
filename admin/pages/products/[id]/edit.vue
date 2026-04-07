<script setup lang="ts">
import type { Product } from '~/types'

const route = useRoute()
const router = useRouter()
const { get, put, getErrorMessage, getValidationErrors } = useApi()
const { success, error: toastError } = useToast()

const product = ref<Product | null>(null)
const pageLoading = ref(true)
const saving = ref(false)
const serverErrors = ref<Record<string, string[]>>({})

async function fetchProduct() {
  pageLoading.value = true
  try {
    const data = await get<{ data: Product }>(`/admin/products/${route.params.id}`)
    product.value = data.data
  }
  catch (e) {
    toastError(getErrorMessage(e))
    await router.push('/products')
  }
  finally {
    pageLoading.value = false
  }
}

async function handleSubmit(data: Record<string, unknown>) {
  saving.value = true
  serverErrors.value = {}

  try {
    const res = await put<{ data: Product }>(`/admin/products/${route.params.id}`, data)
    product.value = res.data
    success('Proizvod sačuvan.')
  }
  catch (e) {
    serverErrors.value = getValidationErrors(e)
    if (!Object.keys(serverErrors.value).length) {
      toastError(getErrorMessage(e))
    }
  }
  finally {
    saving.value = false
  }
}

async function refreshImages() {
  await fetchProduct()
}

onMounted(fetchProduct)
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[
      { label: 'Proizvodi', to: '/products' },
      { label: product?.name || 'Učitavanje...' },
    ]" />

    <h1 class="text-2xl font-bold text-gray-800 mb-6">
      {{ product?.name || 'Učitavanje...' }}
    </h1>

    <div v-if="pageLoading" class="flex items-center justify-center py-20">
      <UiAtomsSpinner size="lg" />
    </div>

    <template v-else-if="product">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form -->
        <div class="lg:col-span-2 bg-white border border-gray-200 p-6">
          <ProductProductForm
            :product="product"
            :loading="saving"
            :server-errors="serverErrors"
            @submit="handleSubmit"
          />
        </div>

        <!-- Images sidebar -->
        <div class="bg-white border border-gray-200 p-6">
          <ProductProductImages
            :product-id="product.id"
            :images="product.images || []"
            @refresh="refreshImages"
          />
        </div>
      </div>
    </template>
  </div>
</template>
