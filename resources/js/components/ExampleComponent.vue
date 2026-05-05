<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { GoogleMap, Marker, Circle } from 'vue3-google-map'
import MapSavedZones from './MapSavedZones.vue'
import ZoneInfoPanel from './ZoneInfoPanel.vue'
import EvacuatorFilters from './EvacuatorFilters.vue'

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

const selectedZoneId = ref(null)
const panelZone = ref(null)
const panelLoading = ref(false)
const panelError = ref(null)

const panelVisible = computed(() => selectedZoneId.value !== null)

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

const handleMapClick = async (event) => {
  const lat = event.latLng.lat()
  const lng = event.latLng.lng()

  center.value = { lat, lng }

  await getAddressFromCoords(lat, lng)
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

const closeZonePanel = () => {
  selectedZoneId.value = null
  panelZone.value = null
  panelError.value = null
  panelLoading.value = false
}

const onSavedZoneSelect = async (zoneId) => {
  selectedZoneId.value = zoneId
  panelError.value = null
  panelLoading.value = true
  panelZone.value = null

  const fromList = zones.value.find((z) => Number(z.id) === Number(zoneId))
  const hasAddress =
    fromList &&
    typeof fromList.address === 'string' &&
    fromList.address.trim() !== ''
  const hasRadius = fromList != null && fromList.radius != null && fromList.radius !== ''

  if (fromList && hasAddress && hasRadius) {
    panelZone.value = { ...fromList }
    panelLoading.value = false
    return
  }

  try {
    const res = await fetch(`/api/zones/${zoneId}`)
    if (!res.ok) {
      throw new Error('fetch failed')
    }
    panelZone.value = await res.json()
  } catch {
    panelError.value = 'Не вдалося завантажити дані зони'
    panelZone.value = null
  } finally {
    panelLoading.value = false
  }
}

watch(filteredZones, (list) => {
  if (selectedZoneId.value == null) {
    return
  }
  const stillVisible = list.some(
    (z) => Number(z.id) === Number(selectedZoneId.value),
  )
  if (!stillVisible) {
    closeZonePanel()
  }
})

onMounted(loadZones)
</script>

<template>
  <div class="map-layout">
    <aside class="map-layout__sidebar" aria-label="Фільтри">
      <div class="map-layout__filter-card">
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
      <GoogleMap
        :api-key="apiKey"
        class="map-layout__map"
        style="width: 100%; height: 100%"
        :center="center"
        :zoom="12"
        @click="handleMapClick"
      >
        <Marker :options="{ position: center }" />
        <Circle
          :options="{
            center,
            radius: radiusMeters,
            strokeColor: '#ff9933',
            fillColor: '#ff9933',
            strokeOpacity: 0.6,
            fillOpacity: 0.2,
            strokeWeight: 2,
            clickable: false,
            draggable: false,
            editable: false,
            zIndex: 0,
          }"
        />

        <MapSavedZones
          :zones="filteredZones"
          :selected-zone-id="selectedZoneId"
          @select="onSavedZoneSelect"
        />
      </GoogleMap>

      <ZoneInfoPanel
        :visible="panelVisible"
        :zone="panelZone"
        :loading="panelLoading"
        :error-message="panelError"
        @close="closeZonePanel"
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

.map-layout__map {
  flex: 1;
  min-height: 320px;
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
