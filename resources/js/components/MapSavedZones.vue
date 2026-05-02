<script setup>
import { ref } from 'vue'
import { Marker, Circle } from 'vue3-google-map'

defineProps({
  zones: {
    type: Array,
    required: true,
  },
})

const hoveredZoneId = ref(null)

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
    strokeWeight: isHovered ? 3 : 2,
  }
}

const handleZoneMouseOver = (zoneId) => {
  hoveredZoneId.value = zoneId
}

const handleZoneMouseOut = () => {
  hoveredZoneId.value = null
}
</script>

<template>
  <template v-for="zone in zones" :key="zone.id">
    <Marker :options="{ position: { lat: Number(zone.lat), lng: Number(zone.lng) } }" />
    <Circle
      :options="getZoneCircleOptions(zone)"
      @mouseover="handleZoneMouseOver(zone.id)"
      @mouseout="handleZoneMouseOut"
    />
  </template>
</template>
