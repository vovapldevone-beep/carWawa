<script setup>
defineProps({
  orders: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
  error: {
    type: String,
    default: '',
  },
})

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
</script>

<template>
  <section class="orders-list" aria-label="Список замовлень">
    <h2 class="orders-list__title">Список замовлень</h2>

    <p v-if="loading" class="orders-list__hint">Завантаження…</p>
    <p
      v-else-if="error"
      class="orders-list__error"
      role="alert"
    >
      {{ error }}
    </p>
    <p
      v-else-if="orders.length === 0"
      class="orders-list__hint"
    >
      Поки немає замовлень.
    </p>

    <ul v-else class="orders-list__items">
      <li
        v-for="order in orders"
        :key="order.id"
        class="orders-list__card"
      >
        <p class="orders-list__card-title">
          {{ order.car_name || '—' }}
        </p>
        <dl class="orders-list__card-dl">
          <div class="orders-list__card-row">
            <dt>Номер авто</dt>
            <dd>{{ order.car_number || '—' }}</dd>
          </div>
          <div class="orders-list__card-row">
            <dt>Вага</dt>
            <dd>{{ formatWeight(order.car_weight) }}</dd>
          </div>
          <div class="orders-list__card-row">
            <dt>Дата завантаження</dt>
            <dd>{{ formatLoadingDate(order.loading_date) }}</dd>
          </div>
          <div class="orders-list__card-row">
            <dt>Локація</dt>
            <dd>{{ order.car_location || '—' }}</dd>
          </div>
          <div class="orders-list__card-row">
            <dt>Дистанція</dt>
            <dd>{{ formatDistance(order.distance) }}</dd>
          </div>
          <div class="orders-list__card-row">
            <dt>Статус</dt>
            <dd class="orders-list__card-status">
              {{ formatStatus(order.status) }}
            </dd>
          </div>
        </dl>
      </li>
    </ul>
  </section>
</template>

<style scoped>
.orders-list {
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
}

.orders-list__title {
  margin: 0 0 10px;
  font-size: 15px;
  font-weight: 700;
  color: #0f172a;
}

.orders-list__hint {
  margin: 0;
  font-size: 14px;
  color: #64748b;
}

.orders-list__error {
  margin: 0;
  font-size: 14px;
  color: #991b1b;
}

.orders-list__items {
  list-style: none;
  margin: 0;
  padding: 0;
  overflow-y: auto;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 10px;
  min-height: 120px;
}

.orders-list__card {
  margin: 0;
  padding: 12px 14px;
  border-radius: 0.75rem;
  border: 1px solid #e2e8f0;
  background: #f8fafc;
}

.orders-list__card-title {
  margin: 0 0 8px;
  font-size: 15px;
  font-weight: 700;
  color: #0f172a;
}

.orders-list__card-dl {
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.orders-list__card-row {
  display: grid;
  grid-template-columns: minmax(0, 38%) 1fr;
  gap: 8px 10px;
  font-size: 13px;
  line-height: 1.35;
}

.orders-list__card-row dt {
  margin: 0;
  font-weight: 600;
  color: #64748b;
}

.orders-list__card-row dd {
  margin: 0;
  color: #0f172a;
  word-break: break-word;
}

.orders-list__card-status {
  text-transform: capitalize;
}
</style>
