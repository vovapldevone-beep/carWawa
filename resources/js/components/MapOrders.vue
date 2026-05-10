<script setup>
import { ref, onMounted } from 'vue'
import { GoogleMap, Marker } from 'vue3-google-map'
import NawBarMenu from './NavBarMenu.vue'

const apiKey = 'AIzaSyCZ_qe1aRHfN0-tijNv8sB3J7ti-jEFtGw'

const center = ref({ lat: 52.2297, lng: 21.0122 })

const mapLoaded = ref(false)
const mapLoadError = ref('')

const onMapIdle = () => {
  mapLoaded.value = true
}

onMounted(() => {
  window.setTimeout(() => {
    if (mapLoaded.value) {
      return
    }
    const hasGoogleMaps =
      typeof window !== 'undefined' &&
      window.google != null &&
      window.google.maps != null

    if (!hasGoogleMaps) {
      mapLoadError.value =
        'Google Maps не завантажився. Перевір API key, billing та обмеження (HTTP referrer/host).'
    } else {
      mapLoadError.value =
        'Карта ініціалізована з помилкою або контейнер має некоректний розмір.'
    }
  }, 3000)
})
</script>

<template>
  <NawBarMenu/>

  <div class="orders-map">
    <!-- <nav class="map-layout__page-nav" aria-label="Навігація">
      <a href="/" class="map-layout__page-link">Карта</a>
    </nav> -->
    <header class="orders-map__header">
      <h1 class="orders-map__title">Orders</h1>
    </header>

    <div class="orders-map__map-area">
      <GoogleMap
        :api-key="apiKey"
        class="orders-map__map"
        style="width: 100%; height: 100%"
        :center="center"
        :zoom="12"
        @idle="onMapIdle"
      >
        <Marker :options="{ position: center }" />
      </GoogleMap>

      <p v-if="mapLoadError" class="orders-map__error" role="alert">
        {{ mapLoadError }}
      </p>
    </div>
  </div>
</template>

<style scoped>
.map-layout__page-nav {
  margin-bottom: 1rem;
}

.map-layout__page-link {
  display: inline-flex;
  align-items: center;
  font-size: 0.9375rem;
  font-weight: 600;
  color: #0f172a;
  text-decoration: none;
  padding: 0.5rem 0.75rem;
  border-radius: 0.5rem;
  border: 1px solid rgb(226 232 240 / 0.9);
  background: #f8fafc;
  transition:
    background 0.15s ease,
    border-color 0.15s ease;
}

.map-layout__page-link:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
}
.orders-map {
  display: flex;
  flex-direction: column;
  gap: 12px;
  box-sizing: border-box;
  width: 100%;
  min-height: 100vh;
  padding: 12px;
  background: #f1f5f9;
}

.orders-map__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 18px;
  background: #fff;
  border-radius: 1rem;
  border: 1px solid rgb(226 232 240 / 0.9);
  box-shadow:
    0 4px 6px -1px rgb(15 23 42 / 0.06),
    0 2px 4px -2px rgb(15 23 42 / 0.04);
}

.orders-map__title {
  margin: 0;
  font-size: 18px;
  line-height: 1.2;
  font-weight: 700;
  color: #0f172a;
}

.orders-map__map-area {
  height: calc(100vh - 96px);
  min-height: 420px;
  display: block;
  border-radius: 1rem;
  overflow: hidden;
  border: 1px solid #e2e8f0;
  background: #e5e7eb;
}

.orders-map__map {
  width: 100%;
  height: 100%;
  min-height: 420px;
}

.orders-map__error {
  margin: 0;
  padding: 10px 12px;
  font-size: 14px;
  color: #7f1d1d;
  background: #fef2f2;
  border-top: 1px solid #fecaca;
}
</style>
