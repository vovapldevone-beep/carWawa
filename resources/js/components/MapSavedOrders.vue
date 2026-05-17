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
  selectedOrderId: {
    type: [Number, String],
    default: null,
  },
})

const emit = defineEmits({
  select: (order) => order != null && typeof order === 'object',
})

const markers = ref([])

const carMarkerIcon = `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(`
<svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 38 38">
  <circle cx="19" cy="19" r="18" fill="#2f7df6"/>
  <path d="M9 22.5h2.15a3.15 3.15 0 0 0 6.15 0h3.4a3.15 3.15 0 0 0 6.15 0H29a1.6 1.6 0 0 0 1.6-1.6v-3.1c0-1.35-1.03-2.48-2.37-2.61l-2.63-.26-2.66-3.25A3.2 3.2 0 0 0 20.46 10h-6.62a3.2 3.2 0 0 0-2.88 1.81l-1.68 3.5A3.2 3.2 0 0 0 7 18.36v2.54a1.6 1.6 0 0 0 2 1.6Z" fill="#fff"/>
  <path d="M12.05 14.8 13.1 12.6a1.1 1.1 0 0 1 .99-.62h2.5v2.82h-4.54Zm6.55 0v-2.82h1.64c.33 0 .65.15.86.41l1.97 2.41H18.6Z" fill="#2f7df6"/>
  <circle cx="14.23" cy="22.5" r="1.65" fill="#2f7df6"/>
  <circle cx="23.77" cy="22.5" r="1.65" fill="#2f7df6"/>
</svg>
`)}`

const flagMarkerIcon = `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(`
<svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 38 38">
  <circle cx="19" cy="19" r="18" fill="#37b782"/>
  <path d="M13 28V10.8c0-.55.45-1 1-1s1 .45 1 1V28c0 .55-.45 1-1 1s-1-.45-1-1Z" fill="#fff"/>
  <path d="M15 11.5h10.2c.75 0 1.18.86.73 1.46l-1.82 2.42 1.82 2.42c.45.6.02 1.46-.73 1.46H15V11.5Z" fill="#fff"/>
</svg>
`)}`

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
  () => [props.orders, props.selectedOrderId],
  ([orderList, selectedOrderId]) => {
    const myRun = ++runId
    ;(async () => {
      const list = Array.isArray(orderList) ? orderList : []
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

        if (selectedOrderId != null && String(selectedOrderId) !== String(id)) {
          continue
        }

        const name = order.car_name != null ? String(order.car_name).trim() : ''
        const isSelectedOrder =
          selectedOrderId != null && String(selectedOrderId) === String(id)
        const addresses = [
          {
            type: 'car',
            label: 'Локація авто',
            address: order?.car_location,
          },
        ]
        if (isSelectedOrder) {
          addresses.push({
            type: 'delivery',
            label: 'Адреса доставки',
            address: order?.delivery_address,
          })
        }
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
            type: addressItem.type,
            order,
            position,
            title: `${prefix}: ${addr}`,
            icon: addressItem.type === 'delivery' ? flagMarkerIcon : carMarkerIcon,
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
      icon: m.icon,
      zIndex: 2,
    }"
    @click="m.type === 'car' ? emit('select', m.order) : null"
  />
</template>
