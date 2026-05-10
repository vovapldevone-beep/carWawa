<script setup>
import { ref } from 'vue'

const emit = defineEmits(['created'])

const carName = ref('')
const carNumber = ref('')
const carWeight = ref('')
const carLocation = ref('')
const loadingDate = ref('')

const submitting = ref(false)
const message = ref('')
const messageIsError = ref(false)

const resetForm = () => {
  carName.value = ''
  carNumber.value = ''
  carWeight.value = ''
  carLocation.value = ''
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
    }

    const w = String(carWeight.value ?? '').trim()
    if (w !== '') {
      const n = Number(w)
      payload.car_weight = Number.isFinite(n) ? n : null
    }

    if (loadingDate.value) {
      // Laravel / MySQL надійніше приймають пробіл замість "T" з datetime-local
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
    <h2 class="order-form__title">Нове замовлення</h2>

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

      <label class="order-form__field order-form__field--wide">
        <span class="order-form__label">Локація авто</span>
        <input
          v-model="carLocation"
          class="order-form__input"
          type="text"
          name="car_location"
          autocomplete="off"
          required
        />
      </label>

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
  padding: 16px 18px;
  background: #fff;
  border-radius: 1rem;
  border: 1px solid rgb(226 232 240 / 0.9);
  box-shadow:
    0 4px 6px -1px rgb(15 23 42 / 0.06),
    0 2px 4px -2px rgb(15 23 42 / 0.04);
}

.order-form__title {
  margin: 0 0 14px;
  font-size: 16px;
  font-weight: 700;
  color: #0f172a;
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
