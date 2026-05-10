import { createApp } from 'vue'
import ExampleComponent from './components/ExampleComponent.vue'
import MapOrders from './components/MapOrders.vue'


const mountEl = document.getElementById('app')

if (mountEl) {
  const page = mountEl.dataset.page ?? 'welcome'

  switch (page) {
    case 'orders':
      createApp(MapOrders).mount(mountEl)
      break
    case 'welcome':
    default:
      createApp(ExampleComponent).mount(mountEl)
      break
  }
}