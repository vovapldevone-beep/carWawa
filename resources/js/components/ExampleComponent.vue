<script setup>
import { ref, onMounted } from 'vue'
import { GoogleMap, Marker, Circle } from 'vue3-google-map'

const apiKey = "AIzaSyCZ_qe1aRHfN0-tijNv8sB3J7ti-jEFtGw"

const center = ref({ lat: 52.2297, lng: 21.0122 })
const radius = ref(1000)
const address = ref('')
const zones = ref([])
const hoveredZoneId = ref(null)

const circleRef = ref(null)

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
    const res = await fetch('http://127.0.0.1:8000/api/zones', {
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
  const res = await fetch('http://127.0.0.1:8000/api/zones')
  zones.value = await res.json()
}

const getZoneCircleOptions = (zone) => {
  const isHovered = hoveredZoneId.value === zone.id

  return {
    center: { lat: Number(zone.lat), lng: Number(zone.lng) },
    radius: Number(zone.radius),
    clickable: true,
    draggable: false,
    editable: false,
    zIndex: 0,
    strokeColor: isHovered ? '#008000' : '#4285F4',
    fillColor: isHovered ? '#008000' : '#4285F4',
    strokeOpacity: isHovered ? 0.9 : 0.6,
    fillOpacity: isHovered ? 0.35 : 0.2,
    strokeWeight: isHovered ? 3 : 2
  }
}

const handleZoneMouseOver = (zoneId) => {
  hoveredZoneId.value = zoneId
}

const handleZoneMouseOut = () => {
  hoveredZoneId.value = null
}

onMounted(loadZones)
</script>

<template>
  <div>
    <input v-model="address" placeholder="Адреса" />
    <button @click="geocode">Знайти</button>
    <button @click="saveZone">Зберегти</button>

    <input type="range" min="100" max="10000" v-model.number="radius" />

    <GoogleMap :api-key="apiKey" style="width:100%;height:100%" :center="center" :zoom="12" @click="handleMapClick">
      
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

      <!-- Збережені -->
      <div v-for="zone in zones" :key="zone.id">
        <Marker :options="{ position: { lat: Number(zone.lat), lng: Number(zone.lng) } }" />
        <Circle
          :options="getZoneCircleOptions(zone)"
          @mouseover="handleZoneMouseOver(zone.id)"
          @mouseout="handleZoneMouseOut"
        />
      </div>

    </GoogleMap>
  </div>
</template>