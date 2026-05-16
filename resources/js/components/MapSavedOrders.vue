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
        if (id == null) {
          continue
        }

        const name = order.car_name != null ? String(order.car_name).trim() : ''
        const addresses = [
          {
            type: 'car',
            label: 'Локація авто',
            address: order?.car_location,
          },
          {
            type: 'delivery',
            label: 'Адреса доставки',
            address: order?.delivery_address,
          },
        ]
        const usedAddresses = new Set()

        for (const addressItem of addresses) {
          const addr = String(addressItem.address ?? '').trim()
          if (addr === '' || usedAddresses.has(addr)) {
            continue
          }
          usedAddresses.add(addr)

          const position = await resolvePosition(addr)
          if (myRun !== runId) {
            return
          }
          if (!position) {
            continue
          }

          const prefix = name !== '' ? `${name} — ${addressItem.label}` : addressItem.label
          nextMarkers.push({
            id: `${id}:${addressItem.type}`,
            position,
            title: `${prefix}: ${addr}`,
          })
        }
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
