<script setup>
import { ref, computed, onMounted } from 'vue'
import MapWithZonesPanel from './MapWithZonesPanel.vue'
import EvacuatorFilters from './EvacuatorFilters.vue'
import NawBarMenu from './NavBarMenu.vue'

const apiKey = 'AIzaSyCZ_qe1aRHfN0-tijNv8sB3J7ti-jEFtGw'

const center = ref({ lat: 52.2297, lng: 21.0122 })
const searchRadiusKm = ref(1)
const radiusMeters = computed(() =>
  Math.min(50_000, Math.max(1_000, Math.round(searchRadiusKm.value * 1000))),
)
const address = ref('')
const zones = ref([])

const evacuatorFilters = ref({
  typeTow: null,
  minLoadCapacity: null,
})

const filteredZones = computed(() => {
  const { typeTow, minLoadCapacity } = evacuatorFilters.value
  return zones.value.filter((z) => {
    if (typeTow != null && typeTow !== '') {
      if (String(z.type_tow ?? '') !== typeTow) {
        return false
      }
    }
    if (minLoadCapacity != null) {
      const cap = z.load_capacity != null ? Number(z.load_capacity) : null
      if (cap == null || cap < minLoadCapacity) {
        return false
      }
    }
    return true
  })
})

const onEvacuatorFiltersUpdate = (payload) => {
  evacuatorFilters.value = payload
}

const geocode = async () => {
  const res = await fetch(
    `https://maps.googleapis.com/maps/api/geocode/json?address=${encodeURIComponent(address.value)}&key=${apiKey}`,
  )
  const data = await res.json()

  if (data.results.length) {
    center.value = data.results[0].geometry.location
  }
}

const saveZone = async () => {
  try {
    const res = await fetch('/api/zones', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        address: address.value,
        lat: center.value.lat,
        lng: center.value.lng,
        radius: radiusMeters.value,
        type_tow: evacuatorFilters.value.typeTow,
        load_capacity: evacuatorFilters.value.minLoadCapacity,
      }),
    })

    await loadZones()

    const data = await res.json()

    if (!res.ok) {
      alert('Помилка при збереженні')
      return
    }

    console.log('STATUS:', res.status)
    console.log('RESPONSE:', data)
  } catch (err) {
    console.error('ERROR:', err)
  }
}

const getAddressFromCoords = async (lat, lng) => {
  const res = await fetch(
    `https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&key=${apiKey}`,
  )

  const data = await res.json()

  if (data.results && data.results.length) {
    address.value = data.results[0].formatted_address
  } else {
    address.value = ''
  }
}

const loadZones = async () => {
  const res = await fetch('/api/zones')
  zones.value = await res.json()
}

const onMapClick = async ({ lat, lng }) => {
  await getAddressFromCoords(lat, lng)
}

onMounted(loadZones)
</script>

<template>
  <NawBarMenu/>
  <div class="map-layout">
    <aside class="map-layout__sidebar" aria-label="Фільтри">
      <div class="map-layout__filter-card">
        <!-- <nav class="map-layout__page-nav" aria-label="Навігація">
          <a href="/orders" class="map-layout__page-link">Замовлення</a>
        </nav> -->
        <EvacuatorFilters
          v-model:address="address"
          v-model:radius-km="searchRadiusKm"
          :filtered-count="filteredZones.length"
          @update:filters="onEvacuatorFiltersUpdate"
          @geocode="geocode"
          @save-zone="saveZone"
          @show-results="geocode"
        />
      </div>
    </aside>

    <div class="map-layout__map-area">
      <MapWithZonesPanel
        v-model:center="center"
        :api-key="apiKey"
        :zones="filteredZones"
        :radius-meters="radiusMeters"
        @map-click="onMapClick"
      />
    </div>
  </div>
</template>

<style scoped>
.map-layout {
  display: flex;
  flex-direction: row;
  align-items: stretch;
  gap: 12px;
  box-sizing: border-box;
  width: 100%;
  min-height: 100vh;
  padding: 12px;
  background: #f1f5f9;
}

.map-layout__sidebar {
  width: min(26rem, 100%);
  flex-shrink: 0;
  overflow-y: auto;
  align-self: stretch;
}

.map-layout__page-nav {
  margin-bottom: 1rem;
}

.map-layout__page-link {
  display: inline-flex;
  align-items: center;
  font-size: 0.9375rem;
  font-weight: 600;
  color: #0f172a;
  text-decoration: none;
  padding: 0.5rem 0.75rem;
  border-radius: 0.5rem;
  border: 1px solid rgb(226 232 240 / 0.9);
  background: #f8fafc;
  transition:
    background 0.15s ease,
    border-color 0.15s ease;
}

.map-layout__page-link:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
}

.map-layout__filter-card {
  height: 100%;
  min-height: min(100%, 100vh);
  padding: 1.25rem;
  background: #fff;
  border-radius: 1rem;
  border: 1px solid rgb(226 232 240 / 0.9);
  box-shadow:
    0 4px 6px -1px rgb(15 23 42 / 0.06),
    0 2px 4px -2px rgb(15 23 42 / 0.04);
  box-sizing: border-box;
}

.map-layout__map-area {
  position: relative;
  flex: 1;
  min-width: 0;
  min-height: 320px;
  display: flex;
  flex-direction: column;
  border-radius: 1rem;
  overflow: hidden;
  border: 1px solid #e2e8f0;
  background: #e5e7eb;
}

@media (max-width: 768px) {
  .map-layout {
    flex-direction: column;
    align-items: stretch;
  }

  .map-layout__sidebar {
    width: 100%;
    max-height: min(55vh, 28rem);
  }

  .map-layout__filter-card {
    min-height: 0;
  }

  .map-layout__map-area {
    min-height: 45vh;
    flex: 1;
  }
}
</style>
