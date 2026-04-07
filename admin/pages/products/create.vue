<script setup lang="ts">
const { post, getErrorMessage, getValidationErrors } = useApi()
const { success, error: toastError } = useToast()
const router = useRouter()

const loading = ref(false)
const serverErrors = ref<Record<string, string[]>>({})

async function handleSubmit(data: Record<string, unknown>) {
  loading.value = true
  serverErrors.value = {}

  try {
    await post('/admin/products', data)
    success('Proizvod kreiran.')
    await router.push('/products')
  }
  catch (e) {
    serverErrors.value = getValidationErrors(e)
    if (!Object.keys(serverErrors.value).length) {
      toastError(getErrorMessage(e))
    }
  }
  finally {
    loading.value = false
  }
}
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[
      { label: 'Proizvodi', to: '/products' },
      { label: 'Novi proizvod' },
    ]" />

    <h1 class="text-2xl font-bold text-gray-800 mb-6">Novi proizvod</h1>

    <div class="bg-white border border-gray-200 p-6">
      <ProductForm
        :loading="loading"
        :server-errors="serverErrors"
        @submit="handleSubmit"
      />
    </div>
  </div>
</template>
