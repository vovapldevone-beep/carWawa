<script setup>
import { computed } from 'vue'

const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['select'])

const getFirstFilledValue = (...values) => {
  return values.find((value) => value != null && String(value).trim() !== '') ?? ''
}

const formatDateTime = (value) => {
  if (value == null || value === '') {
    return '—'
  }

  const d = new Date(value)
  if (Number.isNaN(d.getTime())) {
    return String(value)
  }

  return new Intl.DateTimeFormat('uk-UA', {
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
  }).format(d)
}

const formatStatus = (value) => {
  if (value == null || value === '') {
    return '—'
  }
  return String(value)
}

const statusKey = computed(() => {
  const raw = formatStatus(props.order?.status)
  if (raw === '—') {
    return ''
  }
  return raw.trim().toLowerCase()
})

/**
 * Семантичний варіант бейджа в шапці (pending — зелений як у макеті).
 *
 * @param {string} key
 * @returns {'green' | 'blue' | 'neutral' | 'amber' | 'rose'}
 */
const badgeVariant = (key) => {
  if (!key) {
    return 'neutral'
  }
  if (key.includes('pending') || key.includes('очіку') || key.includes('нов')) {
    return 'green'
  }
  if (
    key.includes('progress') ||
    key.includes('process') ||
    key.includes('в дорозі') ||
    key.includes('актив')
  ) {
    return 'blue'
  }
  if (key.includes('done') || key.includes('complete') || key.includes('заверш')) {
    return 'neutral'
  }
  if (key.includes('cancel') || key.includes('відмін')) {
    return 'rose'
  }
  return 'blue'
}

const headerBadgeVariant = computed(() => badgeVariant(statusKey.value))

const carPhoto = computed(() => {
  return getFirstFilledValue(
    props.order?.car_photo,
    props.order?.car_image,
    props.order?.photo,
    props.order?.image,
    props.order?.thumbnail
  )
})

const carTitle = computed(() => {
  return getFirstFilledValue(props.order?.car_name, props.order?.car_model, '—')
})

const carNumber = computed(() => {
  return getFirstFilledValue(props.order?.car_number, props.order?.license_plate, '—')
})

const carLocation = computed(() => {
  return getFirstFilledValue(props.order?.car_location, props.order?.location, '—')
})

const deliveryAddress = computed(() => {
  return getFirstFilledValue(
    props.order?.delivery_address,
    props.order?.evacuator_location,
    props.order?.evacuation_location,
    props.order?.destination,
    props.order?.dropoff_location,
    props.order?.to_location,
    '—'
  )
})

const onSelect = () => {
  emit('select', props.order)
}

const onKeydown = (event) => {
  if (event.key === 'Enter' || event.key === ' ') {
    event.preventDefault()
    onSelect()
  }
}
</script>

<template>
  <li
    class="order-component"
    role="button"
    tabindex="0"
    :aria-label="`Відкрити замовлення ${order.id ?? ''}`"
    @click="onSelect"
    @keydown="onKeydown"
  >
    <header class="order-component__top">
      <span class="order-component__id">#{{ order.id ?? '—' }}</span>
      <span
        v-if="formatStatus(order.status) !== '—'"
        class="order-component__badge"
        :class="`order-component__badge--${headerBadgeVariant}`"
      >
        {{ formatStatus(order.status) }}
      </span>
    </header>

    <div class="order-component__body">
      <div class="order-component__photo">
        <img
          v-if="carPhoto"
          class="order-component__photo-img"
          :src="carPhoto"
          :alt="carTitle"
          loading="lazy"
        />
        <span v-else class="order-component__photo-empty">Без фото</span>
      </div>

      <div class="order-component__content">
        <h3 class="order-component__title">
          <span>{{ carTitle }}</span>
          <span class="order-component__dot" aria-hidden="true">•</span>
          <span>{{ carNumber }}</span>
        </h3>

        <div class="order-component__locations">
          <div class="order-component__location-row order-component__location-row--car">
            <span class="order-component__pin" aria-hidden="true"></span>
            <div class="order-component__location-text">
              <span class="order-component__label">Місцезнаходження авто</span>
              <span class="order-component__value">{{ carLocation }}</span>
            </div>
          </div>

          <div class="order-component__location-row order-component__location-row--evacuator">
            <span class="order-component__pin" aria-hidden="true"></span>
            <div class="order-component__location-text">
              <span class="order-component__label">Адреса доставки</span>
              <span class="order-component__value">{{ deliveryAddress }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <footer class="order-component__footer">
      <svg
        class="order-component__calendar"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.8"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
      >
        <path d="M8 2v4" />
        <path d="M16 2v4" />
        <rect x="3" y="4" width="18" height="18" rx="2" />
        <path d="M3 10h18" />
      </svg>
      <span>{{ formatDateTime(order.loading_date) }}</span>
    </footer>
  </li>
</template>

<style scoped>
.order-component {
  --oc-blue: #2f7df6;
  --oc-green: #37b782;
  --oc-text: #1f2a44;
  --oc-muted: #7b879f;
  --oc-border: #e8edf5;
  --oc-shadow: 0 10px 26px rgb(31 42 68 / 0.08);

  box-sizing: border-box;
  margin: 0;
  padding: 1rem;
  border-radius: 8px;
  border: 1px solid var(--oc-border);
  background: #fff;
  list-style: none;
  cursor: pointer;
  text-align: left;
  font-family:
    ui-sans-serif,
    system-ui,
    -apple-system,
    'Segoe UI',
    Roboto,
    'Helvetica Neue',
    Arial,
    sans-serif;
  box-shadow: var(--oc-shadow);
  transition:
    border-color 0.15s ease,
    box-shadow 0.15s ease,
    transform 0.12s ease;
}

.order-component:hover {
  border-color: #dbe3ef;
  box-shadow: 0 12px 30px rgb(31 42 68 / 0.1);
}

.order-component:active {
  transform: scale(0.998);
}

.order-component:focus-visible {
  outline: none;
  border-color: rgb(47 125 246 / 0.45);
  box-shadow:
    var(--oc-shadow),
    0 0 0 3px rgb(47 125 246 / 0.16);
}

.order-component__top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  margin-bottom: 0.875rem;
}

