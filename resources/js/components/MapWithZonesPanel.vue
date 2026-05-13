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
  idle: () => true,
})

const selectedZoneId = ref(null)
const panelZone = ref(null)
const panelLoading = ref(false)
const panelError = ref(null)

const panelVisible = computed(() => selectedZoneId.value !== null)

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
</script>

<template>
  <div class="map-with-zones-panel">
    <GoogleMap
      :api-key="apiKey"
      class="map-with-zones-panel__map"
      style="width: 100%; height: 100%"
      :center="center"
      :zoom="zoom"
      @click="handleMapClick"
      @idle="emit('idle')"
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
</style>
