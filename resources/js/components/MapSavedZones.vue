<script setup>
import { ref } from 'vue'
import { Marker, Circle } from 'vue3-google-map'

const props = defineProps({
  zones: {
    type: Array,
    required: true,
  },
  selectedZoneId: {
    type: [Number, String],
    default: null,
  },
})

const hoveredZoneId = ref(null)

const getZoneCircleOptions = (zone) => {
  const isSelected =
    props.selectedZoneId !== null &&
    Number(props.selectedZoneId) === Number(zone.id)
  const isHovered =
    hoveredZoneId.value !== null &&
    Number(hoveredZoneId.value) === Number(zone.id)

  const color = isSelected ? '#990000' : isHovered ? '#008000' : '#4285F4'

  return {
    center: { lat: Number(zone.lat), lng: Number(zone.lng) },
    radius: Number(zone.radius),
    clickable: true,
    draggable: false,
    editable: false,
    zIndex: 0,
    strokeColor: color,
    fillColor: color,
    strokeOpacity: isSelected ? 0.95 : isHovered ? 0.9 : 0.6,
    fillOpacity: isSelected ? 0.35 : isHovered ? 0.35 : 0.2,
    strokeWeight: isSelected ? 4 : isHovered ? 3 : 2,
  }
}

const handleZoneMouseOver = (zoneId) => {
  hoveredZoneId.value = Number(zoneId)
}

const handleZoneMouseOut = () => {
  hoveredZoneId.value = null
}

const emit = defineEmits(['select'])

const handleZoneClick = (zone, event) => {
  if (typeof event?.stop === 'function') {
    event.stop()
  }
  if (Number(hoveredZoneId.value) !== Number(zone.id)) {
    return
  }
  emit('select', zone.id)
}
</script>

<template>
  <template v-for="zone in zones" :key="zone.id">
    <Marker :options="{ position: { lat: Number(zone.lat), lng: Number(zone.lng) } }" />
    <Circle
      :options="getZoneCircleOptions(zone)"
      @mouseover="handleZoneMouseOver(zone.id)"
      @mouseout="handleZoneMouseOut"
      @click="handleZoneClick(zone, $event)"
    />
  </template>
</template>
