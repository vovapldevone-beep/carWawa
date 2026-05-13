<script setup>
import OrderComponent from './OrderComponent.vue'

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

const emit = defineEmits(['select'])
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
      <OrderComponent
        v-for="order in orders"
        :key="order.id"
        :order="order"
        @select="emit('select', $event)"
      />
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
</style>
