<script setup lang="ts">
interface StoreLocation {
  id: number
  name: string
  address: string
  city: string
  phone: string | null
  email: string | null
  latitude: string
  longitude: string
  working_hours: Record<string, string> | null
}

const { get } = useApi()
const locations = ref<StoreLocation[]>([])
const loading = ref(true)
const searchCity = ref('')
const selectedLocation = ref<StoreLocation | null>(null)

async function fetchLocations() {
  loading.value = true
  try {
    const params: Record<string, string> = {}
    if (searchCity.value.trim()) params.city = searchCity.value.trim()
    const data = await get<{ data: StoreLocation[] }>('/v1/store-locations', params)
    locations.value = data.data
  }
  catch { /* ignorisano */ }
  finally { loading.value = false }
}

function selectLocation(loc: StoreLocation) {
  selectedLocation.value = loc
}

onMounted(fetchLocations)

useHead({ title: 'Prodavnice — eLokal' })
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 py-12">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Naše prodavnice</h1>

    <!-- Pretraga -->
    <div class="mb-6 flex gap-3">
      <input
        v-model="searchCity"
        type="text"
        placeholder="Pretražite po gradu..."
        class="flex-1 px-4 py-2.5 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
        @keyup.enter="fetchLocations"
      />
      <UiAtomsButton @click="fetchLocations" :loading="loading">Pretraži</UiAtomsButton>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
      <!-- Lista -->
      <div class="lg:col-span-1 space-y-3 max-h-[600px] overflow-y-auto">
        <div v-if="loading" class="text-sm text-gray-500">Učitavanje...</div>
        <div v-else-if="!locations.length" class="text-sm text-gray-500">Nema pronađenih prodavnica.</div>

        <button
          v-for="loc in locations"
          :key="loc.id"
          class="w-full text-left p-4 border transition-colors"
          :class="selectedLocation?.id === loc.id ? 'border-primary-600 bg-primary-50' : 'border-gray-200 hover:border-gray-300'"
          @click="selectLocation(loc)"
        >
          <h3 class="font-medium text-gray-800">{{ loc.name }}</h3>
          <p class="text-sm text-gray-500 mt-1">{{ loc.address }}, {{ loc.city }}</p>
          <p v-if="loc.phone" class="text-sm text-gray-500 mt-1">{{ loc.phone }}</p>
          <div v-if="loc.working_hours" class="mt-2">
            <p v-for="(hours, day) in loc.working_hours" :key="day" class="text-xs text-gray-400">
              {{ day }}: {{ hours }}
            </p>
          </div>
        </button>
      </div>

      <!-- Mapa -->
      <div class="lg:col-span-2">
        <div id="store-map" class="w-full h-[600px] bg-gray-100 border border-gray-200">
          <ClientOnly>
            <div class="w-full h-full flex items-center justify-center text-sm text-gray-400">
              <template v-if="locations.length">
                <!-- Leaflet mapa se mountuje ovde -->
                <StoreLocatorMap :locations="locations" :selected="selectedLocation" @select="selectLocation" />
              </template>
              <template v-else>
                Nema lokacija za prikaz na mapi.
              </template>
            </div>
          </ClientOnly>
        </div>
      </div>
    </div>
  </div>
</template>
