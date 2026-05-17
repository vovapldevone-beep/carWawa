<script setup>
import { computed, ref, watch } from 'vue'

const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['close', 'saved'])

const carName = ref('')
const carNumber = ref('')
const carWeight = ref('')
const loadingDate = ref('')
const carLocation = ref('')
const deliveryAddress = ref('')
const distance = ref('')
const status = ref('')

const statusOptions = [
  {
    value: 'pending',
    label: 'Очікує',
  },
  {
    value: 'in_progress',
    label: 'В роботі',
  },
  {
    value: 'transporting',
    label: 'Перевозиться',
  },
  {
    value: 'delivered',
    label: 'Перевезений',
  },
  {
    value: 'cancelled',
    label: 'Скасований',
  },
]

const availableStatusOptions = computed(() => {
  const currentStatus = status.value.trim()
  if (
    currentStatus === '' ||
    statusOptions.some((option) => option.value === currentStatus)
  ) {
    return statusOptions
  }

  return [
    ...statusOptions,
    {
      value: currentStatus,
      label: currentStatus,
    },
  ]
})

const statusClass = computed(() => {
  const value = status.value.trim()
  if (value === '') {
    return 'order-edit__input--status-empty'
  }
  return `order-edit__input--status-${value.replace(/[^a-z0-9_-]/gi, '-').toLowerCase()}`
})

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
  deliveryAddress.value =
    order.delivery_address != null ? String(order.delivery_address) : ''
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

const submitting = ref(false)
const message = ref('')
const messageIsError = ref(false)

const parseResponsePayload = async (res) => {
  const raw = await res.text()
  if (!raw) {
    return {}
  }
  try {
    return JSON.parse(raw)
  } catch {
    return { _raw: raw.slice(0, 500) }
  }
}

const buildPayload = () => {
  const payload = {
    car_name: carName.value.trim(),
    car_number: carNumber.value.trim(),
    car_location: carLocation.value.trim(),
    delivery_address: deliveryAddress.value.trim(),
    status: status.value.trim() === '' ? null : status.value.trim(),
  }

  const w = String(carWeight.value ?? '').trim()
  if (w === '') {
    payload.car_weight = null
  } else {
    const n = Number(w)
    payload.car_weight = Number.isFinite(n) ? n : null
  }

  if (loadingDate.value) {
    payload.loading_date = loadingDate.value.includes('T')
      ? loadingDate.value.replace('T', ' ')
      : loadingDate.value
  } else {
    payload.loading_date = null
  }

  const d = String(distance.value ?? '').trim()
  if (d === '') {
    payload.distance = null
  } else {
    const n = Number(d)
    payload.distance = Number.isFinite(n) ? n : null
  }

  return payload
}

const submit = async () => {
  message.value = ''
  submitting.value = true

  const id = props.order?.id
  if (id == null) {
    message.value = 'Немає ідентифікатора замовлення.'
    messageIsError.value = true
    submitting.value = false
    return
  }

  try {
    const res = await fetch(`/api/orders/${id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify(buildPayload()),
    })

    const data = await parseResponsePayload(res)

    if (!res.ok) {
      const fromErrors =
        typeof data.errors === 'object' && data.errors !== null
          ? Object.values(data.errors)
              .flat()
              .filter(Boolean)
              .join(' ')
          : ''
      message.value =
        fromErrors ||
        data.message ||
        (data._raw ? `Помилка ${res.status}: ${data._raw}` : `Помилка ${res.status}`)
      messageIsError.value = true
      return
    }

    message.value = 'Збережено.'
    messageIsError.value = false
    applyFromOrder(data)
    emit('saved', data)
  } catch (err) {
    const hint =
      err instanceof TypeError && err.message === 'Failed to fetch'
        ? ' Переконайся, що сервер запущений і сторінка з того ж хоста.'
        : ''
    message.value = `Не вдалося зберегти.${hint} ${err instanceof Error ? err.message : ''}`.trim()
    messageIsError.value = true
  } finally {
    submitting.value = false
  }
}

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
        <span class="order-edit__label">Локація авто</span>
        <input
          v-model="carLocation"
          class="order-edit__input"
          type="text"
          name="car_location"
          autocomplete="street-address"
        />
      </label>

      <label class="order-edit__field order-edit__field--wide">
        <span class="order-edit__label">Адреса доставки</span>
        <input
          v-model="deliveryAddress"
          class="order-edit__input"
          type="text"
          name="delivery_address"
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
        <select
          v-model="status"
          class="order-edit__input"
          :class="statusClass"
          name="status"
        >
          <option
            v-for="option in availableStatusOptions"
            :key="option.value"
            :value="option.value"
          >
            {{ option.label }}
          </option>
        </select>
      </label>
    </div>

    <div class="order-edit__actions">
      <button
        class="order-edit__submit"
        type="button"
        :disabled="submitting"
        @click="submit"
      >
        {{ submitting ? 'Збереження…' : 'Зберегти' }}
      </button>
    </div>

    <p
      v-if="message"
      class="order-edit__message"
      :class="{ 'order-edit__message--error': messageIsError }"
      role="status"
    >
      {{ message }}
    </p>
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

.order-edit__input--status-empty {
  border-color: #cbd5e1;
  background: #f8fafc;
  color: #475569;
}

.order-edit__input--status-pending {
  border-color: #fde68a;
  background: #fffbeb;
  color: #92400e;
}

.order-edit__input--status-in_progress {
  border-color: #bfdbfe;
  background: #eff6ff;
  color: #1d4ed8;
}

.order-edit__input--status-transporting {
  border-color: #c4b5fd;
  background: #f5f3ff;
  color: #6d28d9;
}

.order-edit__input--status-delivered {
  border-color: #bbf7d0;
  background: #f0fdf4;
  color: #15803d;
}

.order-edit__input--status-cancelled {
  border-color: #fecaca;
  background: #fef2f2;
  color: #b91c1c;
}

.order-edit__actions {
  margin-top: 16px;
}

.order-edit__submit {
  padding: 10px 20px;
  font-size: 14px;
  font-weight: 600;
  color: #fff;
  background: #0f172a;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: background 0.15s ease;
}

.order-edit__submit:hover:not(:disabled) {
  background: #1e293b;
}

.order-edit__submit:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.order-edit__message {
  margin: 12px 0 0;
  font-size: 14px;
  color: #166534;
}

.order-edit__message--error {
  color: #991b1b;
}
</style>
