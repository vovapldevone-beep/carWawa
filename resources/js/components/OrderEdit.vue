<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['close'])

const carName = ref('')
const carNumber = ref('')
const carWeight = ref('')
const loadingDate = ref('')
const carLocation = ref('')
const distance = ref('')
const status = ref('')

const loadingDateToInput = (value) => {
  if (value == null || value === '') {
    return ''
  }
  const s = String(value).trim()
  if (s.includes('T')) {
    return s.slice(0, 16)
  }
  return s.replace(' ', 'T').slice(0, 16)
}

const applyFromOrder = (order) => {
  carName.value = order.car_name != null ? String(order.car_name) : ''
  carNumber.value = order.car_number != null ? String(order.car_number) : ''
  carWeight.value =
    order.car_weight != null && order.car_weight !== ''
      ? String(order.car_weight)
      : ''
  loadingDate.value = loadingDateToInput(order.loading_date)
  carLocation.value =
    order.car_location != null ? String(order.car_location) : ''
  distance.value =
    order.distance != null && order.distance !== ''
      ? String(order.distance)
      : ''
  status.value = order.status != null ? String(order.status) : ''
}

watch(
  () => props.order,
  (order) => {
    applyFromOrder(order)
  },
  { immediate: true, deep: true },
)

const onClose = () => {
  emit('close')
}
</script>

<template>
  <section class="order-edit" aria-label="Редагування замовлення">
    <header class="order-edit__header">
      <h2 class="order-edit__title">
        Замовлення
        <span v-if="order.id != null" class="order-edit__id">#{{ order.id }}</span>
      </h2>
      <button
        type="button"
        class="order-edit__close"
        aria-label="Закрити"
        @click="onClose"
      >
        <span aria-hidden="true">×</span>
      </button>
    </header>

    <div class="order-edit__grid">
      <label class="order-edit__field">
        <span class="order-edit__label">Назва авто</span>
        <input
          v-model="carName"
          class="order-edit__input"
          type="text"
          name="car_name"
          autocomplete="off"
        />
      </label>

      <label class="order-edit__field">
        <span class="order-edit__label">Номер авто</span>
        <input
          v-model="carNumber"
          class="order-edit__input"
          type="text"
          name="car_number"
          autocomplete="off"
        />
      </label>

      <label class="order-edit__field">
        <span class="order-edit__label">Вага авто (т)</span>
        <input
          v-model="carWeight"
          class="order-edit__input"
          type="number"
          name="car_weight"
          min="0"
          step="0.01"
        />
      </label>

      <label class="order-edit__field order-edit__field--wide">
        <span class="order-edit__label">Дата завантаження</span>
        <input
          v-model="loadingDate"
          class="order-edit__input"
          type="datetime-local"
          name="loading_date"
        />
      </label>

      <label class="order-edit__field order-edit__field--wide">
        <span class="order-edit__label">Локація</span>
        <input
          v-model="carLocation"
          class="order-edit__input"
          type="text"
          name="car_location"
          autocomplete="street-address"
        />
      </label>

      <label class="order-edit__field">
        <span class="order-edit__label">Дистанція (км)</span>
        <input
          v-model="distance"
          class="order-edit__input"
          type="text"
          name="distance"
          inputmode="decimal"
        />
      </label>

      <label class="order-edit__field">
        <span class="order-edit__label">Статус</span>
        <input
          v-model="status"
          class="order-edit__input"
          type="text"
          name="status"
        />
      </label>
    </div>
  </section>
</template>

<style scoped>
.order-edit {
  flex: 1;
  min-height: 0;
  display: flex;
  flex-direction: column;
  padding: 14px 16px;
  background: #fff;
  border-radius: 1rem;
  border: 1px solid rgb(226 232 240 / 0.9);
  box-shadow:
    0 4px 6px -1px rgb(15 23 42 / 0.06),
    0 2px 4px -2px rgb(15 23 42 / 0.04);
  font-family: ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
  color: #0f172a;
  overflow-y: auto;
}

.order-edit__header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 10px;
  margin-bottom: 14px;
}

.order-edit__title {
  margin: 0;
  font-size: 15px;
  font-weight: 700;
  color: #0f172a;
  line-height: 1.25;
}

.order-edit__id {
  font-weight: 600;
  color: #64748b;
}

.order-edit__close {
  flex-shrink: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2rem;
  height: 2rem;
  margin: -4px -4px 0 0;
  padding: 0;
  border: none;
  border-radius: 0.5rem;
  background: transparent;
  color: #64748b;
  font-size: 1.5rem;
  line-height: 1;
  cursor: pointer;
  transition:
    background 0.15s ease,
    color 0.15s ease;
}

.order-edit__close:hover {
  background: #f1f5f9;
  color: #0f172a;
}

.order-edit__close:focus-visible {
  outline: none;
  box-shadow: 0 0 0 3px rgb(0 102 255 / 0.2);
}

.order-edit__grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 12px 16px;
}

.order-edit__field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.order-edit__field--wide {
  grid-column: 1 / -1;
}

.order-edit__label {
  font-size: 13px;
  font-weight: 600;
  color: #334155;
}

.order-edit__input {
  box-sizing: border-box;
  width: 100%;
  padding: 10px 12px;
  font-size: 14px;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  background: #f8fafc;
  color: #0f172a;
}

.order-edit__input:focus {
  outline: none;
  border-color: #94a3b8;
  background: #fff;
}
</style>
