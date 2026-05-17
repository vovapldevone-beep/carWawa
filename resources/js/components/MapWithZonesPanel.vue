<script setup>
import { ref, computed, watch } from 'vue'
import { GoogleMap, Marker, Circle } from 'vue3-google-map'
import MapSavedZones from './MapSavedZones.vue'
import MapSavedOrders from './MapSavedOrders.vue'
import ZoneInfoPanel from './ZoneInfoPanel.vue'

const props = defineProps({
  apiKey: {
    type: String,
    required: true,
  },
  zones: {
    type: Array,
    default: () => [],
  },
  orders: {
    type: Array,
    default: () => [],
  },
  radiusMeters: {
    type: Number,
    required: true,
  },
  zoom: {
    type: Number,
    default: 12,
  },
  showCenterMarker: {
    type: Boolean,
    default: true,
  },
  showRadiusCircle: {
    type: Boolean,
    default: true,
  },
  showSavedZones: {
    type: Boolean,
    default: true,
  },
  showSavedOrders: {
    type: Boolean,
    default: true,
  },
  selectedOrderId: {
    type: [Number, String],
    default: null,
  },
})

const center = defineModel('center', {
  type: Object,
  required: true,
})

const emit = defineEmits({
  'map-click': (payload) =>
    payload != null &&
    typeof payload.lat === 'number' &&
    typeof payload.lng === 'number',
  'order-select': (order) => order != null && typeof order === 'object',
  idle: () => true,
})

const selectedZoneId = ref(null)
const panelZone = ref(null)
const panelLoading = ref(false)
const panelError = ref(null)
const mapRef = ref(null)
const directionsRenderer = ref(null)
const routeDistance = ref('')
const routeDuration = ref('')
const routeError = ref('')
const routeLoading = ref(false)
const currentRouteKey = ref('')

let routeRunId = 0

const panelVisible = computed(() => selectedZoneId.value !== null)

const selectedOrder = computed(() => {
  if (props.selectedOrderId == null) {
    return null
  }
  return props.orders.find((order) => String(order?.id) === String(props.selectedOrderId)) ?? null
})

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

  const fromList = props.zones.find((z) => Number(z.id) === Number(zoneId))
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

const handleMapClick = async (event) => {
  const lat = event.latLng.lat()
  const lng = event.latLng.lng()

  center.value = { lat, lng }
  emit('map-click', { lat, lng })
}

const getFilledAddress = (value) => {
  const address = String(value ?? '').trim()
  return address === '' ? null : address
}

const getGoogleMapsApi = () => {
  return mapRef.value?.api ?? window.google?.maps ?? null
}

const clearRoute = () => {
  routeRunId += 1
  if (directionsRenderer.value) {
    directionsRenderer.value.setMap(null)
  }
  currentRouteKey.value = ''
  routeDistance.value = ''
  routeDuration.value = ''
  routeError.value = ''
  routeLoading.value = false
}

const renderSelectedOrderRoute = async () => {
  const order = selectedOrder.value
  if (!order) {
    clearRoute()
    return
  }

  const origin = getFilledAddress(order.car_location)
  const destination = getFilledAddress(order.delivery_address)
  if (!origin || !destination) {
    clearRoute()
    routeError.value = 'Немає адреси для побудови маршруту.'
    return
  }

  const routeKey = `${origin}::${destination}`
  if (currentRouteKey.value === routeKey) {
    return
  }

  const maps = getGoogleMapsApi()
  const map = mapRef.value?.map
  if (!maps || !map) {
    return
  }

  const myRouteRun = ++routeRunId
  currentRouteKey.value = routeKey
  routeLoading.value = true
  routeError.value = ''

  const service = new maps.DirectionsService()
  if (!directionsRenderer.value) {
    directionsRenderer.value = new maps.DirectionsRenderer({
      suppressMarkers: true,
      preserveViewport: false,
      polylineOptions: {
        strokeColor: '#2f7df6',
        strokeOpacity: 0.9,
        strokeWeight: 5,
      },
    })
  }
  directionsRenderer.value.setMap(map)

  service.route(
    {
      origin,
      destination,
      travelMode: maps.TravelMode.DRIVING,
    },
    (result, status) => {
      if (myRouteRun !== routeRunId) {
        return
      }

      routeLoading.value = false
      if (status !== maps.DirectionsStatus.OK || !result) {
        directionsRenderer.value?.setMap(null)
        routeDistance.value = ''
        routeDuration.value = ''
        routeError.value = 'Не вдалося побудувати маршрут.'
        return
      }

      directionsRenderer.value.setDirections(result)
      const leg = result.routes?.[0]?.legs?.[0]
      routeDistance.value = leg?.distance?.text ?? ''
      routeDuration.value = leg?.duration?.text ?? ''
    },
  )
}

const onGoogleMapIdle = () => {
  emit('idle')
  renderSelectedOrderRoute()
}

watch(
  () => props.zones,
  (list) => {
    if (selectedZoneId.value == null) {
      return
    }
    const stillVisible = list.some(
      (z) => Number(z.id) === Number(selectedZoneId.value),
    )
    if (!stillVisible) {
      closeZonePanel()
    }
  },
  { deep: true },
)

watch(
  () => props.showSavedZones,
  (visible) => {
    if (!visible) {
      closeZonePanel()
    }
  },
)

watch(
  () => [props.selectedOrderId, props.orders],
  () => {
    renderSelectedOrderRoute()
  },
  { deep: true },
)
</script>

<template>
  <div class="map-with-zones-panel">
    <GoogleMap
      ref="mapRef"
      :api-key="apiKey"
      class="map-with-zones-panel__map"
      style="width: 100%; height: 100%"
      :center="center"
      :zoom="zoom"
      @click="handleMapClick"
      @idle="onGoogleMapIdle"
    >
      <Marker
        v-if="showCenterMarker"
        :options="{ position: center }"
      />
      <Circle
        v-if="showRadiusCircle"
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
        v-if="showSavedZones"
        :zones="zones"
        :selected-zone-id="selectedZoneId"
        @select="onSavedZoneSelect"
      />

      <MapSavedOrders
        v-if="showSavedOrders"
        :orders="orders"
        :api-key="apiKey"
        :selected-order-id="selectedOrderId"
        @select="emit('order-select', $event)"
      />
    </GoogleMap>

    <div
      v-if="selectedOrderId != null && (routeLoading || routeDistance || routeError)"
      class="map-with-zones-panel__route-info"
      role="status"
    >
      <span v-if="routeLoading">Будуємо маршрут…</span>
      <span v-else-if="routeError">{{ routeError }}</span>
      <template v-else>
        <strong>{{ routeDistance }}</strong>
        <span v-if="routeDuration">~ {{ routeDuration }}</span>
      </template>
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
.map-with-zones-panel {
  position: relative;
  flex: 1;
  min-width: 0;
  min-height: 0;
  display: flex;
  flex-direction: column;
  width: 100%;
  height: 100%;
}

.map-with-zones-panel__map {
  flex: 1;
  min-height: 320px;
}

.map-with-zones-panel__route-info {
  position: absolute;
  left: 1rem;
  bottom: 1rem;
  z-index: 3;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  max-width: calc(100% - 2rem);
  padding: 0.65rem 0.85rem;
  border-radius: 999px;
  background: #fff;
  color: #1f2a44;
  font-size: 0.875rem;
  font-weight: 500;
  box-shadow: 0 10px 26px rgb(31 42 68 / 0.16);
}

.map-with-zones-panel__route-info strong {
  color: #2f7df6;
  font-weight: 800;
}
</style>
