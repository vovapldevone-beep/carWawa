<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  apiKey: {
    type: String,
    required: true,
  },
})

const emit = defineEmits(['created'])

const carLocation = defineModel('carLocation', { type: String, default: '' })
const center = defineModel('center', { type: Object, required: true })
const showSavedZones = defineModel('showSavedZones', { type: Boolean, default: true })

const carName = ref('')
const carNumber = ref('')
const carWeight = ref('')
const loadingDate = ref('')
const deliveryAddress = ref('')

const submitting = ref(false)
const message = ref('')
const messageIsError = ref(false)

let debounceTimer = null
let deliveryAddressDebounceTimer = null
let geocodeRequestId = 0

const geocodeFromText = async (text, target) => {
  const query = String(text).trim()
  if (query.length < 3) {
    return
  }
  const reqId = ++geocodeRequestId
  try {
    const res = await fetch(
      `https://maps.googleapis.com/maps/api/geocode/json?address=${encodeURIComponent(
        query,
      )}&key=${props.apiKey}`,
    )
    const data = await res.json()
    if (reqId !== geocodeRequestId) {
      return
    }
    if (data.results?.length) {
      center.value = data.results[0].geometry.location
      target.value = data.results[0].formatted_address
    }
  } catch {
    /* ignore */
  }
}

watch(carLocation, (val) => {
  window.clearTimeout(debounceTimer)
  const t = String(val ?? '').trim()
  if (t.length < 4) {
    return
  }
  debounceTimer = window.setTimeout(() => {
    geocodeFromText(t, carLocation)
  }, 550)
})

watch(deliveryAddress, (val) => {
  window.clearTimeout(deliveryAddressDebounceTimer)
  const t = String(val ?? '').trim()
  if (t.length < 4) {
    return
  }
  deliveryAddressDebounceTimer = window.setTimeout(() => {
    geocodeFromText(t, deliveryAddress)
  }, 550)
})

const onLocationFindClick = () => {
  window.clearTimeout(debounceTimer)
  geocodeFromText(carLocation.value, carLocation)
}

const onDeliveryAddressFindClick = () => {
  window.clearTimeout(deliveryAddressDebounceTimer)
  geocodeFromText(deliveryAddress.value, deliveryAddress)
}

const toggleShowSavedZones = () => {
  showSavedZones.value = !showSavedZones.value
}

const resetForm = () => {
  carName.value = ''
  carNumber.value = ''
  carWeight.value = ''
  carLocation.value = ''
  deliveryAddress.value = ''
  loadingDate.value = ''
}

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

