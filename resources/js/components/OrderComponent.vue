<script setup>
const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['select'])

const formatWeight = (value) => {
  if (value == null || value === '') {
    return '—'
  }
  const n = Number(value)
  return Number.isFinite(n) ? `${n} т` : String(value)
}

const formatDistance = (value) => {
  if (value == null || value === '') {
    return '—'
  }
  return `${value} км`
}

const formatLoadingDate = (value) => {
  if (value == null || value === '') {
    return '—'
  }
  const d = new Date(value)
  if (Number.isNaN(d.getTime())) {
    return String(value)
  }
  return new Intl.DateTimeFormat('uk-UA', {
    dateStyle: 'short',
    timeStyle: 'short',
  }).format(d)
}

const formatStatus = (value) => {
  if (value == null || value === '') {
    return '—'
  }
  return String(value)
}

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
    <p class="order-component__title">
      {{ order.car_name || '—' }}
    </p>
    <dl class="order-component__dl">
      <div class="order-component__row">
        <dt>Номер авто</dt>
        <dd>{{ order.car_number || '—' }}</dd>
      </div>
      <div class="order-component__row">
        <dt>Вага</dt>
        <dd>{{ formatWeight(order.car_weight) }}</dd>
      </div>
      <div class="order-component__row">
        <dt>Дата завантаження</dt>
        <dd>{{ formatLoadingDate(order.loading_date) }}</dd>
      </div>
      <div class="order-component__row">
        <dt>Локація</dt>
        <dd>{{ order.car_location || '—' }}</dd>
      </div>
      <div class="order-component__row">
        <dt>Дистанція</dt>
        <dd>{{ formatDistance(order.distance) }}</dd>
      </div>
      <div class="order-component__row">
        <dt>Статус</dt>
        <dd class="order-component__status">
          {{ formatStatus(order.status) }}
        </dd>
      </div>
    </dl>
  </li>
</template>

<style scoped>
.order-component {
  margin: 0;
  padding: 12px 14px;
  border-radius: 0.75rem;
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  list-style: none;
  cursor: pointer;
  text-align: left;
  transition:
    border-color 0.15s ease,
    background 0.15s ease,
    box-shadow 0.15s ease;
}

.order-component:hover {
  border-color: #cbd5e1;
  background: #f1f5f9;
}

.order-component:focus-visible {
  outline: none;
  border-color: rgb(0 102 255 / 0.45);
  box-shadow: 0 0 0 3px rgb(0 102 255 / 0.15);
}

.order-component__title {
  margin: 0 0 8px;
  font-size: 15px;
  font-weight: 700;
  color: #0f172a;
}

.order-component__dl {
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.order-component__row {
  display: grid;
  grid-template-columns: minmax(0, 38%) 1fr;
  gap: 8px 10px;
  font-size: 13px;
  line-height: 1.35;
}

.order-component__row dt {
  margin: 0;
  font-weight: 600;
  color: #64748b;
}

.order-component__row dd {
  margin: 0;
  color: #0f172a;
  word-break: break-word;
}

.order-component__status {
  text-transform: capitalize;
}
</style>