.order-component__id {
  color: #1f6eea;
  font-size: 0.875rem;
  font-weight: 700;
}

.order-component__body {
  display: grid;
  grid-template-columns: 6.35rem minmax(0, 1fr);
  gap: 0.875rem;
  align-items: start;
}

.order-component__photo {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 6.35rem;
  height: 4.5rem;
  overflow: hidden;
  border-radius: 6px;
  background: linear-gradient(135deg, #eef3f9, #dfe7f1);
  color: #7b879f;
  font-size: 0.8rem;
  font-weight: 700;
}

.order-component__photo-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.order-component__photo-empty {
  padding: 0.5rem;
  text-align: center;
}

.order-component__content {
  min-width: 0;
}

.order-component__title {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.3rem;
  margin: -0.15rem 0 0.75rem;
  color: var(--oc-text);
  font-size: 0.875rem;
  font-weight: 700;
  line-height: 1.25;
}

.order-component__dot {
  color: #8b96aa;
  font-size: 0.9rem;
}

.order-component__badge {
  display: inline-flex;
  align-items: center;
  max-width: 45%;
  padding: 0.25rem 0.55rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
  line-height: 1.25;
  text-transform: capitalize;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.order-component__badge--green {
  background: #dff6eb;
  color: #15865a;
}

.order-component__badge--blue {
  background: #e7f0ff;
  color: #007bff;
}

.order-component__badge--neutral {
  background: #f1f5f9;
  color: #475569;
}

.order-component__badge--amber {
  background: #fff8e6;
  color: #b45309;
}

.order-component__badge--rose {
  background: #ffeef0;
  color: #c41e3a;
}

.order-component__locations {
  display: flex;
  flex-direction: column;
  gap: 0;
}

.order-component__location-row {
  position: relative;
  display: flex;
  gap: 0.55rem;
  min-height: 2.55rem;
}

.order-component__location-row:not(:last-child)::after {
  position: absolute;
  top: 1rem;
  bottom: -0.15rem;
  left: 0.25rem;
  width: 1px;
  background: #cfd7e6;
  content: '';
}

.order-component__pin {
  position: relative;
  z-index: 1;
  flex: 0 0 auto;
  width: 0.55rem;
  height: 0.55rem;
  margin-top: 0.28rem;
  border-radius: 50%;
  box-shadow: 0 0 0 2px #fff;
}

.order-component__location-row--car .order-component__pin {
  background: var(--oc-blue);
}

.order-component__location-row--evacuator .order-component__pin {
  background: var(--oc-green);
}

.order-component__location-text {
  display: flex;
  min-width: 0;
  flex-direction: column;
  gap: 0.15rem;
  padding-bottom: 0.6rem;
}

.order-component__label {
  color: var(--oc-muted);
  font-size: 0.78rem;
  font-weight: 500;
  line-height: 1.2;
}

.order-component__value {
  color: var(--oc-text);
  font-size: 0.875rem;
  font-weight: 500;
  line-height: 1.25;
  word-break: break-word;
}

.order-component__footer {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  margin-top: 0.7rem;
  color: #4b5873;
  font-size: 0.82rem;
  font-weight: 500;
}

.order-component__calendar {
  width: 0.95rem;
  height: 0.95rem;
  color: #6d7890;
}

@media (max-width: 420px) {
  .order-component {
    padding: 0.875rem;
  }

  .order-component__body {
    grid-template-columns: 5.5rem minmax(0, 1fr);
    gap: 0.75rem;
  }

  .order-component__photo {
    width: 5.5rem;
    height: 4rem;
  }
}
</style>