const submit = async () => {
  message.value = ''
  submitting.value = true

  try {
    const payload = {
      car_name: carName.value.trim(),
      car_number: carNumber.value.trim(),
      car_location: carLocation.value.trim(),
      delivery_address: deliveryAddress.value.trim(),
    }

    const w = String(carWeight.value ?? '').trim()
    if (w !== '') {
      const n = Number(w)
      payload.car_weight = Number.isFinite(n) ? n : null
    }

    if (loadingDate.value) {
      payload.loading_date = loadingDate.value.includes('T')
        ? loadingDate.value.replace('T', ' ')
        : loadingDate.value
    }

    const res = await fetch('/api/orders', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify(payload),
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

    message.value = 'Замовлення збережено.'
    messageIsError.value = false
    resetForm()
    emit('created')
  } catch (err) {
    const hint =
      err instanceof TypeError && err.message === 'Failed to fetch'
        ? ' Переконайся, що сервер Laravel запущений і сторінка відкрита з того ж хоста/порту (наприклад php artisan serve).'
        : ''
    message.value = `Не вдалося зберегти.${hint} ${err instanceof Error ? err.message : ''}`.trim()
    messageIsError.value = true
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <form class="order-form" @submit.prevent="submit">
    <div class="order-form__title-row">
      <h2 class="order-form__title">Нове замовлення</h2>
      <div class="order-form__switch-row">
        <span class="order-form__switch-text">Зони на карті</span>
        <button
          type="button"
          role="switch"
          :aria-checked="showSavedZones"
          class="order-form__switch"
          :class="{ 'order-form__switch--on': showSavedZones }"
          @click="toggleShowSavedZones"
        >
          <span class="order-form__switch-knob" />
        </button>
      </div>
    </div>

    <div class="order-form__grid">
      <label class="order-form__field">
        <span class="order-form__label">Назва авто</span>
        <input
          v-model="carName"
          class="order-form__input"
          type="text"
          name="car_name"
          autocomplete="off"
          required
        />
      </label>

      <label class="order-form__field">
        <span class="order-form__label">Номер авто</span>
        <input
          v-model="carNumber"
          class="order-form__input"
          type="text"
          name="car_number"
          autocomplete="off"
          required
        />
      </label>

      <label class="order-form__field">
        <span class="order-form__label">Вага авто (т)</span>
        <input
          v-model="carWeight"
          class="order-form__input"
          type="number"
          name="car_weight"
          min="0"
          step="0.01"
        />
      </label>

      <div class="order-form__field order-form__field--wide">
        <span class="order-form__label order-form__label--caps">Локація авто</span>
        <div class="order-form__location">
          <span class="order-form__pin" aria-hidden="true">
            <svg class="order-form__pin-svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
            </svg>
          </span>
          <input
            v-model="carLocation"
            type="text"
            class="order-form__location-input"
            name="car_location"
            placeholder="Київ, вул. Хрещатик, 1"
            autocomplete="street-address"
            required
            @keydown.enter.prevent="onLocationFindClick"
          />
          <button type="button" class="order-form__btn-find" @click="onLocationFindClick">
            Знайти
          </button>
        </div>
      </div>

      <div class="order-form__field order-form__field--wide">
        <span class="order-form__label order-form__label--caps">Адреса доставки</span>
        <div class="order-form__location">
          <span class="order-form__pin" aria-hidden="true">
            <svg class="order-form__pin-svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
            </svg>
          </span>
          <input
            v-model="deliveryAddress"
            type="text"
            class="order-form__location-input"
            name="delivery_address"
            placeholder="Київ, вул. Хрещатик, 1"
            autocomplete="street-address"
            required
            @keydown.enter.prevent="onDeliveryAddressFindClick"
          />
          <button type="button" class="order-form__btn-find" @click="onDeliveryAddressFindClick">
            Знайти
          </button>
        </div>
      </div>

      <label class="order-form__field">
        <span class="order-form__label">Дата завантаження</span>
        <input
          v-model="loadingDate"
          class="order-form__input"
          type="datetime-local"
          name="loading_date"
        />
      </label>
    </div>

    <div class="order-form__actions">
      <button
        class="order-form__submit"
        type="submit"
        :disabled="submitting"
      >
        {{ submitting ? 'Збереження…' : 'Додати' }}
      </button>
    </div>

    <p
      v-if="message"
      class="order-form__message"
      :class="{ 'order-form__message--error': messageIsError }"
      role="status"
    >
      {{ message }}
    </p>
  </form>
</template>

<style scoped>
.order-form {
  --of-primary: #0066ff;
  --of-primary-hover: #0052cc;
  --of-border: #e2e8f0;
  --of-text: #0f172a;
  --of-muted: #64748b;
  --of-bg-soft: #f8fafc;

  padding: 16px 18px;
  background: #fff;
  border-radius: 1rem;
  border: 1px solid rgb(226 232 240 / 0.9);
  box-shadow:
    0 4px 6px -1px rgb(15 23 42 / 0.06),
    0 2px 4px -2px rgb(15 23 42 / 0.04);
  font-family: ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
  color: var(--of-text);
}

.order-form__title-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 14px;
  flex-wrap: wrap;
}

.order-form__title {
  margin: 0;
  font-size: 16px;
  font-weight: 700;
  color: #0f172a;
}

.order-form__switch-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-shrink: 0;
}

.order-form__switch-text {
  font-size: 0.8125rem;
  font-weight: 500;
  color: #334155;
}

.order-form__switch {
  position: relative;
  width: 2.75rem;
  height: 1.75rem;
  flex-shrink: 0;
  border: none;
  border-radius: 999px;
  background: #cbd5e1;
  cursor: pointer;
  padding: 0;
  transition: background 0.15s;
}

.order-form__switch--on {
  background: var(--of-primary);
}

.order-form__switch-knob {
  position: absolute;
  top: 0.25rem;
  left: 0.25rem;
  width: 1.25rem;
  height: 1.25rem;
  border-radius: 50%;
  background: #fff;
  box-shadow: 0 1px 2px rgb(0 0 0 / 0.15);
  transition: transform 0.15s;
}

.order-form__switch--on .order-form__switch-knob {
  transform: translateX(1.25rem);
}

.order-form__grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 12px 16px;
}

.order-form__field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.order-form__field--wide {
  grid-column: 1 / -1;
}

.order-form__label {
  font-size: 13px;
  font-weight: 600;
  color: #334155;
}

.order-form__label--caps {
  font-size: 0.6875rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--of-muted);
}

.order-form__location {
  display: flex;
  align-items: stretch;
  overflow: hidden;
  border-radius: 0.75rem;
  border: 1px solid var(--of-border);
  background: #fff;
  box-shadow: 0 1px 2px rgb(15 23 42 / 0.05);
}

.order-form__location:focus-within {
  border-color: rgb(0 102 255 / 0.45);
  box-shadow: 0 0 0 3px rgb(0 102 255 / 0.15);
}

.order-form__pin {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 0.75rem;
  background: var(--of-bg-soft);
  border-right: 1px solid #f1f5f9;
  color: #94a3b8;
  flex-shrink: 0;
}

.order-form__pin-svg {
  width: 1.25rem;
  height: 1.25rem;
}

.order-form__location-input {
  flex: 1;
  min-width: 0;
  border: none;
  padding: 0.625rem 0.75rem;
  font-size: 0.875rem;
  background: transparent;
  color: var(--of-text);
}

.order-form__location-input::placeholder {
  color: #94a3b8;
}

.order-form__location-input:focus {
  outline: none;
}

.order-form__btn-find {
  flex-shrink: 0;
  padding: 0 1rem;
  border: none;
  background: var(--of-primary);
  color: #fff;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s ease;
}

.order-form__btn-find:hover {
  background: var(--of-primary-hover);
}

.order-form__input {
  box-sizing: border-box;
  width: 100%;
  padding: 10px 12px;
  font-size: 14px;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  background: #f8fafc;
  color: #0f172a;
}

.order-form__input:focus {
  outline: none;
  border-color: #94a3b8;
  background: #fff;
}

.order-form__actions {
  margin-top: 16px;
}

.order-form__submit {
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

.order-form__submit:hover:not(:disabled) {
  background: #1e293b;
}

.order-form__submit:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.order-form__message {
  margin: 12px 0 0;
  font-size: 14px;
  color: #166534;
}

.order-form__message--error {
  color: #991b1b;
}
</style>
