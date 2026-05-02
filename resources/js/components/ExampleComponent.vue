<script setup>
import { ref, computed, onMounted } from 'vue'
import { GoogleMap, Marker, Circle } from 'vue3-google-map'
import MapSavedZones from './MapSavedZones.vue'
import ZoneInfoPanel from './ZoneInfoPanel.vue'

const apiKey = "AIzaSyCZ_qe1aRHfN0-tijNv8sB3J7ti-jEFtGw"

const center = ref({ lat: 52.2297, lng: 21.0122 })
const radius = ref(1000)
const address = ref('')
const zones = ref([])

const selectedZoneId = ref(null)
const panelZone = ref(null)
const panelLoading = ref(false)
const panelError = ref(null)

const panelVisible = computed(() => selectedZoneId.value !== null)

//const selectedPoint = ref(null)

// отримати координати
const geocode = async () => {
  const res = await fetch(`https://maps.googleapis.com/maps/api/geocode/json?address=${address.value}&key=${apiKey}`)
  const data = await res.json()

  if (data.results.length) {
    center.value = data.results[0].geometry.location
  }
}

// зберегти
const saveZone = async () => {
  try {
    const res = await fetch('/api/zones', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        address: address.value,
        lat: center.value.lat,
        lng: center.value.lng,
        radius: radius.value
      })
    })

    await loadZones()

    const data = await res.json()

    console.log('STATUS:', res.status)
    console.log('RESPONSE:', data)

    if (!res.ok) {
      alert('Помилка при збереженні')
    }

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
    `https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&key=${apiKey}`
  )

  const data = await res.json()

  if (data.results && data.results.length) {
    address.value = data.results[0].formatted_address
  } else {
    address.value = ''
  }
  console.log(address.value)
}

// отримати всі зони
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

onMounted(loadZones)
</script>

<template>
  <div class="map-layout">
    <div class="map-layout__main">
      <input v-model="address" placeholder="Адреса" />
      <button @click="geocode">Знайти</button>
      <button @click="saveZone">Зберегти</button>

      <input type="range" min="100" max="10000" v-model.number="radius" />

      <GoogleMap
        :api-key="apiKey"
        class="map-layout__map"
        style="width:100%;height:100%"
        :center="center"
        :zoom="12"
        @click="handleMapClick"
      >
        <!-- Поточна зона -->
        <Marker :options="{ position: center }" />
        <Circle :options="{ 
          center, 
          radius, 
          strokeColor: '#4285F4',
          fillColor: '#4285F4',
          strokeOpacity: 0.6,
          fillOpacity: 0.2,
          strokeWeight: 2,
          clickable: false,
          draggable: false,
          editable: false,
          zIndex: 0 }"
        />

        <MapSavedZones :zones="zones" @select="onSavedZoneSelect" />
      </GoogleMap>
    </div>

    <ZoneInfoPanel
      :visible="panelVisible"
      :zone="panelZone"
      :loading="panelLoading"
      :error-message="panelError"
      @close="closeZonePanel"
    />
  </div>
</template>

<style scoped>
.map-layout {
  display: flex;
  width: 100%;
  min-height: 100vh;
  align-items: stretch;
}

.map-layout__main {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}

.map-layout__map {
  flex: 1;
  min-height: 320px;
}
</style>