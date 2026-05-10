<template>
    <header class="top-menu">
      <nav class="map-layout__page-nav" aria-label="Навігація">
  <a href="/" class="map-layout__page-link">
    Евакуатори
  </a>

  <a href="/orders" class="map-layout__page-link">
    Замовлення
  </a>
</nav>
    </header>
  </template>
  
  <script setup>
  // логіка поки не потрібна
  </script>
  
  <style>
  body {
    padding-top: 80px;
  }
  </style>
  <style scoped>
  body {
    padding-top: 80px;
  }
  .map-layout__page-nav {
  position: fixed;
  top: 0;
  left: 0;

  width: 100%;
  height: 70px;

  display: flex;
  align-items: center;
  gap: 20px;

  padding: 0 30px;

  background: #1e293b;

  z-index: 1000;
}

.map-layout__page-link {
  color: white;
  text-decoration: none;

  padding: 10px 16px;
  border-radius: 8px;

  transition: 0.2s;
}

.map-layout__page-link:hover {
  background: rgba(255, 255, 255, 0.12);
}
  </style>