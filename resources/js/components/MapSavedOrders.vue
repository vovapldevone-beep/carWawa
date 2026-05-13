<script setup>
import { ref, watch } from 'vue'
import { Marker } from 'vue3-google-map'

const props = defineProps({
  orders: {
    type: Array,
    required: true,
  },
  apiKey: {
    type: String,
    required: true,
  },
})

const markers = ref([])

const fetchGeocode = async (address) => {
  const query = String(address).trim()
  if (query.length < 2) {
    return null
  }
  try {
    const res = await fetch(
      `https://maps.googleapis.com/maps/api/geocode/json?address=${encodeURIComponent(
        query,
      )}&key=${props.apiKey}`,
    )
    const data = await res.json()
    if (data.results?.length) {
      const loc = data.results[0].geometry.location
      return {
        lat: Number(loc.lat),
        lng: Number(loc.lng),
      }
    }
  } catch {
    /* ignore */
  }
  return null
}

let runId = 0

watch(
  () => props.orders,
  (orders) => {
    const myRun = ++runId
    ;(async () => {
      const list = Array.isArray(orders) ? orders : []
      const addressPromiseCache = new Map()
      const resolvePosition = (address) => {
        const key = String(address).trim()
        if (addressPromiseCache.has(key)) {
          return addressPromiseCache.get(key)
        }
        const p = fetchGeocode(key)
        addressPromiseCache.set(key, p)
        return p
      }

      const nextMarkers = []
      for (const order of list) {
        if (myRun !== runId) {
          return
        }
        const id = order?.id
        const addr = order?.car_location
        if (id == null || addr == null || String(addr).trim() === '') {
          continue
        }
        const position = await resolvePosition(addr)
        if (myRun !== runId) {
          return
        }
        if (!position) {
          continue
        }
        const label = String(addr).trim()
        const name = order.car_name != null ? String(order.car_name).trim() : ''
        const title = name !== '' ? `${name} — ${label}` : label
        nextMarkers.push({
          id,
          position,
          title,
        })
      }
      if (myRun !== runId) {
        return
      }
      markers.value = nextMarkers
    })()
  },
  { deep: true, immediate: true },
)
</script>

<template>
  <Marker
    v-for="m in markers"
    :key="m.id"
    :options="{
      position: m.position,
      title: m.title,
      zIndex: 2,
    }"
  />
</template>
