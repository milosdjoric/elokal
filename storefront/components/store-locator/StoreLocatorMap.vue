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

const props = defineProps<{
  locations: StoreLocation[]
  selected: StoreLocation | null
}>()

const emit = defineEmits<{
  select: [location: StoreLocation]
}>()

const mapContainer = ref<HTMLElement | null>(null)
/* eslint-disable @typescript-eslint/no-explicit-any */
let map: any = null
let markers: any[] = []
let leaflet: any = null

async function initMap() {
  if (!mapContainer.value || !import.meta.client) return

  leaflet = await import('leaflet' as string)

  // Leaflet CSS
  if (!document.querySelector('link[href*="leaflet"]')) {
    const link = document.createElement('link')
    link.rel = 'stylesheet'
    link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'
    document.head.appendChild(link)
  }

  // Fix za Leaflet marker ikone u bundled okruženju
  delete (leaflet.Icon.Default.prototype as Record<string, unknown>)._getIconUrl
  leaflet.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
    iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
  })

  // Default centar: Beograd
  const defaultCenter: [number, number] = [44.8176, 20.4633]
  map = leaflet.map(mapContainer.value).setView(defaultCenter, 7)

  leaflet.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors',
    maxZoom: 18,
  }).addTo(map)

  updateMarkers()
}

function updateMarkers() {
  if (!map || !leaflet) return

  markers.forEach((m: any) => m.remove())
  markers = []

  props.locations.forEach(loc => {
    const lat = parseFloat(loc.latitude)
    const lng = parseFloat(loc.longitude)
    if (isNaN(lat) || isNaN(lng)) return

    const marker = leaflet.marker([lat, lng])
      .addTo(map)
      .bindPopup(`<b>${loc.name}</b><br>${loc.address}, ${loc.city}${loc.phone ? `<br>${loc.phone}` : ''}`)
      .on('click', () => emit('select', loc))

    markers.push(marker)
  })

  if (markers.length > 0) {
    const group = leaflet.featureGroup(markers)
    map.fitBounds(group.getBounds().pad(0.1))
  }
}

watch(() => props.locations, updateMarkers, { deep: true })

watch(() => props.selected, (loc) => {
  if (!map || !loc) return
  const lat = parseFloat(loc.latitude)
  const lng = parseFloat(loc.longitude)
  if (isNaN(lat) || isNaN(lng)) return

  map.setView([lat, lng], 14, { animate: true })

  const marker = markers.find((m: any) => {
    const pos = m.getLatLng()
    return Math.abs(pos.lat - lat) < 0.0001 && Math.abs(pos.lng - lng) < 0.0001
  })
  marker?.openPopup()
})

onMounted(initMap)

onUnmounted(() => {
  map?.remove()
  map = null
})
</script>

<template>
  <div ref="mapContainer" class="w-full h-full" />
</template>
